<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip;
use App\Repositories\TripRepositoryInterface;
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

        return response()->json(['data' => $repo->findByIsPublished(true, true, false, $user ? $user->id : null)->toArray()]);
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

    public function deleteFriend(Request $request, Friend $friend_model)
    {
        $user_id = $request->user_id;
        $friend_id = $request->friend_id;

        $friend_model->where('user_id', $user_id)
            ->where('friend_user_id', $friend_id)
            ->delete();

        $friend_model->where('user_id', $friend_id)
            ->where('friend_user_id', $user_id)
            ->delete();
    }
}
