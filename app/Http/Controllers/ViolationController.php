<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations
     */
    public function index()
    {
        // Get real violations from the database with eager loading of relationships
        $violations = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // If no violations exist yet, add some sample data for demonstration
        if ($violations->isEmpty()) {
            // Get a random student
            $student = User::whereNotNull('student_id')->first();
            
            // Get a violation type
            $violationType = ViolationType::first();
            
            if ($student && $violationType) {
                // Create a sample violation
                Violation::create([
                    'student_id' => $student->student_id,
                    'violation_type_id' => $violationType->id,
                    'severity' => 'Low',
                    'offense' => '1st',
                    'violation_date' => now()->subDays(5),
                    'penalty' => 'W',
                    'consequence' => 'Student must report to the office before classes for one week.',
                    'status' => 'pending'
                ]);
                
                // Refresh the violations collection
                $violations = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }
        
        return view('educator.violation', ['violations' => $violations]);
    }

    /**
     * Show the form for creating a new violation
     */
    public function create()
    {
        // Get all students for the dropdown
        $students = User::role('student')
            ->whereNotNull('student_id')
            ->orderBy('lname')
            ->get();
            
        // Get all offense categories for the dropdown
        $offenseCategories = OffenseCategory::orderBy('category_name')->get();
        
        return view('educator.addViolator', [
            'students' => $students,
            'offenseCategories' => $offenseCategories
        ]);
    }

    /**
     * Store a newly created violation in storage
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'violation_date' => 'required|date',
            'violation_type_id' => 'required|exists:violation_types,id',
            'severity' => 'required|string|in:Low,Medium,High,Very High',
            'offense' => 'required|string|in:1st,2nd,3rd',
            'penalty' => 'required|string|in:W,VW,WW,Pro,Exp',
            'consequence' => 'required|string'
        ]);
        
        // Create the violation record
        $violation = Violation::create([
            'student_id' => $request->student_id,
            'violation_type_id' => $request->violation_type_id,
            'violation_date' => $request->violation_date,
            'severity' => $request->severity,
            'offense' => $request->offense,
            'penalty' => $request->penalty,
            'consequence' => $request->consequence,
            'status' => 'pending'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Violation recorded successfully',
            'data' => [
                'id' => $violation->id
            ]
        ]);
    }

    /**
     * Show the form for editing the specified violation
     */
    public function edit($id)
    {
        // Get the violation with relationships
        $violation = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
            ->findOrFail($id);
            
        // Get all students for the dropdown
        $students = User::role('student')
            ->whereNotNull('student_id')
            ->orderBy('lname')
            ->get();
            
        // Get all offense categories for the dropdown
        $offenseCategories = OffenseCategory::orderBy('category_name')->get();
        
        return view('educator.editViolation', [
            'violation' => $violation,
            'students' => $students,
            'offenseCategories' => $offenseCategories
        ]);
    }

    /**
     * Update the specified violation in storage
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'violation_date' => 'required|date',
            'violation_type_id' => 'required|exists:violation_types,id',
            'offense_category_id' => 'required|exists:offense_categories,id',
            'severity' => 'required',
            'offense' => 'required',
            'penalty' => 'required',
            'consequence' => 'required',
            'status' => 'required|in:pending,resolved,cancelled'
        ]);
        
        // Find the violation
        $violation = Violation::findOrFail($id);
        
        // Update the violation
        $violation->update($validated);
        
        return redirect()->route('educator.violation')
            ->with('success', 'Violation updated successfully');
    }

    /**
     * Get violation types by category
     */
    public function getViolationTypesByCategory($categoryId)
    {
        $violationTypes = DB::table('violation_types')
            ->where('offense_category_id', $categoryId)
            ->get();
            
        // Transform the data for the frontend
        $formattedTypes = $violationTypes->map(function($type) {
            return [
                'id' => $type->id,
                'name' => $type->violation_name,
                'default_penalty' => $type->default_penalty
            ];
        });
        
        return response()->json($formattedTypes);
    }

    /**
     * Get form data for the new violation type form
     */
    public function getFormData()
    {
        $categories = DB::table('offense_categories')->get();
        $severities = ['Low', 'Medium', 'High', 'Very High'];
        $offenses = ['1st', '2nd', '3rd'];
        $penalties = [
            ['value' => 'W', 'label' => 'Warning'],
            ['value' => 'VW', 'label' => 'Verbal Warning'],
            ['value' => 'WW', 'label' => 'Written Warning'],
            ['value' => 'Pro', 'label' => 'Probation'],
            ['value' => 'Exp', 'label' => 'Expulsion']
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'severities' => $severities,
                'offenses' => $offenses,
                'penalties' => $penalties
            ]
        ]);
    }

    /**
     * Store a new violation type
     */
    public function storeViolationType(Request $request)
    {
        // Validate the request
        $request->validate([
            'violation_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'severity' => 'required|string|in:Low,Medium,High,Very High',
            'offense' => 'required|string|in:1st,2nd,3rd',
            'penalty' => 'required|string|in:W,VW,WW,Pro,Exp'
        ]);
        
        // Find or create the offense category
        $category = DB::table('offense_categories')
            ->where('category_name', $request->category)
            ->first();
            
        if (!$category) {
            // If category doesn't exist, create it
            $categoryId = DB::table('offense_categories')->insertGetId([
                'category_name' => $request->category,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $categoryId = $category->id;
        }
        
        // Create the new violation type
        $violationTypeId = DB::table('violation_types')->insertGetId([
            'violation_name' => $request->violation_name,
            'offense_category_id' => $categoryId,
            'default_penalty' => $request->penalty,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Violation type added successfully',
            'data' => [
                'id' => $violationTypeId,
                'violation_name' => $request->violation_name,
                'category_id' => $categoryId,
                'category_name' => $request->category,
                'default_penalty' => $request->penalty
            ]
        ]);
    }
    
    /**
     * Show the form for adding a violator
     */
    public function addViolatorForm()
    {
        // Get students and offense categories for the form
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();
        
        $offenseCategories = OffenseCategory::all();
        
        // Define severities, offenses, and penalties for the form
        $severities = ['Low', 'Medium', 'High', 'Very High'];
        $offenses = ['1st', '2nd', '3rd'];
        $penalties = [
            ['value' => 'W', 'label' => 'Warning'],
            ['value' => 'VW', 'label' => 'Verbal Warning'],
            ['value' => 'WW', 'label' => 'Written Warning'],
            ['value' => 'Pro', 'label' => 'Probation'],
            ['value' => 'Exp', 'label' => 'Expulsion']
        ];
        
        // Set unreadCount for notifications
        $unreadCount = 0;
        
        return view('educator.addViolator', [
            'students' => $students,
            'offenseCategories' => $offenseCategories,
            'severities' => $severities,
            'offenses' => $offenses,
            'penalties' => $penalties,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Store a new violator record
     */
    public function addViolatorSubmit(Request $request)
    {
        // Handle form submission
        try {
            // Validate request
            $validated = $request->validate([
                'student_id' => 'required',
                'violation_date' => 'required|date',
                'violation_type_id' => 'required',
                'severity' => 'required',
                'offense' => 'required',
                'penalty' => 'required',
                'consequence' => 'nullable'
            ]);
            
            // Create the violation record
            $violation = new Violation([
                'student_id' => $validated['student_id'],
                'violation_type_id' => $validated['violation_type_id'],
                'severity' => $validated['severity'],
                'offense' => $validated['offense'],
                'violation_date' => $validated['violation_date'],
                'penalty' => $validated['penalty'],
                'consequence' => $validated['consequence'] ?? '',
                'recorded_by' => auth()->id(),
                'status' => 'active'
            ]);
            
            $violation->save();
            
            return response()->json(['success' => true, 'message' => 'Violation recorded successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show the form for editing a violation
     */
    public function editViolation($id)
    {
        // Get the violation with relationships
        $violation = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
            ->findOrFail($id);
        
        // Get all students for the dropdown
        $students = User::role('student')
            ->whereNotNull('student_id')
            ->orderBy('lname')
            ->get();
            
        // Get all offense categories for the dropdown
        $offenseCategories = OffenseCategory::orderBy('category_name')->get();
        
        // Get all violation types
        $violationTypes = ViolationType::all();
        
        return view('educator.editViolation', [
            'violation' => $violation,
            'students' => $students,
            'offenseCategories' => $offenseCategories,
            'violationTypes' => $violationTypes
        ]);
    }
    
    /**
     * Update a violation record
     */
    public function updateViolation(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'violation_date' => 'required|date',
            'violation_type_id' => 'required|exists:violation_types,id',
            'severity' => 'required|string|in:Low,Medium,High,Very High',
            'offense' => 'required|string|in:1st,2nd,3rd',
            'penalty' => 'required|string|in:W,VW,WW,Pro,Exp',
            'consequence' => 'required|string',
            'status' => 'required|in:active,resolved,cancelled'
        ]);
        
        // Find the violation
        $violation = Violation::findOrFail($id);
        
        // Update the violation
        $violation->update($validated);
        
        return redirect()->route('educator.violation')
            ->with('success', 'Violation updated successfully');
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
}
