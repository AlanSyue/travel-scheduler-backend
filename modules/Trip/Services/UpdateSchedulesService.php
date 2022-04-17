<?php

declare(strict_types=1);

namespace Trip\Services;

use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateSchedulesService
{
    private $trip_repo;

    private $schedule_repo;

    public function __construct(TripRepositoryInterface $trip_repo, ScheduleRepositoryInterface $schedule_repo)
    {
        $this->trip_repo = $trip_repo;
        $this->schedule_repo = $schedule_repo;
    }

    public function execute(int $trip_id, int $user_id, array $schedules)
    {
        $trip = $this->trip_repo->find($trip_id);

        if ($user_id !== $trip->getUserId()) {
            throw new Exception('不可更改別人的 trip', 1);
        }

        collect($schedules)->each(function ($schedule) {
            $schedule_id = $schedule['id'];
            $description = $schedule['description'];

            $this->schedule_repo->update($schedule_id, [
                'description' => $description,
            ]);

            $images = $schedule['images'];

            $images_data = collect($images)->map(function ($image) use ($schedule_id) {
                $image_name = Str::random(8) . time();
                $image = base64_decode($image);
                $response = Storage::disk('s3')->put($image_name, $image, [
                    'visibility' => 'public',
                ]);

                if ($response) {
                    return [
                        'schedule_id' => $schedule_id,
                        'image_name' => $image_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            })->values()->toArray();

            $this->schedule_repo->insertImages($schedule_id, $images_data);
        });
    }
}
