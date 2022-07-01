<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Comment as ModelsComment;
use Illuminate\Support\Collection;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    private $comment_model;

    public function __construct(ModelsComment $comment_model)
    {
        $this->comment_model = $comment_model;
    }

    public function findByTripId(int $trip_id): Collection
    {
        return $this->comment_model->where('trip_id', $trip_id)->get();
    }

    public function save(int $trip_id, int $user_id, string $content)
    {
        $this->comment_model->trip_id = $trip_id;
        $this->comment_model->user_id = $user_id;
        $this->comment_model->content = $content;
        $this->comment_model->save();

        return $this->comment_model;
    }

    public function delete(int $trip_id, int $user_id, int $comment_id)
    {
        $this->comment_model
            ->where('trip_id', $trip_id)
            ->where('user_id', $user_id)
            ->where('id', $comment_id)
            ->delete();
    }
}
