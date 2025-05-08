<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Gate::allows('access-admin')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access. Admin privileges required.');
    }
}
