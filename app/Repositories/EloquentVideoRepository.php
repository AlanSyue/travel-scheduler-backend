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

    public function findMany(): Collection
    {
        return $this->video_model
            ->with(['user'])
            ->get()
            ->map(function($video) {
                return new Video($video->user, $video->name);
            });
    }

    public function create(int $user_id, string $video_name)
    {
        $this->video_model->user_id = $user_id;
        $this->video_model->name = $video_name;
        $this->video_model->save();
    }
}
