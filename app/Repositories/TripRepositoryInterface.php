<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use Trip\Entities\Trip;

interface TripRepositoryInterface
{
    public function findByUserId(int $user_id): Collection;

    public function insertGetId(Trip $trip): int;

    public function find(int $trip_id): ?Trip;

    public function update(int $trip_id, array $update_data);

    public function findByIsPublished(bool $is_published): Collection;
}
