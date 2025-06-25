@extends('layouts.educator')

@section('title', 'Educator Dashboard')

@section('css')
<link rel="stylesheet" href="{{ asset('css/educator/educator.css') }}">
<style>
    /* Violation Tooltip Styles */
    .violation-tooltip {
        position: fixed;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        padding: 8px;
        max-width: 280px;
        min-width: 220px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        font-size: 11px;
        line-height: 1.3;
    }

    /* Custom scrollbar for tooltip */
    .violation-tooltip::-webkit-scrollbar {
        width: 4px;
    }

    .violation-tooltip::-webkit-scrollbar-track {
        background: transparent;
    }

    .violation-tooltip::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 2px;
    }

    .violation-tooltip::-webkit-scrollbar-thumb:hover {
        background: #bbb;
    }

    /* Tooltip arrow for left positioning */
    .violation-tooltip::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 15px;
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-right: 6px solid #ddd;
    }

    .violation-tooltip::after {
        content: '';
        position: absolute;
        left: -5px;
        top: 15px;
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-right: 6px solid #fff;
    }

    /* Tooltip arrow for right positioning */
    .violation-tooltip.arrow-right::before {
        left: auto;
        right: -6px;
        border-right: none;
        border-left: 6px solid #ddd;
    }

    .violation-tooltip.arrow-right::after {
        left: auto;
        right: -5px;
        border-right: none;
        border-left: 6px solid #fff;
    }

    .violation-tooltip .tooltip-header {
        font-weight: bold;
        color: #dc3545;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }

    .violation-tooltip .tooltip-header {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #eee;
        font-size: 11px;
    }

    .violation-tooltip .violation-item {
        margin-bottom: 6px;
        padding: 6px;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 2px solid #dc3545;
    }

    .violation-tooltip .violation-item:last-child {
        margin-bottom: 0;
    }

    .violation-tooltip .violation-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
        font-size: 11px;
    }

    .violation-tooltip .violation-details {
        font-size: 10px;
        color: #666;
        margin-bottom: 1px;
    }

    .violation-tooltip .violation-date {
        font-size: 9px;
        color: #999;
        font-style: italic;
    }

    .violation-tooltip .violation-date {
        color: #007bff;
        font-weight: 500;
    }

    .violation-tooltip .violation-penalty {
        color: #dc3545;
        font-weight: 500;
    }

    .violation-tooltip .loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    .violation-tooltip .error {
        text-align: center;
        padding: 20px;
        color: #dc3545;
    }

    /* Violation Report Styles */
    .violation-text {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .violation-name {
        font-weight: 500;
        color: #333;
        flex: 1;
    }

    .violation-count {
        background: #dc3545;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        min-width: 24px;
        text-align: center;
    }

    /* Hoverable violation items */
    .hoverable-violation {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .hoverable-violation:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Violation Item Tooltip Styles */
    .violation-item-tooltip {
        position: fixed;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        padding: 12px;
        max-width: 350px;
        min-width: 250px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        font-size: 12px;
        line-height: 1.4;
    }

    .violation-item-tooltip::-webkit-scrollbar {
        width: 4px;
    }

    .violation-item-tooltip::-webkit-scrollbar-track {
        background: transparent;
    }

    .violation-item-tooltip::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 2px;
    }

    .violation-item-tooltip::-webkit-scrollbar-thumb:hover {
        background: #bbb;
    }

    .violation-item-tooltip .tooltip-header {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }

    .violation-item-tooltip .student-item {
        margin-bottom: 4px;
        padding: 6px 8px;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid #dc3545;
    }

    .violation-item-tooltip .student-item:last-child {
        margin-bottom: 0;
    }

    .violation-item-tooltip .student-name {
        font-weight: 500;
        color: #333;
        font-size: 12px;
        margin: 0 0 2px 0;
    }

    .violation-item-tooltip .student-date {
        font-size: 10px;
        color: #666;
        margin: 0;
        font-style: italic;
    }

    .violation-item-tooltip .loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    .violation-item-tooltip .error {
        text-align: center;
        padding: 20px;
        color: #dc3545;
    }

    /* Hover effect for student names */
    .violator-info h6.hoverable-student {
        cursor: pointer;
        transition: color 0.2s ease;
        position: relative;
    }

    .violator-info h6.hoverable-student:hover {
        color: #007bff;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

<!-- Student Violation Tooltip -->
<div id="violation-tooltip" class="violation-tooltip">
    <div class="tooltip-content">
        <!-- Content will be populated by JavaScript -->
    </div>
</div>

<!-- Violation Item Tooltip -->
<div id="violation-item-tooltip" class="violation-item-tooltip"></div>

<!-- Educator Profile Header -->
<div class="educator-header">
    <div class="last-login">
        <i class="fas fa-clock"></i> Last login: {{ date('M d, Y h:i A', strtotime(Auth::user()->last_login ?? now())) }}
    </div>
    <h1 class="educator-name">{{ Auth::user()->name }}</h1>
    <p class="educator-role">Educator <span class="badge bg-light text-primary" style="font-size: 0.7rem; vertical-align: middle;">Active</span></p>
    <div class="educator-stats" style="position: relative;">
        <div class="stat-item">
            <p class="stat-value">{{ $totalViolations }}</p>
            <p class="stat-label">Violations Logged</p>
        </div>
        <div class="stat-item">
            <p class="stat-value" id="total-students-count">{{ $totalStudents ?? 0 }}</p>
            <p class="stat-label">Students</p>
        </div>
        <div class="stat-item">
            <p class="stat-value">{{ now()->format('M Y') }}</p>
            <p class="stat-label">Current Period</p>
        </div>
    </div>
</div>

<div class="batch-filter mt-3 mb-3">
    <div class="d-flex align-items-center">
        <label for="batchSelect" class="form-label me-3 mb-0 fw-semibold">Filter by Class:</label>
        <div class="dropdown">
            <select class="form-select" id="batchSelect" style="min-width: 200px;">
                <option value="all" selected>Loading classes...</option>
            </select>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card" style="height: 200px">
            <p class="title">Total Student Violations <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3 id="total-violations-count">{{ DB::table('violations')->count() }}</h3>
            <p class="text-muted small">Total violations in the system</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="color: black; height: 200px">
            <p class="title mb-5">Current Date & Time <i class="fas fa-clock" style="margin-left: 5px;"></i></p>
            <h3 id="current-time">Loading...</h3>
        </div>
    </div>
</div>

<!-- Charts and Reports Row -->
<div class="row g-3 mt-3">
    <!-- Violation Status Overview Chart -->
    <div class="col-md-6">
        <div class="card violation-status-overview-card shadow-sm border-0" style="border-radius: 12px; height: 100%;">
            @php
                // Calculate violation statistics
                $totalStudents = DB::table('users')
                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'student')
                    ->count();
                
                $violatorCount = DB::table('users')
                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'student')
                    ->whereExists(function($query) {
                        $query->select(DB::raw(1))
                              ->from('violations')
                              ->whereRaw('violations.student_id = users.student_id')
                              ;
                    })
                    ->count();
                
                $nonViolatorCount = $totalStudents - $violatorCount;
                $violatorPercentage = $totalStudents > 0 ? round(($violatorCount / $totalStudents) * 100, 1) : 0;
                $nonViolatorPercentage = $totalStudents > 0 ? round(($nonViolatorCount / $totalStudents) * 100, 1) : 0;
            @endphp
            
            <div class="violation-status-header" style=" border-bottom: 1px solid rgba(0,0,0,0.05);">
                <h2>Violation Status Overview</h2>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-7">
                        <div style="position: relative; height: 200px; width: 100%;">
                            <canvas id="violationStatusChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="stats-container mt-3 mt-md-0">
                            <div class="stat-card d-flex align-items-center" style="border-bottom: 3px solid red">
                                <div class="stat-icon me-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-exclamation-triangle" style="color:rgb(234, 48, 88);"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Violators</h6>
                                    <div class="d-flex align-items-baseline">
                                        <h3 class="mb-0 me-2">{{ $violatorCount }}</h3>
                                        <span class="text-danger">{{ $violatorPercentage }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-card d-flex align-items-center" style="border-bottom: 3px solid green">
                                <div class="stat-icon me-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-check-circle" style="color:  #4CAF50"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Non-Violators</h6>
                                    <div class="d-flex align-items-baseline">
                                        <h3 class="mb-0 me-2">{{ $nonViolatorCount }}</h3>
                                        <span class="text-success">{{ $nonViolatorPercentage }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Violation Report Card -->
    <div class="col-md-6">
        <div class="card violation-report-card">
            <div class="violation-report-header">
                <h2>Violation Report</h2>
                <div class="filter-wrapper">
                    <select id="violation-filter">
                        <option value="month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="last_3_months">Last 3 Months</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>
            
            <div class="violation-stats">
                <div id="violation-list" class="violation-report-list">
                    @if(count($violationStats) > 0)
                        @php $maxCount = $violationStats->max('count'); @endphp
                        @foreach($violationStats as $violation)
                            <div class="violation-item hoverable-violation" data-violation-name="{{ $violation->violation_name }}">
                                <div class="violation-text">
                                    <span class="violation-name">{{ $violation->violation_name }}</span>
                                    <span class="violation-count">{{ $violation->count }}</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ ($violation->count / $maxCount) * 100 }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <h5>No Violations</h5>
                            <p class="text-muted">No violations recorded for this period.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="violation-footer">
                <span class="violation-info"><i class="fas fa-info-circle"></i> Showing top violations by frequency</span>
                <button class="btn-refresh-violations" title="Refresh Data"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Batch-Specific Students Section -->
<div class="row mt-3">
    <!-- Batch 2025 Students -->
    <div class="col-md-6">
        <div class="card top-violators-card">
            <div class="card-header">
                <h5 class="mb-0">Class 2025 Students</h5>
                <ul class="nav nav-tabs card-header-tabs" id="batch2025Tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="batch2025-violators-tab" data-bs-toggle="tab" data-bs-target="#batch2025-violators" type="button" role="tab" aria-controls="batch2025-violators" aria-selected="true">
                            Non-Compliant Students
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="batch2025-non-violators-tab" data-bs-toggle="tab" data-bs-target="#batch2025-non-violators" type="button" role="tab" aria-controls="batch2025-non-violators" aria-selected="false">
                            Compliant Students
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="batch2025TabsContent">
                    <!-- Batch 2025 Violators Tab -->
                    <div class="tab-pane fade show active" id="batch2025-violators" role="tabpanel" aria-labelledby="batch2025-violators-tab">
                        @php
                            $batch2025Violators = DB::table('users')
                                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                                ->join('violations', 'users.student_id', '=', 'violations.student_id')
                                ->where('roles.name', 'student')
                                ->where('users.student_id', 'like', '202501%')
                                
                                ->select('users.name', 'users.student_id', DB::raw('count(violations.id) as violations_count'))
                                ->groupBy('users.id', 'users.name', 'users.student_id')
                                ->orderBy('violations_count', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @if($batch2025Violators->count() > 0)
                            @foreach($batch2025Violators as $violator)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $violator->name ?? 'Student' }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold hoverable-student" data-student-id="{{ $violator->student_id }}">{{ $violator->name ?? 'Student' }}</h6>
                                        <p class="text-muted small mb-0">{{ $violator->student_id }}</p>
                                        <span class="badge bg-danger">{{ $violator->violations_count }} {{ Str::plural('violation', $violator->violations_count) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No violation records found for Batch 2025.
                            </div>
                        @endif
                    </div>

                    <!-- Batch 2025 Non-Violators Tab -->
                    <div class="tab-pane fade" id="batch2025-non-violators" role="tabpanel" aria-labelledby="batch2025-non-violators-tab">
                        @php
                            $batch2025NonViolators = DB::table('users')
                                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                                ->where('roles.name', 'student')
                                ->where('users.student_id', 'like', '202501%')
                                ->whereNotExists(function($query) {
                                    $query->select(DB::raw(1))
                                          ->from('violations')
                                          ->whereRaw('violations.student_id = users.student_id')
                                          ;
                                })
                                ->select('users.name', 'users.student_id')
                                ->limit(10)
                                ->get();
                        @endphp

                        @if($batch2025NonViolators->count() > 0)
                            @foreach($batch2025NonViolators as $student)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $student->name }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold">{{ $student->name }}</h6>
                                        <p class="text-muted small mb-0">{{ $student->student_id }}</p>
                                        <span class="badge bg-success">No Violations</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No compliant students found for Batch 2025.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Batch 2026 Students -->
    <div class="col-md-6">
        <div class="card top-violators-card">
            <div class="card-header">
                <h5 class="mb-0">Class 2026 Students</h5>
                <ul class="nav nav-tabs card-header-tabs" id="batch2026Tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="batch2026-violators-tab" data-bs-toggle="tab" data-bs-target="#batch2026-violators" type="button" role="tab" aria-controls="batch2026-violators" aria-selected="true">
                            Non-Compliant Students
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="batch2026-non-violators-tab" data-bs-toggle="tab" data-bs-target="#batch2026-non-violators" type="button" role="tab" aria-controls="batch2026-non-violators" aria-selected="false">
                            Compliant Students
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="batch2026TabsContent">
                    <!-- Batch 2026 Violators Tab -->
                    <div class="tab-pane fade show active" id="batch2026-violators" role="tabpanel" aria-labelledby="batch2026-violators-tab">
                        @php
                            $batch2026Violators = DB::table('users')
                                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                                ->join('violations', 'users.student_id', '=', 'violations.student_id')
                                ->where('roles.name', 'student')
                                ->where('users.student_id', 'like', '202601%')
                                
                                ->select('users.name', 'users.student_id', DB::raw('count(violations.id) as violations_count'))
                                ->groupBy('users.id', 'users.name', 'users.student_id')
                                ->orderBy('violations_count', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @if($batch2026Violators->count() > 0)
                            @foreach($batch2026Violators as $violator)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $violator->name ?? 'Student' }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold hoverable-student" data-student-id="{{ $violator->student_id }}">{{ $violator->name ?? 'Student' }}</h6>
                                        <p class="text-muted small mb-0">{{ $violator->student_id }}</p>
                                        <span class="badge bg-danger">{{ $violator->violations_count }} {{ Str::plural('violation', $violator->violations_count) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No violation records found for Batch 2026.
                            </div>
                        @endif
                    </div>

                    <!-- Batch 2026 Non-Violators Tab -->
                    <div class="tab-pane fade" id="batch2026-non-violators" role="tabpanel" aria-labelledby="batch2026-non-violators-tab">
                        @php
                            $batch2026NonViolators = DB::table('users')
                                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                                ->where('roles.name', 'student')
                                ->where('users.student_id', 'like', '202601%')
                                ->whereNotExists(function($query) {
                                    $query->select(DB::raw(1))
                                          ->from('violations')
                                          ->whereRaw('violations.student_id = users.student_id')
                                          ;
                                })
                                ->select('users.name', 'users.student_id')
                                ->limit(10)
                                ->get();
                        @endphp

                        @if($batch2026NonViolators->count() > 0)
                            @foreach($batch2026NonViolators as $student)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $student->name }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold">{{ $student->name }}</h6>
                                        <p class="text-muted small mb-0">{{ $student->student_id }}</p>
                                        <span class="badge bg-success">No Violations</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No compliant students found for Batch 2026.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/behavior-charts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load available batches for dropdown
        loadAvailableBatches();

        // Initialize clock
        function updateClock() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
        }
        updateClock();
        setInterval(updateClock, 1000);
        
        // Initialize doughnut chart for violation status
        const violationStatusChart = new Chart(
            document.getElementById('violationStatusChart').getContext('2d'), 
            {
                type: 'doughnut',
                data: {
                    labels: ['Violators', 'Non-Violators'],
                    datasets: [{
                        data: [{{ $violatorCount }}, {{ $nonViolatorCount }}],
                        backgroundColor: ['#FF6B6B', '#4CAF50'],
                        borderColor: ['#fff', '#fff'],
                        borderWidth: 2,
                        hoverOffset: 8,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false // We're showing custom legend in the stats cards
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const count = context.raw || 0;
                                    const total = {{ $violatorCount + $nonViolatorCount }};
                                    // Use the same percentage calculation as displayed in the chart
                                    let percentage;
                                    if (label === 'Violators') {
                                        percentage = {{ $violatorPercentage }};
                                    } else {
                                        percentage = {{ $nonViolatorPercentage }};
                                    }
                                    return `${label}: ${percentage}% (${count} students)`;
                                }
                            }
                        }
                    }
                }
            }
        );
        
        // Load available batches and populate dropdown
        function loadAvailableBatches() {
            fetch('/educator/available-batches')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const batchSelect = document.getElementById('batchSelect');
                        batchSelect.innerHTML = '';

                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = `${batch.label}`;
                            if (batch.value === 'all') {
                                option.selected = true;
                            }
                            batchSelect.appendChild(option);
                        });
                    } else {
                        console.error('Failed to load batches:', data.message);
                        // Fallback to default options
                        const batchSelect = document.getElementById('batchSelect');
                        batchSelect.innerHTML = `
    <option value="all" selected>All Classes</option>
    <option value="2025">Class 2025</option>
    <option value="2026">Class 2026</option>
`;
                    }
                })
                .catch(error => {
                    console.error('Error loading batches:', error);
                    // Fallback to default options
                    const batchSelect = document.getElementById('batchSelect');
                    batchSelect.innerHTML = `
    <option value="all" selected>All Classes</option>
    <option value="2025">Class 2025</option>
    <option value="2026">Class 2026</option>
`;
                });
        }

        // Handle batch filter dropdown change
        document.getElementById('batchSelect').addEventListener('change', function() {
            const batch = this.value;
            dashboardFilterByBatch(batch);
        });

        // Global function to filter data by batch (dashboard-specific)
        window.dashboardFilterByBatch = function(batch) {
            console.log('Dashboard filterDataByBatch called with batch:', batch);

            // Update dropdown selection
            const batchSelect = document.getElementById('batchSelect');
            if (batchSelect) {
                batchSelect.value = batch;
            }

            // Update violation status chart with batch-specific data
            updateViolationStatusByBatch(batch);

            // Filter students by batch
            filterStudentsByBatch(batch);
        };

        // Fallback global function for compatibility
        if (typeof window.filterDataByBatch === 'undefined') {
            window.filterDataByBatch = window.dashboardFilterByBatch;
        }

        // Local function to filter data by batch
        function dashboardFilterByBatch(batch) {
            // Update violation status chart with batch-specific data
            updateViolationStatusByBatch(batch);

            // Filter students by batch
            filterStudentsByBatch(batch);
        }
        
        // Function to filter students by batch
        function filterStudentsByBatch(batch) {
            // Get the total students count element
            let studentCountElement = document.getElementById('total-students-count');
            // Get the total violations count element
            let violationsCountElement = document.getElementById('total-violations-count');

            // Show loading state
            if (studentCountElement) studentCountElement.textContent = '...';
            if (violationsCountElement) violationsCountElement.textContent = '...';

            // Make an AJAX request to get students by batch
            fetch(`/educator/students-by-batch?batch=${batch}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && studentCountElement) {
                        studentCountElement.textContent = data.count;
                    } else if (studentCountElement) {
                        studentCountElement.textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching students by batch:', error);
                    if (studentCountElement) studentCountElement.textContent = 'Error';
                    showToast('Failed to load student count. Please try again.', 'error');
                });

            // Make an AJAX request to get violations count by batch
            fetch(`/educator/violations/count?batch=${batch}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && violationsCountElement) {
                        violationsCountElement.textContent = data.count;
                    } else if (violationsCountElement) {
                        violationsCountElement.textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching violations count by batch:', error);
                    if (violationsCountElement) violationsCountElement.textContent = 'Error';
                    showToast('Failed to load violations count. Please try again.', 'error');
                });

            // Update the violation report with the current period and selected batch
            const currentPeriod = document.getElementById('violation-filter').value;
            updateViolationReport(currentPeriod, batch);
        }
        
        // Function to show toast notifications
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast show align-items-center text-white bg-${type === 'info' ? 'primary' : type}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toastContainer.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        // Function to update violation status chart based on selected batch
        function updateViolationStatusByBatch(batch) {
            // Show loading state
            document.querySelector('.stats-container').innerHTML = `
                <div class="loading-state d-flex justify-content-center align-items-center" style="height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Fetch batch-specific violation stats from API
            fetch(`/api/violation-stats-by-batch?batch=${batch}`)
                .then(response => response.json())
                .then(data => {
                    // Calculate total students count
                    const totalStudents = data.violatorCount + data.nonViolatorCount;
                    
                    // Calculate percentages consistently
                    const violatorPercentage = totalStudents > 0 ? Math.round((data.violatorCount / totalStudents) * 100 * 10) / 10 : 0;
                    const nonViolatorPercentage = totalStudents > 0 ? Math.round((data.nonViolatorCount / totalStudents) * 100 * 10) / 10 : 0;
                    
                    // Update chart data with the actual counts
                    violationStatusChart.data.datasets[0].data = [data.violatorCount, data.nonViolatorCount];

                    // Update tooltip callback to use these counts and percentages
                    violationStatusChart.options.plugins.tooltip.callbacks.label = function(context) {
                        const label = context.label || '';
                        const count = context.raw || 0;
                        let percentage;
                        if (label === 'Violators') {
                            percentage = violatorPercentage;
                        } else {
                            percentage = nonViolatorPercentage;
                        }
                        return `${label}: ${percentage}% (${count} students)`;
                    };
                    
                    // Update the chart
                    violationStatusChart.update();
                    
                    // Update stats cards with consistent styling
                    document.querySelector('.stats-container').innerHTML = `
                        <div class="stat-card d-flex align-items-center" style="border-bottom: 3px solid red">
                            <div class="stat-icon me-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-exclamation-triangle" style="color:rgb(234, 48, 88);"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Violators</h6>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="mb-0 me-2">${data.violatorCount}</h3>
                                    <span class="text-danger">${violatorPercentage}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card d-flex align-items-center" style="border-bottom: 3px solid green">
                            <div class="stat-icon me-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-check-circle" style="color: #4CAF50"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Non-Violators</h6>
                                <div class="d-flex align-items-baseline">
                                    <h3 class="mb-0 me-2">${data.nonViolatorCount}</h3>
                                    <span class="text-success">${nonViolatorPercentage}%</span>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching batch data:', error);
                    showToast('Failed to load batch data. Please try again.', 'error');
                });
        }
        
        // Violation report functions
        function updateViolationReport(period, batch) {
            // If batch is not provided, use the current batch filter
            if (batch === undefined) {
                // Get the selected batch from dropdown
                const batchSelect = document.getElementById('batchSelect');
                batch = batchSelect ? batchSelect.value : 'all';
            }
            
            const violationList = document.getElementById('violation-list');
            violationList.innerHTML = `
                <div class="loading-overlay">
                    <div class="loading-message-container">
                        <div class="loading-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="loading-text">Updating violation statistics...</div>
                        </div>
                    </div>
                </div>
            `;
            
            fetch(`/api/violation-stats?period=${period}&batch=${batch}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                violationList.innerHTML = '';
                
                // Sort data by count in descending order and take only the top 5
                const top5Violations = data.sort((a, b) => b.count - a.count).slice(0, 5);

                if (top5Violations.length > 0) {
                    const maxCount = Math.max(...top5Violations.map(item => item.count));
                    top5Violations.forEach(violation => {
                        const violationItem = document.createElement('div');
                        violationItem.className = 'violation-item hoverable-violation';
                        violationItem.setAttribute('data-violation-name', violation.violation_name);
                        violationItem.innerHTML = `
                            <div class="violation-text">
                                <span class="violation-name">${violation.violation_name}</span>
                                <span class="violation-count">${violation.count}</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(violation.count / maxCount) * 100}%;"></div>
                            </div>
                        `;
                        violationList.appendChild(violationItem);
                    });
                    

                } else {
                    violationList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <h5>No Violations</h5>
                            <p class="text-muted">No violations recorded for this period.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching violation stats:', error);
                violationList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <h5>Error Loading Data</h5>
                        <p class="text-muted">There was a problem loading the violation data.</p>
                    </div>
                `;
            });
        }
        
        // Event listeners
        document.getElementById('violation-filter').addEventListener('change', function() {
            updateViolationReport(this.value);
        });
        
        const refreshButton = document.querySelector('.btn-refresh-violations');
        refreshButton.addEventListener('click', function() {
            updateViolationReport(document.getElementById('violation-filter').value);
        });
        
        refreshButton.addEventListener('mouseenter', function() {
            this.style.transform = 'rotate(180deg)';
        });
        
        refreshButton.addEventListener('mouseleave', function() {
            this.style.transform = 'rotate(0deg)';
        });
        
        // Initialize violation report
        updateViolationReport(document.getElementById('violation-filter').value);

        // Initialize violation tooltip functionality
        initializeViolationTooltip();

        // Initialize violation item tooltip functionality
        initializeViolationItemTooltip();
    });

    // Violation tooltip functionality
    function initializeViolationTooltip() {
        const tooltip = document.getElementById('violation-tooltip');
        let currentRequest = null;
        let hideTimeout = null;

        // Add event listeners to all hoverable student names
        document.addEventListener('mouseenter', function(e) {
            if (e.target.classList.contains('hoverable-student')) {
                const studentId = e.target.getAttribute('data-student-id');
                const studentName = e.target.textContent;

                // Clear any existing hide timeout
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }

                // Cancel any existing request
                if (currentRequest) {
                    currentRequest.abort();
                }

                // Show loading state
                tooltip.innerHTML = `
                    <div class="tooltip-header">${studentName}'s Violations</div>
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading violations...
                    </div>
                `;

                // Position and show tooltip
                positionTooltip(e.target, tooltip);
                tooltip.style.display = 'block';

                // Fetch violation data
                const controller = new AbortController();
                currentRequest = controller;

                fetch(`/api/student-violations?student_id=${encodeURIComponent(studentId)}`, {
                    signal: controller.signal,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.violations.length > 0) {
                        let violationsHtml = `<div class="tooltip-header">${studentName}'s Violations (${data.violations.length})</div>`;

                        data.violations.forEach(violation => {
                            const violationDate = new Date(violation.violation_date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });

                            violationsHtml += `
                                <div class="violation-item">
                                    <div class="violation-name">${violation.violation_name}</div>
                                    <div class="violation-details">${violation.violation_category || 'General Violation'}</div>
                                    <div class="violation-date">${violationDate}</div>
                                </div>
                            `;
                        });

                        tooltip.innerHTML = violationsHtml;
                    } else if (data.success && data.violations.length === 0) {
                        tooltip.innerHTML = `
                            <div class="tooltip-header">${studentName}'s Violations</div>
                            <div style="text-align: center; padding: 20px; color: #666;">
                                <i class="fas fa-info-circle"></i><br>
                                No violations found
                            </div>
                        `;
                    } else {
                        tooltip.innerHTML = `
                            <div class="tooltip-header">${studentName}'s Violations</div>
                            <div class="error">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Error loading violations
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        console.error('Error fetching violations:', error);
                        tooltip.innerHTML = `
                            <div class="tooltip-header">${studentName}'s Violations</div>
                            <div class="error">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Error loading violations
                            </div>
                        `;
                    }
                })
                .finally(() => {
                    currentRequest = null;
                });
            }
        }, true);

        // Hide tooltip when mouse leaves
        document.addEventListener('mouseleave', function(e) {
            if (e.target.classList.contains('hoverable-student')) {
                hideTimeout = setTimeout(() => {
                    tooltip.style.display = 'none';
                    if (currentRequest) {
                        currentRequest.abort();
                        currentRequest = null;
                    }
                }, 300); // Small delay to allow moving to tooltip
            }
        }, true);

        // Keep tooltip visible when hovering over it
        tooltip.addEventListener('mouseenter', function() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        });

        // Hide tooltip when leaving tooltip
        tooltip.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
            }
        });
    }

    // Position tooltip relative to the hovered element
    function positionTooltip(element, tooltip) {
        const rect = element.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Reset arrow classes
        tooltip.classList.remove('arrow-right');

        // Show tooltip temporarily to get its dimensions
        tooltip.style.visibility = 'hidden';
        tooltip.style.display = 'block';
        const tooltipRect = tooltip.getBoundingClientRect();
        tooltip.style.visibility = 'visible';

        // Position tooltip to the right of the element by default
        let left = rect.right + 10;
        let top = rect.top;

        // If tooltip would go off the right edge, position it to the left
        if (left + tooltipRect.width > viewportWidth - 10) {
            left = rect.left - tooltipRect.width - 10;
            tooltip.classList.add('arrow-right');
        }

        // Ensure tooltip doesn't go off the left edge
        if (left < 10) {
            left = 10;
        }

        // Adjust vertical position if tooltip would go off screen
        if (top + tooltipRect.height > viewportHeight - 10) {
            top = viewportHeight - tooltipRect.height - 10;
        }

        // Ensure tooltip doesn't go above the viewport
        if (top < 10) {
            top = 10;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    // Violation item tooltip functionality
    function initializeViolationItemTooltip() {
        const tooltip = document.getElementById('violation-item-tooltip');
        let currentRequest = null;
        let hideTimeout = null;

        // Add event listeners to all hoverable violation items
        document.addEventListener('mouseenter', function(e) {
            if (e.target.closest('.hoverable-violation')) {
                const violationElement = e.target.closest('.hoverable-violation');
                const violationName = violationElement.getAttribute('data-violation-name');

                // Clear any existing hide timeout
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }

                // Cancel any existing request
                if (currentRequest) {
                    currentRequest.abort();
                }

                // Show loading state
                tooltip.innerHTML = `
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                `;

                // Position and show tooltip
                positionViolationItemTooltip(violationElement, tooltip);
                tooltip.style.display = 'block';

                // Get current filter values
                const currentPeriod = document.getElementById('violation-filter').value;
                const batchSelect = document.getElementById('batchSelect');
                const currentBatch = batchSelect ? batchSelect.value : 'all';

                // Fetch violation students data
                const controller = new AbortController();
                currentRequest = controller;

                fetch(`/api/violation-students?violation_name=${encodeURIComponent(violationName)}&period=${currentPeriod}&batch=${currentBatch}`, {
                    signal: controller.signal,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.students.length > 0) {
                        let studentsHtml = '';

                        data.students.forEach(student => {
                            studentsHtml += `
                                <div class="student-item">
                                    <div class="student-name">${student.name}</div>
                                    <div class="student-date">${student.violation_date}</div>
                                </div>
                            `;
                        });

                        tooltip.innerHTML = studentsHtml;
                    } else if (data.success && data.students.length === 0) {
                        tooltip.innerHTML = `
                            <div style="text-align: center; padding: 20px; color: #666;">
                                <i class="fas fa-info-circle"></i><br>
                                No students found
                            </div>
                        `;
                    } else {
                        tooltip.innerHTML = `
                            <div class="error">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Error loading data
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        console.error('Error fetching violation students:', error);
                        tooltip.innerHTML = `
                            <div class="error">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Error loading data
                            </div>
                        `;
                    }
                })
                .finally(() => {
                    currentRequest = null;
                });
            }
        }, true);

        // Hide tooltip when mouse leaves
        document.addEventListener('mouseleave', function(e) {
            if (e.target.closest('.hoverable-violation')) {
                hideTimeout = setTimeout(() => {
                    tooltip.style.display = 'none';
                    if (currentRequest) {
                        currentRequest.abort();
                        currentRequest = null;
                    }
                }, 300); // Small delay to allow moving to tooltip
            }
        }, true);

        // Keep tooltip visible when hovering over it
        tooltip.addEventListener('mouseenter', function() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        });

        // Hide tooltip when leaving tooltip
        tooltip.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
            }
        });
    }

    // Position violation item tooltip relative to the hovered element
    function positionViolationItemTooltip(element, tooltip) {
        const rect = element.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Show tooltip temporarily to get its dimensions
        tooltip.style.visibility = 'hidden';
        tooltip.style.display = 'block';
        const tooltipRect = tooltip.getBoundingClientRect();
        tooltip.style.visibility = 'visible';

        // Position tooltip to the right of the element by default
        let left = rect.right + 10;
        let top = rect.top;

        // If tooltip would go off the right edge, position it to the left
        if (left + tooltipRect.width > viewportWidth - 10) {
            left = rect.left - tooltipRect.width - 10;
        }

        // Ensure tooltip doesn't go off the left edge
        if (left < 10) {
            left = 10;
        }

        // Adjust vertical position if tooltip would go off screen
        if (top + tooltipRect.height > viewportHeight - 10) {
            top = viewportHeight - tooltipRect.height - 10;
        }

        // Ensure tooltip doesn't go above the viewport
        if (top < 10) {
            top = 10;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }
</script>
@endpush


