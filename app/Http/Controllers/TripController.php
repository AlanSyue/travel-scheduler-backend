<?php

namespace App\Http\Controllers;

use App\Repositories\CommentRepositoryInterface;
use App\Repositories\LikeRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Trip\Entities\Trip;
use Trip\Services\CreateSchedulesService;
use Trip\Services\DuplicateTripService;
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
    public function create(
        Request $request,
        TripRepositoryInterface $trip_repo,
        UserRepositoryInterface $user_repo
    ): JsonResponse {
        $user_id = $request->user()->id;

        $validated = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $trip = new Trip(
            null,
            $user_repo->find($user_id),
            $request->title,
            $request->start_date,
            $request->end_date
        );

        $trip_id = $trip_repo->insertGetId($trip);

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

        try {
            $trip = $service->execute($trip_id, $user_id, $request->day, $request->schedules);

            return response()->json([
                'data' => $trip->toDetailArray(),
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

    public function duplicate(Request $request, DuplicateTripService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'trip_id' => 'required|int',
        ]);

        try {
            $trip = $service->execute(
                $request->title,
                $request->start_date,
                $request->end_date,
                $request->trip_id,
                auth('api')->user()->id
            );

            return response()->json([
                'data' => [
                    'id' => $trip->getId(),
                    'title' => $trip->getTitle(),
                    'start_date' => $trip->getStartAt()->format('Y-m-d'),
                    'end_date' => $trip->getEndAt()->format('Y-m-d'),
                    'days' => $trip->getDays(),
                ],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addLikes(int $trip_id, LikeRepositoryInterface $like_repo)
    {
        try {
            $like_repo->save($trip_id, auth('api')->user()->id);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteLikes(int $trip_id, LikeRepositoryInterface $like_repo)
    {
        try {
            $like_repo->delete($trip_id, auth('api')->user()->id);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addComments(int $trip_id, Request $request, CommentRepositoryInterface $comment_repo)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $comment_repo->save($trip_id, auth('api')->user()->id, $request->content);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteComments(int $trip_id, int $comment_id, CommentRepositoryInterface $comment_repo)
    {
        try {
            $comment_repo->delete($trip_id, auth('api')->user()->id, $comment_id);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
