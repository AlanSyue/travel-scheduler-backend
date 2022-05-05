<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Trip\Entities\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Trip\Services\GetDetailService;
use Trip\Transformer\TripsTransformer;
use Trip\Services\DuplicateTripService;
use Trip\Services\CreateSchedulesService;
use Trip\Services\UpdateSchedulesService;
use Trip\Transformer\TripDetailTransformer;
use App\Repositories\LikeRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\EditorRepositoryInterface;
use App\Repositories\CommentRepositoryInterface;

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
    public function index(Request $request, TripRepositoryInterface $repo, EditorRepositoryInterface $editor_repo, TripsTransformer $transformer): JsonResponse
    {
        $user_id = $request->user()->id;

        $own_trips = $repo->findByUserId($user_id);

        $edit_trip_ids = $editor_repo->findByUserId($user_id)->pluck('trip_id')->toArray();

        $edit_trips = $repo->findMany($edit_trip_ids, $user_id, false);

        $trips = $own_trips->concat($edit_trips);

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
            $request->end_date,
            0,
            0,
            collect()
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
        $user = auth('api')->user();

        try {
            $trip_detail = $service->execute($trip_id, $user ? $user->id : null, $day);

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
            'is_private' => 'required|boolean',
            'is_publish' => 'required|boolean',
            'schedules' => 'required|array',
        ]);

        try {
            $service->execute($trip_id, $user_id, $request->schedules, $request->is_private, $request->is_publish);

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

        $user = auth('api')->user();

        try {
            $comment = $comment_repo->save($trip_id, $user->id, $request->content);

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                    ],
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'comment_by_me' => true,
                    'duration' => Carbon::parse($comment->created_at)->diffInMinutes(Carbon::now()),
                ],
            ]);
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

    public function addEditor(int $trip_id, Request $request, TripRepositoryInterface $trip_reo, EditorRepositoryInterface $editor_repo, UserRepositoryInterface $user_repo)
    {
        $editor_id = $request->editor_user_id;

        $trip = $trip_reo->find($trip_id);

        if (! $trip) {
            throw new Exception('Can not find this trip', 1);
        }

        if ($trip->getUserId() !== auth('api')->user()->id) {
            throw new Exception('This is not your trip', 1);
        }

        $editors = $editor_repo->findByTripId($trip_id);

        $is_editor = $editors->filter(function($editor) use ($editor_id) {
            return $editor->user_id === $editor_id;
        })->isNotEmpty();

        if ($is_editor) {
            throw new Exception('You have been the editor', 1);
        }

        $editor_repo->save($editor_id, $trip_id);

        $user = $user_repo->find($editor_id);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
            ],
        ]);
    }

    public function deleteEditor(int $trip_id, Request $request, TripRepositoryInterface $trip_reo, EditorRepositoryInterface $editor_repo)
    {
        $editor_id = $request->editor_user_id;

        $trip = $trip_reo->find($trip_id);

        if (! $trip) {
            throw new Exception('Can not find this trip', 1);
        }

        if ($trip->getUserId() !== auth('api')->user()->id) {
            throw new Exception('This is not your trip', 1);
        }

        $editors = $editor_repo->findByTripId($trip_id);

        $is_editor = $editors->filter(function($editor) use ($editor_id) {
            return $editor->user_id === $editor_id;
        })->isNotEmpty();

        if ($is_editor) {
            $editor_repo->delete($editor_id, $trip_id);
        }

        return response()->json();
    }
}
