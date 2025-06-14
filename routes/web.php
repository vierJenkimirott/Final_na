<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BehaviorDataController;
use App\Http\Controllers\EducatorController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\StudentManualController;
use App\Http\Controllers\EducatorManualController;
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
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');

    // User Management
    Route::get('/create-user', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.create_user');
    Route::post('/store-user', [\App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.store_user');
    Route::get('/edit-user/{id}', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.edit_user');
    Route::put('/update-user/{id}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.update_user');
    Route::delete('/delete-user/{id}', [\App\Http\Controllers\AdminController::class, 'destroyUser'])->name('admin.delete_user');

    // Student Management
    Route::get('/manage-student', [\App\Http\Controllers\AdminController::class, 'manageStudent'])->name('admin.manage_student');

    // Educator Management
    Route::get('/manage-educator', [\App\Http\Controllers\AdminController::class, 'manageEducator'])->name('admin.manage_educator');
});

// Educator Routes
Route::prefix('educator')->middleware(['auth', \App\Http\Middleware\EducatorMiddleware::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [EducatorController::class, 'dashboard'])->name('educator.dashboard');
    Route::get('/students-by-batch', [EducatorController::class, 'getStudentsByBatch'])->name('educator.students-by-batch');
    Route::get('/violations/count', [ViolationController::class, 'countViolationsByBatchFilter'])->name('educator.violations-count');

    // Violations Listing
    Route::get('/violation', [ViolationController::class, 'index'])->name('educator.violation');

    // Add Violator Form and Submission
    Route::get('/add-violator', [ViolationController::class, 'addViolatorForm'])->name('educator.add-violator-form');
    Route::post('/add-violator', [ViolationController::class, 'addViolatorSubmit'])->name('educator.add-violator');

    // View students by penalty type
    Route::get('/students-by-penalty/{penalty}', [EducatorController::class, 'studentsByPenalty'])->name('educator.students-by-penalty');

    // Edit and Update Violation
    Route::get('/edit-violation/{id}', [ViolationController::class, 'editViolation'])->name('educator.edit-violation');
    Route::put('/update-violation/{id}', [ViolationController::class, 'updateViolation'])->name('educator.update-violation');

    // View Violation
    Route::get('/view-violation/{id}', [EducatorController::class, 'viewViolation'])->name('educator.view-violation');

    // New Violation Type Form and Submission
    Route::get('/new-violation', [EducatorController::class, 'showViolationTypeForm'])->name('educator.new-violation');
    Route::get('/add-violation', [EducatorController::class, 'showViolationTypeForm'])->name('educator.add-violation');
    Route::post('/add-violation-type', [ViolationController::class, 'storeViolationType'])->name('educator.add-violation-type');

    // API Routes for Form Data
    Route::get('/violation-form-data', [ViolationController::class, 'getFormData'])->name('educator.violation-form-data');
    Route::get('/violation-types/{categoryId}', [ViolationController::class, 'getViolationTypesByCategory']);
    Route::get('/check-existing-violations', [ViolationController::class, 'checkExistingViolations'])->name('educator.check-existing-violations');

    // Violation Statistics Route
    Route::get('/violation-stats', [ViolationController::class, 'getViolationStatsByPeriod'])->name('educator.violation-stats');

    // Additional routes for the student dashboard
    Route::get('/student-violations', [ViolationController::class, 'studentViolations'])->name('educator.student-violations');

    // Route for filtering students by penalty
    Route::get('/students-by-penalty/{penalty}', [EducatorController::class, 'studentsByPenalty'])->name('educator.students-by-penalty');

    // Behavior routes
    Route::get('/behavior', [EducatorController::class, 'behavior'])->name('educator.behavior');
    Route::get('/behavior-data', [BehaviorDataController::class, 'getBehaviorData'])->name('educator.behavior-data');
    Route::get('/behavior/data', [BehaviorDataController::class, 'getBehaviorData'])->name('educator.behavior-data-alt');
    Route::get('/student-behavior-data/{student_id}', [EducatorController::class, 'getStudentBehaviorData'])->name('educator.student-behavior-data');
    Route::get('/student-behavior/{student_id}', [EducatorController::class, 'viewStudentBehavior'])->name('educator.view-student-behavior');
    Route::post('/clear-behavior-data', [EducatorController::class, 'clearBehaviorData'])->name('educator.clear-behavior-data');
    Route::get('/check-behavior-updates', [EducatorController::class, 'checkBehaviorUpdates'])->name('educator.check-behavior-updates');
    Route::get('/generate-sample-violations', [EducatorController::class, 'generateSampleViolations'])->name('educator.generate-sample-violations');

    // Manual edit routes
    Route::get('/manual/edit', [EducatorController::class, 'editManual'])->name('educator.manual.edit');
    Route::post('/manual/update', [EducatorController::class, 'updateManual'])->name('educator.manual.update');
    Route::post('/manual/delete-category', [EducatorController::class, 'deleteOffenseCategory'])->name('educator.manual.delete-category');
    Route::post('/manual/delete-violation-type', [EducatorController::class, 'deleteViolationType'])->name('educator.manual.delete-violation-type');
    Route::get('/student-manual', [StudentManualController::class, 'index'])->name('student-manual')->middleware('auth');
});

// API Routes
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/violation-stats', [ViolationController::class, 'getViolationStatsByPeriod'])->name('api.violation-stats');
    Route::get('/violation-stats-by-batch', [BehaviorDataController::class, 'getViolationStatsByBatch'])->name('api.violation-stats-by-batch');
    Route::get('/violations/count', [ViolationController::class, 'countViolationsByBatchFilter'])->name('api.violations-count');
    Route::get('/students/compliance', [EducatorController::class, 'getStudentComplianceByBatch'])->name('api.students-compliance');
    Route::get('/student-violations', [ViolationController::class, 'getStudentViolations'])->name('api.student-violations');
    Route::get('/violation-students', [ViolationController::class, 'getViolationStudents'])->name('api.violation-students');
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

    // For students
    Route::get('/student-manual', [StudentManualController::class, 'index'])->name('student.manual');

    // For educators
    Route::get('/educator-manual', [EducatorManualController::class, 'index'])->name('educator.manual');
});

Route::fallback(function () {
    return redirect('/login');
});
