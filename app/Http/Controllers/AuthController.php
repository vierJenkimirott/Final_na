<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->input('email')]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            Log::info('Login successful', ['user_id' => Auth::id()]);
            $request->session()->regenerate();
            $user = Auth::user();
            
            if ($role = $user->roles->first()) {
                return $this->redirectBasedOnRole($user);
            }

            Auth::logout();
            Log::warning('User has no role', ['user_id' => Auth::id()]);
            return back()->withErrors([
                'email' => 'No role assigned to this account.',
            ]);
        }

        Log::warning('Login failed', ['email' => $request->input('email')]);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    protected function redirectBasedOnRole($user)
    {
        $role = $user->roles->first();
        if (!$role) {
            Auth::logout();
            return redirect('/login')->withErrors(['email' => 'No role assigned to this account.']);
        }

        switch ($role->name) {
            case 'admin':
                return redirect()->intended(route('admin.dashboard'));
            case 'educator':
                return redirect()->intended(route('educator.dashboard'));
            case 'student':
                return redirect('/student/violation');
            default:
                return redirect('/login');
        }
    }
    

}
