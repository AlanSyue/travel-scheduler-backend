<?php

namespace App\Providers;

use App\Repositories\ClientRepositoryInterface;
use App\Repositories\EloquentClientRepository;
use App\Repositories\EloquentUserRepository;
use App\Repositories\UserRepositoryInterface;
use Auth\Services\EmailRegisterService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->when(EmailRegisterService::class)
            ->needs(UserRepositoryInterface::class)
            ->give(EloquentUserRepository::class);

        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);

    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }
    }
}
