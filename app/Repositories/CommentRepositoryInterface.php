<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface CommentRepositoryInterface
{
    public function findByTripId(int $trip_id): Collection;

    public function save(int $trip_id, int $user_id, string $content);

    public function delete(int $trip_id, int $user_id, int $comment_id);
}
