<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function __construct()
    {
        // Constructor
    }


    /**
     * Display the student dashboard
     */
    public function dashboard()
    {
        return view('student.dashboard', ['user' => auth()->user()]);
    }

    /**
     * Display the student account information
     */
    public function account()
    {
        $user = Auth::user();

        // Check if student has any violations to determine status
        $studentId = $user->student_id ?? $user->id;
        $violationCount = \App\Models\Violation::where('student_id', $studentId)
            ->where('status', 'active')
            ->count();

        // Get additional data that might be needed for the dashboard
        $data = [
            'user' => $user,
            'status' => $violationCount > 0 ? 'Needs Attention' : 'Good Standing',
            'studentId' => 'STU-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
            'gradeLevel' => '11', // This could be fetched from a database in a real application
        ];

        return view('student.dashboard', $data);
    }

    public function violation()
    {
        // Get the authenticated student's ID
        $studentId = auth()->user()->student_id;

        // Fetch the student's violations with related data
        $violations = \App\Models\Violation::with(['violationType', 'violationType.offenseCategory'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($violation) {
                // Format the violation data for the view
                return (object) [
                    'id' => $violation->id,
                    'violation_name' => $violation->violationType->violation_name ?? 'Unknown',
                    'category_name' => $violation->violationType->offenseCategory->category_name ?? 'Unknown',
                    'violation_date' => $violation->violation_date,
                    'severity' => $violation->severity,
                    'offense' => $violation->offense,
                    'penalty' => $violation->penalty,
                    'consequence' => $violation->consequence,
                    'status' => $violation->status,
                    'created_at' => $violation->created_at
                ];
            });

        return view('student.violation', compact('violations'));
    }

    public function behavior(Request $request)
    {
        // Get the period from the request or default to 6 months
        $months = $request->input('months', 6);

        // Validate that months is one of the allowed values (3, 6, or 12)
        if (!in_array($months, [3, 6, 12])) {
            $months = 6; // Default to 6 months if invalid
        }

        return view('student.behavior', compact('months'));
    }

    /**
     * Get behavior data for the chart
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBehaviorData(Request $request)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            // Get student ID with fallback
            $studentId = $user->student_id ?? $user->id;

            // Get the period (3, 6 or 12 months)
            $months = $request->input('months', 6);

            // Validate that months is one of the allowed values
            if (!in_array($months, [3, 6, 12])) {
                $months = 6; // Default to 6 months if invalid
            }

            \Illuminate\Support\Facades\Log::info('Generating behavior data', [
                'student_id' => $studentId,
                'months' => $months
            ]);

            // Generate month labels and initialize scores
            $labels = [];
            $scoreData = [];

            // Generate last X months
            $startDate = now()->subMonths($months)->startOfMonth();
            $currentDate = clone $startDate;
            $currentMonth = now();

            // Generate labels and default scores
            while ($currentDate <= $currentMonth) {
                $labels[] = $currentDate->format('M Y');
                // Future months get 100%, past and current months start at 100% and will be adjusted for violations
                $scoreData[] = 100;
                $currentDate->addMonth();
            }

            \Illuminate\Support\Facades\Log::info('Generated labels', [
                'labels' => $labels,
                'months' => $months
            ]);

            // Get ALL violations for this student from the violation history
            // Try multiple approaches to find violations
            $violations = \App\Models\Violation::where('student_id', $studentId)
                ->where('status', 'active')
                ->orderBy('violation_date')
                ->get();

            // If no violations found, try without status filter (in case status is different)
            if ($violations->count() === 0) {
                $violations = \App\Models\Violation::where('student_id', $studentId)
                    ->whereNotIn('status', ['deleted', 'cancelled'])
                    ->orderBy('violation_date')
                    ->get();
            }

            // If still no violations and user has an ID, try using user ID as student_id
            if ($violations->count() === 0 && $studentId !== $user->id) {
                $violations = \App\Models\Violation::where('student_id', $user->id)
                    ->where('status', 'active')
                    ->orderBy('violation_date')
                    ->get();
            }

            \Illuminate\Support\Facades\Log::info('Found violations for student', [
                'user_id' => $user->id,
                'student_id' => $studentId,
                'violations_count' => $violations->count(),
                'search_attempts' => ['active_status', 'non_deleted', 'user_id_fallback']
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
                        $deduction = 0;
                        $severity = strtolower(trim($violation->severity ?? ''));

                        // Enhanced severity detection
                        if (empty($severity) || $severity === 'null') {
                            // If no severity, try to get it from violation type
                            try {
                                if ($violation->violation_type_id) {
                                    $violationType = \App\Models\ViolationType::find($violation->violation_type_id);
                                    if ($violationType && !empty($violationType->severity)) {
                                        $severity = strtolower(trim($violationType->severity));
                                    }
                                }
                            } catch (\Exception $typeEx) {
                                // Continue with penalty-based detection
                            }
                        }

                        // Simplified severity tracking without points
                        $severity = strtolower(trim($severity));
                        $deduction = 0; // No longer using points-based deduction
                        
                        // Just record the severity level for tracking purposes
                        if (strpos($severity, 'very high') !== false || $severity === 'very high') {
                            $severityLevel = 'very high';
                        } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                            $severityLevel = 'high';
                        } elseif (strpos($severity, 'medium') !== false) {
                            $severityLevel = 'medium';
                        } elseif (strpos($severity, 'low') !== false) {
                            $severityLevel = 'low';
                        } else {
                            // Fallback: use penalty to determine severity if missing
                            $penalty = trim($violation->penalty ?? '');
                            switch ($penalty) {
                                case 'W':
                                    $severityLevel = 'low';
                                    break;
                                case 'VW':
                                    $severityLevel = 'medium'; // Verbal Warning
                                    break;
                                case 'WW':
                                    $severityLevel = 'high'; // Written Warning
                                    break;
                                case 'Pro':
                                case 'Exp':
                                    $severityLevel = 'very high'; // Probation or Expulsion
                                    break;
                                default:
                                    $severityLevel = 'medium'; // Default severity for unknown penalty
                            }
                        }

                        // Record the violation without applying a points-based deduction
                        // We're tracking violations but not using the points system anymore
                        
                        \Illuminate\Support\Facades\Log::info('Recorded violation', [
                            'violation_id' => $violation->id,
                            'violation_date' => $violation->violation_date,
                            'month_label' => $violationMonthLabel,
                            'month_index' => $monthIndex,
                            'severity' => $violation->severity,
                            'severity_level' => $severityLevel,
                            'penalty' => $violation->penalty
                        ]);
                    }
                } catch (\Exception $vEx) {
                    \Illuminate\Support\Facades\Log::error('Error processing violation', [
                        'violation_id' => $violation->id ?? 'unknown',
                        'error' => $vEx->getMessage()
                    ]);
                    continue;
                }
            }

            // Helper function to determine severity level for a violation
            $determineSeverityLevel = function($violation) {
                $severityLevel = 'medium'; // Default severity level
                $severity = strtolower(trim($violation->severity ?? ''));

                // Determine severity level based on the violation severity
                if (strpos($severity, 'low') !== false) {
                    $severityLevel = 'low';
                } elseif (strpos($severity, 'medium') !== false) {
                    $severityLevel = 'medium';
                } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                    $severityLevel = 'high';
                } elseif (strpos($severity, 'very high') !== false) {
                    $severityLevel = 'very high';
                } else {
                    // Fallback: use penalty to determine severity level
                    switch ($violation->penalty) {
                        case 'W':
                            $severityLevel = 'low';
                            break;
                        case 'VW':
                            $severityLevel = 'medium';
                            break;
                        case 'WW':
                            $severityLevel = 'high';
                            break;
                        case 'Pro':
                        case 'Exp':
                            $severityLevel = 'very high';
                            break;
                    }
                }

                return $severityLevel;
            };

            // Count violations by severity instead of calculating a points-based score
            $violationCounts = [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'very_high' => 0
            ];
            
            // Count violations by severity level
            foreach ($violations as $violation) {
                $severityLevel = $determineSeverityLevel($violation);
                $key = str_replace(' ', '_', $severityLevel);
                if (isset($violationCounts[$key])) {
                    $violationCounts[$key]++;
                }
            }
            
            // For backward compatibility, still provide a score value
            // but it's now based on the count of violations rather than points
            $currentScore = 100; // Start with perfect score

            // Get timestamp for update checking
            $lastUpdate = time();

            // Final log of the data being sent to the chart
            \Illuminate\Support\Facades\Log::info('Final behavior data being sent to chart', [
                'labels' => $labels,
                'violation_counts' => $violationCounts,
                'months_selected' => $months,
                'total_violations' => $violations->count()
            ]);

            return response()->json([
                'labels' => $labels,
                'violationCounts' => $violationCounts,
                'totalViolations' => $violations->count(),
                'lastUpdate' => $lastUpdate
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in behavior data', [
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
                'yAxisStep' => 10
            ]);
        }
    }

    /**
     * Check if there are new violations for the student since the last check
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkForViolationUpdates(Request $request)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            // Get student ID with fallback
            $studentId = $user->student_id ?? $user->id;

            // Get the timestamp of the last check
            $lastCheck = $request->input('lastCheck', 0);

            // Create a proper date object for comparison
            $lastCheckDate = $lastCheck > 0 ? date('Y-m-d H:i:s', $lastCheck) : '1970-01-01 00:00:00';

            \Illuminate\Support\Facades\Log::info("Checking for violations since: {$lastCheckDate} for student {$studentId}");

            // Initialize counters
            $newViolations = 0;
            $updatedViolations = 0;

            try {
                // Check if there are any new violations since the last check
                $newViolations = \App\Models\Violation::where('student_id', $studentId)
                    ->where('created_at', '>', $lastCheckDate)
                    ->count();

                // Also check for violations with the same date but updated recently
                $updatedViolations = \App\Models\Violation::where('student_id', $studentId)
                    ->where('updated_at', '>', $lastCheckDate)
                    ->where('created_at', '<=', $lastCheckDate)
                    ->count();

                $totalNewViolations = $newViolations + $updatedViolations;

                if ($totalNewViolations > 0) {
                    \Illuminate\Support\Facades\Log::info("Found {$totalNewViolations} new/updated violations for student {$studentId}");
                }

                // Get current timestamp for update checking
                $lastUpdate = time();

                return response()->json([
                    'hasUpdates' => ($newViolations > 0 || $updatedViolations > 0),
                    'newViolationsCount' => $totalNewViolations,
                    'lastUpdate' => $lastUpdate
                ]);

            } catch (\Exception $dbEx) {
                // Log database error but continue
                \Illuminate\Support\Facades\Log::error('Database error checking for violation updates', [
                    'error' => $dbEx->getMessage()
                ]);

                // Return a valid response even on database error
                return response()->json([
                    'hasUpdates' => false,
                    'newViolationsCount' => 0,
                    'lastUpdate' => time(),
                    'error' => $dbEx->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking for violation updates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a valid response even on error
            return response()->json([
                'hasUpdates' => false,
                'newViolationsCount' => 0,
                'lastUpdate' => time(),
                'error' => $e->getMessage()
            ]);
        }
    }


}