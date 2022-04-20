<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\TripRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Trip\Entities\Trip;
use Trip\Services\CreateSchedulesService;
use Trip\Services\GetDetailService;
use Trip\Services\UpdateSchedulesService;
use Trip\Transformer\TripDetailTransformer;
use Trip\Transformer\TripsTransformer;

class TripController extends Controller
{
    /**
     * Get the trips data.
     *
     * @param Request                 $request
     * @param TripRepositoryInterface $repo
     * @param TripsTransformer        $transformer
     *
     * @return JsonResponse
     */
    public function index(Request $request, TripRepositoryInterface $repo, TripsTransformer $transformer): JsonResponse
    {
        $user_id = $request->user()->id;

        $trips = $repo->findByUserId($user_id);

        return response()->json($transformer->transform($trips));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create(Request $request, TripRepositoryInterface $repo): JsonResponse
    {
        $user_id = $request->user()->id;

        $validated = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $trip = new Trip(
            null,
            User::find($user_id),
            $request->title,
            $request->start_date,
            $request->end_date
        );

        $trip_id = $repo->insertGetId($trip);

        return response()->json([
            'data' => [
                'id' => $trip_id,
                'title' => $trip->getTitle(),
                'start_date' => $trip->getStartAt()->format('Y-m-d'),
                'end_date' => $trip->getEndAt()->format('Y-m-d'),
                'days' => $trip->getDays(),
            ],
        ]);
    }

    /**
     * Get the trip detail.
     *
     * @param int                   $trip_id
     * @param Request               $request
     * @param GetDetailService      $service
     * @param TripDetailTransformer $transformer
     *
     * @return JsonResponse
     */
    public function detail(
        int $trip_id,
        Request $request,
        GetDetailService $service,
        TripDetailTransformer $transformer
    ): JsonResponse {
        $day = $request->query('day');
        $user_id = $request->user()->id;

        try {
            $trip_detail = $service->execute($trip_id, $user_id, $day);

            return response()->json($transformer->transform($trip_detail));
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Create the schedules of the trip.
     *
     * @param int                    $trip_id
     * @param Request                $request
     * @param CreateSchedulesService $service
     *
     * @return JsonResponse
     */
    public function createSchedules(int $trip_id, Request $request, CreateSchedulesService $service): JsonResponse
    {
        $user_id = $request->user()->id;

        $validated = $request->validate([
            'day' => 'required|int',
            'schedules' => 'required|array',
        ]);

        try {
            $schedules = $service->execute($trip_id, $user_id, $request->day, $request->schedules);

            return response()->json([
                'data' => $schedules,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(int $trip_id, Request $request, UpdateSchedulesService $service)
    {
        $user_id = $request->user()->id;

        $validated = $request->validate([
            'schedules' => 'required|array',
        ]);

        try {
            $service->execute($trip_id, $user_id, $request->schedules);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
