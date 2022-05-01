<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Video\Services\CreateVideoService;
use Video\Services\FindVideosService;

class VideoController extends Controller
{
    public function index(FindVideosService $service)
    {
        try {
            $videos = $service->execute();

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
}
