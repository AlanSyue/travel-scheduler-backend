<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface VideoRepositoryInterface
{
    public function findMany(Collection $friends): Collection;

    public function create(int $user_id, mixed $video_name, string $location);
}
