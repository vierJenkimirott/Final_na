<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EducatorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Gate::allows('access-educator')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access. Educator privileges required.');
    }
}
