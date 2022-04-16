<?php

declare(strict_types=1);

namespace Trip\Transformer;

use Trip\Entities\Schedule;

class SchedulesTransformer
{
    public function transform(array $schedulues, int $trip_id, int $day): array
    {
        return collect($schedulues)->map(function (array $schedule) use ($trip_id, $day) {
            return (new Schedule(
                null,
                $trip_id,
                $schedule['type'],
                $day,
                $schedule['name'],
                $schedule['address'],
                $schedule['start_time'],
                $schedule['duration'],
                $schedule['traffic_time'],
                $schedule['position']['lat'],
                $schedule['position']['long']
            ))->toArray();
        })->toArray();
    }
}
