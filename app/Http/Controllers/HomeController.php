<?php

namespace App\Http\Controllers;

use App\Repositories\TripRepositoryInterface;

class HomeController extends Controller
{
    public function index(TripRepositoryInterface $repo)
    {
        return response()->json(['data' => $repo->findByIsPublished(true)->toArray()]);
    }
}
