<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\BlockRepositoryInterface;
use App\Repositories\EditorRepositoryInterface;
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
     * The editor repository instance.
     *
     * @var EditorRepositoryInterface
     */
    private $editor_repo;

    private $block_repo;

    /**
     * Create a new service instance.
     *
     * @param TripRepositoryInterface     $trip_repo
     * @param ScheduleRepositoryInterface $schedule_repo
     * @param EditorRepositoryInterface   $editor_repo
     * @param BlockRepositoryInterface    $block_repo
     */
    public function __construct(
        TripRepositoryInterface $trip_repo,
        ScheduleRepositoryInterface $schedule_repo,
        EditorRepositoryInterface $editor_repo,
        BlockRepositoryInterface $block_repo
    ) {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
        $this->editor_repo = $editor_repo;
        $this->block_repo = $block_repo;
    }

    /**
     * Execute the service.
     *
     * @param int      $trip_id
     * @param null|int $user_id
     * @param null|int $day
     *
     * @return Trip
     */
    public function execute(int $trip_id, ?int $user_id, ?int $day): Trip
    {
        /** @var Trip $trip */
        $trip = $this->trip_repo->find($trip_id, $user_id);

        if (! $trip) {
            throw new Exception('找不到這個 trip', 1);
        }

        $owner_id = $trip->getUserId();

        if ($user_id && $this->block_repo->find($owner_id, $user_id)) {
            throw new Exception('You can not access this page', 1);
        }

        $editor_ids = collect($trip->getEditors())->pluck('id')->toArray();

        if (! $trip->getIsPublished() && $user_id !== $owner_id && ! in_array($user_id, $editor_ids)) {
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
