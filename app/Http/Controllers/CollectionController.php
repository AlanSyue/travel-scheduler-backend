<?php

namespace App\Http\Controllers;

use Collection\Services\GetCollectionsService;
use Collection\Services\SwitchCollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request, GetCollectionsService $service)
    {
        $user_id = $request->user()->id;

        return response()->json($service->execute($user_id));
    }

    public function switch(Request $request, SwitchCollectionService $service): JsonResponse
    {
        $validated = $request->validate([
            'is_collected' => 'required|boolean',
            'trip_id' => 'required|int',
        ]);

        $user_id = $request->user()->id;

        try {
            $service->execute($user_id, $request->trip_id, $request->is_collected);

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
