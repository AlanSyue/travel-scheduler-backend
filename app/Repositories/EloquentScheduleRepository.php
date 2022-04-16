<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule as ModelsSchedule;
use Illuminate\Support\Collection;
use Trip\Entities\Schedule;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * The schedule model instance.
     *
     * @var ModelsSchedule
     */
    private $schedule_model;

    /**
     * Create a new repository instance.
     *
     * @param ModelsSchedule $schedule_model
     */
    public function __construct(ModelsSchedule $schedule_model)
    {
        $this->schedule_model = $schedule_model;
    }

    /**
     * Delete the schedule by trip ID.
     *
     * @param int $trip_id
     * @param int $day
     *
     * @return void
     */
    public function deleteByTripId(int $trip_id, int $day)
    {
        $this->schedule_model->where('trip_id', $trip_id)->where('day', $day)->delete();
    }

    /**
     * Insert the schedules.
     *
     * @param array $schedules
     *
     * @return void
     */
    public function insert(array $schedules)
    {
        $schedules = collect($schedules)->map(function ($schedule) {
            $schedule['created_at'] = now();
            $schedule['updated_at'] = now();

            return $schedule;
        })->toArray();

        $this->schedule_model->insert($schedules);
    }

    /**
     * Find the schedules by trip ID.
     *
     * @param int      $trip_id
     * @param null|int $day
     *
     * @return Collection
     */
    public function findByTripId(int $trip_id, ?int $day): Collection
    {
        return $this->schedule_model->where('trip_id', $trip_id)
            ->when($day, function ($query, $day) {
                return $query->where('day', $day);
            })
            ->orderBy('day')
            ->get()
            ->groupBy('day')
            ->map(function ($schedules) {
                return $schedules->transform(function ($schedule) {
                    return new Schedule(
                        $schedule->id,
                        $schedule->trip_id,
                        $schedule->type,
                        $schedule->day,
                        $schedule->name,
                        $schedule->address,
                        $schedule->start_time,
                        $schedule->duration,
                        $schedule->traffic_time,
                        $schedule->lat,
                        $schedule->long
                    );
                });
            });
    }
}
