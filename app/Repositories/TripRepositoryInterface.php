<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use Trip\Entities\Trip;

interface TripRepositoryInterface
{
    public function findByUserId(int $user_id): Collection;

    public function insertGetId(Trip $trip): int;
}
