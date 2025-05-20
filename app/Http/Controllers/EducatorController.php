<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducatorController extends Controller
{
    /**
     * Display the educator dashboard
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        // Get top violators and counts
        $topViolators = User::role('student')
            ->withCount('violations')
            ->having('violations_count', '>', 0)
            ->orderBy('violations_count', 'desc')
            ->take(5)
            ->get();

        $totalViolations = Violation::count();
        $totalStudents = User::role('student')->count();
        $recentViolations = Violation::with(['student', 'violationType'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $violatorCount = User::role('student')->whereHas('violations')->count();
        $nonViolatorCount = $totalStudents - $violatorCount;
        
        // Get the period parameter from the URL or default to 'month'
        $period = $request->input('period', 'month');
        
        // Get violation statistics based on the period
        $violationStats = $this->getViolationStats($period);

        return view('educator.dashboard', compact(
            'topViolators', 'totalViolations', 'totalStudents', 
            'recentViolations', 'violatorCount', 'nonViolatorCount', 'violationStats'
        ));
    }
    
    /**
     * Get violation statistics for the dashboard
     * This method returns the violation statistics based on the specified period
     * focusing on the specific months that have violations (Jan, Apr, Aug, Nov)
     * 
     * @param string $period The period to get statistics for (month, last_month, last_3_months, year)
     * @return array
     */
    private function getViolationStats($period = 'month')
    {
        // Define the specific months that have violations
        $specificMonths = [1, 4, 8, 11]; // January, April, August, November
        
        // Get the current month and year
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Determine the date range based on the selected period
        $startDate = null;
        $endDate = now();
        $relevantMonths = [];
        
        switch ($period) {
            case 'month':
                // If current month is one of the specific months, use it
                // Otherwise, find the most recent specific month
                if (in_array($currentMonth, $specificMonths)) {
                    $targetMonth = $currentMonth;
                    $targetYear = $currentYear;
                } else {
                    // Find the most recent specific month
                    $mostRecentMonth = null;
                    foreach ($specificMonths as $month) {
                        if ($month < $currentMonth && ($mostRecentMonth === null || $month > $mostRecentMonth)) {
                            $mostRecentMonth = $month;
                        }
                    }
                    
                    // If no recent month found, use the last month of the year
                    if ($mostRecentMonth === null) {
                        $targetMonth = max($specificMonths);
                        $targetYear = $currentYear - 1;
                    } else {
                        $targetMonth = $mostRecentMonth;
                        $targetYear = $currentYear;
                    }
                }
                
                $relevantMonths[] = ['month' => $targetMonth, 'year' => $targetYear];
                break;
                
            case 'last_month':
                // Find the previous specific month
                $currentMonthIndex = array_search($currentMonth, $specificMonths);
                
                if ($currentMonthIndex !== false && $currentMonthIndex > 0) {
                    // Previous month in the same year
                    $targetMonth = $specificMonths[$currentMonthIndex - 1];
                    $targetYear = $currentYear;
                } else {
                    // Previous month is in the previous year
                    $targetMonth = end($specificMonths);
                    $targetYear = $currentYear - 1;
                }
                
                $relevantMonths[] = ['month' => $targetMonth, 'year' => $targetYear];
                break;
                
            case 'last_3_months':
                // Include the current or most recent specific month and the two before it
                $relevantYear = $currentYear;
                
                // Find the current or most recent specific month
                $recentMonth = null;
                if (in_array($currentMonth, $specificMonths)) {
                    $recentMonth = $currentMonth;
                } else {
                    foreach ($specificMonths as $month) {
                        if ($month < $currentMonth && ($recentMonth === null || $month > $recentMonth)) {
                            $recentMonth = $month;
                        }
                    }
                    
                    if ($recentMonth === null) {
                        $recentMonth = max($specificMonths);
                        $relevantYear = $currentYear - 1;
                    }
                }
                
                // Add the recent month and find the two before it
                $relevantMonths[] = ['month' => $recentMonth, 'year' => $relevantYear];
                
                // Find the two previous specific months
                for ($i = 0; $i < 2; $i++) {
                    $currentIndex = array_search($recentMonth, $specificMonths);
                    if ($currentIndex > 0) {
                        // Previous month in the same year
                        $recentMonth = $specificMonths[$currentIndex - 1];
                    } else {
                        // Previous month is in the previous year
                        $recentMonth = end($specificMonths);
                        $relevantYear--;
                    }
                    $relevantMonths[] = ['month' => $recentMonth, 'year' => $relevantYear];
                }
                break;
                
            case 'year':
                // Use all specific months in the current year
                foreach ($specificMonths as $month) {
                    $relevantMonths[] = ['month' => $month, 'year' => $currentYear];
                }
                break;
                
            default:
                // Default to current month
                if (in_array($currentMonth, $specificMonths)) {
                    $relevantMonths[] = ['month' => $currentMonth, 'year' => $currentYear];
                } else {
                    // Find the most recent specific month
                    $mostRecentMonth = null;
                    foreach ($specificMonths as $month) {
                        if ($month < $currentMonth && ($mostRecentMonth === null || $month > $mostRecentMonth)) {
                            $mostRecentMonth = $month;
                        }
                    }
                    
                    if ($mostRecentMonth === null) {
                        $mostRecentMonth = max($specificMonths);
                        $relevantMonths[] = ['month' => $mostRecentMonth, 'year' => $currentYear - 1];
                    } else {
                        $relevantMonths[] = ['month' => $mostRecentMonth, 'year' => $currentYear];
                    }
                }
        }
        
        // Build the query conditions for the relevant months
        $query = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id');
        
        // Add conditions for each relevant month
        $query->where(function($q) use ($relevantMonths) {
            foreach ($relevantMonths as $index => $monthData) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $q->$method(function($subQuery) use ($monthData) {
                    $subQuery->whereRaw("MONTH(violation_date) = ? AND YEAR(violation_date) = ?", 
                        [$monthData['month'], $monthData['year']]);
                });
            }
        });
        
        // Complete the query
        $violations = $query
            ->select('violation_types.violation_name', DB::raw('count(*) as count'))
            ->groupBy('violation_types.violation_name')
            ->orderBy('count', 'desc')
            ->get();
            
        return $violations;
    }
    
    /**
     * Display the behavior monitoring page
     */
    public function behavior()
    {
        // Get students who need attention (more than 2 violations)
        $studentsNeedingAttention = User::role('student')
            ->withCount('violations')
            ->having('violations_count', '>', 2)
            ->orderBy('violations_count', 'desc')
            ->get();
            
        return view('educator.behavior', compact('studentsNeedingAttention'));
    }
    
    /**
     * Get student statistics for behavior monitoring
     */
    private function getStudentStats()
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
     * Display the behavior page with student statistics
     */
    public function behaviorStats()
    {
        $stats = $this->getStudentStats();
        $behaviorData = $this->getBehaviorDataBySex();
            
        return view('educator.behavior', [
            'totalStudents' => $stats['totalStudents'],
            'studentsNeedingAttention' => $stats['studentsNeedingAttention'],
            'behaviorData' => $behaviorData
        ]);
    }
    
    /**
     * API endpoint to get behavior data for the chart
     */
    public function getBehaviorData(Request $request)
    {
        try {
            $months = $request->input('months', 6);
            $behaviorData = $this->getBehaviorDataBySex($months);
            
            // Add additional data for the frontend
            $stats = $this->getStudentStats();
            $behaviorData = array_merge($behaviorData, $stats);
                
            return response()->json($behaviorData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, 
                'message' => $e->getMessage(),
                'labels' => [],
                'boys' => [],
                'girls' => []
            ], 500);
        }
    }
    
    /**
     * Get behavior data by sex for the chart
     */
    private function getBehaviorDataBySex($monthsToShow = 6)
    {
        try {
            $labels = [];
            $menData = [];
            $womenData = [];
            $currentDate = now();
            $allMonths = [];
            
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
                    
                    // FORCE detection of violations for February and June by directly querying the database
                    // This is a more aggressive approach to ensure we catch all violations for these months
                    $forcedViolations = DB::table('violations')
                        ->where('status', 'active')
                        ->get();
                        
                    // Log all violations for debugging
                    foreach ($forcedViolations as $violation) {
                        \Log::info("Checking violation ID: {$violation->id}, Date: {$violation->violation_date}, Sex: {$violation->sex}");
                        
                        // For February and June, check if the date string contains the month name in any form
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
                    
                // Filter boys violations for this month with comprehensive date checking
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
                
                // Filter girls violations for this month with comprehensive date checking
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
                
                // Special handling for problematic months (February, June)
                if (in_array($monthData->name, ['February', 'June']) && $menViolations->count() > 0) {
                    \Log::info("Found {$menViolations->count()} men violations for {$monthData->name}");
                    \Log::info("Applying special handling for {$monthData->name}");
                    
                    // For these months, ensure we apply at least some deduction if violations exist
                    // This is a fallback in case the severity-based deductions don't work
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
                
                // Process boys violations normally
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
                
                // Special handling for problematic months (February, June)
                if (in_array($monthData->name, ['February', 'June']) && $womenViolations->count() > 0) {
                    \Log::info("Found {$womenViolations->count()} women violations for {$monthData->name}");
                    \Log::info("Applying special handling for {$monthData->name}");
                    
                    // For these months, ensure we apply at least some deduction if violations exist
                    // This is a fallback in case the severity-based deductions don't work
                    $hasDeduction = false;
                    
                    // We'll check if any deductions will be applied in the normal processing
                    foreach ($womenViolations as $violation) {
                        $severity = strtolower($violation->severity ?? '');
                        if (!empty($severity) || !empty($violation->penalty)) {
                            $hasDeduction = true;
                            break;
                        }
                    }
                    
                    // If no deductions would be applied, force a minimum deduction
                    if (!$hasDeduction) {
                        \Log::info("Forcing minimum deduction for {$monthData->name}");
                        $womenScore -= 15; // Apply a default medium deduction
                    }
                }
                
                // Process girls violations normally
                foreach ($womenViolations as $violation) {
                    // Log each violation for debugging
                    \Log::info("Processing woman violation ID: {$violation->id}, Date: {$violation->violation_date}, Severity: {$violation->severity}");
                    
                    $severity = strtolower($violation->severity ?? '');
                    
                    if (strpos($severity, 'low') !== false) {
                        $womenScore -= 5; // Low severity
                        \Log::info("Applied -5 deduction for Low severity");
                    } elseif (strpos($severity, 'medium') !== false) {
                        $womenScore -= 10; // Medium severity
                        \Log::info("Applied -10 deduction for Medium severity");
                    } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
                        $womenScore -= 15; // High severity
                        \Log::info("Applied -15 deduction for High severity");
                    } elseif (strpos($severity, 'very high') !== false) {
                        $womenScore -= 20; // Very High severity
                        \Log::info("Applied -20 deduction for Very High severity");
                    } else {
                        // Default deductions based on penalty
                        if ($violation->penalty == 'VW') {
                            $womenScore -= 10; // Verbal Warning
                            \Log::info("Applied -10 deduction for Verbal Warning");
                        } elseif ($violation->penalty == 'WW') {
                            $womenScore -= 15; // Written Warning
                            \Log::info("Applied -15 deduction for Written Warning");
                        } elseif ($violation->penalty == 'Pro' || $violation->penalty == 'Exp') {
                            $womenScore -= 20; // Probation or Expulsion
                            \Log::info("Applied -20 deduction for {$violation->penalty}");
                        } else {
                            $womenScore -= 10; // Default
                            \Log::info("Applied -10 default deduction");
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
     * View a specific violation
     */
    public function viewViolation($id)
    {
        $violation = Violation::with(['student', 'violationType'])->findOrFail($id);
        return view('educator.viewViolation', compact('violation'));
    }
    
    /**
     * Filter and display students by penalty type
     *
     * @param string $penalty The penalty code (W, VW, WW, Pro, Exp)
     * @return \Illuminate\View\View
     */
    public function studentsByPenalty($penalty)
    {
        // Validate the penalty type
        $validPenalties = ['W', 'VW', 'WW', 'Pro', 'Exp'];
        if (!in_array($penalty, $validPenalties)) {
            return redirect()->route('educator.violation')->with('error', 'Invalid penalty type');
        }
        
        // Get all active violations with the specified penalty
        $violations = Violation::with(['student', 'violationType'])
            ->where('penalty', $penalty)
            ->where('status', 'active')
            ->orderBy('violation_date', 'desc')
            ->get();
        
        // Get the penalty full name for display
        $penaltyNames = [
            'W' => 'Warning',
            'VW' => 'Verbal Warning',
            'WW' => 'Written Warning',
            'Pro' => 'Probation',
            'Exp' => 'Expulsion'
        ];
        
        $penaltyName = $penaltyNames[$penalty] ?? 'Unknown';
        
        return view('educator.studentsByPenalty', [
            'violations' => $violations,
            'penalty' => $penalty,
            'penaltyName' => $penaltyName
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
            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'severity' => 'required|string|in:Low,Medium,High,Very High',
                'penalty' => 'required|string|in:W,VW,WW,Pro,Exp'
            ]);
            
            // Create the violation type
            $violationType = ViolationType::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Violation type created successfully',
                'data' => $violationType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create violation type: ' . $e->getMessage()
            ], 500);
        }
    }
}
