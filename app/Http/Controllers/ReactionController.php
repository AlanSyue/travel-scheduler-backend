<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\LikeRepositoryInterface;

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
                    'image_url' => $user->image_url ?? '',
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
        try {
            $comments = $repo->findByTripId($trip_id)
                ->map(function (Comment $comment) {
                    $user = $comment->user;

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'image_url' => $user->image_url ?? '',
                        ],
                        'id' => $comment->id,
                        'content' => $comment->content,
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
