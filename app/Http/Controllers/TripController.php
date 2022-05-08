<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleImage;
use App\Models\Trip as ModelsTrip;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\EditorRepositoryInterface;
use App\Repositories\LikeRepositoryInterface;
use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Carbon\Carbon;
use Exception;
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

    public function update(
        int $trip_id,
        Request $request,
        TripRepositoryInterface $repo,
        ScheduleRepositoryInterface $schedule_repo
    ) {
        $validated = $request->validate([
            'title' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
        ]);

        $trip = $repo->find($trip_id);

        if ($trip->getIsPublished()) {
            throw new Exception('已發布的行程不能修改', 1);
        }

        $user_id = auth('api')->user()->id;

        if ($user_id !== $trip->getUserId()) {
            throw new Exception('不可修改別人的 trip', 1);
        }

        $title = $request->title;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (Carbon::parse($start_date)->gte(Carbon::parse($end_date))) {
            throw new Exception('開始時間不能大於結束時間', 1);
        }

        $update_data = [];
        $is_modify_date = false;

        if ($title) {
            $update_data['title'] = $title;
        }

        if ($start_date) {
            $is_modify_date = true;
            $update_data['start_at'] = $start_date;
        }

        if ($end_date) {
            $is_modify_date = true;
            $update_data['end_at'] = $end_date;
        }

        try {
            $repo->update($trip_id, $update_data);

            $new_days = $is_modify_date
                ? (Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date))) + 1
                : $trip->getDays();

            $origin_days = $trip->getDays();

            $unused_days = array_diff(range(1, $origin_days), range(1, $new_days));

            foreach ($unused_days as $unused_day) {
                $schedule_repo->deleteByTripId($trip_id, $unused_day);
            }

            $trip = $repo->find($trip_id);

            return response()->json([
                'data' => [
                    'id' => $trip_id,
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

    public function delete(int $id, ModelsTrip $trip_model, Schedule $schedule_model, ScheduleImage $image_model)
    {
        $trip = $trip_model->find($id);
        if (! $trip) {
            throw new Exception('無此 trip', 1);
        }

        if ($trip->user_id !== auth('api')->user()->id) {
            throw new Exception('不能刪除別人的 Trip', 1);
        }

        $schedule_ids = $schedule_model->where('trip_id', $id)->get()->pluck('id')->toArray();

        $image_model->whereIn('schedule_id', $schedule_ids)->delete();
        $schedule_model->where('trip_id', $id)->delete();
        $trip_model->where('id', $id)->delete();
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
        $is_finished = $request->is_finished ?? false;

        try {
            $trip = $service->execute($trip_id, $user_id, $request->day, $request->schedules, $is_finished);

            return response()->json([
                'data' => $trip->toDetailArray(),
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateSchedules(int $trip_id, Request $request, UpdateSchedulesService $service)
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

        $is_editor = $editors->filter(function ($editor) use ($editor_id) {
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

        $is_editor = $editors->filter(function ($editor) use ($editor_id) {
            return $editor->user_id === $editor_id;
        })->isNotEmpty();

        if ($is_editor) {
            $editor_repo->delete($editor_id, $trip_id);
        }

        return response()->json();
    }
}
