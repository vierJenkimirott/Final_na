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
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Eager load relationships and paginate for better performance
            $violations = Violation::with(['student', 'violationType'])
                ->orderBy('created_at', 'desc')
                ->paginate(15); // Paginate results for better performance with large datasets
                
            return view('educator.violation', ['violations' => $violations]);
        } catch (Exception $e) {
            Log::error('Error fetching violations: ' . $e->getMessage());
            return view('educator.violation', ['violations' => collect()])
                ->with('error', 'Unable to load violations. Please try again later.');
        }
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
            'student_id' => 'required|exists:users,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        // Get student sex for the record
        $student = User::where('student_id', $request->student_id)->first();
        
        // Handle case where student might not be found
        if ($student) {
            $validated['sex'] = $student->sex;
        } else {
            // Default to a placeholder value if student not found
            $validated['sex'] = 'unknown';
            Log::warning('Student not found when updating violation', ['student_id' => $request->student_id]);
        }
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        
        // Handle case where violation type might not have a valid severity
        if ($violationType && $violationType->severity_id) {
            $severity = Severity::find($violationType->severity_id);
            
            if ($severity) {
                $validated['severity'] = $severity->severity_name;
            } else {
                // Default to a placeholder value if severity not found
                $validated['severity'] = 'Medium';
                Log::warning('Severity not found when updating violation', [
                    'violation_type_id' => $request->violation_type_id,
                    'severity_id' => $violationType->severity_id
                ]);
            }
        } else {
            // Default to a placeholder value if violation type or severity_id not found
            $validated['severity'] = 'Medium';
            Log::warning('Violation type not found or missing severity_id when updating violation', [
                'violation_type_id' => $request->violation_type_id
            ]);
        }
        
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
            'student_id' => 'required|exists:users,student_id',
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
        $violationTypes = ViolationType::with('severityRelation')
            ->where('offense_category_id', $categoryId)
            ->get()
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->violation_name,
                    'severity' => $type->severityRelation ? $type->severityRelation->severity_name : null,
                    'description' => $type->description,
                    'default_penalty' => $type->default_penalty
                ];
            });
        
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
        $severities = ['Low', 'Medium', 'High', 'Very High'];
        $offenses = ['1st', '2nd', '3rd'];
        $penalties = [
            ['value' => 'W', 'label' => 'Warning'],
            ['value' => 'VW', 'label' => 'Verbal Warning'],
            ['value' => 'WW', 'label' => 'Written Warning'],
            ['value' => 'Pro', 'label' => 'Probation'],
            ['value' => 'Exp', 'label' => 'Expulsion']
        ];
        
        return view('educator.addViolator', compact('students', 'offenseCategories', 'severities', 'offenses', 'penalties'));
    }

    /**
     * Store a new violator record
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addViolatorSubmit(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'student_id' => 'required|exists:users,student_id',
                'violation_type_id' => 'required|exists:violation_types,id',
                'violation_date' => 'required|date',
                'offense' => 'required|string',
                'penalty' => 'required|string',
                'consequence' => 'nullable|string',
                'status' => 'required|in:active,resolved',
            ]);
            
            // Start a database transaction for data consistency
            DB::beginTransaction();
            
            // Get student information
            $student = User::where('student_id', $request->student_id)->firstOrFail();
            $validated['sex'] = $student->sex ?? 'unknown';
            
            // Get severity from the violation type
            $violationType = ViolationType::with('severityRelation')->findOrFail($request->violation_type_id);
            $validated['severity'] = $violationType->severityRelation->severity_name ?? $request->severity;
            
            // Add recorded_by if authenticated
            if (Auth::check()) {
                $validated['recorded_by'] = Auth::id();
            }
            
            // Create the violation record
            $violation = Violation::create($validated);
            
            // Commit the transaction
            DB::commit();
            
            // Log the successful creation
            Log::info('Violation record created', ['id' => $violation->id, 'student_id' => $student->student_id]);
            
            return redirect()->route('educator.violation')
                ->with('success', 'Violation record created successfully.');
                
        } catch (ValidationException $e) {
            // Validation errors are automatically handled by Laravel
            return back()->withErrors($e->validator)->withInput();
            
        } catch (ModelNotFoundException $e) {
            // Handle not found errors
            DB::rollBack();
            Log::error('Resource not found when creating violation: ' . $e->getMessage());
            return back()->with('error', 'Student or violation type not found.')
                ->withInput($request->except('password'));
                
        } catch (Exception $e) {
            // Handle any other exceptions
            DB::rollBack();
            Log::error('Error creating violation record: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while creating the violation record. Please try again.')
                ->withInput($request->except('password'));
        }
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
            'student_id' => 'required|exists:users,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'offense' => 'required|string',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
        ]);
        
        // Get student sex for the record
        $student = User::where('student_id', $request->student_id)->first();
        
        // Handle case where student might not be found
        if ($student) {
            $validated['sex'] = $student->sex;
        } else {
            // Default to a placeholder value if student not found
            $validated['sex'] = 'unknown';
            Log::warning('Student not found when updating violation', ['student_id' => $request->student_id]);
        }
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        
        // Handle case where violation type might not have a valid severity
        if ($violationType && $violationType->severity_id) {
            $severity = Severity::find($violationType->severity_id);
            
            if ($severity) {
                $validated['severity'] = $severity->severity_name;
            } else {
                // Default to a placeholder value if severity not found
                $validated['severity'] = 'Medium';
                Log::warning('Severity not found when updating violation', [
                    'violation_type_id' => $request->violation_type_id,
                    'severity_id' => $violationType->severity_id
                ]);
            }
        } else {
            // Default to a placeholder value if violation type or severity_id not found
            $validated['severity'] = 'Medium';
            Log::warning('Violation type not found or missing severity_id when updating violation', [
                'violation_type_id' => $request->violation_type_id
            ]);
        }
        
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
            // Get and validate the period parameter
            $period = $this->validatePeriod($request->input('period', 'month'));
            
            // Get date range for the selected period
            $dateRange = $this->getDateRangeForPeriod($period);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            $relevantMonths = $dateRange['relevantMonths'] ?? [];
            
            // Log the date range for debugging
            Log::info('Violation stats query', [
                'period' => $period,
                'dateRange' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]);
            
            // Get violation statistics directly from the database with optimized query
            $violationStats = $this->getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths);
            
            // Log the results and return
            Log::info('Violation stats results count: ' . count($violationStats));
            return response()->json($violationStats);
            
        } catch (Exception $e) {
            Log::error('Error in getViolationStatsByPeriod: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => 'An error occurred while retrieving violation statistics.',
                'message' => app()->environment('production') ? null : $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get violation statistics from database
     * 
     * @param string $period
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $relevantMonths
     * @return array
     */
    private function getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths)
    {
        try {
            // Query to get violation statistics grouped by violation type
            $stats = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->select(
                    'violation_types.id',
                    'violation_types.violation_name',
                    DB::raw('COUNT(violations.id) as count')
                )
                ->where('violations.status', 'active')
                ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->groupBy('violation_types.id', 'violation_types.violation_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
            
            // Log the results for debugging
            Log::info('Violation stats query results', [
                'count' => count($stats),
                'period' => $period,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ]);
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error in getViolationStatsFromDatabase: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate the period parameter
     *
     * @param string $period
     * @return string
     */
    private function validatePeriod($period)
    {
        $validPeriods = ['month', 'last_month', 'last_3_months', 'year'];
        
        if (!in_array($period, $validPeriods)) {
            // Default to 'month' if invalid period provided
            return 'month';
        }
        
        return $period;
    }
    
    /**
     * Get date range for the selected period
     * 
     * @param string $period
     * @return array
     */
    private function getDateRangeForPeriod($period)
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        switch ($period) {
            case 'month':
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                break;
                
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $startDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->endOfMonth()->endOfDay();
                break;
                
            case 'last_3_months':
                $startDate = $now->copy()->subMonths(3)->startOfMonth()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
                
            case 'year':
                $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();
                break;
                
            default:
                // Default to current month
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
        }
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    // End of ViolationController class methods
    
    /**
     * Get date range for the 'month' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getMonthPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $targetMonth = null;
        $targetYear = null;
        $startDate = null;
        $endDate = null;
        
        // If current month is one of the specific months, use it
        if (in_array($currentMonth, $specificMonths)) {
            $targetMonth = $currentMonth;
            $targetYear = $currentYear;
            $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();
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
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear
        ];
    }
    
    /**
     * Get date range for the 'last_month' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getLastMonthPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $targetMonth = null;
        $targetYear = null;
        
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
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear
        ];
    }
    
    /**
     * Get date range for the 'last_3_months' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getLast3MonthsPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $relevantMonths = [];
        
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
        $latestMonth = reset($relevantMonths);
        $endDate = Carbon::createFromDate($latestMonth['year'], $latestMonth['month'], 1)->endOfMonth();
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'relevantMonths' => $relevantMonths
        ];
    }
    
    // Using the studentsByPenalty method from EducatorController instead
    
    // End of ViolationController methods
}
