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
    // =============================================
    // DASHBOARD METHODS
    // =============================================

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
    public function studentsByPenalty($penalty)
    {
        try {
            // Validate the penalty type
            $validPenalties = ['W', 'VW', 'WW', 'Pro', 'Exp'];
            if (!in_array($penalty, $validPenalties)) {
                return redirect()->route('educator.violation')
                    ->with('error', 'Invalid penalty type specified.');
            }

            // Get all active violations with the specified penalty
            $violations = \App\Models\Violation::where('penalty', $penalty)
                ->where('status', 'active')
                ->with(['student', 'violationType'])
                ->orderBy('violation_date', 'desc')
                ->get();

            return view('educator.studentsByPenalty', [
                'violations' => $violations,
                'penalty' => $penalty
            ]);
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

        // Get count of students with more than 2 violations
        $studentsWithMultipleViolations = User::role('student')
            ->select('users.id')
            ->join('violations', 'users.student_id', '=', 'violations.student_id')
            ->where('violations.status', 'active')
            ->groupBy('users.id')
            ->havingRaw('COUNT(violations.id) > 2')
            ->count();

        return view('educator.behavior', [
            'totalStudents' => $totalStudents,
            'studentsWithMultipleViolations' => $studentsWithMultipleViolations
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
            ->where(function($q) use ($query) {
                // Apply the same date filters as the main query
                if ($query->getQuery()->wheres) {
                    foreach ($query->getQuery()->wheres as $where) {
                        if (isset($where['column']) &&
                            (strpos($where['column'], 'created_at') !== false)) {
                            $q->where($where['column'], $where['operator'], $where['value']);
                        }
                    }
                }
            })
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

            // Calculate current score FIRST to use as baseline
            $violations = \App\Models\Violation::where('student_id', $lookupId)
                ->where('status', 'active')
                ->orderBy('violation_date')
                ->get();

            // Calculate overall current score based on ALL violations
            $currentScore = 100; // Start with perfect score

            // Apply deductions for ALL violations
            foreach ($violations as $violation) {
                $deduction = 10; // Default deduction
                $severity = strtolower(trim($violation->severity ?? ''));

                // Apply deduction based on severity
                if (strpos($severity, 'low') !== false) {
                    $deduction = 5;
                } elseif (strpos($severity, 'medium') !== false) {
                    $deduction = 10;
                } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                    $deduction = 15;
                } elseif (strpos($severity, 'very high') !== false) {
                    $deduction = 20;
                } else {
                    // Fallback: use penalty to determine deduction
                    switch ($violation->penalty) {
                        case 'W':
                            $deduction = 5;
                            break;
                        case 'VW':
                            $deduction = 10;
                            break;
                        case 'WW':
                            $deduction = 15;
                            break;
                        case 'Pro':
                        case 'Exp':
                            $deduction = 20;
                            break;
                        default:
                            $deduction = 10;
                    }
                }

                $currentScore = max(0, $currentScore - $deduction);
            }

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

            // Process each violation and apply deductions to the appropriate month
            foreach ($violations as $violation) {
                try {
                    // Parse the violation date
                    $violationDate = \Carbon\Carbon::parse($violation->violation_date);
                    $violationMonthLabel = $violationDate->format('M Y');

                    // Find the index of this month in our labels array
                    $monthIndex = array_search($violationMonthLabel, $labels);

                    if ($monthIndex !== false) {
                        // Determine the point deduction based on severity
                        $deduction = 10; // Default deduction
                        $severity = strtolower(trim($violation->severity ?? ''));

                        // Apply deduction based on severity
                        if (strpos($severity, 'low') !== false) {
                            $deduction = 5;
                        } elseif (strpos($severity, 'medium') !== false) {
                            $deduction = 10;
                        } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                            $deduction = 15;
                        } elseif (strpos($severity, 'very high') !== false) {
                            $deduction = 20;
                        } else {
                            // Fallback: use penalty to determine deduction
                            switch ($violation->penalty) {
                                case 'W':
                                    $deduction = 5;
                                    break;
                                case 'VW':
                                    $deduction = 10;
                                    break;
                                case 'WW':
                                    $deduction = 15;
                                    break;
                                case 'Pro':
                                case 'Exp':
                                    $deduction = 20;
                                    break;
                                default:
                                    $deduction = 10;
                            }
                        }

                        // Apply the deduction to the specific month (deduct from 100% baseline)
                        $scoreData[$monthIndex] = max(0, $scoreData[$monthIndex] - $deduction);
                    }
                } catch (\Exception $vEx) {
                    continue;
                }
            }







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
        try {
            $labels = [];
            $menData = [];
            $womenData = [];
            $currentDate = now();
            $allMonths = [];
            $totalViolations = 0;

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

                // Calculate scores
                $menScore = 100;
                $womenScore = 100;

                // Special handling for problematic months
                if (in_array($monthData->name, ['February', 'June']) && $menViolations->count() > 0) {
                    \Log::info("Found {$menViolations->count()} men violations for {$monthData->name}");
                    \Log::info("Applying special handling for {$monthData->name}");

                    // For these months, ensure we apply at least some deduction if violations exist
                    $hasDeduction = false;

                    // We'll check if any deductions will be applied in the normal processing
                    foreach ($menViolations as $violation) {
                        $severity = strtolower($violation->severity ?? '');
                        if (!empty($severity) || !empty($violation->penalty)) {
                            $hasDeduction = true;
                            break;
                        }
                    }

                    // If no deductions would be applied, force a minimum deduction
                    if (!$hasDeduction) {
                        \Log::info("Forcing minimum deduction for {$monthData->name}");
                        $menScore -= 15; // Apply a default medium deduction
                    }
                }

                // Process men violations normally
                foreach ($menViolations as $violation) {
                    // Log each violation for debugging
                    \Log::info("Processing violation ID: {$violation->id}, Date: {$violation->violation_date}, Severity: {$violation->severity}");

                    $severity = strtolower($violation->severity ?? '');

                    if (strpos($severity, 'low') !== false) {
                        $menScore -= 5; // Low severity
                        \Log::info("Applied -5 deduction for Low severity");
                    } elseif (strpos($severity, 'medium') !== false) {
                        $menScore -= 10; // Medium severity
                        \Log::info("Applied -10 deduction for Medium severity");
                    } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                        $menScore -= 15; // High severity
                        \Log::info("Applied -15 deduction for High severity");
                    } elseif (strpos($severity, 'very high') !== false) {
                        $menScore -= 20; // Very High severity
                        \Log::info("Applied -20 deduction for Very High severity");
                    } else {
                        // Default deductions based on penalty
                        if ($violation->penalty == 'VW') {
                            $menScore -= 10; // Verbal Warning
                            \Log::info("Applied -10 deduction for Verbal Warning");
                        } elseif ($violation->penalty == 'WW') {
                            $menScore -= 15; // Written Warning
                            \Log::info("Applied -15 deduction for Written Warning");
                        } elseif ($violation->penalty == 'Pro' || $violation->penalty == 'Exp') {
                            $menScore -= 20; // Probation or Expulsion
                            \Log::info("Applied -20 deduction for {$violation->penalty}");
                        } else {
                            $menScore -= 10; // Default
                            \Log::info("Applied -10 default deduction");
                        }
                    }
                }

                // Apply similar logic for women violations
                if (in_array($monthData->name, ['February', 'June']) && $womenViolations->count() > 0) {
                    \Log::info("Found {$womenViolations->count()} women violations for {$monthData->name}");

                    $hasDeduction = false;
                    foreach ($womenViolations as $violation) {
                        $severity = strtolower($violation->severity ?? '');
                        if (!empty($severity) || !empty($violation->penalty)) {
                            $hasDeduction = true;
                            break;
                        }
                    }

                    if (!$hasDeduction) {
                        $womenScore -= 15;
                    }
                }

                // Process women violations normally
                foreach ($womenViolations as $violation) {
                    $severity = strtolower($violation->severity ?? '');

                    if (strpos($severity, 'low') !== false) {
                        $womenScore -= 5;
                    } elseif (strpos($severity, 'medium') !== false) {
                        $womenScore -= 10;
                    } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                        $womenScore -= 15;
                    } elseif (strpos($severity, 'very high') !== false) {
                        $womenScore -= 20;
                    } else {
                        // Default deductions based on penalty
                        if ($violation->penalty == 'VW') {
                            $womenScore -= 10;
                        } elseif ($violation->penalty == 'WW') {
                            $womenScore -= 15;
                        } elseif ($violation->penalty == 'Pro' || $violation->penalty == 'Exp') {
                            $womenScore -= 20;
                        } else {
                            $womenScore -= 10;
                        }
                    }
                }

                // Ensure scores are within 0-100 range
                $menScore = max(0, min(100, $menScore));
                $womenScore = max(0, min(100, $womenScore));

                // Add scores to data arrays
                $menData[] = $menScore;
                $womenData[] = $womenScore;
            }

            return [
                'labels' => $labels,
                'men' => $menData,
                'women' => $womenData,
                'lastUpdated' => now()->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Failed to load behavior data: ' . $e->getMessage(),
                'labels' => [],
                'boys' => [],
                'girls' => []
            ];
        }
    }

    /**
     * Show the form for editing the student manual
     */
    public function editManual()
    {
        // Get all offense categories with their violation types
        $categories = OffenseCategory::with('violationTypes')->get();
        
        return view('educator.editManual', compact('categories'));
    }

    /**
     * Update the student manual
     */
    public function updateManual(Request $request)
    {
        // Log the incoming request data
        Log::info('Manual update request received', ['data' => $request->all()]);
        
        try {
            // Update existing categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryIndex => $categoryData) {
                    if (isset($categoryData['id'])) {
                        $category = OffenseCategory::find($categoryData['id']);
                        if ($category) {
                            $category->category_name = $categoryData['category_name'];
                            $category->save();
                            
                            // Update existing violations
                            if (isset($categoryData['violationTypes'])) {
                                foreach ($categoryData['violationTypes'] as $violationData) {
                                    if (isset($violationData['id'])) {
                                        $violation = ViolationType::find($violationData['id']);
                                        if ($violation) {
                                            $violation->violation_name = $violationData['violation_name'];
                                            $violation->default_penalty = $violationData['default_penalty'] ?? 'W';
                                            $violation->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Keep the code for adding new categories
            if ($request->has('new_category') && !empty($request->input('new_category.name'))) {
                $newCategory = new OffenseCategory();
                $newCategory->category_name = $request->input('new_category.name');
                $newCategory->save();
                
                // Add violations to new category
                if (isset($request->new_category['violations'])) {
                    foreach ($request->new_category['violations'] as $violationData) {
                        if (!empty($violationData['name'])) {
                            $newViolation = new ViolationType();
                            $newViolation->offense_category_id = $newCategory->id;
                            $newViolation->violation_name = $violationData['name'];
                            $newViolation->default_penalty = $violationData['default_penalty'] ?? 'W';
                            $newViolation->save();
                        }
                    }
                }
            }
            
            // Clear any cache
            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }
            
            Log::info('Manual updated successfully');
            return redirect()->route('student.manual')->with('success', 'Manual updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating manual: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update manual: ' . $e->getMessage());
        }
    }
    

}






