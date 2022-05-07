<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Events\UpdateSchedules;
use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Trip\Entities\Schedule;
use Trip\Entities\Trip;
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
     * @return Trip
     */
    public function execute(int $trip_id, int $user_id, int $day, array $schedules): Trip
    {
        $trip = $this->trip_repo->find($trip_id);

        $editor_ids = collect($trip->getEditors())->pluck('id')->toArray();

        if ($user_id !== $trip->getUserId() && ! in_array($user_id, $editor_ids)) {
            throw new Exception('不可刪除別人的 trip', 1);
        }

        $duration_days = $trip->getDays();

        foreach (range(1, $duration_days) as $duration_day) {
            if ($duration_day != $day && $this->schedule_repo->isEmptyByDay($trip_id, $duration_day)) {
                throw new Exception("第{$duration_day}天行程是空", 1);
            }
        }

        $trip_id = $trip->getId();

        $schedules = $this->transformer->transform($schedules, $trip_id, $day);

        DB::beginTransaction();

        try {
            $this->schedule_repo->deleteByTripId($trip_id, $day);
            $this->schedule_repo->insert($schedules);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        DB::commit();

        $schedules = ($this->schedule_repo->findByTripId($trip->getId(), $day))
            ->map(function (Collection $schedules) {
                return $schedules->map(function (Schedule $schedule) {
                    return $schedule->toDetailArray();
                })->toArray();
            })
            ->values()
            ->toArray();

        event(new UpdateSchedules($trip_id, collect($schedules)->isNotEmpty() ? $schedules[0] : []));

        $trip->setSchedules($schedules);

        return $trip;
    }
}
