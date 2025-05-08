<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $role = $user->roles->first();
                
                if (!$role) {
                    Auth::logout();
                    return redirect('/login')->withErrors(['email' => 'No role assigned to this account.']);
                }

                switch ($role->name) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'educator':
                        return redirect()->route('educator.dashboard');
                    case 'student':
                        return redirect()->route('student.dashboard');
                    default:
                        return redirect('/login');
                }
            }
        }

        return $next($request);
    }
}
