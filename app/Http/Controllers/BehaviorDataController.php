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
     * Get violation statistics by class (1st year, 2nd year, or all students)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByClass(Request $request)
    {
        try {
            // Get class filter parameters
            $classFilter = $request->input('batch', 'all');
            $classYear = $request->input('batchYear');
            $startYear = $request->input('startYear');
            $endYear = $request->input('endYear');
            
            // Base query to get students
            $studentsQuery = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'student');
            
            // Apply class filter
            if ($classFilter === 'range' && $startYear && $endYear) {
                // Filter for class range
                $studentsQuery->where(function($query) use ($startYear, $endYear) {
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $query->orWhere('users.student_id', 'like', $year . '%');
                    }
                });
            } elseif ($classFilter === 'specific' && $classYear) {
                // Filter for specific class year
                $studentsQuery->where('users.student_id', 'like', $classYear . '%');
            }
            
            // Count total students in the class
            $totalStudents = $studentsQuery->count();
            
            // Count violators in the class
            $violatorQuery = clone $studentsQuery;
            $violatorCount = $violatorQuery->whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('violations')
                      ->whereRaw('violations.student_id = users.student_id')
                      ->where('violations.status', 'active');
            })->count();
            
            // Calculate non-violators
            $nonViolatorCount = $totalStudents - $violatorCount;
            
            return response()->json([
                'violatorCount' => $violatorCount,
                'nonViolatorCount' => $nonViolatorCount,
                'totalStudents' => $totalStudents,
                'count' => $totalStudents, // For compatibility with existing code
                'class' => $classFilter,
                'classYear' => $classYear,
                'classRange' => [$startYear, $endYear]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching class violation stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch class data'], 500);
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
            // Get filter parameters from request
            $currentYear = $request->input('year', date('Y'));
            $selectedMonth = $request->input('month', 'all');
            
            // Get class filter from URL parameters first, then from request
            $classFilter = $request->query('batch', $request->input('batchFilter', 'all'));
            $classYear = $request->query('batchYear', $request->input('batchYear'));
            $startYear = $request->query('startYear', $request->input('startYear'));
            $endYear = $request->query('endYear', $request->input('endYear'));
            
            // Ensure we have valid class filter values
            if ($classFilter === 'specific' && empty($classYear)) {
                $classFilter = 'all'; // Reset to all if no class year provided
            }
            if ($classFilter === 'range' && (empty($startYear) || empty($endYear))) {
                $classFilter = 'all'; // Reset to all if range years not provided
            }
            $scale = $request->input('scale', 'auto');
            
            // Log the filter parameters
            \Log::info('Behavior data filters', [
                'year' => $currentYear,
                'month' => $selectedMonth,
                'classFilter' => $classFilter,
                'classYear' => $classYear,
                'startYear' => $startYear,
                'endYear' => $endYear,
                'scale' => $scale
            ]);
            
            $months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
            
            // Initialize violation counts
            $maleViolationCounts = [];
            $femaleViolationCounts = [];
            
            // Determine which months to process based on the selected month
            $monthsToProcess = ($selectedMonth === 'all') ? $months : [$months[(int)$selectedMonth]];
            
            // Process each month
            foreach ($monthsToProcess as $index => $month) {
                $monthNum = ($selectedMonth === 'all') ? $index + 1 : (int)$selectedMonth + 1;
                
                // Base query for male violations
                $maleQuery = Violation::where(function($query) {
                        $query->where('sex', 'male')
                              ->orWhere('sex', 'Male');
                    })
                    ->whereMonth('violation_date', $monthNum)
                    ->whereYear('violation_date', $currentYear)
                    ->where('status', 'active');
                
                // Base query for female violations
                $femaleQuery = Violation::where(function($query) {
                        $query->where('sex', 'female')
                              ->orWhere('sex', 'Female');
                    })
                    ->whereMonth('violation_date', $monthNum)
                    ->whereYear('violation_date', $currentYear)
                    ->where('status', 'active');
                
                // Apply class filter if specified
                if ($classFilter === 'specific' && $classYear) {
                    // Filter for specific class year
                    $maleQuery->where('student_id', 'like', $classYear . '%');
                    $femaleQuery->where('student_id', 'like', $classYear . '%');
                } 
                else if ($classFilter === 'range' && $startYear && $endYear) {
                    // Filter for class range
                    $maleQuery->where(function($query) use ($startYear, $endYear) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $query->orWhere('student_id', 'like', $year . '%');
                        }
                    });
                    
                    $femaleQuery->where(function($query) use ($startYear, $endYear) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $query->orWhere('student_id', 'like', $year . '%');
                        }
                    });
                }
                
                // Count violations
                $maleCount = $maleQuery->count();
                $femaleCount = $femaleQuery->count();
                
                $maleViolationCounts[$month] = $maleCount;
                $femaleViolationCounts[$month] = $femaleCount;
                
                // Generate weekly data for this month
                $maleWeeklyData = $this->getWeeklyViolationData($monthNum, $currentYear, 'male', $classFilter, $classYear, $startYear, $endYear);
                $femaleWeeklyData = $this->getWeeklyViolationData($monthNum, $currentYear, 'female', $classFilter, $classYear, $startYear, $endYear);
                
                // Add weekly data to the violation counts
                $maleViolationCounts[$month . '_weekly'] = $maleWeeklyData;
                $femaleViolationCounts[$month . '_weekly'] = $femaleWeeklyData;
            }
            
            // Prepare data in the format expected by the chart
            $labels = [];
            $menData = [];
            $womenData = [];
            
            // Extract month labels and data
            if ($selectedMonth === 'all') {
                foreach ($months as $month) {
                    $labels[] = ucfirst(substr($month, 0, 3)); // Jan, Feb, etc.
                    $menData[] = $maleViolationCounts[$month] ?? 0;
                    $womenData[] = $femaleViolationCounts[$month] ?? 0;
                }
            } else {
                $month = $months[(int)$selectedMonth];
                $labels[] = ucfirst(substr($month, 0, 3)); // Jan, Feb, etc.
                $menData[] = $maleViolationCounts[$month] ?? 0;
                $womenData[] = $femaleViolationCounts[$month] ?? 0;
            }
            
            // Calculate Y-axis scale based on the selected scale option
            $yAxisMax = 'auto';
            if ($scale !== 'auto') {
                $yAxisMax = (int)$scale;
            }
            
            // Get class description for chart title
            $classDescription = 'All Students';
            if ($classFilter === 'specific' && $classYear) {
                $classDescription = 'Class ' . $classYear;
            } else if ($classFilter === 'range' && $startYear && $endYear) {
                $classDescription = 'Classes ' . $startYear . '-' . $endYear;
            }
            
            // Return the data as JSON in the format expected by the chart
            // Debug log the data being returned
            \Log::info('Returning behavior data', [
                'labels' => $labels,
                'menData' => $menData,
                'womenData' => $womenData,
                'maleViolationCounts' => array_slice($maleViolationCounts, 0, 3), // Log just a sample
                'femaleViolationCounts' => array_slice($femaleViolationCounts, 0, 3), // Log just a sample
            ]);
            
            // Return the data as JSON in the format expected by the chart
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'men' => $menData,
                'women' => $womenData,
                'maleViolationCounts' => $maleViolationCounts,
                'femaleViolationCounts' => $femaleViolationCounts,
                'yAxisMax' => $yAxisMax,
                'lastUpdated' => now()->format('Y-m-d H:i:s'),
                'filters' => [
                    'year' => $currentYear,
                    'month' => $selectedMonth,
                    'classFilter' => $classFilter,
                    'classYear' => $classYear,
                    'classRange' => [$startYear, $endYear],
                    'scale' => $scale
                ],
                'selectedClass' => $classFilter === 'specific' ? $classYear : ($classFilter === 'range' ? $startYear . '-' . $endYear : 'all'),
                'classDescription' => $classDescription,
                'chartTitle' => ($selectedMonth === 'all' ? 
                    'Student Violations by Month (' . $currentYear . ', ' . $classDescription . ')' : 
                    'Weekly Violations - ' . ucfirst($months[(int)$selectedMonth]) . ' ' . $currentYear . ' (' . $classDescription . ')')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getBehaviorData: ' . $e->getMessage());
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
     * @return array Array of violation data for each week
     */
    private function getWeeklyViolationData($monthNum, $year, $gender, $classFilter = 'all', $classYear = null, $startYear = null, $endYear = null)
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
        
        // Log the parameters for debugging
        \Log::info('getWeeklyViolationData called with', [
            'monthNum' => $monthNum,
            'year' => $year,
            'gender' => $gender,
            'classFilter' => $classFilter,
            'classYear' => $classYear,
            'startYear' => $startYear,
            'endYear' => $endYear,
            'numWeeks' => $numWeeks
        ]);
        
        // Base query for violations
        $query = Violation::where(function($query) use ($gender) {
                $query->where('sex', $gender)
                      ->orWhere('sex', ucfirst($gender));
            })
            ->whereMonth('violation_date', $monthNum)
            ->whereYear('violation_date', $year)
            ->where('status', 'active');
            
        // Apply class filter if specified
        if ($classFilter === 'specific' && $classYear) {
            // Filter for specific class year
            $query->where('student_id', 'like', $classYear . '%');
        } 
        else if ($classFilter === 'range' && $startYear && $endYear) {
            // Filter for class range
            $query->where(function($subquery) use ($startYear, $endYear) {
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $subquery->orWhere('student_id', 'like', $year . '%');
                }
            });
        }
        
        // Get violations
        $violations = $query->get();
        
        // Log the number of violations found
        \Log::info('Found violations for weekly data', [
            'count' => $violations->count(),
            'gender' => $gender,
            'month' => $monthNum,
            'year' => $year
        ]);
        
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
        
        // Log the weekly data before returning
        \Log::info('Weekly data to be returned', [
            'weeklyData' => $weeklyData,
            'gender' => $gender,
            'month' => $monthNum,
            'year' => $year
        ]);
        
        // For backward compatibility, return just the array of counts
        // This is what the JavaScript code expects
        return $weeklyData;
    }
}