<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface EditorRepositoryInterface
{
    public function save(int $user_id, int $trip_id);

    public function delete(int $user_id, int $trip_id);

    public function findByTripId(int $trip_id): Collection;

    public function findByUserId(int $user_id): Collection;
}
