<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Repositories\TripRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Trip\Services\SearchTripsService;

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

        return response()->json(['data' => $repo->findByIsPublished(true, false, $user ? $user->id : null)->toArray()]);
    }

    public function search(Request $request, SearchTripsService $service): JsonResponse
    {
        $validated = $request->validate([
            'word' => 'required|string',
        ]);

        $word = $request->word;

        $trips = $service->execute($word);

        return response()->json([
            'data' => $trips->toArray(),
        ]);
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
