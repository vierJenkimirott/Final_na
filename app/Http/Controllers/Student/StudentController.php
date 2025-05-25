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

                        // Apply deduction based on severity
                        if (strpos($severity, 'very high') !== false || $severity === 'very high') {
                            $deduction = 20; // Very High severity: -20 points
                        } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                            $deduction = 15; // High severity: -15 points
                        } elseif (strpos($severity, 'medium') !== false) {
                            $deduction = 10; // Medium severity: -10 points
                        } elseif (strpos($severity, 'low') !== false) {
                            $deduction = 5; // Low severity: -5 points
                        } else {
                            // Fallback: use penalty to determine deduction if severity is still missing
                            $penalty = trim($violation->penalty ?? '');
                            switch ($penalty) {
                                case 'W':
                                    $deduction = 5; // Warning
                                    break;
                                case 'VW':
                                    $deduction = 10; // Verbal Warning
                                    break;
                                case 'WW':
                                    $deduction = 15; // Written Warning
                                    break;
                                case 'Pro':
                                case 'Exp':
                                    $deduction = 20; // Probation or Expulsion
                                    break;
                                default:
                                    $deduction = 10; // Default deduction for unknown severity/penalty
                            }
                        }

                        // Apply the deduction to the specific month
                        $scoreData[$monthIndex] = max(0, $scoreData[$monthIndex] - $deduction);

                        \Illuminate\Support\Facades\Log::info('Applied violation deduction', [
                            'violation_id' => $violation->id,
                            'violation_date' => $violation->violation_date,
                            'month_label' => $violationMonthLabel,
                            'month_index' => $monthIndex,
                            'severity' => $violation->severity,
                            'penalty' => $violation->penalty,
                            'deduction' => $deduction,
                            'new_score' => $scoreData[$monthIndex]
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

            // Helper function to calculate deduction for a violation (same as educator controller)
            $calculateDeduction = function($violation) {
                $deduction = 10; // Default deduction
                $severity = strtolower(trim($violation->severity ?? ''));

                // Apply deduction based on severity - Violation Impact: Low = -5 points | Medium = -10 points | High = -15 points | Very High = -20 points
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
                return $deduction;
            };

            // Calculate overall current score based on ALL violations
            $currentScore = 100; // Start with perfect score

            // Apply deductions for ALL violations using the same calculation function
            foreach ($violations as $violation) {
                $currentScore = max(0, $currentScore - $calculateDeduction($violation));
            }

            // Get timestamp for update checking
            $lastUpdate = time();

            // Final log of the data being sent to the chart
            \Illuminate\Support\Facades\Log::info('Final behavior data being sent to chart', [
                'labels' => $labels,
                'scoreData' => $scoreData,
                'current_score' => $currentScore,
                'months_selected' => $months,
                'violations_count' => $violations->count()
            ]);

            return response()->json([
                'labels' => $labels,
                'scoreData' => $scoreData,
                'currentScore' => $currentScore,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
                'lastUpdate' => $lastUpdate,
                'violationsCount' => $violations->count()
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