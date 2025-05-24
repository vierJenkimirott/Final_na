@extends('layouts.educator')

@section('title', 'Educator Dashboard')

@section('css')
<style>
    /* Violation Report Card Styling */
    .violation-report-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 20px;
        height: 100%;
    }
    
    .violation-report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .violation-report-header h2 {
        font-size: 1.5rem;
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    
    .filter-wrapper select {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
        font-size: 0.9rem;
    }
    
    .violation-stats {
        margin-bottom: 15px;
    }
    
    .violation-report-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .violation-item {
        margin-bottom: 15px;
    }
    
    .violation-text {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #333;
    }
    
    .progress {
        height: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .progress-bar {
        background-color: #007bff;
        height: 100%;
    }
    
    .violation-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        color: #6c757d;
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: auto;
    }
    
    .violation-info {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-refresh-violations {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
    }
    
    .btn-refresh-violations:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }
    
    .empty-state {
        text-align: center;
        padding: 30px 0;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #adb5bd;
    }
</style>
@endsection

@section('content')
<!-- Educator Profile Header -->
<div class="educator-header">
    <div class="last-login">
        <i class="fas fa-clock"></i> Last login: {{ date('M d, Y h:i A', strtotime(Auth::user()->last_login ?? now())) }}
    </div>
    <h1 class="educator-name">{{ Auth::user()->name }}</h1>
    <p class="educator-role">Educator <span class="badge bg-light text-primary" style="font-size: 0.7rem; vertical-align: middle;">Active</span></p>
    <div class="educator-stats">
        <div class="stat-item">
            <p class="stat-value">{{ $totalViolations }}</p>
            <p class="stat-label">Violations Logged</p>
        </div>
        <div class="stat-item">
            <p class="stat-value">{{ $totalStudents ?? 0 }}</p>
            <p class="stat-label">Students</p>
        </div>
        <div class="stat-item">
            <p class="stat-value">{{ now()->format('M Y') }}</p>
            <p class="stat-label">Current Period</p>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <p class="title">Total Student Violations <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3>{{ DB::table('violations')->where('status', 'active')->count() }}</h3>
            <p class="text-muted small">Active violations in the system</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="background-color: #2c3e50; color: white;">
            <p class="title">Current Date & Time <i class="fas fa-clock" style="margin-left: 5px;"></i></p>
            <h3 id="current-time">Loading...</h3>
        </div>
    </div>
</div>

<!-- Charts and Reports Row -->
<div class="row g-3 mt-3">
    <!-- Violation Status Overview Chart -->
    <div class="col-md-6">
        <div class="card violation-status-overview-card" style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%;">
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
            
            <div style="padding: 20px;">
                <h2 style="text-align: center; color: #2c3e50; font-size: 1.5rem; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Violation Status Overview</h2>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="violationStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Violation Report Card -->
    <div class="col-md-6">
        <div class="card violation-report-card" style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%;">
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

<!-- Top Violators Section -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card top-violators-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Top Violators</h5>
                <a href="{{ route('educator.dashboard') }}" class="btn btn-sm btn-outline-primary btn-refresh-violations" title="Refresh Data"><i class="fas fa-sync-alt"></i></a>
            </div>
            <div class="card-body">
                @php
                    // Ensure $topViolators is properly handled whether it's a collection or array
                    $hasViolators = false;
                    
                    if (isset($topViolators)) {
                        if (is_object($topViolators) && method_exists($topViolators, 'count')) {
                            $hasViolators = $topViolators->count() > 0;
                        } elseif (is_array($topViolators)) {
                            $hasViolators = count($topViolators) > 0;
                        }
                    }
                @endphp
                
                @if($hasViolators)
                    @foreach($topViolators as $violator)
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
                        <i class="fas fa-info-circle"></i> No violation records found.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        // Initialize pie chart
        const violationStatusChart = new Chart(
            document.getElementById('violationStatusChart').getContext('2d'), 
            {
                type: 'pie',
                data: {
                    labels: ['Violators', 'Non-Violators'],
                    datasets: [{
                        data: [{{ $violatorCount }}, {{ $nonViolatorCount }}],
                        backgroundColor: ['#FF6B6B', '#4CAF50'],
                        borderColor: ['#fff', '#fff'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function(label, i) {
                                            const meta = chart.getDatasetMeta(0);
                                            const style = meta.controller.getStyle(i);
                                            const count = data.datasets[0].data[i];
                                            const total = {{ $violatorCount + $nonViolatorCount }};
                                            const percentage = total > 0 ? Math.round((count / total) * 100 * 10) / 10 : 0;
                                            
                                            return {
                                                text: `${label} (${percentage}%) - ${count} students`,
                                                fillStyle: style.backgroundColor,
                                                strokeStyle: style.borderColor,
                                                lineWidth: style.borderWidth,
                                                hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const count = context.raw || 0;
                                    const total = {{ $violatorCount + $nonViolatorCount }};
                                    const percentage = total > 0 ? Math.round((count / total) * 100 * 10) / 10 : 0;
                                    return `${label}: ${percentage}% (${count} students)`;
                                }
                            }
                        }
                    }
                }
            }
        );
        
        // Violation report functions
        function updateViolationReport(period) {
            const violationList = document.getElementById('violation-list');
            violationList.innerHTML = '<div class="loading-container"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            fetch(`/api/violation-stats?period=${period}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                violationList.innerHTML = '';
                
                if (data.length > 0) {
                    const maxCount = Math.max(...data.map(item => item.count));
                    data.forEach(violation => {
                        const violationItem = document.createElement('div');
                        violationItem.className = 'violation-item';
                        violationItem.innerHTML = `
                            <span class="violation-name">
                                <span><i class="fas fa-exclamation-triangle mr-2"></i> ${violation.violation_name}</span>
                                <span class="violation-count">${violation.count}</span>
                            </span>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(violation.count / maxCount) * 100}%;"></div>
                            </div>
                        `;
                        violationList.appendChild(violationItem);
                    });
                } else {
                    violationList.innerHTML = '<div class="empty-state"><i class="fas fa-clipboard-check"></i><h5>No Violations</h5><p class="text-muted">No violations recorded for this period.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error fetching violation stats:', error);
                violationList.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h5>Error Loading Data</h5><p class="text-muted">There was a problem loading the violation data.</p></div>';
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
