<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\BlockRepositoryInterface;
use App\Repositories\FriendRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function find(
        int $target_user_id,
        UserRepositoryInterface $repo,
        FriendRepositoryInterface $friend_repo,
        TripRepositoryInterface $trip_repo,
        BlockRepositoryInterface $block_repo
    ) {
        $target_user = $repo->find($target_user_id);

        if (! $target_user) {
            return response()->json([
                'data' => [],
            ]);
        }

        /** @var User $user */
        $user = auth('api')->user();

        $friend = $user ? $friend_repo->findFriend($user->id, $target_user_id) : null;

        if ($user && $block_repo->find($target_user_id, $user->id)) {
            throw new Exception('You can not access this page', 1);
        }

        return response()->json([
            'data' => [
                'id' => $target_user->id,
                'name' => $target_user->name,
                'image_url' => $target_user->image_name ? env('AWS_URL') . $target_user->image_name : '',
                'is_friend' => $user && $friend && $friend->is_active ? true : false,
                'is_invite' => $user && $friend && ! $friend->is_active ? true : false,
                'friends_count' => $target_user->friends->reject(function ($friend) { return ! $friend->is_active; })->count(),
                'trip_count' => $trip_repo->findByIsPublished(true, false, false, null, $target_user_id)->count(),
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
            if ($user->image_name) {
                Storage::delete($user->image_name);
            }

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

        if ($repo->findFriend($user_id, $friend_id)) {
            throw new Exception('You have already invite this friend', 1);
        }

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

    public function getFriends(
        int $user_id,
        FriendRepositoryInterface $repo,
        UserRepositoryInterface $user_repo,
        BlockRepositoryInterface $block_repo
    ) {
        $friend_user_ids = $repo->findMany($user_id, true)->pluck('friend_user_id')->toArray();
        $block_user_ids = $block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray();
        $friends = ($user_repo->findMany($friend_user_ids))
            ->map(function ($user) use ($block_user_ids) {
                if (in_array($user->id, $block_user_ids)) {
                    return;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })
            ->reject(function ($friend) {
                return ! $friend;
            })
            ->values()
            ->toArray();

        return response()->json([
            'data' => $friends,
        ]);
    }

    public function getInvites(FriendRepositoryInterface $repo, UserRepositoryInterface $user_repo, BlockRepositoryInterface $block_repo)
    {
        $user_id = auth('api')->user()->id;
        $invite_user_ids = $repo->findFriends($user_id, false)->pluck('user_id')->toArray();
        $block_user_ids = $block_repo($user_id)->pluck('block_user_id')->toArray();

        $friends = ($user_repo->findMany($invite_user_ids))
            ->map(function ($user) use ($block_user_ids) {
                if (in_array($user->id, $block_user_ids)) {
                    return;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })
            ->reject(function ($friend) {
                return ! $friend;
            })
            ->values()
            ->toArray();

        return response()->json([
            'data' => $friends,
        ]);
    }

    public function findTrips(int $target_user_id, TripRepositoryInterface $repo, FriendRepositoryInterface $friend_repo)
    {
        $user = auth('api')->user();

        $friend = $user ? $friend_repo->findFriend($user->id, $target_user_id) : null;

        $filter_is_active = $user && ($friend && $friend->is_active || $user->id === $target_user_id) ? false : true;

        return response()->json(['data' => $repo->findByIsPublished(true, $filter_is_active, false, $user ? $user->id : null, $target_user_id)->toArray()]);
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

    public function blockUser(int $block_user_id, BlockRepositoryInterface $repo)
    {
        $user_id = auth('api')->user()->id;
        if ($repo->find($user_id, $block_user_id)) {
            throw new Exception('You have been already block this user', 1);
        }

        $repo->save($user_id, $block_user_id);

        return response()->json();
    }

    public function unBlockUser(int $block_user_id, BlockRepositoryInterface $repo)
    {
        $user_id = auth('api')->user()->id;

        if (! $repo->find($user_id, $block_user_id)) {
            throw new Exception('You have not block this user', 1);
        }

        $repo->delete($user_id, $block_user_id);

        return response()->json();
    }
}
