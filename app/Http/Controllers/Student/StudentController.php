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
        
        // Get additional data that might be needed for the dashboard
        $data = [
            'user' => $user,

            'status' => 'Good Standing', // This could be fetched from a database in a real application
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
            
            // Generate sample data for testing
            $labels = [];
            $scoreData = [];
            
            // Generate last X months
            $startDate = now()->subMonths($months)->startOfMonth();
            $currentDate = clone $startDate;
            
            // Generate labels and default scores
            while ($currentDate <= now()) {
                $labels[] = $currentDate->format('M Y'); // Include year for clarity
                $scoreData[] = 100; // Start with perfect score
                $currentDate->addMonth();
            }
            
            // Get ALL violations for this student from the violation history
            $violations = [];
            try {
                \Illuminate\Support\Facades\Log::info('Fetching ALL violations from violation history for student', [
                    'student_id' => $studentId
                ]);
                
                // Get all violations for this student without date filtering
                // This ensures we capture the complete violation history
                $violations = \App\Models\Violation::where('student_id', $studentId)
                    ->where('status', '!=', 'deleted') // Only include active violations
                    ->orderBy('violation_date')
                    ->get();
                
                \Illuminate\Support\Facades\Log::info('Found violations in history', [
                    'count' => $violations->count(),
                    'student_id' => $studentId
                ]);
                
                // If no violations found, check if we need to look up by user ID instead
                if ($violations->count() == 0 && isset($user->id)) {
                    \Illuminate\Support\Facades\Log::info('No violations found by student_id, trying user_id', [
                        'user_id' => $user->id
                    ]);
                    
                    $violations = \App\Models\Violation::where('student_id', $user->id)
                        ->where('status', '!=', 'deleted')
                        ->orderBy('violation_date')
                        ->get();
                        
                    \Illuminate\Support\Facades\Log::info('Found violations by user_id', [
                        'count' => $violations->count()
                    ]);
                }
                
                // Detailed logging of each violation from history
                foreach ($violations as $index => $violation) {
                    \Illuminate\Support\Facades\Log::info("Violation #{$index} from history", [
                        'id' => $violation->id,
                        'date' => $violation->violation_date,
                        'severity' => $violation->severity ?? 'unknown',
                        'penalty' => $violation->penalty ?? 'none',
                        'status' => $violation->status ?? 'unknown'
                    ]);
                }
            } catch (\Exception $dbEx) {
                // Log database error but continue with empty violations
                \Illuminate\Support\Facades\Log::error('Database error fetching violations from history', [
                    'error' => $dbEx->getMessage(),
                    'trace' => $dbEx->getTraceAsString()
                ]);
            }
            
            // Process violations from the student's history and apply score reductions
            foreach ($violations as $violation) {
                try {
                    // Skip violations that aren't active
                    if (isset($violation->status) && strtolower($violation->status) !== 'active' && strtolower($violation->status) !== 'pending') {
                        \Illuminate\Support\Facades\Log::info('Skipping inactive violation', [
                            'violation_id' => $violation->id,
                            'status' => $violation->status
                        ]);
                        continue;
                    }
                    
                    // Parse the violation date
                    $violationDate = \Carbon\Carbon::parse($violation->violation_date);
                    
                    // Calculate which month this violation falls into (relative to our start date)
                    // This will be negative for violations before our start date
                    $monthDiff = $violationDate->diffInMonths($startDate, false);
                    
                    // Convert to a positive index for our arrays
                    $monthIndex = max(0, $monthDiff);
                    
                    // Skip if month index is out of range (future months)
                    if ($monthIndex >= count($scoreData)) {
                        \Illuminate\Support\Facades\Log::info('Skipping violation - out of range', [
                            'violation_id' => $violation->id,
                            'date' => $violation->violation_date,
                            'monthIndex' => $monthIndex,
                            'scoreDataCount' => count($scoreData)
                        ]);
                        continue;
                    }
                    
                    // Determine reduction based on severity or penalty from violation history
                    $reduction = 5; // Default reduction for low severity
                    
                    // Check severity field first
                    if (!empty($violation->severity)) {
                        $severity = strtolower(trim($violation->severity));
                        if ($severity === 'medium') $reduction = 10;
                        elseif ($severity === 'high') $reduction = 15;
                        elseif ($severity === 'very high') $reduction = 20;
                        
                        \Illuminate\Support\Facades\Log::info('Using severity from violation history', [
                            'violation_id' => $violation->id,
                            'severity' => $severity,
                            'reduction' => $reduction
                        ]);
                    } 
                    // If no severity or it's empty, check penalty
                    elseif (!empty($violation->penalty)) {
                        $penalty = trim($violation->penalty);
                        if (strpos(strtolower($penalty), 'verbal warning') !== false) $reduction = 5;
                        elseif (strpos(strtolower($penalty), 'written warning') !== false || $penalty === 'WW') $reduction = 10;
                        elseif (strpos(strtolower($penalty), 'probation') !== false || $penalty === 'Pro') $reduction = 15;
                        elseif (strpos(strtolower($penalty), 'expulsion') !== false || $penalty === 'Exp') $reduction = 20;
                        
                        \Illuminate\Support\Facades\Log::info('Using penalty from violation history', [
                            'violation_id' => $violation->id,
                            'penalty' => $penalty,
                            'reduction' => $reduction
                        ]);
                    }
                    
                    // Check if we need to look at violation type for additional context
                    if ($reduction === 5 && !empty($violation->violation_type_id)) {
                        try {
                            $violationType = \App\Models\ViolationType::find($violation->violation_type_id);
                            if ($violationType && !empty($violationType->severity)) {
                                $typeSeverity = strtolower(trim($violationType->severity));
                                $typeReduction = 5;
                                
                                if ($typeSeverity === 'medium') $typeReduction = 10;
                                elseif ($typeSeverity === 'high') $typeReduction = 15;
                                elseif ($typeSeverity === 'very high') $typeReduction = 20;
                                
                                // Use the higher reduction
                                if ($typeReduction > $reduction) {
                                    $reduction = $typeReduction;
                                    \Illuminate\Support\Facades\Log::info('Using violation type severity', [
                                        'violation_id' => $violation->id,
                                        'type_id' => $violation->violation_type_id,
                                        'type_severity' => $typeSeverity,
                                        'reduction' => $reduction
                                    ]);
                                }
                            }
                        } catch (\Exception $typeEx) {
                            // Just log and continue if we can't get the violation type
                            \Illuminate\Support\Facades\Log::warning('Error getting violation type', [
                                'error' => $typeEx->getMessage()
                            ]);
                        }
                    }
                    
                    // Log the reduction being applied
                    \Illuminate\Support\Facades\Log::info('Applying reduction', [
                        'violation_id' => $violation->id,
                        'month_index' => $monthIndex,
                        'month' => $labels[$monthIndex],
                        'reduction' => $reduction,
                        'before_score' => $scoreData[$monthIndex]
                    ]);
                    
                    // Apply reduction
                    $scoreData[$monthIndex] = max(0, $scoreData[$monthIndex] - $reduction);
                    
                    // Log the score after reduction
                    \Illuminate\Support\Facades\Log::info('After reduction', [
                        'after_score' => $scoreData[$monthIndex]
                    ]);
                } catch (\Exception $vEx) {
                    // Log the error and skip this violation
                    \Illuminate\Support\Facades\Log::error('Error processing violation', [
                        'violation_id' => $violation->id ?? 'unknown',
                        'error' => $vEx->getMessage()
                    ]);
                    continue;
                }
            }
            
            // Log scores before propagation
            \Illuminate\Support\Facades\Log::info('Scores before propagation', [
                'scores' => $scoreData,
                'labels' => $labels
            ]);
            
            // Log scores after direct violations
            \Illuminate\Support\Facades\Log::info('Scores after direct violations:', [
                'scores' => $scoreData,
                'labels' => $labels
            ]);
            
            // Create an array to track which months have direct violations
            $monthsWithViolations = array_fill(0, count($scoreData), false);
            
            // Mark months that have direct violations
            foreach ($violations as $violation) {
                try {
                    $violationDate = \Carbon\Carbon::parse($violation->violation_date);
                    $monthDiff = $violationDate->diffInMonths($startDate, false);
                    $monthIndex = max(0, $monthDiff);
                    
                    if ($monthIndex < count($monthsWithViolations)) {
                        $monthsWithViolations[$monthIndex] = true;
                        \Illuminate\Support\Facades\Log::info("Month with violation: {$labels[$monthIndex]}");
                    }
                } catch (\Exception $e) {
                    // Skip this violation if there's an error
                    continue;
                }
            }
            
            // Log which months have violations
            \Illuminate\Support\Facades\Log::info('Months with violations:', [
                'months' => $monthsWithViolations
            ]);
            
            // Reset scores to 100 for months without violations
            // This is the new behavior requested by the user
            for ($i = 0; $i < count($scoreData); $i++) {
                if (!$monthsWithViolations[$i]) {
                    // If this month has no direct violations, reset to 100
                    \Illuminate\Support\Facades\Log::info("Resetting score for {$labels[$i]} to 100 (no violations)");
                    $scoreData[$i] = 100;
                }
            }
            
            // Log scores after propagation
            \Illuminate\Support\Facades\Log::info('Scores after propagation', [
                'scores' => $scoreData
            ]);
            
            // Get timestamp for update checking
            $lastUpdate = time();
            
            return response()->json([
                'labels' => $labels,
                'scoreData' => $scoreData,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
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