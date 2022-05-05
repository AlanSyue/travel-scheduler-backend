<?php

declare(strict_types=1);

namespace App\Repositories;

use Video\Entities\Video;
use Illuminate\Support\Collection;
use App\Models\Video as ModelsVideo;

class EloquentVideoRepository implements VideoRepositoryInterface
{
    private $video_model;

    public function __construct(ModelsVideo $video_model)
    {
        $this->video_model = $video_model;
    }

    public function findMany(Collection $friends, array $block_user_ids = []): Collection
    {
        return $this->video_model
            ->with(['user'])
            ->whereNotIn('user_id', $block_user_ids)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($video) use ($friends) {
                return new Video($video->user, $video->name, $video->location, $video->created_at, $friends);
            });
    }

    public function create(int $user_id, mixed $video_name, string $location)
    {
        $this->video_model->user_id = $user_id;
        $this->video_model->name = $video_name;
        $this->video_model->location = $location;
        $this->video_model->save();
    }
}
