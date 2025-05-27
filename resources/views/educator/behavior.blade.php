@extends('layouts.educator')

@section('title', 'Student Behavior Monitoring')

@section('css')  
    <link rel="stylesheet" href="{{ asset('css/behavior.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
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
        .behavior-toast {
            border-radius: 0.5rem;
            box-shadow: 0 4px 24px rgba(44,62,80,0.15);
            font-size: 1rem;
            padding: 1rem 1.5rem;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .behavior-toast {
            transition: opacity 0.3s;
        }
        h5{
            margin: 0;
            color:#2c3e50;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-1">
        <!-- Students List Modal -->
        <div class="modal fade" id="studentsListModal" tabindex="-1" aria-labelledby="studentsListModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="studentsListModalLabel"><i class="fas fa-users me-2"></i>All Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="studentSearch" class="form-control" placeholder="Search by name or ID...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Student ID</th>
                                        <th>Sex</th>
                                        <th>Violations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsList">
                                    <!-- Student rows will be populated here -->
                                    @foreach(\App\Models\User::whereNotNull('student_id')->get() as $student)
                                    <tr>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->student_id }}</td>
                                        <td>{{ $student->sex ?? 'Not specified' }}</td>
                                        <td>
                                            @php
                                                $violationCount = \App\Models\Violation::where('student_id', $student->student_id)->count();
                                            @endphp
                                            <span class="badge {{ $violationCount > 0 ? 'bg-danger' : 'bg-success' }}">
                                                {{ $violationCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('educator.student-violations', ['student_id' => $student->student_id]) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="mb-4">Student Behavior Monitoring</h2>
        
        <!-- Stat Cards Section -->
        <div class="row mb-4">
            <!-- Total Students Card -->
            <div class="col-md-6">
                <button type="button" class="stat-box-link btn btn-link p-0 border-0 w-100 text-start" id="total-students-btn" data-bs-toggle="modal" data-bs-target="#studentsListModal">
                    <div class="stat-box">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Total Students</h6>
                            <h2 class="total-students">{{ $totalStudents }}</h2>
                        </div>
                    </div>
                </button>
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
                            <h2 class="attention-students">{{ $studentsWithMultipleViolations }}</h2>
                            <div class="small text-muted mt-1">With more than 2 violations</div>
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
                
                <!-- Main chart section starts directly here -->
                
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
            const activeBtn = document.querySelector('.period-btn.active');
            const months = activeBtn ? parseInt(activeBtn.getAttribute('data-months')) : 12;
            
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
            let toast = document.createElement('div');
            toast.className = 'behavior-toast alert alert-info alert-dismissible fade show shadow';
            toast.style.position = 'fixed';
            toast.style.top = '1.5rem';
            toast.style.right = '1.5rem';
            toast.style.zIndex = '1080';
            toast.style.minWidth = '320px';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-lg me-2"></i>
                    <div>
                        <strong>Time Period Changed!</strong>
                        <div>Showing behavior data for the last ${periodText}.</div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
`;
// Remove any existing toasts
            document.querySelectorAll('.behavior-toast').forEach(el => el.remove());
            document.body.appendChild(toast);

// Auto-dismiss after 3 seconds
            setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
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
        // Student search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const studentSearch = document.getElementById('studentSearch');
            if (studentSearch) {
                studentSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const studentRows = document.querySelectorAll('#studentsList tr');
                    
                    studentRows.forEach(row => {
                        const name = row.cells[0].textContent.toLowerCase();
                        const studentId = row.cells[1].textContent.toLowerCase();
                        const searchText = name + ' ' + studentId;
                        
                        if (searchText.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@endpush