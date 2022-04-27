<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();

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
