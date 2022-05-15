<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\VideoRating;
use Illuminate\Support\Collection;

class EloquentVideoRatingRepository implements VideoRatingRepositoryInterface
{
    private $video_rating_model;

    public function __construct(VideoRating $video_rating_model)
    {
        $this->video_rating_model = $video_rating_model;
    }

    public function findByVideoId(int $video_id): Collection
    {
        return $this->video_rating_model
            ->with(['user'])
            ->where('video_id', $video_id)
            ->get();
    }

    public function find(int $video_id, int $user_id): ?VideoRating
    {
        return $this->video_rating_model->where('video_id', $video_id)->where('user_id', $user_id)->first();
    }

    public function save(int $video_id, int $user_id, int $type)
    {
        $like = $this->video_rating_model->where('video_id', $video_id)->where('user_id', $user_id)->first();

        if ($like && $like->type === $type) {
            return;
        }

        if ($like && $like->type !== $type) {
            $like->type = $type;
            $like->save();

            return;
        }

        $this->video_rating_model->video_id = $video_id;
        $this->video_rating_model->user_id = $user_id;
        $this->video_rating_model->type = $type;
        $this->video_rating_model->save();
    }

    public function delete(int $video_id, int $user_id)
    {
        $this->video_rating_model->where('video_id', $video_id)->where('user_id', $user_id)->delete();
    }
}
