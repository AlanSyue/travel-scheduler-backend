<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
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
            Route::post('/email/register', 'register');
            Route::post('/apple/login', 'appleLogin');
            Route::post('/email/login', 'login');
            Route::patch('/email/reset', 'resetPassword');
        });

    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index');
        Route::post('/search', 'search');
        Route::delete('/delete/{id}', 'delete');
        Route::delete('/friend', 'deleteFriend');
    });

    Route::controller(UserController::class)
        ->prefix('user')
        ->group(function () {
            Route::middleware('auth:api')
                ->group(function () {
                    Route::patch('/', 'update');
                    Route::delete('/', 'delete');
                    Route::get('/blocks', 'getBlockUsers');
                    Route::post('/{id}/invite', 'invite');
                    Route::post('/{id}/reply', 'reply');
                    Route::get('/invites', 'getInvites');
                    Route::post('/{id}/block', 'blockUser');
                    Route::delete('/{id}/block', 'unBlockUser');
                });
            Route::get('/{id}/friends', 'getFriends');
            Route::get('/{id}', 'find');
            Route::get('/{id}/trips', 'findTrips');
        });

    Route::prefix('/trips')
        ->group(function () {
            Route::controller(TripController::class)
                ->group(function () {
                    Route::middleware('auth:api')->group(function() {
                        Route::get('/', 'index');
                        Route::post('/', 'create');
                        Route::post('/duplicate', 'duplicate');
                        Route::post('/{id}', 'createSchedules');
                        Route::patch('/{id}', 'update');
                        Route::post('/{id}/editors', 'addEditor');
                        Route::delete('/{id}/editors', 'deleteEditor');
                        Route::post('/{id}/likes', 'addLikes');
                        Route::delete('/{id}/likes', 'deleteLikes');
                        Route::post('/{id}/comments', 'addComments');
                        Route::delete('/{id}/comments/{comment_id}', 'deleteComments');
                    });

                    Route::get('/{id}', 'detail');
                });

            Route::controller(ReactionController::class)
                ->group(function () {
                    Route::get('/{id}/likes', 'getLikeUsers');
                    Route::get('/{id}/comments', 'getComments');
                });
        });

    Route::prefix('/collections')
        ->middleware('auth:api')
        ->controller(CollectionController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'switch');
        });

    Route::prefix('/videos')
        ->controller(VideoController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::middleware('auth:api')->post('/', 'create');
        });
});
