<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface ScheduleRepositoryInterface
{
    public function deleteByTripId(int $trip_id, int $day);

    public function insert(array $schedules);

    public function findByTripId(int $trip_id, ?int $day): Collection;

    public function update(int $schedule_id, array $update_data);

    public function insertImages(int $schedule_id, array $images);

    public function searchByName(string $word): Collection;

    public function isEmptyByDay(int $trip_id, int $day): bool;
}
