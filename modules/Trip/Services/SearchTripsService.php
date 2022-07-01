<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Illuminate\Support\Collection;

class SearchTripsService
{
    private $trip_repo;

    private $schedule_repo;

    public function __construct(TripRepositoryInterface $trip_repo, ScheduleRepositoryInterface $schedule_repo)
    {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
    }

    public function execute(string $word): Collection
    {
        $schedules = $this->schedule_repo->searchByName($word);
        $trip_ids = $schedules->pluck('trip_id')->unique()->toArray();

        return $this->trip_repo->findMany($trip_ids);
    }
}
