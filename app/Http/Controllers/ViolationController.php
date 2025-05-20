<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use App\Models\Severity;
use App\Models\User;
use App\Http\Requests\StoreViolationTypeRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations
     */
    public function index()
    {
        $violations = Violation::with(['student', 'violationType'])->orderBy('created_at', 'desc')->get();
        return view('educator.violation', ['violations' => $violations]);
    }

    /**
     * Show the form for creating a new violation
     */
    public function create()
    {
        $students = User::role('student')->get();
        $violationTypes = ViolationType::all();
        $offenseCategories = OffenseCategory::all();
        return view('educator.addViolator', compact('students', 'violationTypes', 'offenseCategories'));
    }

    /**
     * Store a newly created violation in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        // Get student sex for the record
        $student = User::find($request->student_id);
        $validated['sex'] = $student->sex;
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        $severity = Severity::find($violationType->severity_id);
        $validated['severity'] = $severity->severity_name;
        
        Violation::create($validated);
        
        return redirect()->route('educator.violation')->with('success', 'Violation created successfully.');
    }

    /**
     * Show the form for editing the specified violation
     */
    public function edit($id)
    {
        $violation = Violation::findOrFail($id);
        $students = User::role('student')->get();
        $violationTypes = ViolationType::all();
        return view('educator.editViolation', compact('violation', 'students', 'violationTypes'));
    }

    /**
     * Update the specified violation in storage
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        $violation = Violation::findOrFail($id);
        $violation->update($validated);
        
        return redirect()->route('educator.violation')->with('success', 'Violation updated successfully.');
    }

    /**
     * Get violation types by category
     */
    public function getViolationTypesByCategory($categoryId)
    {
        $violationTypes = ViolationType::where('offense_category_id', $categoryId)->get();
        return response()->json($violationTypes);
    }

    /**
     * Get form data for the new violation type form
     */
    public function getFormData()
    {
        $categories = OffenseCategory::all();
        $severities = Severity::all();
        return response()->json([
            'categories' => $categories,
            'severities' => $severities
        ]);
    }

    /**
     * Show the form for creating a new violation type
     */
    public function createViolationType()
    {
        $categories = OffenseCategory::all();
        $severities = Severity::all();
        return view('educator.newViolation', compact('categories', 'severities'));
    }

    /**
     * Store a new violation type
     * 
     * @param \App\Http\Requests\StoreViolationTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeViolationType(StoreViolationTypeRequest $request)
    {
        // Get validated data
        $validated = $request->validated();
        
        // Find or create the offense category
        $offenseCategory = OffenseCategory::firstOrCreate(['name' => $validated['category']]);
        
        // Create the violation type with proper offense_category_id
        $violationType = ViolationType::create([
            'offense_category_id' => $offenseCategory->id,
            'violation_name' => $validated['violation_name'],
            'description' => $validated['offense'] ?? null,
            'default_penalty' => $validated['penalty'] ?? null,
            'severity_id' => Severity::where('severity_name', $validated['severity'])->first()->id ?? null
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Violation type created successfully',
            'data' => $violationType
        ]);
    }

    /**
     * Show the form for adding a violator
     */
    public function addViolatorForm()
    {
        $students = User::role('student')->get();
        $offenseCategories = OffenseCategory::all();
        return view('educator.addViolator', compact('students', 'offenseCategories'));
    }

    /**
     * Store a new violator record
     */
    public function addViolatorSubmit(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        // Get student sex for the record
        $student = User::find($request->student_id);
        $validated['sex'] = $student->sex;
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        $severity = Severity::find($violationType->severity_id);
        $validated['severity'] = $severity->severity_name;
        
        Violation::create($validated);
        
        return redirect()->route('educator.violation')->with('success', 'Violation record created successfully.');
    }

    /**
     * Show the form for editing a violation
     */
    public function editViolation($id)
    {
        $violation = Violation::findOrFail($id);
        $students = User::role('student')->get();
        $offenseCategories = OffenseCategory::all();
        $violationTypes = ViolationType::where('offense_category_id', $violation->violationType->offense_category_id)->get();
        
        return view('educator.editViolation', compact('violation', 'students', 'offenseCategories', 'violationTypes'));
    }

    /**
     * Update a violation record
     */
    public function updateViolation(Request $request, $id)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        // Get student sex for the record
        $student = User::find($request->student_id);
        $validated['sex'] = $student->sex;
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        $severity = Severity::find($violationType->severity_id);
        $validated['severity'] = $severity->severity_name;
        
        // Update the violation
        $violation = Violation::findOrFail($id);
        $violation->update($validated);
        
        return redirect()->route('educator.violation')->with('success', 'Violation record updated successfully.');
    }

    /**
     * Display student violations
     */
    public function studentViolations()
    {
        // Get all violations with student information
        $violations = Violation::with(['student', 'violationType'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('educator.violation', ['violations' => $violations]);
    }
    
    /**
     * Get violation statistics by time period for the educator dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByPeriod(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            
            // Initialize violationStats as an empty collection to avoid null issues
            $violationStats = collect([]);
            
            // Get the current month and year
            $currentMonth = now()->month;
            $currentYear = now()->year;
            
            // Define the specific months that have violations
            $specificMonths = [1, 4, 8, 11]; // January, April, August, November
            
            // Initialize variables
            $startDate = null;
            $endDate = now();
            $relevantMonths = [];
            $targetMonth = null;
            $targetYear = null;
            
            // Determine date ranges based on period
            if ($period === 'month') {
                // If current month is one of the specific months, use it
                if (in_array($currentMonth, $specificMonths)) {
                    $targetMonth = $currentMonth;
                    $targetYear = $currentYear;
                    $startDate = now()->startOfMonth();
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
                    
                    $startDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth();
                    $endDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->endOfMonth();
                }
                
                $relevantMonths[] = ['month' => $targetMonth, 'year' => $targetYear];
            } 
            elseif ($period === 'last_month') {
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
                
                $startDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->endOfMonth();
                
                $relevantMonths[] = ['month' => $targetMonth, 'year' => $targetYear];
            }
            elseif ($period === 'last_3_months') {
                // Find the current or most recent specific month
                $recentMonth = null;
                $relevantYear = $currentYear;
                
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
                
                // Set the date range to cover all relevant months
                $earliestMonth = end($relevantMonths);
                $startDate = Carbon::createFromDate($earliestMonth['year'], $earliestMonth['month'], 1)->startOfMonth();
            }
            elseif ($period === 'year') {
                // Use all specific months in the current year
                $startDate = Carbon::createFromDate($currentYear, min($specificMonths), 1)->startOfMonth();
                
                // Add all specific months in the current year to relevantMonths
                foreach ($specificMonths as $month) {
                    $relevantMonths[] = ['month' => $month, 'year' => $currentYear];
                }
            }
            else {
                // Default to current month
                $startDate = now()->startOfMonth();
                $targetMonth = $currentMonth;
                $targetYear = $currentYear;
                $relevantMonths[] = ['month' => $targetMonth, 'year' => $targetYear];
            }
            
            // Log the date range for debugging
            Log::info('Violation stats query for period: ' . $period);
            Log::info('Date range: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
            
            // Get all violations
            $allViolations = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->select('violations.*', 'violation_types.violation_name')
                ->get();
            
            // Group manually by violation name
            $manualStats = [];
            
            foreach ($allViolations as $violation) {
                try {
                    // Try to parse the date
                    $date = new \DateTime($violation->violation_date);
                    $violationMonth = (int)$date->format('m');
                    $violationYear = (int)$date->format('Y');
                    
                    // Check if the violation date is within the range
                    $inRange = false;
                    
                    // For specific periods, check if the month is one of the specific months
                    if ($period === 'month' || $period === 'last_month') {
                        // For month/last_month, check exact month and year
                        if ($violationMonth === $targetMonth && $violationYear === $targetYear) {
                            $inRange = true;
                        }
                    } elseif ($period === 'last_3_months') {
                        // For last_3_months, check if the month is one of the relevant months
                        foreach ($relevantMonths as $relevantMonth) {
                            if ($violationMonth === $relevantMonth['month'] && $violationYear === $relevantMonth['year']) {
                                $inRange = true;
                                break;
                            }
                        }
                    } elseif ($period === 'year') {
                        // For year, check if the month is one of the specific months and the year matches
                        if (in_array($violationMonth, $specificMonths) && $violationYear === $currentYear) {
                            $inRange = true;
                        }
                    }
                    
                    if ($inRange) {
                        if (!isset($manualStats[$violation->violation_name])) {
                            $manualStats[$violation->violation_name] = 0;
                        }
                        $manualStats[$violation->violation_name]++;
                    }
                } catch (\Exception $e) {
                    // Skip violations with unparseable dates
                    continue;
                }
            }
            
            // Convert to the expected format if manualStats is not empty
            $violationStats = collect();
            
            if (!empty($manualStats)) {
                // Process each violation statistic
                foreach ($manualStats as $name => $count) {
                    // Create a simple array and convert to object
                    $data = array(
                        'violation_name' => $name,
                        'count' => $count
                    );
                    
                    // Add to collection
                    $violationStats->push((object) $data);
                }
            }
            
            // Sort by count descending
            $violationStats = $violationStats->sortByDesc('count')->values();
            
            // Log the count and return the results (violationStats is now always a collection, never null)
            Log::info('Violation stats results count: ' . $violationStats->count());
            return response()->json($violationStats);
        } catch (\Exception $e) {
            Log::error('Error in getViolationStatsByPeriod: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([], 500);
        }
    }
}
