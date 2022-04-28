<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\FriendRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function find(int $user_id, UserRepositoryInterface $repo)
    {
        $user = $repo->find($user_id);

        if (! $user) {
            return response()->json([
                'data' => [],
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|nullable',
            'image' => 'string|nullable',
        ]);

        /** @var User $user */
        $user = auth('api')->user();

        if ($request->name) {
            $user->name = $request->name;
        }

        if ($request->image) {
            $image_name = $user->id . Str::random(10) . time();
            $image = base64_decode($request->image);
            Storage::delete($user->image_name);
            $response = Storage::disk('s3')->put($image_name, $image, [
                'visibility' => 'public',
            ]);

            if ($response) {
                $user->image_name = $image_name;
            }
        }

        $user->save();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
            ],
        ]);
    }

    public function invite(int $friend_id, FriendRepositoryInterface $repo)
    {
        $user_id = auth('api')->user()->id;
        $repo->save($user_id, $friend_id, false);

        return response()->json([]);
    }

    public function reply(int $friend_id, Request $request, FriendRepositoryInterface $repo)
    {
        $is_accept = $request->accept;
        $user_id = auth('api')->user()->id;

        if ($is_accept) {
            $repo->save($user_id, $friend_id, true);
            $repo->update($friend_id, $user_id, true);
        } else {
            $repo->delete($friend_id, $user_id);
        }

        return response()->json([]);
    }

    public function getFriends(FriendRepositoryInterface $repo, UserRepositoryInterface $user_repo)
    {
        $user_id = auth('api')->user()->id;
        $friend_user_ids = $repo->findMany($user_id, true)->pluck('friend_user_id')->toArray();
        $friends = ($user_repo->findMany($friend_user_ids))
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })
            ->toArray();

        return response()->json([
            'data' => $friends,
        ]);
    }

    public function getInvites(FriendRepositoryInterface $repo, UserRepositoryInterface $user_repo)
    {
        $user_id = auth('api')->user()->id;
        $invite_user_ids = $repo->findFriends($user_id, false)->pluck('user_id')->toArray();
        $friends = ($user_repo->findMany($invite_user_ids))
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })
            ->toArray();

        return response()->json([
            'data' => $friends,
        ]);
    }

    public function delete()
    {
        /** @var User $user */
        $user = auth('api')->user();
        $user_id = $user->id;
        $trip_ids = Trip::where('user_id', $user_id)->get()->pluck('id')->toArray();
        $schedule_ids = Schedule::whereIn('trip_id', $trip_ids)->get()->pluck('id')->toArray();

        ScheduleImage::whereIn('schedule_id', $schedule_ids)->delete();
        Comment::where('user_id', $user_id)->delete();
        Collection::where('user_id', $user_id)->delete();
        Like::where('user_id', $user_id)->delete();
        Schedule::whereIn('trip_id', $trip_ids)->delete();
        Trip::where('user_id', $user_id)->delete();

        $user->delete();
    }
}
