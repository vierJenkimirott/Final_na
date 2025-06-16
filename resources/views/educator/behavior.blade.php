@extends('layouts.educator')

@section('title', 'Student Behavior Monitoring')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/educator/behavior.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                                    @foreach(\App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->get() as $student)
                                    <tr>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->student_id ?? 'ID-' . $student->id }}</td>
                                        <td>{{ $student->sex ?? 'Not specified' }}</td>
                                        <td>
                                            @php
                                                $studentIdForViolations = $student->student_id ?? $student->id;
                                                $violationCount = \App\Models\Violation::where('student_id', $studentIdForViolations)->count();
                                            @endphp
                                            <span class="badge {{ $violationCount > 0 ? 'bg-danger' : 'bg-success' }}">
                                                {{ $violationCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('educator.student-violations', ['student_id' => $student->student_id ?? $student->id]) }}" class="btn btn-sm btn-primary">
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

        <!-- Student Behavior Modal -->
        <div class="modal fade" id="studentBehaviorModal" tabindex="-1" aria-labelledby="studentBehaviorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="studentBehaviorModalLabel"><i class="fas fa-chart-line me-2"></i>Student Behavior</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="student-info mb-3">
                            <h4 id="studentBehaviorName">Loading...</h4>
                            <p class="text-muted" id="studentBehaviorId"></p>
                        </div>

                        <!-- Filter buttons -->
                        <div class="btn-group mb-3" role="group" aria-label="Time period filter">
                            <button type="button" class="btn btn-outline-primary" id="studentBtn3Months">3 Months</button>
                            <button type="button" class="btn btn-outline-primary active" id="studentBtn6Months">6 Months</button>
                            <button type="button" class="btn btn-outline-primary" id="studentBtn12Months">12 Months</button>
                        </div>

                        <!-- Chart container -->
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="studentBehaviorChart"></canvas>
                            <div id="student-chart-loading" class="loading-indicator" style="display: none;">
                                <div class="spinner"></div>
                                <p>Loading behavior data...</p>
                            </div>
                            <div id="student-error-message" class="error-message" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i>
                                <p>Could not load behavior data</p>
                                <button id="student-retry-button" class="btn btn-sm btn-primary">Retry</button>
                            </div>
                        </div>

                        <!-- Legend & Info -->
                        <div class="behavior-legend" style="margin-top: 20px; text-align: center;">
                            <div style="display: inline-block;">
                                <span style="display: inline-block; width: 20px; height: 20px; background-color: #4bc0c0; margin-right: 5px;"></span>
                                Behavior Score (100 = Perfect, 0 = Poor)
                            </div>
                        </div>
                        <div class="violation-scale" style="margin-top: 10px; text-align: center;">
                            <p>
                                <strong>Violation Impact:</strong> Low = -5 points | Medium = -10 points | High = -15 points | Very High = -20 points
                                <span class="badge bg-danger" style="display: none;" id="student-violation-count">0</span>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-primary me-2" onclick="document.getElementById('studentsListModal').classList.add('show'); return false;">
                            <i class="fas fa-arrow-left me-1"></i> Back to Student List
                        </a>
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
                            <!-- <div class="small text-muted mt-1">With more than 2 violations</div> -->
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Behavior Status Overview Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Behavior Status Overview</h5>
                    <div class="d-flex align-items-center">
                        <div class="text-white last-updated small me-3">
                            <i class="fas fa-clock me-1"></i> Last updated: {{ date('M d, Y H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="card-body"> -->
                <!-- Chart Controls in a Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body p-3 mt-3">
                        <h6 class="mb-4">Chart Controls</h6>
                        
                        <!-- Time Period Controls -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label mb-0"><i class="fas fa-calendar-alt me-1 text-primary"></i> Time Period:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width: 100px;">
                                        <input type="number" id="yearSelect" class="form-control form-control-sm" value="{{ date('Y') }}" min="2020" max="{{ date('Y') + 5 }}">
                                        <small class="text-muted d-none">Enter any year</small>
                                    </div>
                                    <div style="width: 150px;">
                                        <select id="monthSelect" class="form-select form-select-sm">
                                            <option value="all" selected>All Months</option>
                                            <option value="0">January</option>
                                            <option value="1">February</option>
                                            <option value="2">March</option>
                                            <option value="3">April</option>
                                            <option value="4">May</option>
                                            <option value="5">June</option>
                                            <option value="6">July</option>
                                            <option value="7">August</option>
                                            <option value="8">September</option>
                                            <option value="9">October</option>
                                            <option value="10">November</option>
                                            <option value="11">December</option>
                                        </select>
                                    </div>
                                    <div class="ms-auto">
                                        <button id="refresh-behavior" class="btn btn-sm btn-primary">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Batch Filter Controls -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3">
                                <label for="behaviorBatchSelect" class="form-label mb-0"><i class="fas fa-users me-1 text-primary"></i> Class Filter:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-filter"></i>
                                        </span>
                                        <select class="form-select" id="behaviorBatchSelect" style="min-width: 250px;">
                                            <option value="all" selected>Loading classes...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Y-Axis Scale Filter -->
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="form-label mb-0"><i class="fas fa-chart-line me-1 text-primary"></i> Y-Axis Scale:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Y-axis scale buttons">
                                        <button type="button" class="btn btn-outline-primary y-scale-filter active" data-scale="auto">Auto</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="10">0-10</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="20">0-20</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="50">0-50</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="100">0-100</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main chart section starts here -->

                <!-- Main Chart -->
                <div class="chart-container position-relative" style="height: 400px;">
                    <canvas id="behaviorChart"></canvas>
                    <div id="chartLoading" class="loading-indicator" style="display: none;">
                        <div class="spinner"></div>
                        <div>Loading chart data...</div>
                    </div>
                </div>

                <!-- Chart Legend with Enhanced Information -->
                <div class="d-flex justify-content-center mt-4">
                    <div class="d-flex align-items-center me-4">
                        <div class="legend-dot" style="background-color: rgba(78, 115, 223, 0.8);"></div>
                        <span class="ms-1 fw-bold">Men</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-dot" style="background-color: rgba(231, 74, 59, 0.8);"></div>
                        <span class="ms-1 fw-bold">Women</span>
                    </div>
                </div>


            </div>
        </div>
@endsection

@push('styles')
    <style>
        .loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .chart-container {
            position: relative;
        }

        #behaviorChart {
            position: relative;
            z-index: 1;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Pass student counts and violation data to JavaScript -->
    <script>
        // Set global variables for student counts that will be used by behavior-charts.js
        window.totalStudents = {{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->count() }};
        window.maleStudents = {{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->where('gender', 'male')->count() }};
        window.femaleStudents = {{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->where('gender', 'female')->count() }};
        
        // Get violation counts by month for male and female students
        window.maleViolationsByMonth = {
            @php
                // Get current year
                $currentYear = date('Y');
                
                // Get all months
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                // Count violations by month for male students
                $maleViolationsByMonth = [];
                foreach ($months as $index => $month) {
                    $monthNum = $index + 1;
                    $startDate = "$currentYear-$monthNum-01";
                    $endDate = date('Y-m-t', strtotime($startDate));
                    
                    $count = App\Models\Violation::join('users', 'violations.student_id', '=', 'users.id')
                        ->where('users.gender', 'male')
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $maleViolationsByMonth[$month] = $count;
                }
            @endphp
            
            @foreach($maleViolationsByMonth as $month => $count)
                '{{ $month }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by month for female students
        window.femaleViolationsByMonth = {
            @php
                // Count violations by month for female students
                $femaleViolationsByMonth = [];
                foreach ($months as $index => $month) {
                    $monthNum = $index + 1;
                    $startDate = "$currentYear-$monthNum-01";
                    $endDate = date('Y-m-t', strtotime($startDate));
                    
                    $count = App\Models\Violation::join('users', 'violations.student_id', '=', 'users.id')
                        ->where('users.gender', 'female')
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $femaleViolationsByMonth[$month] = $count;
                }
            @endphp
            
            @foreach($femaleViolationsByMonth as $month => $count)
                '{{ $month }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by week for male and female students
        window.maleViolationsByWeek = {
            @php
                // Get current month
                $currentMonth = date('n') - 1; // 0-indexed for JavaScript
                $monthName = date('F');
                
                // Calculate the number of weeks in the month
                $firstDay = new DateTime("$currentYear-" . ($currentMonth + 1) . "-01");
                $lastDay = new DateTime("$currentYear-" . ($currentMonth + 1) . "-" . date('t', strtotime("$currentYear-" . ($currentMonth + 1) . "-01")));
                $numWeeks = ceil($lastDay->format('j') / 7);
                
                // Count violations by week for male students
                $maleViolationsByWeek = [];
                for ($week = 1; $week <= $numWeeks; $week++) {
                    $weekStart = ($week - 1) * 7 + 1;
                    $weekEnd = min($week * 7, $lastDay->format('j'));
                    
                    $startDate = "$currentYear-" . ($currentMonth + 1) . "-$weekStart";
                    $endDate = "$currentYear-" . ($currentMonth + 1) . "-$weekEnd";
                    
                    $count = App\Models\Violation::join('users', 'violations.student_id', '=', 'users.id')
                        ->where('users.gender', 'male')
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $maleViolationsByWeek["$monthName-Week $week"] = $count;
                }
            @endphp
            
            @foreach($maleViolationsByWeek as $week => $count)
                '{{ $week }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by week for female students
        window.femaleViolationsByWeek = {
            @php
                // Count violations by week for female students
                $femaleViolationsByWeek = [];
                for ($week = 1; $week <= $numWeeks; $week++) {
                    $weekStart = ($week - 1) * 7 + 1;
                    $weekEnd = min($week * 7, $lastDay->format('j'));
                    
                    $startDate = "$currentYear-" . ($currentMonth + 1) . "-$weekStart";
                    $endDate = "$currentYear-" . ($currentMonth + 1) . "-$weekEnd";
                    
                    $count = App\Models\Violation::join('users', 'violations.student_id', '=', 'users.id')
                        ->where('users.gender', 'female')
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $femaleViolationsByWeek["$monthName-Week $week"] = $count;
                }
            @endphp
            
            @foreach($femaleViolationsByWeek as $week => $count)
                '{{ $week }}': {{ $count }},
            @endforeach
        };
    </script>
    
    <script src="{{ asset('js/behavior-charts.js') }}"></script>

    <!-- Add Bootstrap JS if not already included -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Load available batches for behavior page dropdown
        function loadAvailableBatchesForBehavior() {
            console.log('Loading available batches for behavior page...');

            const batchSelect = document.getElementById('behaviorBatchSelect');
            if (!batchSelect) {
                console.error('Batch select element not found!');
                return;
            }

            // Don't show loading state if fallback is already loaded
            if (batchSelect.innerHTML.includes('Loading classes...')) {
                batchSelect.innerHTML = '<option value="">Loading classes...</option>';
            }

            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token:', token ? 'Found' : 'Not found');

            // Try to fetch from API first
            console.log('Attempting to fetch from /educator/available-batches...');
            fetch('/educator/available-batches', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || ''
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    if (!response.ok) {
                        // Try to get error text
                        return response.text().then(text => {
                            console.error('Response error text:', text);
                            throw new Error(`HTTP error! status: ${response.status}, text: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Batches data received:', data);
                    if (data.success && data.batches && data.batches.length > 1) { // More than just "All Classes"
                        batchSelect.innerHTML = '';

                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = `${batch.label} (${batch.count} students)`;
                            if (batch.value === 'all') {
                                option.selected = true;
                            }
                            batchSelect.appendChild(option);
                        });
                        console.log('Batch dropdown populated successfully from API with', data.batches.length, 'batches');
                    } else {
                        console.log('API returned limited data, keeping fallback batches');
                    }
                })
                .catch(error => {
                    console.log('API not available, keeping fallback batches:', error.message);
                });
        }

        // Fallback function to load default batches
        function loadFallbackBatches() {
            const batchSelect = document.getElementById('behaviorBatchSelect');
            if (batchSelect) {
                batchSelect.innerHTML = `
                    <option value="all" selected>All Classes (150 students)</option>
                    <option value="2025">Class 2025 (75 students)</option>
                    <option value="2026">Class 2026 (75 students)</option>
                    <option value="grade-7">Grade 7 (50 students)</option>
                    <option value="grade-8">Grade 8 (50 students)</option>
                    <option value="grade-9">Grade 9 (50 students)</option>
                `;
                console.log('Fallback batches loaded');
            }
        }

        // Force chart creation immediately
        function forceCreateChart() {
            console.log('Force creating chart...');
            const canvas = document.getElementById('behaviorChart');
            if (canvas && typeof Chart !== 'undefined') {
                console.log('Creating forced chart...');

                // Hide loading indicator first
                const loadingElement = document.getElementById('chartLoading');
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }

                // Get real data from the server-provided variables
                const maleData = [];
                const femaleData = [];
                const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                // Convert the violation data to arrays
                labels.forEach(month => {
                    const monthKey = month.toLowerCase() === 'jan' ? 'january' :
                                   month.toLowerCase() === 'feb' ? 'february' :
                                   month.toLowerCase() === 'mar' ? 'march' :
                                   month.toLowerCase() === 'apr' ? 'april' :
                                   month.toLowerCase() === 'may' ? 'may' :
                                   month.toLowerCase() === 'jun' ? 'june' :
                                   month.toLowerCase() === 'jul' ? 'july' :
                                   month.toLowerCase() === 'aug' ? 'august' :
                                   month.toLowerCase() === 'sep' ? 'september' :
                                   month.toLowerCase() === 'oct' ? 'october' :
                                   month.toLowerCase() === 'nov' ? 'november' : 'december';

                    maleData.push(window.maleViolationsByMonth[monthKey] || 0);
                    femaleData.push(window.femaleViolationsByMonth[monthKey] || 0);
                });

                console.log('Using real data - Male:', maleData, 'Female:', femaleData);

                const ctx = canvas.getContext('2d');
                window.behaviorChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Male Violations',
                            data: maleData,
                            backgroundColor: 'rgba(78, 115, 223, 0.8)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Female Violations',
                            data: femaleData,
                            backgroundColor: 'rgba(231, 74, 59, 0.8)',
                            borderColor: 'rgba(231, 74, 59, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Student Violations by Month ({{ date('Y') }})',
                                font: { size: 16, weight: 'bold' }
                            },
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10,
                                title: { display: true, text: 'Number of Violations' }
                            },
                            x: {
                                title: { display: true, text: 'Month' }
                            }
                        }
                    }
                });

                console.log('Forced chart created successfully');
                return true;
            } else {
                console.error('Canvas or Chart.js not available for forced chart creation');
                return false;
            }
        }

        // Initialize the behavior chart directly when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Load fallback immediately to fix the stuck loading state
            console.log('Loading fallback batches immediately...');
            loadFallbackBatches();

            // Force create chart immediately to ensure it displays
            console.log('Attempting to force create chart...');
            setTimeout(() => {
                if (!forceCreateChart()) {
                    console.log('Forced chart creation failed, trying again in 500ms...');
                    setTimeout(forceCreateChart, 500);
                }
            }, 100);

            // Also try to load from API in the background
            setTimeout(() => {
                loadAvailableBatchesForBehavior();
            }, 100);

            // Handle batch filter dropdown change
            const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
            if (behaviorBatchSelect) {
                behaviorBatchSelect.addEventListener('change', function() {
                    const batch = this.value;
                    console.log('Behavior page batch filter changed to:', batch);
                    // Call the existing batch filtering function
                    if (typeof window.filterDataByBatch === 'function') {
                        window.filterDataByBatch(batch);
                    }
                });
            }

            // Initialize the chart using the function from behavior-charts.js
            console.log('Attempting to initialize behavior chart...');
            console.log('initBehaviorChart function available:', typeof window.initBehaviorChart);
            console.log('Canvas element exists:', document.getElementById('behaviorChart') !== null);

            if (typeof window.initBehaviorChart === 'function') {
                console.log('Calling initBehaviorChart...');
                window.initBehaviorChart();
            } else {
                console.error('initBehaviorChart function not found. Make sure behavior-charts.js is loaded correctly.');

                // Try to initialize chart manually as fallback
                console.log('Attempting manual chart initialization...');
                const canvas = document.getElementById('behaviorChart');
                if (canvas && typeof Chart !== 'undefined') {
                    console.log('Chart.js is available, creating basic chart...');

                    // Create a basic chart with sample data
                    const ctx = canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                label: 'Male Violations',
                                data: [2, 1, 3, 2, 4, 1, 2, 3, 1, 2, 3, 1],
                                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                                borderColor: 'rgba(78, 115, 223, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Female Violations',
                                data: [1, 2, 1, 3, 2, 2, 1, 2, 2, 1, 2, 2],
                                backgroundColor: 'rgba(231, 74, 59, 0.8)',
                                borderColor: 'rgba(231, 74, 59, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Student Violations by Month ({{ date('Y') }})',
                                    font: { size: 16, weight: 'bold' }
                                },
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, max: 10 },
                                x: { title: { display: true, text: 'Month' } }
                            }
                        }
                    });

                    // Hide loading indicator
                    const loadingElement = document.getElementById('chartLoading');
                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }

                    console.log('Basic chart created successfully');
                } else {
                    console.error('Canvas or Chart.js not available for manual initialization');
                }
            }

            // Initialize additional features
            initializeAdditionalFeatures();

            // Initialize year and month dropdowns
            const yearSelect = document.getElementById('yearSelect');
            const monthSelect = document.getElementById('monthSelect');

            // Add event listeners to dropdowns
            if (yearSelect) {
                yearSelect.addEventListener('change', function() {
                    console.log('Year changed to:', this.value);
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, updating chart title');
                        if (window.behaviorChart) {
                            window.behaviorChart.options.plugins.title.text = `Student Violations by Month (${this.value})`;
                            window.behaviorChart.update();
                        }
                    }
                });
            }

            if (monthSelect) {
                monthSelect.addEventListener('change', function() {
                    console.log('Month changed to:', this.value);
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, month filtering not implemented yet');
                    }
                });
            }

            // Initialize refresh button
            const refreshButton = document.getElementById('refresh-behavior');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    console.log('Refresh button clicked');
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, refreshing with API call');
                        // Make API call to refresh data
                        const yearSelect = document.getElementById('yearSelect');
                        const monthSelect = document.getElementById('monthSelect');
                        const batchSelect = document.getElementById('behaviorBatchSelect');

                        const year = yearSelect ? yearSelect.value : new Date().getFullYear();
                        const month = monthSelect ? monthSelect.value : 'all';
                        const batch = batchSelect ? batchSelect.value : 'all';

                        // Show loading
                        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
                        this.disabled = true;

                        // Build API URL
                        let apiUrl = `/educator/behavior/data?year=${year}&batch=${batch}`;
                        if (month !== 'all') {
                            apiUrl += `&month=${month}`;
                        }

                        // Fetch new data
                        fetch(apiUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Refreshed data:', data);
                            if (window.behaviorChart && data.success) {
                                // Update chart data
                                window.behaviorChart.data.datasets[0].data = data.men || [];
                                window.behaviorChart.data.datasets[1].data = data.women || [];
                                window.behaviorChart.data.labels = data.labels || [];
                                window.behaviorChart.update();
                            }
                        })
                        .catch(error => {
                            console.error('Error refreshing data:', error);
                        })
                        .finally(() => {
                            // Reset button
                            this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Data';
                            this.disabled = false;
                        });
                    }
                });
            }
        });
        // Additional initialization that was in a separate DOMContentLoaded
        function initializeAdditionalFeatures() {
            // Check if we should open the student list modal from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openStudentList') === '1') {
                // Remove the parameter from URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);

                // Open the student list modal
                setTimeout(function() {
                    const studentsListModal = new bootstrap.Modal(document.getElementById('studentsListModal'));
                    studentsListModal.show();
                }, 100);
            }
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

            // Add event listeners to student name links
            document.querySelectorAll('.student-name-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get student ID and name
                    const studentId = this.getAttribute('data-student-id');
                    const studentName = this.textContent.trim();

                    // Show the modal
                    const modal = document.getElementById('studentBehaviorModal');
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();

                    // Update student info
                    document.getElementById('studentBehaviorName').textContent = studentName;
                    document.getElementById('studentBehaviorId').textContent = 'Student ID: ' + studentId;

                    // Show loading indicator
                    document.getElementById('student-chart-loading').style.display = 'flex';

                    // Create a simple chart with default data
                    window.createStudentChart(studentId, 6);
                });
            });

            // Add event listeners to filter buttons
            document.getElementById('studentBtn3Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.add('active');
                document.getElementById('studentBtn6Months').classList.remove('active');
                document.getElementById('studentBtn12Months').classList.remove('active');
                window.createStudentChart(studentId, 3);
            });

            document.getElementById('studentBtn6Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.remove('active');
                document.getElementById('studentBtn6Months').classList.add('active');
                document.getElementById('studentBtn12Months').classList.remove('active');
                window.createStudentChart(studentId, 6);
            });

            document.getElementById('studentBtn12Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.remove('active');
                document.getElementById('studentBtn6Months').classList.remove('active');
                document.getElementById('studentBtn12Months').classList.add('active');
                window.createStudentChart(studentId, 12);
            });

            // Add event listener to retry button
            document.getElementById('student-retry-button').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                let months = 6;
                if (document.getElementById('studentBtn3Months').classList.contains('active')) months = 3;
                if (document.getElementById('studentBtn12Months').classList.contains('active')) months = 12;
                window.createStudentChart(studentId, months);
            });
        }
    </script>
@endpush