<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Repositories\TripRepositoryInterface;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Get the home page content.
     *
     * @param TripRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function index(TripRepositoryInterface $repo): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json(['data' => $repo->findByIsPublished(true, $user ? $user->id : null)->toArray()]);
    }

    public function delete(int $id, Trip $trip_model, Schedule $schedule_model, ScheduleImage $image_model)
    {
        $trip = $trip_model->find($id);
        if (! $trip) {
            throw new \Exception('無此 trip', 1);
        }

        $schedule_ids = $schedule_model->where('trip_id', $id)->get()->pluck('id')->toArray();

        $image_model->whereIn('schedule_id', $schedule_ids)->delete();
        $schedule_model->where('trip_id', $id)->delete();
        $trip_model->where('id', $id)->delete();
    }
}
