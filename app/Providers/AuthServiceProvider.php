<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function ($user) {
            return $user->roles->where('name', 'admin')->isNotEmpty();
        });

        Gate::define('access-educator', function ($user) {
            return $user->roles->where('name', 'educator')->isNotEmpty();
        });

        Gate::define('access-student', function ($user) {
            return $user->roles->where('name', 'student')->isNotEmpty();
        });

        // Add more specific permissions as needed
        Gate::define('manage-users', function ($user) {
            return $user->roles->where('name', 'admin')->isNotEmpty();
        });

        Gate::define('manage-courses', function ($user) {
            return $user->roles->whereIn('name', ['admin', 'educator'])->isNotEmpty();
        });
    }
}
