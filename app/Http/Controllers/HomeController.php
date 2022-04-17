<?php

namespace App\Http\Controllers;

use App\Repositories\TripRepositoryInterface;
use Illuminate\Http\JsonResponse;

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
        return response()->json(['data' => $repo->findByIsPublished(true)->toArray()]);
    }
}
