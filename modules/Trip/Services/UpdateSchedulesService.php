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

    public function execute(int $trip_id, int $user_id, array $schedules, bool $is_private, bool $is_publish)
    {
        $trip = $this->trip_repo->find($trip_id);

        $editor_ids = collect($trip->getEditors())->pluck('id')->toArray();

        if ($user_id !== $trip->getUserId() && ! in_array($user_id, $editor_ids)) {
            throw new Exception('不可刪除別人的 trip', 1);
        }

        collect($schedules)->each(function ($schedule) {
            $schedule_id = $schedule['id'];
            $description = $schedule['description'] ?? '';

            $this->schedule_repo->update($schedule_id, ['description' => $description]);

            $images = $schedule['images'];

            $images_data = collect($images)->map(function ($image) use ($schedule_id) {
                if (Str::contains($image, env('AWS_URL'))) {
                    return;
                }
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
            })
            ->reject(function($image_data) {
                return !$image_data;
            })
            ->values()
            ->toArray();

            if (count($images_data) > 0) {
                $this->schedule_repo->insertImages($schedule_id, $images_data);
            }
        });

        if ($is_publish) {
            $this->trip_repo->update($trip_id, ['is_published' => $is_publish, 'is_private' => $is_private]);
        }

    }
}
