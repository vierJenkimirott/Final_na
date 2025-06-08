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
        $username = $request->input('username');
        Log::info('Login attempt', ['username' => $username]);

        // Validate the login data
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);
        
        // Check if username is an email (for admin) or ID (for educators/students)
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        // First attempt: try with student_id for students
        if (!$isEmail && Auth::attempt(['student_id' => $username, 'password' => $request->input('password')])) {
            Log::info('Login successful with student_id', ['user_id' => Auth::id()]);
            return $this->processSuccessfulLogin($request);
        }
        
        // Second attempt: try with educator_id for educators
        if (!$isEmail) {
            $user = User::where('educator_id', $username)->first();

            if ($user && Hash::check($request->input('password'), $user->password)) {
                Auth::login($user);
                Log::info('Login successful with educator_id', ['user_id' => Auth::id()]);
                return $this->processSuccessfulLogin($request);
            }
        }
        
        // Third attempt: try with email for admin
        if ($isEmail && Auth::attempt(['email' => $username, 'password' => $request->input('password')])) {
            Log::info('Login successful with email', ['user_id' => Auth::id()]);
            return $this->processSuccessfulLogin($request);
        }

        // If we reach here, both attempts failed
        Log::warning('Login failed', ['username' => $username, 'is_email' => $isEmail]);
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }
    
    /**
     * Process a successful login
     */
    private function processSuccessfulLogin(Request $request)
    {
        $request->session()->regenerate();
        $user = Auth::user();
        
        if ($role = $user->roles->first()) {
            // Update last login timestamp
            $user->update(['last_login' => now()]);
            return $this->redirectBasedOnRole($user);
        }

        Auth::logout();
        Log::warning('User has no role', ['user_id' => Auth::id()]);
        return back()->withErrors([
            'username' => 'No role assigned to this account.',
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
