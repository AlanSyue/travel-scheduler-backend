<?php

namespace App\Http\Controllers;

use App\Repositories\TripRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

}
