<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * EducatorController
 * Handles all educator-related functionality including violation management,
 * student tracking, and dashboard statistics.
 */
class EducatorController extends Controller
{
    /**
     * Show a student's profile by student_id
     */
    public function showStudentProfile($student_id)
    {
        $student = \App\Models\User::where('student_id', $student_id)->first();
        if (!$student) {
            $student = \App\Models\User::find($student_id);
        }
        if (!$student) {
            abort(404, 'Student not found');
        }
        $violations = \App\Models\Violation::where('student_id', $student->student_id ?? $student->id)
            ->with('violationType')
            ->orderByDesc('violation_date')
            ->get();
        return view('educator.student_violation_history', compact('student', 'violations'));
    }

    // =============================================
    // DASHBOARD METHODS
    // =============================================

    /**
     * Show all students page
     */
    public function studentsPage()
    {
        $students = \App\Models\User::role('student')->get();
        // Example batch extraction, adjust as needed for your batch logic
        $batches = \App\Models\User::role('student')
            ->selectRaw('LEFT(student_id, 4) as id, LEFT(student_id, 4) as name')
            ->distinct()->get();
        return view('educator.students', compact('students', 'batches'));
    }

    /**
     * Show all active violation cases
     */
    public function activeViolations(Request $request)
    {
        $query = \App\Models\Violation::with(['student', 'violationType'])
            ->where('status', 'active');

        // Search by student name
        if ($request->filled('name')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        // Filter by batch
        if ($request->filled('batch') && $request->batch !== 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_id', 'like', $request->batch . '01%');
            });
        }
        $violations = $query->orderByDesc('violation_date')->get();
        // Get available batches for filter dropdown
        $batches = \App\Models\User::role('student')
            ->selectRaw('LEFT(student_id, 4) as batch')
            ->distinct()
            ->pluck('batch');
        return view('educator.activeViolations', [
            'violations' => $violations,
            'batches' => $batches,
            'currentBatch' => $request->batch ?? 'all',
            'currentName' => $request->name ?? ''
        ]);
    }
    
    /**
     * Get students count by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsByBatch(Request $request)
    {
        $batch = $request->query('batch', 'all');

        try {
            if ($batch === 'all') {
                $count = User::role('student')->count();
            } else {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $count = User::role('student')
                    ->where('student_id', 'like', $batch . '01%')
                    ->count();
            }

            return response()->json([
                'success' => true,
                'count' => $count,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getStudentsByBatch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching students by batch: ' . $e->getMessage(),
                'count' => 0
            ]);
        }
    }
    
    /**
     * Get violations count by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationsCount(Request $request)
    {
        $batch = $request->query('batch', 'all');
        
        try {
            if ($batch === 'all') {
                $count = Violation::where('status', 'active')->count();
            } else {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $count = Violation::where('status', 'active')
                    ->where('student_id', 'like', $batch . '01%')
                    ->count();
            }

            return response()->json([
                'success' => true,
                'count' => $count,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching violations count by batch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching violations count: ' . $e->getMessage(),
                'count' => 0
            ]);
        }
    }
    
    /**
     * Get available batches dynamically from the database
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableBatches()
    {
        try {
            // Get distinct batches from student_id patterns
            $batches = User::role('student')
                ->whereNotNull('student_id')
                ->get()
                ->map(function ($user) {
                    // Extract year from student_id (e.g., 202501001 -> 2025)
                    if (preg_match('/^(\d{4})/', $user->student_id, $matches)) {
                        return $matches[1];
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->map(function ($year) {
                    return [
                        'value' => $year,
                        'label' => "Class {$year}",
                        'count' => User::role('student')
                            ->where('student_id', 'like', $year . '%')
                            ->count()
                    ];
                })
                ->filter(function ($batch) {
                    return $batch['count'] > 0;
                });

            // Add "All Classes" option at the beginning
            $allBatches = collect([
                [
                    'value' => 'all',
                    'label' => 'All Classes',
                    'count' => User::role('student')->count()
                ]
            ])->concat($batches);

            return response()->json([
                'success' => true,
                'batches' => $allBatches
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableBatches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available batches: ' . $e->getMessage(),
                'batches' => [
                    [
                        'value' => 'all',
                        'label' => 'All Classes',
                        'count' => 0
                    ]
                ]
            ]);
        }
    }

    /**
     * Get students compliance status (violators and non-violators) by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentComplianceByBatch(Request $request)
    {
        try {
            $batch = $request->query('batch', 'all');
            
            // Get all students
            $studentsQuery = User::role('student');

            // Apply batch filter
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $studentsQuery->where('student_id', 'like', $batch . '01%');
            }
            
            // Get non-compliant students (with violations)
            $nonCompliantQuery = clone $studentsQuery;
            $nonCompliant = $nonCompliantQuery
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = users.student_id')
                          ->where('violations.status', 'active');
                })
                ->select('users.name', 'users.student_id', DB::raw('(SELECT COUNT(*) FROM violations WHERE violations.student_id = users.student_id AND violations.status = "active") as violations_count'))
                ->orderBy('violations_count', 'desc')
                ->limit(10)
                ->get();
            
            // Get compliant students (without violations)
            $compliantQuery = clone $studentsQuery;
            $compliant = $compliantQuery
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = users.student_id')
                          ->where('violations.status', 'active');
                })
                ->select('users.name', 'users.student_id')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'nonCompliant' => $nonCompliant,
                'compliant' => $compliant,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student compliance data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student compliance data: ' . $e->getMessage(),
                'nonCompliant' => [],
                'compliant' => []
            ], 500);
        }
    }

    /**
     * Display the educator dashboard
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        try {
            // Get total students
            $totalStudents = User::role('student')->count();

            // Get total violations
            $totalViolations = Violation::count();

            // Get top violators (students with most violations)
            try {
                // First, check if the violations table exists
                if (!Schema::hasTable('violations')) {
                    throw new \Exception('Violations table does not exist');
                }

                // Get all violations
                $allViolations = DB::table('violations')->get();

                // Log the total violations found
                \Log::info('Total violations found: ' . $allViolations->count());

                // Group violations by student_id and count them
                $violationsPerStudent = $allViolations->groupBy('student_id')
                    ->map(function ($group) {
                        return count($group);
                    })
                    ->sortDesc()
                    ->take(5);

                // Log the violations per student
                \Log::info('Violations per student: ' . json_encode($violationsPerStudent));

                // Get all student IDs with violations
                $studentIds = $violationsPerStudent->keys()->toArray();

                // Get all users with these student IDs
                $users = DB::table('users')->whereIn('student_id', $studentIds)->get();

                // Log the users found
                \Log::info('Users found: ' . $users->count());

                // Create a lookup array for quick access to user data
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user->student_id] = $user;
                }

                // Create the final top violators collection
                $topViolators = collect();
                foreach ($violationsPerStudent as $studentId => $count) {
                    $user = $userLookup[$studentId] ?? null;

                    if ($user) {
                        \Log::info("Adding violator: {$user->name} with {$count} violations");
                        $topViolators->push((object) [
                            'id' => $user->id,
                            'name' => $user->name,
                            'student_id' => $studentId,
                            'violations_count' => $count
                        ]);
                    } else {
                        \Log::warning("No user found for student_id: {$studentId}");
                        // Add a placeholder for missing users
                        $topViolators->push((object) [
                            'id' => 0,
                            'name' => "Student {$studentId}",
                            'student_id' => $studentId,
                            'violations_count' => $count
                        ]);
                    }
                }

                // Log the final top violators collection
                \Log::info('Final top violators: ' . json_encode($topViolators));

                // If no violators found, log this information
                if ($topViolators->isEmpty()) {
                    \Log::info('No violators found in the database');
                    // Keep it as an empty collection, don't convert to array
                    // This ensures consistent type handling in the view
                }

                // Log for debugging
                \Log::info('Top violators query result: ' . $topViolators->count() . ' records found with violations');
                foreach ($topViolators as $violator) {
                    \Log::info("Violator: {$violator->name} ({$violator->student_id}) - {$violator->violations_count} violations");
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching top violators: ' . $e->getMessage());
                // Initialize with an empty collection on error to be consistent
                $topViolators = collect();
            }

            // Get recent violations - safely handling potential missing tables
            try {
                // Check if violation_types table exists
                $hasViolationTypesTable = Schema::hasTable('violation_types');

                if ($hasViolationTypesTable) {
                    $recentViolations = Violation::with(['student'])
                        ->select(
                            'violations.*',
                            'users.name as student_name'
                        )
                        ->join('users', 'violations.student_id', '=', 'users.student_id')
                        ->orderBy('violations.created_at', 'desc')
                        ->take(5)
                        ->get();
                } else {
                    // Simplified query without violation_types
                    $recentViolations = Violation::select(
                            'violations.*',
                            'users.name as student_name'
                        )
                        ->join('users', 'violations.student_id', '=', 'users.student_id')
                        ->orderBy('violations.created_at', 'desc')
                        ->take(5)
                        ->get();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching recent violations: ' . $e->getMessage());
                $recentViolations = collect([]);
            }

            // Get violations by month
            $violationsByMonth = DB::table('violations')
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get violations by type - safely handling potential missing tables
            try {
                // Check if violation_types table exists
                $hasViolationTypesTable = Schema::hasTable('violation_types');

                if ($hasViolationTypesTable) {
                    $violationsByType = DB::table('violations')
                        ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                        ->select('violation_types.name', DB::raw('COUNT(violations.id) as count'))
                        ->groupBy('violation_types.id', 'violation_types.name')
                        ->orderBy('count', 'desc')
                        ->take(5)
                        ->get();
                } else {
                    // Group by violation_type_id instead if table doesn't exist
                    $violationsByType = DB::table('violations')
                        ->select('violation_type_id as name', DB::raw('COUNT(id) as count'))
                        ->groupBy('violation_type_id')
                        ->orderBy('count', 'desc')
                        ->take(5)
                        ->get();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching violations by type: ' . $e->getMessage());
                $violationsByType = collect([]);
            }

            // Get count of students with violations
            $violatorCount = User::role('student')
                ->whereHas('violations')
                ->count();

            $nonViolatorCount = $totalStudents - $violatorCount;

            // Get the period parameter from the URL or default to 'month'
            $period = $request->input('period', 'month');

            // Get violation statistics based on the period
            try {
                $violationStats = $this->getViolationStatsByPeriod($period);
            } catch (Exception $e) {
                Log::error('Error getting violation stats: ' . $e->getMessage());
                $violationStats = [];
            }

            return view('educator.dashboard', [
                'topViolators' => $topViolators,
                'totalViolations' => $totalViolations,
                'totalStudents' => $totalStudents,
                'recentViolations' => $recentViolations,
                'violationsByMonth' => $violationsByMonth,
                'violationsByType' => $violationsByType,
                'violatorCount' => $violatorCount,
                'nonViolatorCount' => $nonViolatorCount,
                'violationStats' => $violationStats
            ]);
        } catch (Exception $e) {
            Log::error('Error in dashboard: ' . $e->getMessage());
            return view('educator.dashboard', [
                'topViolators' => collect(),
                'totalViolations' => 0,
                'totalStudents' => 0,
                'recentViolations' => [],
                'violationsByMonth' => [],
                'violationsByType' => [],
                'violatorCount' => 0,
                'nonViolatorCount' => 0,
                'violationStats' => [],
                'error' => 'Unable to load dashboard data'
            ]);
        }
    }

    // Dashboard helper methods

    /**
     * Get violation statistics by period
     *
     * @param string $period The period to get statistics for (month, last_month, last_3_months)
     * @return \Illuminate\Support\Collection Violation statistics
     */
    private function getViolationStatsByPeriod($period = 'month')
    {
        Log::info('Violation stats query', ['period' => $period]);

        // Determine date range based on period
        $dateRange = $this->getDateRangeForPeriod($period);

        try {
            // Check if violation_types table exists
            if (Schema::hasTable('violation_types')) {
                // Join with violation_types table to get names
                $stats = DB::table('violations')
                    ->select(
                        'violations.violation_type_id',
                        'violation_types.violation_name as violation_name',
                        DB::raw('COUNT(*) as count')
                    )
                    ->leftJoin('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                    ->whereBetween('violations.violation_date', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('violations.violation_type_id', 'violation_types.violation_name')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get();

                // If no data found, return empty collection
                if ($stats->isEmpty()) {
                    Log::info('No violation stats found for period: ' . $period);
                    return collect([]);
                }

                // Ensure each item has a violation_name
                $stats = $stats->map(function($item) {
                    if (empty($item->violation_name)) {
                        $item->violation_name = 'Type ' . $item->violation_type_id;
                    }
                    return $item;
                });
            } else {
                // Group by violation_type_id only if violation_types table doesn't exist
                $stats = DB::table('violations')
                    ->select('violation_type_id', DB::raw('COUNT(*) as count'))
                    ->whereBetween('violation_date', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('violation_type_id')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($item) {
                        $item->violation_name = 'Type ' . $item->violation_type_id;
                        return $item;
                    });
            }

            Log::info('Violation stats query results: ' . $stats->count() . ' records');
            return $stats;

        } catch (\Exception $e) {
            Log::error('Error in violation stats query: ' . $e->getMessage());
            // Return empty collection on error
            return collect([]);
        }
    }

    /**
     * Get date range for a given period
     *
     * @param string $period The period (month, last_month, last_3_months)
     * @return array Start and end dates
     */
    private function getDateRangeForPeriod($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;

            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->endOfMonth()->format('Y-m-d');
                break;

            case 'month':
            default:
                $start = $now->copy()->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    // =============================================
    // VIOLATION MANAGEMENT METHODS
    // =============================================

    /**
     * View a specific violation
     * @param int $id The violation ID
     * @return \Illuminate\View\View
     */
    public function viewViolation($id)
    {
        try {
            $violation = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
                ->findOrFail($id);

            return view('educator.viewViolation', [
                'violation' => $violation
            ]);
        } catch (Exception $e) {
            Log::error('Error viewing violation: ' . $e->getMessage());
            return redirect()->route('educator.violation')
                ->with('error', 'Unable to find the requested violation record.');
        }
    }

    /**
     * Show the form for creating a new violation type
     */
    public function showViolationTypeForm()
    {
        $categories = OffenseCategory::all();
        return view('educator.newViolation', [
            'categories' => $categories
        ]);
    }

    /**
     * Create a new violation type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createViolationType(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:offense_categories,id',
                'penalty' => 'required|string|max:50',
                'severity' => 'required|string|in:low,medium,high,very high'
            ]);

            $violationType = new ViolationType();
            $violationType->name = $validated['name'];
            $violationType->description = $validated['description'];
            $violationType->offense_category_id = $validated['category_id'];
            $violationType->penalty = $validated['penalty'];
            $violationType->severity = $validated['severity'];
            $violationType->save();

            return response()->json([
                'success' => true,
                'message' => 'Violation type created successfully',
                'data' => $violationType
            ]);
        } catch (Exception $e) {
            Log::error('Error creating violation type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create violation type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter and display students by penalty type
     *
     * @param string $penalty The penalty code (W, VW, WW, Pro, Exp)
     * @return \Illuminate\View\View
     */
    public function studentsByPenalty(Request $request, $penalty)
    {
        try {
            // Validate the penalty type
            $validPenalties = ['W', 'VW', 'WW', 'Pro', 'Exp'];
            if (!in_array($penalty, $validPenalties)) {
                return redirect()->route('educator.violation')
                    ->with('error', 'Invalid penalty type specified.');
            }

            $batch = $request->query('batch', 'all');
            $violationsQuery = Violation::with(['student', 'violationType'])
                ->where('penalty', $penalty)
                ->where('status', 'active');
            if ($batch !== 'all') {
                $violationsQuery->whereHas('student', function($query) use ($batch) {
                    $query->where('student_id', 'like', $batch . '%');
                });
            }
            $violations = $violationsQuery->orderBy('violation_date', 'desc')->get();

            return view('educator.studentsByPenalty', compact('violations', 'penalty', 'batch'));

        } catch (\Exception $e) {
            Log::error('Error in studentsByPenalty: ' . $e->getMessage());
            return redirect()->route('educator.dashboard')
                ->with('error', 'Unable to load students by penalty.');
        }
    }

    /**
     * Display the behavior monitoring page
     */
    public function behavior()
    {
        // Get total students count from the database
        $totalStudents = User::role('student')->count();

        // Get count of active violation cases
        $activeViolationCases = \App\Models\Violation::where('status', 'active')->count();
        // $studentsWithMultipleViolations = User::role('student')
        //     ->select('users.id')
        //     ->join('violations', 'users.student_id', '=', 'violations.student_id')
        //     ->where('violations.status', 'active')
        //     ->groupBy('users.id')
        //     ->havingRaw('COUNT(violations.id) > 2')
        //     ->count();

        return view('educator.behavior', [
            'totalStudents' => $totalStudents,
            'activeViolationCases' => $activeViolationCases
        ]);
    }

    /**
     * Display the behavior page with student statistics
     */
    public function behaviorStats()
    {
        $stats = $this->getStudentStats();
        return view('educator.behaviorStats', $stats);
    }

    /**
     * API endpoint to get behavior data for the chart
     */
    public function getBehaviorData(Request $request)
    {
        $monthsToShow = $request->input('months', 6);
        return $this->getBehaviorDataBySex($monthsToShow);
    }

    /**
     * Get severity level based on penalty
     * @param string $penalty The penalty code
     * @return string The severity level
     */
    protected function getSeverityFromPenalty($penalty)
    {
        switch ($penalty) {
            case 'W':
                return 'low';
            case 'VW':
                return 'medium';
            case 'WW':
                return 'high';
            case 'Pro':
            case 'Exp':
                return 'very high';
            default:
                return 'medium';
        }
    }

    /**
     * Get penalty code from penalty name
     * @param string $penalty The penalty name
     * @return string The penalty code
     */
    protected function getPenaltyCode($penalty)
    {
        switch (strtolower($penalty)) {
            case 'warning':
                return 'W';
            case 'verbal warning':
                return 'VW';
            case 'written warning':
                return 'WW';
            case 'probation':
                return 'Pro';
            case 'expulsion':
                return 'Exp';
            default:
                return $penalty;
        }
    }

    /**
     * Get violation statistics for the dashboard
     *
     * @param string $period The period to get statistics for (month, last_month, last_3_months, year)
     * @return array
     */
    protected function getViolationStats($period = 'month')
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Define date ranges based on period
        switch ($period) {
            case 'month':
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'This Month';
                break;

            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $startDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'Last Month';
                break;

            case 'last_3_months':
                $startDate = $now->copy()->subMonths(3)->startOfMonth()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodLabel = 'Last 3 Months';
                break;

            case 'year':
                $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();
                $periodLabel = 'This Year';
                break;

            default:
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'This Month';
        }

        // Get total violations for this period
        $totalViolations = Violation::where('status', 'active')
            ->whereBetween('violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Get violations by type for this period
        $violationsByType = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->select('violation_types.violation_name', DB::raw('COUNT(*) as count'))
            ->where('violations.status', 'active')
            ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('violation_types.id', 'violation_types.violation_name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        // Get violations by severity for this period
        $violationsBySeverity = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->select('violation_types.severity', DB::raw('COUNT(*) as count'))
            ->where('violations.status', 'active')
            ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('violation_types.severity')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'totalViolations' => $totalViolations,
            'violationsByType' => $violationsByType,
            'violationsBySeverity' => $violationsBySeverity,
            'periodLabel' => $periodLabel
        ];
    }

    /**
     * Get student statistics for behavior monitoring
     */
    protected function getStudentStats()
    {
        return [
            'totalStudents' => User::role('student')->count(),
            'studentsNeedingAttention' => User::role('student')
                ->withCount('violations')
                ->having('violations_count', '>', 2)
                ->count()
        ];
    }

    /**
     * View student behavior page
     *
     * @param string $studentId
     * @return \Illuminate\View\View
     */
    public function viewStudentBehavior($studentId)
    {
        // Find the student
        $student = User::where('student_id', $studentId)->first();
        if (!$student) {
            return redirect()->route('educator.behavior')->with('error', 'Student not found');
        }

        // Get violation count
        $violationCount = \App\Models\Violation::where('student_id', $studentId)->count();

        return view('educator.student-behavior', [
            'student' => $student,
            'violationCount' => $violationCount
        ]);
    }

    /**
     * Get behavior data for a specific student
     *
     * @param Request $request
     * @param string $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentBehaviorData(Request $request, $studentId)
    {
        try {
            // Get the period (3, 6 or 12 months)
            $months = $request->input('months', 6);

            // Validate that months is one of the allowed values
            if (!in_array($months, [3, 6, 12])) {
                $months = 6; // Default to 6 months if invalid
            }

            // Find the student
            $student = User::where('student_id', $studentId)->first();
            if (!$student) {
                $student = User::where('id', $studentId)->first();
            }

            if (!$student) {
                throw new \Exception('Student not found');
            }

            // Use student_id for violation lookup, fallback to user id
            $lookupId = $student->student_id ?? $student->id;

            // Generate month labels and initialize scores
            $labels = [];
            $scoreData = [];

            // Generate last X months
            $startDate = now()->subMonths($months)->startOfMonth();
            $currentDate = clone $startDate;



            // Generate labels and default scores (all start at 100% level)
            while ($currentDate <= now()) {
                $labels[] = $currentDate->format('M Y');
                $scoreData[] = 100; // All months start at 100%
                $currentDate->addMonth();
            }

            \Illuminate\Support\Facades\Log::info('Processing student behavior chart', [
                'student_id' => $studentId,
                'lookup_id' => $lookupId,
                'student_name' => $student->name,
                'violations_count' => $violations->count(),
                'months' => $months,
                'current_score' => $currentScore
            ]);







            // Get the last violation date if any
            $lastViolationDate = null;
            if ($violations->count() > 0) {
                $lastViolation = $violations->sortByDesc('violation_date')->first();
                $lastViolationDate = $lastViolation ? \Carbon\Carbon::parse($lastViolation->violation_date)->format('M d, Y') : null;
            }

            \Illuminate\Support\Facades\Log::info('Final behavior chart data', [
                'student' => $student->name,
                'violations' => $violations->count(),
                'current_score' => $currentScore,
                'labels' => $labels,
                'scores' => $scoreData
            ]);

            // Return the data
            return response()->json([
                'labels' => $labels,
                'scoreData' => $scoreData,
                'currentScore' => $currentScore,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
                'violationsCount' => $violations->count(),
                'studentName' => $student->name,
                'studentId' => $student->student_id ?? $student->id,
                'lastViolationDate' => $lastViolationDate
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getStudentBehaviorData', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a valid response even on error
            $defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $defaultData = [100, 100, 100, 100, 100, 100];

            return response()->json([
                'error' => 'Could not load behavior data: ' . $e->getMessage(),
                'labels' => $defaultLabels,
                'scoreData' => $defaultData,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
                'violationsCount' => 0,
                'studentName' => 'Unknown Student',
                'studentId' => $studentId
            ]);
        }
    }

    /**
     * Convert penalty code to a human-readable name
     *
     * @param string $penalty The penalty code
     * @return string The full name of the penalty
     */
    protected function getPenaltyName($penalty)
    {
        switch ($penalty) {
            case 'W':
                return 'Warning';
            case 'VW':
                return 'Verbal Warning';
            case 'WW':
                return 'Written Warning';
            case 'Pro':
                return 'Probation';
            case 'Exp':
                return 'Expulsion';
            default:
                return 'Unknown Penalty';
        }
    }

    /**
     * Get behavior data by sex for the chart
     */
    protected function getBehaviorDataBySex($monthsToShow = 6)
    {
        // Initialize arrays and variables
        $labels = [];
        $menData = [];
        $womenData = [];
        $currentDate = now();
        $allMonths = [];
        $totalViolations = 0;
        
        try {
            // Generate months array based on requested period
            if ($monthsToShow == 12) {
                // Use all calendar months of the current year
                for ($month = 1; $month <= 12; $month++) {
                    $monthDate = Carbon::createFromDate($currentDate->year, $month, 1);
                    $allMonths[] = (object)[
                        'year' => $currentDate->year,
                        'month' => $month,
                        'date' => $monthDate,
                        'name' => $monthDate->format('F')
                    ];
                }
            } else {
                // Use relative months from current date
                $tempDate = now()->subMonths($monthsToShow - 1)->startOfMonth();
                for ($i = 0; $i < $monthsToShow; $i++) {
                    $allMonths[] = (object)[
                        'year' => $tempDate->year,
                        'month' => $tempDate->month,
                        'date' => $tempDate->copy(),
                        'name' => $tempDate->format('F')
                    ];
                    $tempDate->addMonth();
                }
            }

            // Process each month
            foreach ($allMonths as $monthData) {
                $monthNum = $monthData->month;
                $yearNum = $monthData->year;
                $labels[] = $monthData->name;

                // Get all violations from the database
                $allViolations = DB::table('violations')
                    ->where('status', 'active')
                    ->get();

                // Special handling for problematic months (February, April, June)
                if (in_array($monthData->name, ['February', 'April', 'June'])) {
                    \Log::info("Processing {$monthData->name} {$yearNum} - Total violations: {$allViolations->count()}");

                    // FORCE detection of violations for these months by directly querying the database
                    $forcedViolations = DB::table('violations')
                        ->where('status', 'active')
                        ->get();

                    // Log all violations for debugging
                    foreach ($forcedViolations as $violation) {
                        \Log::info("Checking violation ID: {$violation->id}, Date: {$violation->violation_date}, Sex: {$violation->sex}");

                        // Check if the date string contains the month name in any form
                        $violationDate = strtolower($violation->violation_date);
                        $monthLower = strtolower($monthData->name);
                        $monthShort = strtolower(substr($monthData->name, 0, 3)); // feb, jun, apr

                        if (strpos($violationDate, $monthLower) !== false ||
                            strpos($violationDate, $monthShort) !== false ||
                            strpos($violationDate, "{$monthNum}/") !== false ||
                            strpos($violationDate, "-{$monthNum}-") !== false) {

                            \Log::info("Found {$monthData->name} violation: {$violation->id}");
                            // Add this violation to our collection if it's not already there
                            $allViolations->push($violation);
                        }
                    }
                }

                // Filter men violations for this month with comprehensive date checking
                $menViolations = $allViolations->filter(function($violation) use ($monthData, $monthNum, $yearNum) {
                    // Check if this is a male/man violation
                    $isMale = in_array(strtolower($violation->sex), ['male', 'm', 'boy', 'man', 'men']);
                    if (!$isMale) {
                        return false;
                    }

                    // Get the violation date
                    $violationDate = $violation->violation_date;

                    // Check for month name in the date string
                    if (stripos($violationDate, $monthData->name) !== false) {
                        return true;
                    }

                    // Check for standard date format (YYYY-MM-DD)
                    try {
                        $date = new \DateTime($violationDate);
                        $dateMonth = (int)$date->format('n');
                        $dateYear = (int)$date->format('Y');

                        if ($dateMonth === $monthNum && $dateYear === $yearNum) {
                            return true;
                        }
                    } catch (\Exception $e) {
                        // Date parsing failed, continue with other checks
                    }

                    // Check for numeric formats (MM/DD/YYYY, MM-DD-YYYY)
                    $paddedMonth = str_pad($monthNum, 2, '0', STR_PAD_LEFT);
                    if (strpos($violationDate, "{$paddedMonth}/") !== false ||
                        strpos($violationDate, "{$paddedMonth}-") !== false) {
                        return true;
                    }

                    return false;
                });

                // Filter women violations for this month with comprehensive date checking
                $womenViolations = $allViolations->filter(function($violation) use ($monthData, $monthNum, $yearNum) {
                    // Check if this is a female/woman violation
                    $isFemale = in_array(strtolower($violation->sex), ['female', 'f', 'girl', 'woman', 'women']);
                    if (!$isFemale) {
                        return false;
                    }

                    // Get the violation date
                    $violationDate = $violation->violation_date;

                    // Check for month name in the date string
                    if (stripos($violationDate, $monthData->name) !== false) {
                        return true;
                    }

                    // Check for standard date format (YYYY-MM-DD)
                    try {
                        $date = new \DateTime($violationDate);
                        $dateMonth = (int)$date->format('n');
                        $dateYear = (int)$date->format('Y');

                        if ($dateMonth === $monthNum && $dateYear === $yearNum) {
                            return true;
                        }
                    } catch (\Exception $e) {
                        // Date parsing failed, continue with other checks
                    }

                    // Check for numeric formats (MM/DD/YYYY, MM-DD-YYYY)
                    $paddedMonth = str_pad($monthNum, 2, '0', STR_PAD_LEFT);
                    if (strpos($violationDate, "{$paddedMonth}/") !== false ||
                        strpos($violationDate, "{$paddedMonth}-") !== false) {
                        return true;
                    }

                    return false;
                });

                // Count the violations for this month
                $menCount = $menViolations->count();
                $womenCount = $womenViolations->count();
                
                // Log the counts for debugging
                \Log::info("Month {$monthData->name}: Men violations: {$menCount}, Women violations: {$womenCount}");
                
                // Add the counts to our data arrays
                $menData[] = $menCount;
                $womenData[] = $womenCount;
                
                // Add to total violations count
                $totalViolations += $menCount + $womenCount;
            }
            
            \Log::info("Total violations processed: {$totalViolations}");
            \Log::info("Men data: " . json_encode($menData));
            \Log::info("Women data: " . json_encode($womenData));

           
           
            return [
                'labels' => $labels,
                'men' => $menData,
                'women' => $womenData,
                'lastUpdated' => now()->format('Y-m-d H:i:s')
            ];
        } 
        catch (\Exception $e) {
            \Log::error('Error in getBehaviorDataBySex: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to load behavior data: ' . $e->getMessage(),
                'labels' => [],
                'men' => [],
                'women' => []
            ];
        }
    }

    /**
     * Show the form for editing the student manual
     */
    public function editManual()
    {
        // Get all offense categories with their violation types
        $categories = OffenseCategory::with(['violationTypes' => function($query) {
            $query->with('severityRelation');
        }])->get();
        
        return view('educator.editManual', compact('categories'));
    }

    /**
     * Update the student manual
     */
    public function updateManual(Request $request)
    {
        // Log the incoming request data
        Log::info('Manual update request received', ['data' => $request->all()]);
        
        DB::beginTransaction();
        try {
            $submittedCategoryIds = collect($request->input('categories', []))->pluck('id')->filter()->values();
            $existingCategoryIds = OffenseCategory::pluck('id');

            // Delete categories not in the submitted data
            OffenseCategory::whereIn('id', $existingCategoryIds->diff($submittedCategoryIds))->delete();

            // Update existing categories and their violations, and add new violations
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryData) {
                    if (isset($categoryData['id'])) {
                        // Existing category
                        $category = OffenseCategory::find($categoryData['id']);
                        if ($category) {
                            $category->category_name = $categoryData['category_name'];
                            $category->save();

                            $submittedViolationIds = collect($categoryData['violationTypes'] ?? [])->pluck('id')->filter()->values();
                            $existingViolationIds = $category->violationTypes()->pluck('id');

                            // Delete violations not in the submitted data for this category
                            $category->violationTypes()->whereIn('id', $existingViolationIds->diff($submittedViolationIds))->delete();

                            if (isset($categoryData['violationTypes'])) {
                                foreach ($categoryData['violationTypes'] as $violationData) {
                                    if (isset($violationData['id']) && !empty($violationData['id'])) {
                                        // Existing violation - preserve severity_id, only update violation_name
                                        $violation = ViolationType::find($violationData['id']);
                                        if ($violation) {
                                            $violation->violation_name = $violationData['violation_name'];
                                            $violation->save();
                                        }
                                    } else if (!empty($violationData['violation_name'])) {
                                        // New violation for existing category
                                        $newViolation = new ViolationType();
                                        $newViolation->offense_category_id = $category->id;
                                        $newViolation->violation_name = $violationData['violation_name'];
                                        $newViolation->severity_id = $violationData['severity_id'] ?? 2; // Default to Medium (ID: 2)
                                        $newViolation->default_penalty = 'VW'; // Default penalty
                                        $newViolation->save();
                                    }
                                }
                            }
                        }
                    } else {
                        // New category
                        if (!empty($categoryData['category_name'])) {
                            $newCategory = new OffenseCategory();
                            $newCategory->category_name = $categoryData['category_name'];
                            $newCategory->save();

                            // Add violations to new category
                            if (isset($categoryData['violationTypes'])) {
                                foreach ($categoryData['violationTypes'] as $violationData) {
                                    if (!empty($violationData['violation_name'])) {
                                        $newViolation = new ViolationType();
                                        $newViolation->offense_category_id = $newCategory->id;
                                        $newViolation->violation_name = $violationData['violation_name'];
                                        $newViolation->severity_id = $violationData['severity_id'] ?? 2; // Default to Medium (ID: 2)
                                        $newViolation->default_penalty = 'VW'; // Default penalty
                                        $newViolation->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Clear any cache
            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }
            
            DB::commit();
            Log::info('Manual updated successfully');
            return response()->json([
                'success' => true, 
                'message' => 'Manual updated successfully.',
                'redirect_url' => route('educator.manual')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating manual: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update manual: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handles the deletion of a violation type.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteViolationType(Request $request)
    {
        $violationTypeId = $request->input('violation_type_id');

        try {
            $violationType = ViolationType::findOrFail($violationTypeId);
            $violationType->delete();

            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }

            return response()->json(['success' => true, 'message' => 'Violation deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting violation type: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete violation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handles the deletion of an offense category.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOffenseCategory(Request $request)
    {
        $categoryId = $request->input('category_id');

        try {
            $category = OffenseCategory::findOrFail($categoryId);
            $category->delete();

            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }

            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check the total number of infractions for a given student.
     * This is used by the frontend to determine the infraction number.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkInfractionCount(Request $request)
    {
        $studentId = $request->query('student_id');
        $excludeId = $request->query('exclude_id'); // For editing, to exclude the current violation

        if (!$studentId) {
            return response()->json(['count' => 0, 'hasTermination' => false]);
        }

        \Log::info('Checking termination for student', ['student_id' => $studentId, 'exclude_id' => $excludeId]);

        // First check if student has any termination penalties (both 'T' and 'Exp')
        $query = Violation::where('student_id', $studentId)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('penalty', 'T')
                  ->orWhere('penalty', 'Exp');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Log the SQL query for debugging
        \Log::info('Termination check query', [
            'sql' => $query->toSql(), 
            'bindings' => $query->getBindings(),
            'violations' => $query->get()->toArray()
        ]);

        $hasTermination = $query->exists();
        
        \Log::info('Termination check result', [
            'student_id' => $studentId,
            'hasTermination' => $hasTermination
        ]);

        // If student has termination, return early with count 0 and termination flag
        if ($hasTermination) {
            return response()->json([
                'count' => 0,
                'hasTermination' => true,
                'message' => 'Student has an existing termination penalty'
            ]);
        }

        // If no termination, count total violations
        $query = Violation::where('student_id', $studentId)
            ->where('status', 'active');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();

        \Log::info('Violation count result', [
            'student_id' => $studentId,
            'count' => $count
        ]);

        return response()->json([
            'count' => $count,
            'hasTermination' => false
        ]);
    }
}






