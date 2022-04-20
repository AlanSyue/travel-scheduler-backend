<?php

namespace App\Providers;

use App\Repositories\CollectionRepositoryInterface;
use App\Repositories\EloquentCollectionRepository;
use App\Repositories\EloquentScheduleRepository;
use App\Repositories\EloquentTripRepository;
use App\Repositories\ScheduleRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
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
