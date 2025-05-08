<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Role-based directives
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Blade::if('anyrole', function ($roles) {
            return auth()->check() && auth()->user()->hasAnyRole(is_array($roles) ? $roles : [$roles]);
        });

        // Permission-based directives
        Blade::if('can', function ($ability) {
            return auth()->check() && Gate::allows($ability);
        });
    }

    public function register()
    {
        //
    }
}
