<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use Illuminate\Support\Facades\DB;

class EducatorController extends Controller
{
    /**
     * Display the educator dashboard
     */
    public function dashboard()
    {
        // Get top violators (students with most violations)
        $topViolators = User::role('student')
            ->withCount('violations')
            ->having('violations_count', '>', 0)
            ->orderBy('violations_count', 'desc')
            ->take(5)
            ->get();

        // Get total violations count
        $totalViolations = Violation::count();

        // Get total students count
        $totalStudents = User::role('student')->count();

        // Get total rewards count
        $totalRewards = DB::table('rewards')->count();

        // Get recent violations
        $recentViolations = Violation::with(['student', 'violationType'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get violations by month
        $violationsByMonth = DB::table('violations')
            ->selectRaw('MONTH(violation_date) as month, COUNT(*) as count')
            ->whereRaw('YEAR(violation_date) = YEAR(CURDATE())')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Get violations by type
        $violationsByType = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->selectRaw('violation_types.violation_name, COUNT(*) as count')
            ->groupBy('violation_types.violation_name')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'violation_name')
            ->toArray();

        // Get violator and non-violator counts
        $violatorCount = User::role('student')
            ->whereHas('violations')
            ->count();

        $nonViolatorCount = $totalStudents - $violatorCount;

        return view('educator.dashboard', [
            'topViolators' => $topViolators,
            'totalViolations' => $totalViolations,
            'totalStudents' => $totalStudents,
            'totalRewards' => $totalRewards,
            'recentViolations' => $recentViolations,
            'violationsByMonth' => $violationsByMonth,
            'violationsByType' => $violationsByType,
            'violatorCount' => $violatorCount,
            'nonViolatorCount' => $nonViolatorCount
        ]);
    }

    /**
     * Show the form for creating a new violation type
     */
    public function createViolationType()
    {
        return view('educator.newViolation');
    }
    
    /**
     * Display the behavior page
     */
    public function behavior()
    {
        return view('educator.behavior');
    }
    
    /**
     * View a specific violation
     * @param int $id The violation ID
     * @return \Illuminate\View\View
     */
    public function viewViolation($id)
    {
        try {
            // Fetch the actual violation data from the database with relationships
            $violation = \App\Models\Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
                ->findOrFail($id);
            
            // If student relationship is null, try to find the student directly
            if (!$violation->student) {
                $student = \App\Models\User::where('student_id', $violation->student_id)->first();
                if ($student) {
                    // Manually attach the student to the violation
                    $violation->setRelation('student', $student);
                }
            }
            
            return view('educator.viewViolation', compact('violation'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error viewing violation: ' . $e->getMessage());
            return redirect()->route('educator.violation')
                ->with('error', 'Error viewing violation: ' . $e->getMessage());
        }
    }
    
    /**
     * Get students by penalty
     * @param string $penalty The penalty code (W, VW, WW, Pro, Exp)
     * @return \Illuminate\View\View
     */
    public function studentsByPenalty($penalty)
    {
        try {
            // Log the penalty parameter for debugging
            \Illuminate\Support\Facades\Log::info('studentsByPenalty called with penalty: ' . $penalty);
            
            // Get all students with active violations of the specified penalty
            $students = User::role('student')
                ->whereHas('violations', function($query) use ($penalty) {
                    $query->where('penalty', $penalty)
                          ->where('status', 'active');
                })
                ->with(['violations' => function($query) use ($penalty) {
                    $query->where('penalty', $penalty)
                          ->where('status', 'active')
                          ->with('violationType');
                }])
                ->get();
            
            // Log the number of students found
            \Illuminate\Support\Facades\Log::info('Found ' . count($students) . ' students with penalty: ' . $penalty);
            
            return view('educator.studentsByPenalty', [
                'students' => $students,
                'penalty' => $penalty
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching students by penalty: ' . $e->getMessage());
            return redirect()->route('educator.violation')
                ->with('error', 'Error fetching students: ' . $e->getMessage());
        }
    }
}
