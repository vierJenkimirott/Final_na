<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * BehaviorDataController
 * Handles fetching and processing behavior data for charts
 */
class BehaviorDataController extends Controller
{
    /**
     * Get violation statistics by batch (1st year, 2nd year, or all students)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByBatch(Request $request)
    {
        try {
            // Get batch from request or use 'all' as default
            $batch = $request->input('batch', 'all');
            
            // Base query to get students
            $studentsQuery = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'student');
            
            // Filter by batch if not 'all'
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $studentsQuery->where('users.student_id', 'like', $batch . '01%');
            }
            
            // Count total students in the batch
            $totalStudents = $studentsQuery->count();
            
            // Count violators in the batch
            $violatorQuery = clone $studentsQuery;
            $violatorCount = $violatorQuery->whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('violations')
                      ->whereRaw('violations.student_id = users.student_id');
                      
            })->count();
            
            // Calculate non-violators
            $nonViolatorCount = $totalStudents - $violatorCount;
            
            return response()->json([
                'violatorCount' => $violatorCount,
                'nonViolatorCount' => $nonViolatorCount,
                'totalStudents' => $totalStudents,
                'batch' => $batch
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching batch violation stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch batch data'], 500);
        }
    }
    
    /**
     * Get behavior data for the chart
     * This method returns the actual violation data from the database
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBehaviorData(Request $request)
    {
        try {
            // Get year from request or use current year as default
            $currentYear = $request->input('year', date('Y'));
            // Get specific month filter (0-11) or 'all' for all months
            $monthFilter = $request->input('month', 'all');
            // Get batch filter or use 'all' as default
            $batchFilter = $request->input('batch', 'all');
            $months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

            // Log the filters we're using
            \Log::info('Filtering violations by year, month, and batch', [
                'year' => $currentYear,
                'month' => $monthFilter,
                'batch' => $batchFilter
            ]);

            // Initialize violation counts
            $maleViolationCounts = [];
            $femaleViolationCounts = [];

            // Determine which months to process
            $monthsToProcess = [];
            if ($monthFilter === 'all') {
                $monthsToProcess = $months;
            } else {
                // Convert month filter to integer and validate
                $monthIndex = intval($monthFilter);
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthsToProcess = [$months[$monthIndex]];
                } else {
                    // Invalid month, default to all months
                    $monthsToProcess = $months;
                }
            }

            // Count violations by month for males and females
            foreach ($monthsToProcess as $month) {
                $index = array_search($month, $months);
                $monthNum = $index + 1;
                
                // Build base query for male violations
                $maleQuery = Violation::where(function($query) {
                        $query->where('sex', 'male')
                              ->orWhere('sex', 'Male');
                    })
                    ->whereMonth('violation_date', $monthNum)
                    ->whereYear('violation_date', $currentYear)
                    ;

                // Apply batch filter if specified
                if ($batchFilter !== 'all') {
                    $maleQuery->where('student_id', 'like', $batchFilter . '01%');
                }

                $maleCount = $maleQuery->count();

                // Build base query for female violations
                $femaleQuery = Violation::where(function($query) {
                        $query->where('sex', 'female')
                              ->orWhere('sex', 'Female');
                    })
                    ->whereMonth('violation_date', $monthNum)
                    ->whereYear('violation_date', $currentYear)
                    ;

                // Apply batch filter if specified
                if ($batchFilter !== 'all') {
                    $femaleQuery->where('student_id', 'like', $batchFilter . '01%');
                }

                $femaleCount = $femaleQuery->count();
                
                $maleViolationCounts[$month] = $maleCount;
                $femaleViolationCounts[$month] = $femaleCount;
                
                // Generate weekly data for this month
                $maleWeeklyData = $this->getWeeklyViolationData($monthNum, $currentYear, 'male', $batchFilter);
                $femaleWeeklyData = $this->getWeeklyViolationData($monthNum, $currentYear, 'female', $batchFilter);
                
                // Add weekly data to the violation counts
                $maleViolationCounts[$month . '_weekly'] = $maleWeeklyData;
                $femaleViolationCounts[$month . '_weekly'] = $femaleWeeklyData;
            }
            
            // Prepare data in the format expected by the chart
            $labels = [];
            $menData = [];
            $womenData = [];

            // Extract month labels and data for the processed months
            foreach ($monthsToProcess as $month) {
                $labels[] = ucfirst(substr($month, 0, 3)); // Jan, Feb, etc.
                $menData[] = $maleViolationCounts[$month] ?? 0;
                $womenData[] = $femaleViolationCounts[$month] ?? 0;
            }
            
            // Return the data as JSON in the format expected by the chart
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'men' => $menData,
                'women' => $womenData,
                'maleViolationCounts' => $maleViolationCounts,
                'femaleViolationCounts' => $femaleViolationCounts,
                'lastUpdated' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load behavior data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get weekly violation data for a specific month and gender
     *
     * @param int $monthNum The month number (1-12)
     * @param int $year The year
     * @param string $gender The gender ('male' or 'female')
     * @param string $batchFilter The batch filter ('all' or specific batch)
     * @return array Array of violation data for each week
     */
    private function getWeeklyViolationData($monthNum, $year, $gender, $batchFilter = 'all')
    {
        // Create a Carbon instance for the first day of the month
        $firstDay = Carbon::createFromDate($year, $monthNum, 1);
        
        // Get the last day of the month
        $lastDay = $firstDay->copy()->endOfMonth();
        
        // Calculate the number of weeks in the month
        $firstDayOfWeek = $firstDay->dayOfWeek;
        $totalDays = $lastDay->day;
        $numWeeks = ceil(($totalDays + $firstDayOfWeek) / 7);
        
        // Initialize weekly data arrays
        $weeklyData = array_fill(0, $numWeeks, 0);
        $weeklyViolators = array_fill(0, $numWeeks, []);
        
        // Get all violations for this month and gender
        $violationsQuery = Violation::where(function($query) use ($gender) {
                $query->where('sex', $gender)
                      ->orWhere('sex', ucfirst($gender));
            })
            ->whereMonth('violation_date', $monthNum)
            ->whereYear('violation_date', $year)
            ;

        // Apply batch filter if specified
        if ($batchFilter !== 'all') {
            $violationsQuery->where('student_id', 'like', $batchFilter . '01%');
        }

        $violations = $violationsQuery->get();
        
        // Eager load student information for all violations
        $studentIds = $violations->pluck('student_id')->filter()->unique()->toArray();
        $students = [];
        
        if (!empty($studentIds)) {
            // Get all students in a single query to avoid N+1 problem
            $studentsCollection = \App\Models\User::whereIn('student_id', $studentIds)->get();
            
            // Index students by student_id for easy lookup
            foreach ($studentsCollection as $student) {
                $students[$student->student_id] = $student;
            }
        }
        
        // Attach student information to each violation
        foreach ($violations as $violation) {
            if ($violation->student_id && isset($students[$violation->student_id])) {
                $student = $students[$violation->student_id];
                $violation->first_name = $student->fname;
                $violation->last_name = $student->lname;
            }
        }
        
        // Group violations by week
        foreach ($violations as $violation) {
            // Parse the violation date
            $violationDate = Carbon::parse($violation->violation_date);
            
            // Calculate which week of the month this date falls in
            $dayOfMonth = $violationDate->day;
            $adjustedDay = $dayOfMonth + $firstDayOfWeek - 1;
            $weekIndex = floor($adjustedDay / 7);
            
            // Ensure the week index is within bounds
            if ($weekIndex >= 0 && $weekIndex < $numWeeks) {
                $weeklyData[$weekIndex]++;
                
                // Get the actual student name from the violation record
                $studentName = null;
                
                // First check if we have first_name and last_name from the join
                if (isset($violation->first_name) && isset($violation->last_name)) {
                    $studentName = $violation->first_name . ' ' . $violation->last_name;
                } 
                // If not, try to get the student information from the database
                else if ($violation->student_id) {
                    try {
                        $student = \App\Models\User::where('student_id', $violation->student_id)->first();
                        if ($student) {
                            $studentName = $student->fname . ' ' . $student->lname;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error fetching student name: ' . $e->getMessage());
                    }
                }
                
                // If we still don't have a name, use the student ID or a fallback
                if (!$studentName) {
                    if ($violation->student_id) {
                        $studentName = 'Student #' . $violation->student_id;
                    } else {
                        $studentName = ucfirst($gender) . ' Student';
                    }
                }
                
                // Add violator info to the week with the best name we could find
                $weeklyViolators[$weekIndex][] = [
                    'name' => $studentName,
                    'date' => $violationDate->format('M d, Y'),
                    'violation_type' => $violation->violation_type
                ];
            }
        }
        
        // Return both the weekly count data and violator details
        return [
            'counts' => $weeklyData,
            'violators' => $weeklyViolators
        ];
    }
    /**
     * Get all violations for a student (AJAX for modal)
     */
    public function getStudentViolationHistory($student_id)
    {
        try {
            $violations = 
                \App\Models\Violation::with('violationType')
                ->where('student_id', $student_id)
                ->orderBy('violation_date', 'desc')
                ->get();
            $result = $violations->map(function($v) {
                return [
                    'violation_date' => \Carbon\Carbon::parse($v->violation_date)->format('M d, Y'),
                    'violation_type' => $v->violationType->violation_name ?? 'N/A',
                    'penalty' => $v->penalty,
                    'status' => ucfirst($v->status),
                ];
            });
            return response()->json([
                'success' => true,
                'violations' => $result
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching student violation history: ' . $e->getMessage());
            return response()->json(['success' => false, 'violations' => []], 500);
        }
    }
}
