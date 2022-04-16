<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface ScheduleRepositoryInterface
{
    public function deleteByTripId(int $trip_id, int $day);

    public function insert(array $schedules);

    public function findByTripId(int $trip_id, ?int $day): Collection;
}
