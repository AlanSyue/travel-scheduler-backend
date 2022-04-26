<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Comment;
use App\Repositories\LikeRepositoryInterface;
use App\Repositories\CommentRepositoryInterface;

class ReactionController extends Controller
{
    public function getLikeUsers(int $trip_id, LikeRepositoryInterface $repo)
    {
        try {
            $like_users = $repo->findByTripId($trip_id);
            $users = $like_users->transform(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })->toArray();

            return response()->json([
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getComments(int $trip_id, CommentRepositoryInterface $repo)
    {
        $user = auth('api')->user();
        $user_id = $user ? $user->id : null;

        try {
            $comments = $repo->findByTripId($trip_id)
                ->map(function (Comment $comment) use ($user_id) {
                    $user = $comment->user;

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                        ],
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'comment_by_me' => $user->id === $user_id ? true : false,
                        'duration' => Carbon::parse($comment->created_at)->diffInMinutes(Carbon::now())
                    ];
                })->toArray();

            return response()->json([
                'data' => $comments,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
