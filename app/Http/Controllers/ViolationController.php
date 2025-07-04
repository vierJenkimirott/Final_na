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
     * Get violation statistics by batch
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByBatch(Request $request)
    {
        $batch = $request->query('batch', 'all');
        $period = $request->query('period', 'month');
        
        // Get date range for the period
        $dateRange = $this->getDateRangeForPeriod($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Base query for violations within the date range
        $query = Violation::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by batch if not 'all'
        if ($batch !== 'all') {
            $query->whereHas('studentDetails', function ($q) use ($batch) {
                $q->where('batch', $batch);
            });
        }
        
        // Get counts by violation type
        $violationStats = $query->with('violationType')
            ->get()
            ->groupBy('violation_type_id')
            ->map(function ($violations, $typeId) {
                $type = $violations->first()->violationType;
                return [
                    'type' => $type->name,
                    'count' => $violations->count(),
                    'color' => $type->color ?? '#' . substr(md5($type->name), 0, 6)
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $violationStats,
            'period' => $period,
            'dateRange' => $dateRange
        ]);
    }
    


    /**
     * Display a listing of violations
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $batch = $request->query('batch', 'all');
            $severity = $request->query('severity', '');
            $search = $request->query('search', '');
            $violationsQuery = Violation::with(['student', 'violationType'])
                ->orderBy('created_at', 'desc');
            if ($batch !== 'all') {
                $violationsQuery->whereHas('student', function($query) use ($batch) {
                    $query->where('student_id', 'like', $batch . '%');
                });
            }
            if ($severity !== '') {
                $violationsQuery->where('severity', $severity);
            }
            if ($search !== '') {
                $violationsQuery->where(function($query) use ($search) {
                    $query->whereHas('student', function($q) use ($search) {
                        $q->where(DB::raw("CONCAT(fname, ' ', lname)"), 'like', "%$search%")
                          ->orWhere('fname', 'like', "%$search%")
                          ->orWhere('lname', 'like', "%$search%")
                          ->orWhere('student_id', 'like', "%$search%") ;
                    })
                    ->orWhereHas('violationType', function($q) use ($search) {
                        $q->where('violation_name', 'like', "%$search%") ;
                    })
                    ->orWhere('offense', 'like', "%$search%") ;
                });
            }
            $violations = $violationsQuery->paginate(10)->appends([
                'batch' => $batch,
                'severity' => $severity,
                'search' => $search
            ]);
            return view('educator.violation', [
                'violations' => $violations,
                'batch' => $batch,
                'severity' => $severity,
                'search' => $search
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching violations: ' . $e->getMessage());
            return view('educator.violation', ['violations' => collect()])
                ->with('error', 'Unable to load violations. Please try again later.');
        }

        $violations = \App\Models\Violation::with(['violationType.offenseCategory', 'student'])
        ->orderBy('violation_date', 'desc')
        ->paginate(10);

    // No need to calculate penalty counts here, as your Blade does it

    return view('educator.violation', compact('violations'));
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
        // --- Backend Validation for Termination Policy ---

        // 1. Check if student already has a termination penalty
        $studentId = $request->input('student_id');
        $hasTermination = Violation::where('student_id', $studentId)
            
            ->where(function($q) {
                $q->where('penalty', 'T')
                  ->orWhere('penalty', 'Exp');
            })
            ->exists();

        if ($hasTermination) {
            return back()->withInput()->with('error', 'Action failed: This student already has a termination/expulsion penalty. No further violations can be recorded.');
        }

        // 2. Get violation type and calculate penalty
        $violationType = ViolationType::find($request->input('violation_type_id'));
        $severity = $violationType ? $violationType->severity : null;
        $infractionCount = Violation::where('student_id', $studentId)
            
            ->count();
        $nextInfractionNum = $infractionCount + 1;

        // Calculate penalty using the matrix
        $penaltyMatrix = [
            'Low'       => ['VW', 'WW', 'P', 'T'],
            'Medium'    => ['WW', 'P', 'T', null],
            'High'      => ['P', 'T', null, null],
            'Very High' => ['T', null, null, null]
        ];

        $penalties = $penaltyMatrix[$severity] ?? [];
        $newPenalty = $penalties[$nextInfractionNum - 1] ?? null;

        // --- End of Validation ---

        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
            'incident_datetime' => 'nullable|date',
            'incident_place' => 'nullable|string|max:255',
            'incident_details' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
        ]);
        
        // Get student sex for the record
        $student = User::where('student_id', $request->student_id)->first();
        
        // Handle case where student might not be found
        if ($student) {
            $validated['sex'] = $student->sex;
        } else {
            // Default to a placeholder value if student not found
            $validated['sex'] = 'unknown';
            Log::warning('Student not found when creating violation', ['student_id' => $request->student_id]);
        }
        
        // Get severity from the violation type
        if ($violationType && $violationType->severity_id) {
            $severityModel = Severity::find($violationType->severity_id);
            
            if ($severityModel) {
                $validated['severity'] = $severityModel->severity_name;
            } else {
                // Default to a placeholder value if severity not found
                $validated['severity'] = 'Medium';
                Log::warning('Severity not found when creating violation', [
                    'violation_type_id' => $request->violation_type_id,
                    'severity_id' => $violationType->severity_id
                ]);
            }
        } else {
            // Default to a placeholder value if violation type or severity_id not found
            $validated['severity'] = 'Medium';
            Log::warning('Violation type not found or missing severity_id when creating violation', [
                'violation_type_id' => $request->violation_type_id
            ]);
        }
        
        // Create a new violation record
        $violation = new Violation($validated);
        
        // Set infraction count and calculated penalty
        $violation->infraction_count = $nextInfractionNum;
        $violation->penalty = $newPenalty ?? $request->input('penalty');

        $violation->save();
        
        // Show appropriate success message
        $message = $violation->penalty === 'T' 
            ? 'Violation added successfully. Note: This violation results in termination/expulsion.'
            : 'Violation added successfully.';
        
        return redirect()->route('educator.violation')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified violation
     */
    public function edit($id)
    {
        $violation = Violation::findOrFail($id);
        $students = User::role('student')->get();
        $violationTypes = ViolationType::with('severityRelation')->get();
        $offenseCategories = OffenseCategory::all();
        return view('educator.editViolation', compact('violation', 'students', 'violationTypes', 'offenseCategories'));
    }

    /**
     * Update the specified violation in storage
     */
    public function update(Request $request, $id)
    {
        // Only validate and update the status field
        $validated = $request->validate([
            'status' => 'required|in:active,resolved',
        ]);

        $violation = Violation::findOrFail($id);
        $violation->status = $validated['status'];
        $violation->save();

        return redirect()->route('educator.violation')
            ->with('success', 'Violation status updated successfully.');
    }

    /**
     * Get violation types by category
     */
    public function getViolationTypesByCategory($categoryId)
    {
        $violationTypes = ViolationType::where('offense_category_id', $categoryId)
            ->with('severityRelation')
            ->orderBy('violation_name')
            ->get()
            ->unique('violation_name') // Only unique violation names
            ->values() // Re-index the collection
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'violation_name' => $type->violation_name,
                    'offense_category_id' => $type->offense_category_id,
                    'default_penalty' => $type->default_penalty,
                    'severity_id' => $type->severity_id,
                    'severity' => $type->severityRelation->severity_name ?? 'Medium'
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
        try {
            // Get validated data
            $validated = $request->validated();
            
            // Find or create the offense category
            $offenseCategory = OffenseCategory::firstOrCreate(['category_name' => $validated['category']]);
            
            // Find the severity by name
            $severity = \App\Models\Severity::where('severity_name', $validated['severity'])->first();
            
            if (!$severity) {
                throw new \Exception('Invalid severity level');
            }
            
            // Create the violation type
            $violationType = ViolationType::create([
                'offense_category_id' => $offenseCategory->id,
                'violation_name' => $validated['violation_name'],
                'description' => $validated['offense'] ?? null,
                'default_penalty' => $validated['penalty'] ?? null,
                'severity_id' => $severity->id
            ]);
            
            // Return a nicer success message
            return response()->json([
                'success' => true,
                'message' => '✅ New violation added successfully! The student manual has been updated.',
                'data' => $violationType,
                'redirect' => route('educator.violation')
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
     * Show the form for adding a violator
     */
    public function addViolatorForm()
    {
        $students = User::role('student')->get();
        $offenseCategories = OffenseCategory::all();
        $severities = ['Low', 'Medium', 'High', 'Very High'];
        $offenses = ['1st', '2nd', '3rd'];
        $penalties = [
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
        // --- Backend Validation for Termination Policy ---

        // 1. Check total existing infractions
        $studentId = $request->input('student_id');
        $infractionCount = Violation::where('student_id', $studentId)->count();

        if ($infractionCount >= 4) {
            return back()->withInput()->with('error', 'Action failed: This student has already reached the maximum of 4 infractions.');
        }

        // 2. Check if the new violation results in termination
        $violationType = ViolationType::find($request->input('violation_type_id'));
        if ($violationType) {
            $severity = $violationType->severity; 
            $nextInfractionNum = $infractionCount + 1;
            
            // Re-use the penalty matrix logic
            $penaltyMatrix = [
                'Low'       => ['V', 'W', 'P', 'T'],
                'Medium'    => ['W', 'P', 'T', null],
                'High'      => ['P', 'T', null, null],
                'Very High' => ['T', null, null, null]
            ];

            $penalties = $penaltyMatrix[$severity] ?? [];
            $newPenalty = $penalties[$nextInfractionNum - 1] ?? null;

            if ($newPenalty === 'T') {
                return back()->withInput()->with('error', 'Action failed: This violation results in a penalty of Termination/Expulsion.');
            }
        }

        // --- End of Validation ---

        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,student_id',
                'violation_type_id' => 'required|exists:violation_types,id',
                'violation_date' => 'required|date',
                'offense' => 'nullable|string',
                'penalty' => 'required|string',
                'consequence' => 'nullable|string',
                'status' => 'required|in:active,resolved',
                'incident_datetime' => 'nullable|date',
                'incident_place' => 'nullable|string|max:255',
                'incident_details' => 'nullable|string',
                'prepared_by' => 'nullable|string|max:255',
            ]);
            
            // Start a database transaction for data consistency
            DB::beginTransaction();
            
            // Get student information
            $student = User::where('student_id', $request->student_id)->firstOrFail();
            $validated['sex'] = $student->sex ?? 'unknown';
            
            // Get severity from the violation type
            $violationType = ViolationType::with('severityRelation')->findOrFail($request->violation_type_id);
            $validated['severity'] = $violationType->severityRelation->severity_name ?? $request->severity;
            
            // Validate and enforce the penalty based on severity and offense count
            $calculatedPenalty = $this->determinePenalty($validated['severity'], $validated['penalty']);
            
            // Check if the student has any existing violations with a higher penalty
            $highestExistingPenalty = $this->getHighestExistingPenalty($validated['student_id']);
            
            // Define penalty ranking (from lowest to highest)
            $penaltyRanks = [
                'VW' => 1,  // Verbal Warning (lowest)
                'WW' => 2,  // Written Warning
                'Pro' => 3, // Probation
                'Exp' => 4  // Expulsion (highest)
            ];
            
            // Compare penalties and use the higher one
            $calculatedRank = $penaltyRanks[$calculatedPenalty] ?? 1;
            $existingRank = $penaltyRanks[$highestExistingPenalty] ?? 0;
            
            // Use the higher penalty (never downgrade)
            $validated['penalty'] = $existingRank > $calculatedRank ? $highestExistingPenalty : $calculatedPenalty;
            
            // Add recorded_by if authenticated
            if (Auth::check()) {
                $validated['recorded_by'] = Auth::id();
            }
            
            // Create a new violation record (always create a new record)
            $violation = new Violation($validated);
            
            // Manually set infraction_count and penalty based on our logic
            $violation->infraction_count = $infractionCount + 1;
            $violation->penalty = $newPenalty ?? $request->input('penalty'); // Fallback to form input if needed

            $violation->save();
            
            // Log the creation
            Log::info('Created new violation record', [
                'id' => $violation->id,
                'student_id' => $validated['student_id'],
                'severity' => $validated['severity'],
                'penalty' => $validated['penalty']
            ]);
            
            // Commit the transaction
            DB::commit();
            
            // Log the successful creation
            Log::info('Violation record created', [
                'id' => $violation->id, 
                'student_id' => $student->student_id,
                'severity' => $validated['severity'],
                'penalty' => $validated['penalty']
            ]);
            
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
        // --- Backend Validation for Termination Policy ---

        // 1. Check total existing infractions for the student, excluding the current one
        $studentId = $request->input('student_id');
        $infractionCount = Violation::where('student_id', $studentId)->where('id', '!=', $id)->count();

        if ($infractionCount >= 4) {
            return back()->withInput()->with('error', 'Update failed: This student has already reached the maximum of 4 infractions.');
        }

        // 2. Check if the edited violation results in termination
        $violationType = ViolationType::find($request->input('violation_type_id'));
        if ($violationType) {
            $severity = $violationType->severity;
            // The infraction number for this violation is the count of *other* violations plus one
            $thisInfractionNum = $infractionCount + 1;

            $penaltyMatrix = [
                'Low'       => ['V', 'W', 'P', 'T'],
                'Medium'    => ['W', 'P', 'T', null],
                'High'      => ['P', 'T', null, null],
                'Very High' => ['T', null, null, null]
            ];

            $penalties = $penaltyMatrix[$severity] ?? [];
            $newPenalty = $penalties[$thisInfractionNum - 1] ?? null;

            if ($newPenalty === 'T') {
                return back()->withInput()->with('error', 'Update failed: This violation results in a penalty of Termination/Expulsion.');
            }
        } else {
            $newPenalty = $request->input('penalty');
        }

        // --- End of Validation ---

        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
            'incident_datetime' => 'nullable|date',
            'incident_place' => 'nullable|string|max:255',
            'incident_details' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
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
        
        $violation = Violation::findOrFail($id);
        
        // Manually set infraction_count and penalty based on our logic
        $violation->infraction_count = $thisInfractionNum ?? ($infractionCount + 1);
        $violation->penalty = $newPenalty;
        $violation->severity = $severity ?? $request->input('severity');

        // Update the rest of the validated fields
        $violation->fill($validated);
        $violation->save();
        
        return redirect()->route('educator.violation')
            ->with('success', 'Violation updated successfully.');
    }

    /**
 * Determine the appropriate penalty based on severity and offense count
 * 
 * @param string $severity The severity level (Low, Medium, High, Very High)
 * @param string $offenseCount The offense count (1st, 2nd, 3rd, 4th)
 * @param string $currentPenalty The current penalty value (used as fallback)
 * @return string The penalty code (VW, WW, Pro, Exp)
 */
private function determinePenalty($severity, $offenseCount, $currentPenalty = null)
{
    $severity = strtolower($severity);
    $offenseCount = strtolower($offenseCount);
    
    // Define the penalty mapping based on severity and offense count
    $penaltyMap = [
        'low' => [
            '1st' => 'VW', // Verbal Warning
            '2nd' => 'WW', // Written Warning
            '3rd' => 'Pro', // Probation
            '4th' => 'Exp', // Expulsion
        ],
        'medium' => [
            '1st' => 'WW', // Written Warning
            '2nd' => 'Pro', // Probation
            '3rd' => 'Exp', // Expulsion
            '4th' => 'Exp', // Expulsion
        ],
        'high' => [
            '1st' => 'Pro', // Probation
            '2nd' => 'Exp', // Expulsion
            '3rd' => 'Exp', // Expulsion
            '4th' => 'Exp', // Expulsion
        ],
        'very high' => [
            '1st' => 'Exp', // Expulsion
            '2nd' => 'Exp', // Expulsion
            '3rd' => 'Exp', // Expulsion
            '4th' => 'Exp', // Expulsion
        ]
    ];
    
    // Get the penalty from the map or use the current penalty as fallback
    if (isset($penaltyMap[$severity][$offenseCount])) {
        return $penaltyMap[$severity][$offenseCount];
    }
    
    // If severity or offense count is not recognized, log a warning and return the current penalty or default to Verbal Warning
    Log::warning('Unrecognized severity or offense count when determining penalty', [
        'severity' => $severity,
        'offense_count' => $offenseCount
    ]);
    
    return $currentPenalty ?: 'VW';
}

/**
 * Get the highest penalty for a student from their existing violations
 * 
 * @param int $studentId
 * @return string|null
 */
private function getHighestExistingPenalty($studentId)
{
    // Define penalty ranking (from lowest to highest)
    $penaltyRanks = [
        'VW' => 1,  // Verbal Warning (lowest)
        'WW' => 2,  // Written Warning
        'Pro' => 3, // Probation
        'Exp' => 4  // Expulsion (highest)
    ];
    
    // Get all active violations for this student
    $violations = Violation::where('student_id', $studentId)
        
        ->get();
    
    if ($violations->isEmpty()) {
        return null;
    }
    
    // Find the highest penalty
    $highestRank = 0;
    $highestPenalty = null;
    
    foreach ($violations as $violation) {
        $rank = $penaltyRanks[$violation->penalty] ?? 0;
        if ($rank > $highestRank) {
            $highestRank = $rank;
            $highestPenalty = $violation->penalty;
        }
    }
    
    Log::info('Found highest existing penalty for student', [
        'student_id' => $studentId,
        'highest_penalty' => $highestPenalty,
        'highest_rank' => $highestRank,
        'violation_count' => $violations->count()
    ]);
    
    return $highestPenalty;
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
     * Get students who committed a specific violation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStudents(Request $request)
    {
        try {
            $violationName = $request->query('violation_name');
            $period = $request->query('period', 'month');
            $batch = $request->query('batch', 'all');

            if (!$violationName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation name is required'
                ], 400);
            }

            // Build the base query
            $query = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->join('users', 'violations.student_id', '=', 'users.student_id')
                ->where('violation_types.violation_name', $violationName)
                ;

            // Apply period filter
            switch ($period) {
                case 'week':
                    $query->where('violations.violation_date', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('violations.violation_date', '>=', now()->subMonth());
                    break;
                case 'last_month':
                    $query->whereBetween('violations.violation_date', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'year':
                    $query->where('violations.violation_date', '>=', now()->subYear());
                    break;
            }

            // Apply batch filter
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $query->where('users.student_id', 'like', $batch . '01%');
            }

            // Get students with violation details
            $students = $query->select(
                'users.name',
                'users.student_id',
                'violations.violation_date',
                'violations.penalty'
            )
            ->orderBy('violations.violation_date', 'desc')
            ->get()
            ->map(function($student) {
                return [
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'violation_date' => $student->violation_date ? date('M d, Y', strtotime($student->violation_date)) : 'Unknown Date',
                    'penalty' => $student->penalty ?? 'No penalty assigned'
                ];
            });

            return response()->json([
                'success' => true,
                'students' => $students,
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching violation students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch violation students'
            ], 500);
        }
    }

    /**
     * Get detailed violations for a specific student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentViolations(Request $request)
    {
        try {
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required'
                ], 400);
            }

            // Get violations for the specific student with related data
            $violations = Violation::with(['violationType', 'violationType.offenseCategory'])
                ->where('student_id', $studentId)
                
                ->orderBy('violation_date', 'desc')
                ->get()
                ->map(function($violation) {
                    return [
                        'id' => $violation->id,
                        'violation_name' => $violation->violationType->violation_name ?? 'Unknown Violation',
                        'category_name' => $violation->violationType->offenseCategory->category_name ?? 'Unknown Category',
                        'violation_date' => $violation->violation_date ? date('M d, Y', strtotime($violation->violation_date)) : 'Unknown Date',
                        'severity' => $violation->severity ?? 'Unknown',
                        'offense' => $violation->offense ?? 'Unknown',
                        'penalty' => $this->formatPenalty($violation->penalty ?? 'Unknown'),
                        'consequence' => $violation->consequence ?? 'No consequence specified',
                        'status' => $violation->status
                    ];
                });

            return response()->json([
                'success' => true,
                'violations' => $violations,
                'total_count' => $violations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student violations',
                'violations' => [],
                'total_count' => 0
            ], 500);
        }
    }

    /**
     * Format penalty code to readable text
     *
     * @param string $penalty
     * @return string
     */
    private function formatPenalty($penalty)
    {
        $penalties = [
            'W' => 'Warning',
            'VW' => 'Verbal Warning',
            'WW' => 'Written Warning',
            'Pro' => 'Probation',
            'Exp' => 'Expulsion'
        ];

        return $penalties[$penalty] ?? $penalty;
    }

    /**
     * Count violations filtered by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countViolationsByBatchFilter(Request $request)
    {
        $batch = $request->query('batch', 'all');
        
        try {
            // Base query for active violations
            $query = Violation::query();
            
            // Filter by batch if not 'all'
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $query->where('student_id', 'like', $batch . '01%');
            }
            
            // Get the count
            $count = $query->count();
            
            // Log for debugging
            \Illuminate\Support\Facades\Log::info('Violation count for batch ' . $batch . ': ' . $count);
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in countViolationsByBatchFilter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get violations count: ' . $e->getMessage(),
                'count' => 0
            ]);
        }
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
            $batch = $request->input('batch', 'all');
            
            // Get date range for the selected period
            $dateRange = $this->getDateRangeForPeriod($period);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            $relevantMonths = $dateRange['relevantMonths'] ?? [];
            
            // Log the date range for debugging
            Log::info('Violation stats query', [
                'period' => $period,
                'batch' => $batch,
                'dateRange' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]);
            
            // Get violation statistics directly from the database with optimized query
            $violationStats = $this->getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths, $batch);
            
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
    private function getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths, $batch = 'all')
    {
        try {
            // Query to get violation statistics grouped by violation type
            $query = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->select(
                    'violation_types.id',
                    'violation_types.violation_name',
                    DB::raw('COUNT(violations.id) as count')
                )
                
                ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
            // Apply batch filter if specified
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 202501 for 2025, 202601 for 2026)
                $query->where('violations.student_id', 'like', $batch . '01%');
            }
            
            $stats = $query->groupBy('violation_types.id', 'violation_types.violation_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
            
            // Log the results for debugging
            Log::info('Violation stats query results', [
                'count' => count($stats),
                'period' => $period,
                'batch' => $batch,
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
    
    /**
     * Check if a student has existing violations with the same severity
     * and return the appropriate offense count
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExistingViolations(Request $request)
    {
        try {
            $studentId = $request->query('student_id');
            $violationTypeId = $request->query('violation_type_id');
            
            if (!$studentId || !$violationTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing student ID or violation type ID',
                    'offenseCount' => 1
                ]);
            }
            
            // Get the severity for the violation type
            $violationType = ViolationType::with('severityRelation')->findOrFail($violationTypeId);
            $severity = $violationType->severityRelation->severity_name ?? null;
            
            if (!$severity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine severity for violation type',
                    'offenseCount' => 1
                ]);
            }
            
            // Count existing violations with the same severity only
            // We don't check for the same violation type, only the severity matters
            $existingViolations = Violation::where('student_id', $studentId)
                ->where('severity', $severity)
                
                ->count();
            
            // Determine the next offense count (1-based)
            $offenseCount = $existingViolations + 1;
            
            // Cap at 4th offense
            $offenseCount = min($offenseCount, 4);
            
            // Convert offense count to string format (1st, 2nd, etc.)
            $offenseStrings = [
                1 => '1st',
                2 => '2nd',
                3 => '3rd',
                4 => '4th'
            ];
            $offenseString = $offenseStrings[$offenseCount] ?? '1st';
            
            // Calculate the new penalty based on severity and offense
            $calculatedPenalty = $this->determinePenalty($severity, $offenseString);
            
            // Check if the student has any existing violations with a higher penalty
            $highestExistingPenalty = $this->getHighestExistingPenalty($studentId);
            
            // Define penalty ranking (from lowest to highest)
            $penaltyRanks = [
                'VW' => 1,  // Verbal Warning (lowest)
                'WW' => 2,  // Written Warning
                'Pro' => 3, // Probation
                'Exp' => 4  // Expulsion (highest)
            ];
            
            // Compare penalties and use the higher one
            $calculatedRank = $penaltyRanks[$calculatedPenalty] ?? 1;
            $existingRank = $penaltyRanks[$highestExistingPenalty] ?? 0;
            
            // Use the higher penalty (never downgrade)
            $finalPenalty = $existingRank > $calculatedRank ? $highestExistingPenalty : $calculatedPenalty;
            
            // Log the result
            Log::info('Checked existing violations', [
                'student_id' => $studentId,
                'violation_type_id' => $violationTypeId,
                'severity' => $severity,
                'existing_count' => $existingViolations,
                'next_offense_count' => $offenseCount,
                'calculated_penalty' => $calculatedPenalty,
                'highest_existing_penalty' => $highestExistingPenalty,
                'final_penalty' => $finalPenalty,
                'calculated_rank' => $calculatedRank,
                'existing_rank' => $existingRank
            ]);
            
            return response()->json([
                'success' => true,
                'offenseCount' => $offenseCount,
                'severity' => $severity,
                'existingViolations' => $existingViolations,
                'offenseString' => $offenseString . ' offense',
                'calculatedPenalty' => $calculatedPenalty,
                'highestExistingPenalty' => $highestExistingPenalty,
                'finalPenalty' => $finalPenalty
            ]);
            
        } catch (Exception $e) {
            Log::error('Error checking existing violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking existing violations',
                'error' => $e->getMessage(),
                'offenseCount' => 1 // Default to 1st offense on error
            ], 500);
        }
    }
    
    // End of ViolationController methods
}




