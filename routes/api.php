<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('/auth')
        ->controller(AuthController::class)
        ->group(function () {
            Route::post('/{driver}/register', 'register');
            Route::post('/{driver}/login', 'login');
        });

    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index');
        Route::delete('/delete/{id}', 'delete');
    });

    Route::prefix('/trips')
        ->middleware('auth:api')
        ->controller(TripController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'create');
            Route::get('/{id}', 'detail');
            Route::post('/{id}', 'createSchedules');
            Route::patch('/{id}', 'update');
        });

    Route::prefix('/collections')
        ->middleware('auth:api')
        ->controller(CollectionController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'switch');
        });
});
