<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\LikeRepositoryInterface;
use BlockRepositoryInterface;
use Carbon\Carbon;

class ReactionController extends Controller
{
    public function getLikeUsers(int $trip_id, LikeRepositoryInterface $repo, BlockRepositoryInterface $block_repo)
    {
        $user = auth('api')->user();
        $user_id = $user ? $user->id : null;

        $block_user_ids = [];

        if ($user) {
            $block_user_ids = $block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray();
        }

        try {
            $like_users = $repo->findByTripId($trip_id);
            $users = $like_users->transform(function (User $user) use ($block_user_ids) {
                if (in_array($user->id, $block_user_ids)) {
                    return;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ];
            })
                ->reject(function ($user) {
                    return ! $user;
                })
                ->values()
                ->toArray();

            return response()->json([
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getComments(int $trip_id, CommentRepositoryInterface $repo, BlockRepositoryInterface $block_repo)
    {
        $user = auth('api')->user();
        $user_id = $user ? $user->id : null;

        $block_user_ids = [];

        if ($user) {
            $block_user_ids = $block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray();
        }

        try {
            $comments = $repo->findByTripId($trip_id)
                ->map(function (Comment $comment) use ($user_id, $block_user_ids) {
                    $user = $comment->user;

                    if (in_array($user->id, $block_user_ids)) {
                        return;
                    }

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                        ],
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'comment_by_me' => $user->id === $user_id ? true : false,
                        'duration' => Carbon::parse($comment->created_at)->diffInMinutes(Carbon::now()),
                    ];
                })
                ->reject(function ($comment) {
                    return ! $comment;
                })
                ->values()
                ->toArray();

            return response()->json([
                'data' => $comments,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
