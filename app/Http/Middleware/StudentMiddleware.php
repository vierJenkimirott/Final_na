<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Gate::allows('access-student')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access. Student privileges required.');
    }
}
