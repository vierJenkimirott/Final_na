<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if user has the specified role
        if (Gate::allows('access-' . $role)) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access. ' . ucfirst($role) . ' privileges required.');
    }
}
