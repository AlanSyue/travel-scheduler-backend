<?php

declare(strict_types=1);

namespace App\Repositories;

use Trip\Entities\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Models\Schedule as ModelsSchedule;
use App\Models\ScheduleImage as ModelsScheduleImage;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * The schedule model instance.
     *
     * @var ModelsSchedule
     */
    private $schedule_model;

    private $schedule_image_model;

    /**
     * Create a new repository instance.
     *
     * @param ModelsSchedule $schedule_model
     * @param $schedule_image_model
     */
    public function __construct(ModelsSchedule $schedule_model, ModelsScheduleImage $schedule_image_model)
    {
        $this->schedule_model = $schedule_model;
        $this->schedule_image_model = $schedule_image_model;
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
        $schedules = collect($schedules)->map(function (array $schedule) {
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
    public function findByTripId(int $trip_id, ?int $day = null): Collection
    {
        return $this->schedule_model->where('trip_id', $trip_id)
            ->with(['images'])
            ->when($day, function ($query, $day) {
                return $query->where('day', $day);
            })
            ->orderBy('day')
            ->get()
            ->groupBy('day')
            ->map(function ($schedules) {
                return $schedules->transform(function ($schedule) {
                    $images = $schedule->images->map(function($image) {
                        return env('AWS_URL') . $image->image_name;
                    })->toArray();

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
                        $schedule->long,
                        $schedule->description,
                        $images
                    );
                });
            });
    }

    public function update(int $schedule_id, array $update_data)
    {
        $this->schedule_model->where('id', $schedule_id)
            ->update($update_data);
    }

    public function insertImages(int $schedule_id, array $images)
    {
        $this->schedule_image_model->where('schedule_id', $schedule_id)->delete();
        $this->schedule_image_model->insert($images);
    }

    public function searchByName(string $word): Collection
    {
        return $this->schedule_model->where('name', 'regexp', $word)->get();
    }

    public function isEmptyByDay(int $trip_id, int $day): bool
    {
        return $this->schedule_model->where('trip_id', $trip_id)
            ->where('day', $day)
            ->get()
            ->isEmpty();
    }
}
