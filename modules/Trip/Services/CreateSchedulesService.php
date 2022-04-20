<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Exception;
use Trip\Transformer\SchedulesTransformer;

class CreateSchedulesService
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
     * The schedule transformer instance..
     *
     * @var SchedulesTransformer
     */
    private $transformer;

    /**
     * Create a new service instance.
     *
     * @param TripRepositoryInterface     $trip_repo
     * @param ScheduleRepositoryInterface $schedule_repo
     * @param SchedulesTransformer        $transformer
     */
    public function __construct(
        TripRepositoryInterface $trip_repo,
        ScheduleRepositoryInterface $schedule_repo,
        SchedulesTransformer $transformer
    ) {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
        $this->transformer = $transformer;
    }

    /**
     * Execute the service.
     *
     * @param int   $trip_id
     * @param int   $user_id
     * @param int   $day
     * @param array $schedules
     *
     * @return void
     */
    public function execute(int $trip_id, int $user_id, int $day, array $schedules)
    {
        $trip = $this->trip_repo->find($trip_id);

        if ($user_id !== $trip->getUserId()) {
            throw new Exception('不可刪除別人的 trip', 1);
        }

        $trip_id = $trip->getId();

        $schedules = $this->transformer->transform($schedules, $trip_id, $day);
        $this->schedule_repo->deleteByTripId($trip_id, $day);
        $this->schedule_repo->insert($schedules);

        return $schedules;
    }
}
