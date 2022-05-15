<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\VideoRatingRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Video\Services\CreateVideoService;
use Video\Services\FindVideoRatingService;
use Video\Services\FindVideosService;

class VideoController extends Controller
{
    public function index(FindVideosService $service)
    {
        try {
            $videos = $service->execute(auth('api')->user());

            return response()->json([
                'data' => $videos->toArray(),
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request, CreateVideoService $service)
    {
        $user_id = auth('api')->user()->id;
        $file = $request->file;
        $location = $request->location;

        try {
            $service->execute($user_id, $file, $location);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getRatingList(int $video_id, FindVideoRatingService $service)
    {
        $user = auth('api')->user();

        try {
            $result = $service->execute($video_id, $user ? $user->id : null);

            return response()->json([
                'data' => $result,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addRating(int $video_id, Request $request, VideoRatingRepositoryInterface $repo)
    {
        $user_id = auth('api')->user()->id;
        $type = $request->type;

        try {
            $repo->save($video_id, $user_id, $type);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteRating(int $video_id, VideoRatingRepositoryInterface $repo)
    {
        $user_id = auth('api')->user()->id;

        if (! $repo->find($video_id, $user_id)) {
            throw new Exception('This is not your rating', 1);
        }

        try {
            $repo->delete($video_id, $user_id);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
