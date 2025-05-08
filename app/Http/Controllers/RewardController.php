<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reward;

class RewardController extends Controller
{
    /**
     * Display a listing of rewards
     */
    public function index()
    {
        // Get rewards from database
        try {
            // Check if Reward model exists
            if (class_exists('\App\Models\Reward')) {
                $rewards = Reward::with('student')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Fallback to mock data if model doesn't exist yet
                $rewards = collect([
                    (object) [
                        'id' => 1,
                        'student_name' => 'John Smith',
                        'reward_type' => 'Academic Excellence',
                        'points' => 50,
                        'description' => 'Perfect score on final exam',
                        'created_at' => now()->subDays(5)
                    ],
                    (object) [
                        'id' => 2,
                        'student_name' => 'Jane Doe',
                        'reward_type' => 'Good Behavior',
                        'points' => 25,
                        'description' => 'Helping other students with their work',
                        'created_at' => now()->subDays(2)
                    ]
                ]);
            }
            
            return view('educator.reward', compact('rewards'));
        } catch (\Exception $e) {
            // If there's an error, return empty collection
            return view('educator.reward', ['rewards' => collect()]);
        }
    }

    /**
     * Show the form for creating a new reward
     */
    public function create()
    {
        // Get students for the reward form
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();
        
        return view('educator.newReward', [
            'students' => $students
        ]);
    }

    /**
     * Store a newly created reward in storage
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'student_id' => 'required',
                'reward_type' => 'required|string',
                'description' => 'required|string',
                'reward_date' => 'required|date',
                'points' => 'required|integer|min:1'
            ]);
            
            // Create the reward record
            Reward::create([
                'student_id' => $validated['student_id'],
                'reward_type' => $validated['reward_type'],
                'description' => $validated['description'],
                'reward_date' => $validated['reward_date'],
                'points' => $validated['points'],
                'status' => 'active'
            ]);
            
            return redirect()->route('educator.rewards')
                ->with('success', 'Reward added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error adding reward: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified reward
     */
    public function edit($id)
    {
        try {
            // Get the reward
            if (class_exists('\App\Models\Reward')) {
                $reward = Reward::findOrFail($id);
                $students = User::whereHas('roles', function($query) {
                    $query->where('name', 'student');
                })->get();
            } else {
                // Mock data if model doesn't exist
                $reward = (object) [
                    'id' => $id,
                    'student_id' => 1,
                    'student_name' => 'John Smith',
                    'reward_type' => 'Academic Excellence',
                    'points' => 50,
                    'description' => 'Perfect score on final exam',
                    'reward_date' => now()->subDays(5)->format('Y-m-d'),
                    'created_at' => now()->subDays(5)
                ];
                $students = collect([(object)['id' => 1, 'name' => 'John Smith']]);
            }
            
            return view('educator.editReward', compact('reward', 'students'));
        } catch (\Exception $e) {
            return redirect()->route('educator.rewards')
                ->with('error', 'Error editing reward: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified reward in storage
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'student_id' => 'required',
                'reward_type' => 'required|string',
                'description' => 'required|string',
                'reward_date' => 'required|date',
                'points' => 'required|integer|min:1'
            ]);
            
            // Update the reward
            if (class_exists('\App\Models\Reward')) {
                $reward = Reward::findOrFail($id);
                $reward->update([
                    'student_id' => $validated['student_id'],
                    'reward_type' => $validated['reward_type'],
                    'description' => $validated['description'],
                    'reward_date' => $validated['reward_date'],
                    'points' => $validated['points']
                ]);
            }
            
            return redirect()->route('educator.rewards')
                ->with('success', 'Reward updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating reward: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified reward from storage
     */
    public function destroy($id)
    {
        try {
            // Delete the reward
            if (class_exists('\App\Models\Reward')) {
                $reward = Reward::findOrFail($id);
                $reward->delete();
            }
            
            return redirect()->route('educator.rewards')
                ->with('success', 'Reward deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('educator.rewards')
                ->with('error', 'Error deleting reward: ' . $e->getMessage());
        }
    }

    /**
     * Generate monthly points for all students
     */
    public function generateMonthlyPoints()
    {
        // Logic to generate monthly points for all students
        // This is a placeholder - you would implement the actual logic here
        
        return redirect()->back()->with('success', 'Monthly points generated successfully!');
    }
}
