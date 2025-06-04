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
        
        // Get students by batch
        $batch2025Students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('student_id', 'like', '202501%')->get();
        
        $batch2026Students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('student_id', 'like', '202601%')->get();
        
        // Get total educators count
        $totalEducators = User::whereHas('roles', function($q) {
            $q->where('name', 'educator');
        })->count();
        
        return view('admin.dashboard', compact('totalStudents', 'totalEducators', 'batch2025Students', 'batch2026Students'));
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
        // Get all students for pagination
        $students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->paginate(10);
        
        // Get students by batch
        $batch2025Students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('student_id', 'like', '202501%')->get();
        
        $batch2026Students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('student_id', 'like', '202601%')->get();
        
        return view('admin.manage_student', compact('students', 'batch2025Students', 'batch2026Students'));
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
        
        // Get the old and new student IDs
        $oldStudentId = $user->student_id;
        $newStudentId = $request->student_id;
        
        try {
            // Start a transaction
            DB::beginTransaction();
            
            // If student_id is changing and there are violations
            if ($oldStudentId !== $newStudentId && $oldStudentId !== null) {
                // Temporarily disable foreign key constraints
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                
                // Update violations first
                DB::table('violations')
                    ->where('student_id', $oldStudentId)
                    ->update(['student_id' => $newStudentId]);
                
                // Update user basic info without student_id first
                $user->update([
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'name' => $request->fname . ' ' . $request->lname,
                    'email' => $request->email,
                    'sex' => $request->sex,
                    'gender' => $request->sex, // For backward compatibility
                ]);
                
                // Now update the student_id directly in the database
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['student_id' => $newStudentId]);
                
                // Re-enable foreign key constraints
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } else {
                // No student_id change or no violations, just update normally
                $user->update([
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'name' => $request->fname . ' ' . $request->lname,
                    'email' => $request->email,
                    'student_id' => $newStudentId,
                    'sex' => $request->sex,
                    'gender' => $request->sex, // For backward compatibility
                ]);
            }
            
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
            
            // Refresh the user model to get the updated data
            $user->refresh();
            
            // Commit the transaction
            DB::commit();
            
            // Get user role for appropriate redirection
            $role = $user->roles->first();
            
            if ($role) {
                switch ($role->name) {
                    case 'student':
                        return redirect()->route('admin.manage_student')
                            ->with('success', 'Student updated successfully');
                    case 'educator':
                        return redirect()->route('admin.manage_educator')
                            ->with('success', 'Educator updated successfully');
                    default:
                        return redirect()->back()
                            ->with('success', 'User updated successfully');
                }
            } else {
                return redirect()->back()
                    ->with('success', 'User updated successfully');
            }
                
        } catch (\Exception $e) {
            // Roll back the transaction if something goes wrong
            DB::rollBack();
            
            // Log the error
            \Log::error('Error updating user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update user. Error: ' . $e->getMessage())
                ->withInput();
        }
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
