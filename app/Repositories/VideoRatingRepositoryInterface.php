<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\VideoRating;
use Illuminate\Support\Collection;

interface VideoRatingRepositoryInterface
{
    public function findByVideoId(int $video_id): Collection;

    public function find(int $video_id, int $user_id): ?VideoRating;

    public function save(int $video_id, int $user_id, int $type);

    public function delete(int $video_id, int $user_id);
}
