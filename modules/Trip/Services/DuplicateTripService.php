<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Trip\Entities\Schedule;
use Trip\Entities\Trip;

class DuplicateTripService
{
    private $trip_repo;

    private $schedule_repo;

    private $user_repo;

    public function __construct(
        TripRepositoryInterface $trip_repo,
        ScheduleRepositoryInterface $schedule_repo,
        UserRepositoryInterface $user_repo
    ) {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
        $this->user_repo = $user_repo;
    }

    public function execute(string $title, string $start_date, string $end_date, int $target_trip_id, int $user_id): Trip
    {
        $trip = new Trip(
            null,
            $this->user_repo->find($user_id),
            $title,
            $start_date,
            $end_date,
            1,
            0,
            collect()
        );

        $trip_id = $this->trip_repo->insertGetId($trip);

        $schedules = $this->schedule_repo->findByTripId($target_trip_id, null);

        $schedules = $schedules->flatten()
            ->map(function (Schedule $schedule) use ($trip_id) {
                $schedule->setTripId($trip_id);

                return $schedule->toArray();
            })
            ->toArray();

        $this->schedule_repo->insert($schedules);

        $trip->setId($trip_id);

        return $trip;
    }
}
