<?php

namespace App\Providers;

use App\Repositories\CollectionRepositoryInterface;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\EloquentCollectionRepository;
use App\Repositories\EloquentCommentRepository;
use App\Repositories\EloquentFriendRepository;
use App\Repositories\EloquentLikeRepository;
use App\Repositories\EloquentScheduleRepository;
use App\Repositories\EloquentTripRepository;
use App\Repositories\EloquentVideoRepository;
use App\Repositories\FriendRepositoryInterface;
use App\Repositories\LikeRepositoryInterface;
use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use App\Repositories\VideoRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TripRepositoryInterface::class, EloquentTripRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, EloquentScheduleRepository::class);
        $this->app->bind(CollectionRepositoryInterface::class, EloquentCollectionRepository::class);
        $this->app->bind(VideoRepositoryInterface::class, EloquentVideoRepository::class);
        $this->app->bind(LikeRepositoryInterface::class, EloquentLikeRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(FriendRepositoryInterface::class, EloquentFriendRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
