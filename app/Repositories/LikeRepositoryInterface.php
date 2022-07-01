<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface LikeRepositoryInterface
{
    public function findByTripId(int $trip_id): Collection;

    public function save(int $trip_id, int $user_id);

    public function delete(int $trip_id, int $user_id);
}
