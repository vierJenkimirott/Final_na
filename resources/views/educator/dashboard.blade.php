@extends('layouts.educator')

@section('title', 'Educator Dashboard')

@section('css')
<link rel="stylesheet" href="{{ asset('css/educator/educator.css') }}">
@endsection

@section('content')
<div class="toast-container" id="toastContainer"></div>

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
        <div class="btn-group" role="group" aria-label="Batch filter buttons">
            <button type="button" class="btn btn-outline-primary batch-filter active" data-batch="all" onclick="window.filterDataByBatch('all')">All Class</button>
            <button type="button" class="btn btn-outline-primary batch-filter" data-batch="2025" onclick="window.filterDataByBatch('2025')">Class 2025</button>
            <button type="button" class="btn btn-outline-primary batch-filter" data-batch="2026" onclick="window.filterDataByBatch('2026')">Class 2026</button>
        </div>
</div>

<!-- Stats Row -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card" style="height: 200px">
            <p class="title">Total Student Violations <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3 id="total-violations-count">{{ DB::table('violations')->where('status', 'active')->count() }}</h3>
            <p class="text-muted small">Active violations in the system</p>
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
                              ->where('violations.status', 'active');
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
                            <div class="violation-item">
                                <div class="violation-text">{{ $violation->violation_name }}</div>
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
                                ->where('violations.status', 'active')
                                ->select('users.name', 'users.student_id', DB::raw('count(violations.id) as violations_count'))
                                ->groupBy('users.id', 'users.name', 'users.student_id')
                                ->orderBy('violations_count', 'desc')
                                ->get();
                        @endphp
                        
                        @if($batch2025Violators->count() > 0)
                            @foreach($batch2025Violators as $violator)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $violator->name ?? 'Student' }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold">{{ $violator->name ?? 'Student' }}</h6>
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
                                          ->where('violations.status', 'active');
                                })
                                ->select('users.name', 'users.student_id')
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
                                ->where('violations.status', 'active')
                                ->select('users.name', 'users.student_id', DB::raw('count(violations.id) as violations_count'))
                                ->groupBy('users.id', 'users.name', 'users.student_id')
                                ->orderBy('violations_count', 'desc')
                                ->get();
                        @endphp
                        
                        @if($batch2026Violators->count() > 0)
                            @foreach($batch2026Violators as $violator)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $violator->name ?? 'Student' }}" class="profile-img" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px;">
                                    <div class="violator-info">
                                        <h6 class="mb-0 fw-bold">{{ $violator->name ?? 'Student' }}</h6>
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
                                          ->where('violations.status', 'active');
                                })
                                ->select('users.name', 'users.student_id')
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
        
        // Handle batch filter buttons
        document.querySelectorAll('.batch-filter-wrapper .btn, .batch-filter .btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons in the same group
                const parentGroup = this.closest('.btn-group');
                parentGroup.querySelectorAll('.btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get selected batch
                const batch = this.getAttribute('data-batch');
                
                // Update violation status chart with batch-specific data
                updateViolationStatusByBatch(batch);
                
                // Filter students by batch
                filterStudentsByBatch(batch);
            });
        });
        
        // Global function to filter data by batch (called from HTML)
        window.filterDataByBatch = function(batch) {
            console.log('Dashboard filterDataByBatch called with batch:', batch);
            
            // Update active state of batch filter buttons
            document.querySelectorAll('.batch-filter').forEach(button => {
                if (button.getAttribute('data-batch') === batch) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
            
            // Call the local function to filter students by batch
            filterStudentsByBatch(batch);
        };
        
        // Function to filter students by batch
        function filterStudentsByBatch(batch) {
            // Get the total students count element
            let studentCountElement = document.getElementById('total-students-count');
            // Get the total violations count element
            let violationsCountElement = document.getElementById('total-violations-count');
            
            // Make an AJAX request to get students by batch
            fetch(`/educator/students-by-batch?batch=${batch}`)
                .then(response => response.json())
                .then(data => {
                    // Update the student count
                    studentCountElement.textContent = data.count;
                })
                .catch(error => {
                    console.error('Error fetching students by batch:', error);
                });
                
            // Make an AJAX request to get violations count by batch
            fetch(`/educator/violations/count?batch=${batch}`)
                .then(response => response.json())
                .then(data => {
                    // Update the violations count
                    violationsCountElement.textContent = data.count;
                })
                .catch(error => {
                    console.error('Error fetching violations count by batch:', error);
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
                    
                    // Update chart data with the calculated percentages
                    violationStatusChart.data.datasets[0].data = [violatorPercentage, nonViolatorPercentage];
                    
                    // Update tooltip callback to use these percentages
                    violationStatusChart.options.plugins.tooltip.callbacks.label = function(context) {
                        const label = context.label || '';
                        const count = context.raw || 0;
                        const total = totalStudents;
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
                // Find the active batch filter button
                const activeBatchButton = document.querySelector('.batch-filter.active');
                batch = activeBatchButton ? activeBatchButton.getAttribute('data-batch') : 'all';
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
                        violationItem.className = 'violation-item';
                        violationItem.innerHTML = `
                            <div class="violation-text">${violation.violation_name}</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(violation.count / maxCount) * 100}%;"></div>
                            </div>
                        `;
                        violationList.appendChild(violationItem);
                    });
                    
                    // Show success message
                    const periodText = document.getElementById('violation-filter').options[document.getElementById('violation-filter').selectedIndex].text;
                    showToast(`Behavior data for ${periodText} loaded successfully`);
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
    });
</script>
@endpush


