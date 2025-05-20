@extends('layouts.educator')

@section('title', 'Student Behavior Monitoring')

@section('css')  
    <link rel="stylesheet" href="{{ asset('css/behavior-charts.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Stats Cards */
        .stat-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            align-items: center;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #fff;
        }
        
        .stat-icon.primary { background-color: #4e73df; }
        .stat-icon.warning { background-color: #f6c23e; }
        .stat-icon.danger { background-color: #e74a3b; }
        
        .stat-content h6 {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #5a5c69;
        }
        
        .stat-content h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0;
        }
        
        /* Period Buttons */
        .period-btn {
            background-color: #fff;
            border: 1px solid #4e73df;
            color: #4e73df;
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .period-btn.active {
            background-color: #4e73df;
            color: white;
        }
        
        .period-btn:hover:not(.active) {
            background-color: rgba(78, 115, 223, 0.1);
        }
        
        /* Export Button */
        .btn-export {
            background-color: #e74a3b;
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-export:hover {
            background-color: #d52a1a;
        }
        
        /* Severity Cards */
        .severity-card {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 1rem;
            height: 100%;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .severity-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .severity-value {
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        /* Clickable Stat Boxes */
        .stat-box-link {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .stat-box-link:hover .stat-box {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-box {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .severity-low { color: #1cc88a; }
        .severity-medium { color: #f6c23e; }
        .severity-high { color: #e74a3b; }
        .severity-very-high { color: #6f42c1; }
        
        /* Legend */
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Student Behavior Monitoring</h2>
        
        <!-- Stat Cards Section -->
        <div class="row mb-4">
            <!-- Total Students Card -->
            <div class="col-md-6">
                <a href="{{ route('educator.student-violations') }}" class="stat-box-link">
                    <div class="stat-box">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Total Students</h6>
                            <h2 class="total-students">5</h2>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Students Needing Attention Card -->
            <div class="col-md-6">
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}" class="stat-box-link">
                    <div class="stat-box">
                        <div class="stat-icon danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Students Needing Attention</h6>
                            <h2 class="attention-students">0</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Behavior Status Overview Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-gradient-primary-to-secondary text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Behavior Status Overview</h5>
                    <div class="d-flex align-items-center">
                        <div class="text-white last-updated small me-3">
                            <i class="fas fa-clock me-1"></i> Last updated: {{ date('M d, Y H:i:s') }}
                        </div>
                        <button id="print-chart" class="btn btn-sm btn-light me-2">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Time Period and Refresh Controls -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <span class="text-primary fw-bold me-2"><i class="fas fa-calendar-alt me-1"></i> Time Period:</span>
                        <div class="btn-group" role="group" aria-label="Time period selection">
                            <button type="button" class="period-btn" data-months="3">3 Months</button>
                            <button type="button" class="period-btn" data-months="6">6 Months</button>
                            <button type="button" class="period-btn active" data-months="12">12 Months</button>
                        </div>
                    </div>
                    <div>
                        <button id="refresh-behavior" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
                
                <!-- Chart Analytics Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100 border-left-primary shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fas fa-chart-bar fa-2x text-primary"></i>
                                    </div>
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average Score</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="average-score">85</div>
                                        <div class="small text-success mt-1" id="score-trend"><i class="fas fa-arrow-up me-1"></i>3.2% from last period</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-left-success shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Excellent Behavior</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="excellent-count">65%</div>
                                        <div class="small text-success mt-1"><i class="fas fa-arrow-up me-1"></i>5.4% from last period</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-left-warning shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                    </div>
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Needs Improvement</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="needs-improvement-count">25%</div>
                                        <div class="small text-danger mt-1"><i class="fas fa-arrow-up me-1"></i>2.1% from last period</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-left-danger shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fas fa-flag fa-2x text-danger"></i>
                                    </div>
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Critical Cases</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="critical-count">10%</div>
                                        <div class="small text-danger mt-1"><i class="fas fa-arrow-up me-1"></i>1.5% from last period</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Chart -->
                <div class="chart-container position-relative" style="height: 400px;">
                    <canvas id="behaviorChart"></canvas>
                </div>
                
                <!-- Chart Legend with Enhanced Information -->
                <div class="d-flex justify-content-center mt-4">
                    <div class="d-flex align-items-center me-4">
                        <div class="legend-dot" style="background-color: rgba(78, 115, 223, 0.8);"></div>
                        <span class="ms-1 fw-bold">Men Behavior</span>
                        <span class="badge bg-primary ms-2" id="men-avg">85 pts</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-dot" style="background-color: rgba(231, 74, 59, 0.8);"></div>
                        <span class="ms-1 fw-bold">Women Behavior</span>
                        <span class="badge bg-danger ms-2" id="women-avg">88 pts</span>
                    </div>
                </div>
                
                <!-- Scoring System Explanation -->
                <div class="alert alert-info mt-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Behavior Scoring System</h6>
                            <p class="mb-0">Students start with a perfect score of 100 points. Violations reduce this score based on severity. Higher scores indicate better behavior.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Violation Impact Legend with Enhanced Design -->
                <div class="mt-4 mb-3">
                    <h6 class="text-center text-primary mb-3">Violation Impact on Behavior Score</h6>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <div class="px-3 py-2 rounded-pill shadow-sm" style="background-color: rgba(78, 115, 223, 0.8); color: white;">
                            <i class="fas fa-arrow-down me-1"></i> Low: -5 points
                        </div>
                        <div class="px-3 py-2 rounded-pill shadow-sm" style="background-color: rgba(246, 194, 62, 0.8); color: white;">
                            <i class="fas fa-arrow-down me-1"></i> Medium: -10 points
                        </div>
                        <div class="px-3 py-2 rounded-pill shadow-sm" style="background-color: rgba(231, 74, 59, 0.8); color: white;">
                            <i class="fas fa-arrow-down me-1"></i> High: -15 points
                        </div>
                        <div class="px-3 py-2 rounded-pill shadow-sm" style="background-color: rgba(111, 66, 193, 0.8); color: white;">
                            <i class="fas fa-arrow-down me-1"></i> Very High: -20 points
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="{{ asset('js/behavior-charts.js') }}"></script>
    
    <script>
        // Initialize period buttons
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Get the period from the button text
                const period = this.textContent.includes('3') ? 3 : 6;
                // Update the chart
                if (typeof fetchBehaviorData === 'function') {
                    fetchBehaviorData(period);
                }
            });
        });
        
        // Initialize export button
        document.querySelector('.btn-export').addEventListener('click', function() {
            const canvas = document.getElementById('behaviorChart');
            if (canvas) {
                // Create a temporary link for download
                const link = document.createElement('a');
                link.download = 'behavior-chart.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // Initialize refresh button
        document.getElementById('refresh-behavior').addEventListener('click', function() {
            // Show loading indicator
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            this.disabled = true;
            
            // Get current time period
            const months = parseInt(document.getElementById('time-period').value || 12);
            
            // Refresh the data
            fetchBehaviorData(months, true);
            
            // Reset button after a short delay
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                this.disabled = false;
            }, 1000);
        });
        
        // Initialize time period selector
        document.getElementById('time-period').addEventListener('change', function() {
            const months = parseInt(this.value || 12);
            
            // Show loading indicator on the refresh button
            const refreshBtn = document.getElementById('refresh-behavior');
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                refreshBtn.disabled = true;
            }
            
            // Update the chart with the selected time period
            fetchBehaviorData(months, true);
            
            // Show notification about the time period change
            const periodText = months === 3 ? '3 Months' : months === 6 ? '6 Months' : '12 Months';
            const notification = document.createElement('div');
            notification.className = 'alert alert-info alert-dismissible fade show';
            notification.innerHTML = `
                <strong>Time Period Changed!</strong> Showing behavior data for the last ${periodText}.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Add notification to the page
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertBefore(notification, container.firstChild);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
            
            // Reset refresh button after data is loaded
            setTimeout(() => {
                if (refreshBtn) {
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                    refreshBtn.disabled = false;
                }
            }, 1000);
        });
    </script>
@endpush