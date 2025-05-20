@extends('layouts.educator')

@section('title', 'Educator Dashboard')

@section('content')
<!-- Custom CSS Styles -->
<style>
    /* Base Styles */
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: none;
    }
    
    h2 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
        font-weight: 600;
    }
    
    .card h2 {
        font-size: 1.5rem;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    /* Educator Header */
    .educator-header {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        border-radius: 15px;
        padding: 20px 30px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        color: white;
    }
    
    .educator-header::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 150px; height: 100%;
        background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PC9zdmc+') repeat;
        opacity: 0.2;
    }
    
    .educator-name {
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    }
    
    .educator-role {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        margin-top: 5px;
    }
    
    .last-login {
        position: absolute;
        top: 20px; right: 30px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.85rem;
    }
    
    .last-login i { margin-right: 5px; }
    
    /* Stats Display */
    .educator-stats {
        display: flex;
        margin-top: 15px;
    }
    
    .stat-item {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 8px 15px;
        margin-right: 15px;
    }
    
    .stat-value {
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
        margin: 0;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.8rem;
        margin: 0;
    }
    
    /* Date Time Card */
    #date-time-card {
        background: linear-gradient(135deg, #2c3e50, #4ca1af);
        color: white;
        border: none;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    #date-time-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIgY3g9IjIwIiBjeT0iMjAiIHI9IjIwIi8+PC9zdmc+') repeat;
        opacity: 0.1;
        z-index: 0;
    }
    
    #date-time-card .title {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }
    
    #date-time-card h3 {
        color: white;
        font-weight: 600;
        font-size: 1.4rem;
        margin-top: 10px;
        position: relative;
        z-index: 1;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    #date-time-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    /* Violation Report Card */
    .violation-report-card {
        height: 400px;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        padding: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .violation-report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
    }
    
    .violation-report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
    }
    
    .violation-report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .violation-report-header h2 {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
        color: #333;
        display: flex;
        align-items: center;
    }
    
    .violation-report-header h2::before {
        content: '\f3ed';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 10px;
        color: #6a11cb;
    }
    
    /* Filter Dropdown */
    #violation-filter {
        width: 180px;
        padding: 10px 15px;
        border: 2px solid #eaeaea;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #444;
        background-color: #fff;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%236a11cb' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: calc(100% - 15px) center;
        padding-right: 40px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    #violation-filter:focus {
        outline: none;
        border-color: #6a11cb;
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.15);
    }
    
    #violation-filter:hover {
        border-color: #6a11cb;
    }
    
    /* Violation Stats */
    .violation-stats {
        overflow-y: auto;
        flex-grow: 1;
        padding-right: 5px;
    }
    
    .violation-stats::-webkit-scrollbar { width: 8px; }
    .violation-stats::-webkit-scrollbar-track { background: #f5f5f5; border-radius: 10px; }
    .violation-stats::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #6a11cb 0%, #2575fc 100%); border-radius: 10px; }
    .violation-stats::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #5a0cb1 0%, #1565e6 100%); }
    
    .violation-report-list {
        padding: 0;
    }
    
    /* Violation Item */
    .violation-item {
        margin-bottom: 20px;
        padding: 12px 15px;
        background-color: #f9f9f9;
        border-radius: 10px;
        transition: all 0.2s ease;
        border-left: 4px solid #6a11cb;
    }
    
    .violation-item:hover {
        background-color: #f0f0f0;
        transform: translateX(5px);
    }
    
    .violation-name {
        font-weight: 600;
        font-size: 1rem;
        color: #333;
        margin-bottom: 8px;
        display: block;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    /* Progress Bar */
    .progress {
        height: 12px;
        background-color: #e9ecef;
        border-radius: 10px;
        position: relative;
        margin-bottom: 5px;
        overflow: visible;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .progress-bar {
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        height: 100%;
        border-radius: 10px;
        position: relative;
        transition: width 0.6s ease;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.8; }
        100% { opacity: 1; }
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 0 10px 10px 0;
    }
    
    /* Count Display */
    .violation-count {
        display: inline-block;
        padding: 3px 10px;
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        border-radius: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        min-width: 30px;
        text-align: center;
    }
    
    /* Violation Footer */
    .violation-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }
    
    .violation-info {
        font-size: 0.85rem;
        color: #666;
    }
    
    .violation-info i {
        color: #6a11cb;
        margin-right: 5px;
    }
    
    .btn-refresh-violations {
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .btn-refresh-violations:hover {
        transform: rotate(180deg);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #6a11cb;
        margin-bottom: 15px;
        opacity: 0.7;
    }
    
    .empty-state h5 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    
    /* Loading Animation */
    .loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top: 4px solid #0d6efd;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 30px 20px;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    /* Student & Violator Info */
    .profile-img {
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    
    .profile-img:hover { transform: scale(1.05); }
    
    .violator-info { transition: transform 0.2s; }
    .violator-info:hover { transform: translateX(5px); }
    
    .badge-danger {
        background-color: #dc3545;
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 15px;
    }
</style>

<!-- Modern Educator Profile Header -->
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

<!-- Statistics Cards Row -->
<div class="row g-3">
    <!-- Violations Card -->
    <div class="col-md-6">
        <div class="card">
            <p class="title">Violation <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3>{{ $totalViolations }}</h3>
        </div>
    </div>
    
    <!-- Real-time Date and Time Card -->
    <div class="col-md-6">
        <div class="card" id="date-time-card">
            <p class="title">Current Date & Time <i class="fas fa-clock" style="color: #3a7bd5;"></i></p>
            <h3 id="current-time">{{ now()->format('M d, Y h:i:s A') }}</h3>
        </div>
    </div>
</div>

<!-- Add JavaScript for real-time clock -->
<script>
function updateClock() {
    const now = new Date();
    const options = { 
        weekday: 'long',
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
}

// Update the clock immediately and then every second
updateClock();
setInterval(updateClock, 1000);
</script>



<!-- Charts and Reports Row -->
<div class="row g-3 mt-3">
    <!-- Violation Status Overview Chart -->
    <div class="col-md-6">
        <div class="card violation-status-overview-card" style="height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h2 style="width: 100%; text-align: center;">Violation Status Overview</h2>
            <div style="width: 100%; height: 320px; display: flex; justify-content: center; align-items: center;">
                <canvas id="behaviorChart" style="max-width: 100%; max-height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Violation Report Card -->
    <div class="col-md-6">
        <div class="violation-report-card">
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
                        @php
                            $maxCount = $violationStats->max('count');
                        @endphp
                        @foreach($violationStats as $violation)
                            <div class="violation-item">
                                <span class="violation-name">
                                    <span><i class="fas fa-exclamation-triangle mr-2"></i> {{ $violation->violation_name }}</span>
                                    <span class="violation-count">{{ $violation->count }}</span>
                                </span>
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
        <div class="card" style="height: 350px; padding: 20px; overflow-y: auto;">
            <h2 class="text-left">Top Violators</h2>
            @forelse ($topViolators as $violator)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ asset('images/newprof.png')}}" alt="{{ $violator->fname }} {{ $violator->lname }}" class="profile-img" style="width: 80px; height: 80px; border-radius: 50%; margin-right: 15px;">
                    <div class="violator-info">
                        <h5 class="mb-0">{{ $violator->fname }} {{ $violator->lname }}</h5>
                        <p class="text-muted mb-0">{{ $violator->student_id }}</p>
                        <span class="badge badge-danger">{{ $violator->violation_count }} violations</span>
                    </div>
                </div>
            @empty
                <p class="text-muted">No violations recorded.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

<!-- JavaScript Section -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Behavior Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('behaviorChart').getContext('2d');
        
        // Calculate percentages for the chart
        const totalStudents = {{ $totalStudents }};
        const violatorCount = {{ $violatorCount }};
        const nonViolatorCount = {{ $nonViolatorCount }};
        
        const violatorPercentage = ((violatorCount / totalStudents) * 100).toFixed(1);
        const nonViolatorPercentage = ((nonViolatorCount / totalStudents) * 100).toFixed(1);
        
        // Chart configuration
        const data = {
            labels: [
                `Violators (${violatorPercentage}%)`,
                `Non-Violators (${nonViolatorPercentage}%)`
            ],
            datasets: [{
                data: [violatorCount, nonViolatorCount],
                backgroundColor: [
                    '#FF6B6B',  // Red for violators
                    '#4CAF50'   // Green for non-violators
                ],
                borderWidth: 0
            }]
        };

        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value} students`;
                            }
                        }
                    }
                }
            }
        };

        new Chart(ctx, config);
    });

    // Handle filter change for violation report
    document.getElementById('violation-filter').addEventListener('change', function() {
        const period = this.value;
        updateViolationReport(period);
    });
    
    // Initialize the refresh button for violation report
    document.querySelector('.btn-refresh-violations').addEventListener('click', function() {
        const period = document.getElementById('violation-filter').value;
        updateViolationReport(period);
    });
    
    // Function to update violation report via AJAX
    function updateViolationReport(period) {
        // Show loading state
        const violationList = document.getElementById('violation-list');
        violationList.innerHTML = `
            <div class="loading-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Make AJAX request to get violation stats
        fetch(`/api/violation-stats?period=${period}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Clear loading state
            violationList.innerHTML = '';
            
            if (data.length > 0) {
                // Find max count for percentage calculation
                const maxCount = Math.max(...data.map(item => item.count));
                
                // Generate HTML for each violation
                data.forEach(violation => {
                    const percentage = (violation.count / maxCount) * 100;
                    const violationItem = document.createElement('div');
                    violationItem.className = 'violation-item';
                    violationItem.innerHTML = `
                        <span class="violation-name">
                            <span><i class="fas fa-exclamation-triangle mr-2"></i> ${violation.violation_name}</span>
                            <span class="violation-count">${violation.count}</span>
                        </span>
                        <div class="progress">
                            <div class="progress-bar" style="width: ${percentage}%;"></div>
                        </div>
                    `;
                    violationList.appendChild(violationItem);
                });
            } else {
                // Show empty state
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

    // Initialize event listeners
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize violation report with current filter value
        const currentPeriod = document.getElementById('violation-filter').value;
        updateViolationReport(currentPeriod);
        
        // Set up spinner animation for the refresh button
        const refreshButton = document.querySelector('.btn-refresh-violations');
        refreshButton.addEventListener('mouseenter', function() {
            this.style.transform = 'rotate(180deg)';
        });
        refreshButton.addEventListener('mouseleave', function() {
            this.style.transform = 'rotate(0deg)';
        });
    });
</script>
@endpush
