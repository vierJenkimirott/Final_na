<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function dashboard()
    {
        // Get total students count
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();
        
        // Get total educators count
        $totalEducators = User::whereHas('roles', function($q) {
            $q->where('name', 'educator');
        })->count();
        
        return view('admin.dashboard', compact('totalStudents', 'totalEducators'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function createUser()
    {
        $roles = Role::all();
        return view('admin.create_user', compact('roles'));
    }
    
    /**
     * Store a newly created user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'student_id' => 'nullable|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,id',
            'sex' => 'required|in:Male,Female',
        ]);
        
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'name' => $request->fname . ' ' . $request->lname,
            'email' => $request->email,
            'student_id' => $request->student_id,
            'password' => Hash::make($request->password),
            'sex' => $request->sex,
            'gender' => $request->sex, // For backward compatibility
        ]);
        
        // Attach role
        $user->roles()->attach($request->role);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'User created successfully');
    }
    
    /**
     * Show the list of students
     */
    public function manageStudent()
    {
        $students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->paginate(10);
        
        return view('admin.manage_student', compact('students'));
    }
    
    /**
     * Show the list of educators
     */
    public function manageEducator()
    {
        $educators = User::whereHas('roles', function($q) {
            $q->where('name', 'educator');
        })->paginate(10);
        
        return view('admin.manage_educator', compact('educators'));
    }
    
    /**
     * Show the form for editing a user
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $userRole = $user->roles->first()->id ?? null;
        
        return view('admin.edit_user', compact('user', 'roles', 'userRole'));
    }
    
    /**
     * Update the specified user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'student_id' => 'nullable|string|max:255|unique:users,student_id,' . $user->id,
            'role' => 'required|exists:roles,id',
            'sex' => 'required|in:Male,Female',
        ]);
        
        $user->update([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'name' => $request->fname . ' ' . $request->lname,
            'email' => $request->email,
            'student_id' => $request->student_id,
            'sex' => $request->sex,
            'gender' => $request->sex, // For backward compatibility
        ]);
        
        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }
        
        // Update role
        $user->roles()->sync([$request->role]);
        
        return redirect()->back()
            ->with('success', 'User updated successfully');
    }
    
    /**
     * Delete the specified user
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->back()
            ->with('success', 'User deleted successfully');
    }
}
