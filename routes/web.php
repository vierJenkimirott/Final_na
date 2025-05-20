<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EducatorController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\StudentManualController;

Route::get('/', function () {
    return redirect('/login');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/student/logout', [AuthController::class, 'logout'])->name('student.logout');

// Admin Routes
Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// Educator Routes
Route::prefix('educator')->middleware(['auth', \App\Http\Middleware\EducatorMiddleware::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [EducatorController::class, 'dashboard'])->name('educator.dashboard');
    
    // Violations Listing
    Route::get('/violation', [ViolationController::class, 'index'])->name('educator.violation');
    
    // Add Violator Form and Submission
    Route::get('/add-violator', [ViolationController::class, 'addViolatorForm'])->name('educator.add-violator-form');
    Route::post('/add-violator', [ViolationController::class, 'addViolatorSubmit'])->name('educator.add-violator');
    
    // Edit and Update Violation
    Route::get('/edit-violation/{id}', [ViolationController::class, 'editViolation'])->name('educator.edit-violation');
    Route::put('/update-violation/{id}', [ViolationController::class, 'updateViolation'])->name('educator_update_violation');
    
    // View Violation
    Route::get('/view-violation/{id}', [EducatorController::class, 'viewViolation'])->name('educator.view-violation');
    
    // New Violation Type Form and Submission
    Route::get('/new-violation', [ViolationController::class, 'createViolationType'])->name('educator.new-violation');
    Route::get('/add-violation', [ViolationController::class, 'createViolationType'])->name('educator.add-violation');
    Route::post('/add-violation-type', [ViolationController::class, 'storeViolationType'])->name('educator.add-violation-type');
    
    // API Routes for Form Data
    Route::get('/violation-form-data', [ViolationController::class, 'getFormData'])->name('educator.violation-form-data');
    Route::get('/violation-types/{categoryId}', [ViolationController::class, 'getViolationTypesByCategory']);
    
    // Violation Statistics Route
    Route::get('/violation-stats', [ViolationController::class, 'getViolationStatsByPeriod'])->name('educator.violation-stats');
    
    // Additional routes for the student dashboard
    Route::get('/student-violations', [ViolationController::class, 'studentViolations'])->name('educator.student-violations');
    
    // Route for filtering students by penalty
    Route::get('/students-by-penalty/{penalty}', [EducatorController::class, 'studentsByPenalty'])->name('educator.students-by-penalty');
    
    // Behavior routes
    Route::get('/behavior', [EducatorController::class, 'behavior'])->name('educator.behavior');
    Route::get('/behavior-data', [EducatorController::class, 'getBehaviorData'])->name('educator.behavior-data');
    Route::post('/clear-behavior-data', [EducatorController::class, 'clearBehaviorData'])->name('educator.clear-behavior-data');
    Route::get('/check-behavior-updates', [EducatorController::class, 'checkBehaviorUpdates'])->name('educator.check-behavior-updates');
    Route::get('/generate-sample-violations', [EducatorController::class, 'generateSampleViolations'])->name('educator.generate-sample-violations');
    

});

// API Routes
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/violation-stats', [ViolationController::class, 'getViolationStatsByPeriod'])->name('api.violation-stats');
});

// Student routes
Route::prefix('student')->middleware(['auth'])->group(function () {
    // Dashboard (keeping for backward compatibility but redirecting to violations)
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    
    // Violation and behavior routes
    Route::get('/violation', [StudentController::class, 'violation'])->name('student.violation');
    Route::get('/behavior', [StudentController::class, 'behavior'])->name('student.behavior');
    Route::get('/behavior-data', [StudentController::class, 'getBehaviorData'])->name('student.behavior-data');
    Route::get('/check-violation-updates', [StudentController::class, 'checkForViolationUpdates'])->name('student.check-violation-updates');

    
    // Manual routes
    Route::get('/manual', [StudentManualController::class, 'index'])->name('student.manual');
    

    

});

Route::fallback(function () {
    return redirect('/login');
});
