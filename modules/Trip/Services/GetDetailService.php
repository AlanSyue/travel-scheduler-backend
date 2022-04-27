<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;
use Trip\Entities\Schedule;
use Trip\Entities\Trip;

class GetDetailService
{
    /**
     * The trip repository instance.
     *
     * @var TripRepositoryInterface
     */
    private $trip_repo;

    /**
     * The schedule repository instance.
     *
     * @var ScheduleRepositoryInterface
     */
    private $schedule_repo;

    /**
     * Create a new service instance.
     *
     * @param TripRepositoryInterface     $trip_repo
     * @param ScheduleRepositoryInterface $schedule_repo
     */
    public function __construct(
        TripRepositoryInterface $trip_repo,
        ScheduleRepositoryInterface $schedule_repo,
    ) {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
    }

    /**
     * Execute the service.
     *
     * @param int      $trip_id
     * @param int      $user_id
     * @param null|int $day
     *
     * @return Trip
     */
    public function execute(int $trip_id, int $user_id, ?int $day): Trip
    {
        /** @var Trip $trip */
        $trip = $this->trip_repo->find($trip_id);

        if (! $trip) {
            throw new Exception('找不到這個 trip', 1);
        }

        if (! $trip->getIsPublished() && $trip->$user_id !== $trip->getUserId()) {
            throw new Exception('不是你的 trip', 1);
        }

        $schedules = ($this->schedule_repo->findByTripId($trip->getId(), $day))
            ->map(function (Collection $schedules) {
                return $schedules->map(function (Schedule $schedule) {
                    return $schedule->toDetailArray();
                })->toArray();
            })
            ->values()
            ->toArray();

        $trip->setSchedules($schedules);

        return $trip;
    }
}
