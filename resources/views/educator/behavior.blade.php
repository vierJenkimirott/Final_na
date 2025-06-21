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
                            <h6>Total Students 
                            @if(isset($batchFilter) && $batchFilter === 'specific' && isset($batchYear))
                                (Batch {{ $batchYear }})
                            @elseif(isset($batchFilter) && $batchFilter === 'range' && isset($startYear) && isset($endYear))
                                (Batches {{ $startYear }}-{{ $endYear }})
                            @endif
                        </h6>
                        <h2 class="total-students">{{ $totalStudents }}</h2>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Students Needing Attention Card -->
            <div class="col-md-6">
                <a href="{{ route('educator.students-by-penalty', ['penalty' => 'WW']) }}{{ isset($batchFilter) && $batchFilter !== 'all' ? '?batch=' . $batchFilter . (isset($batchYear) ? '&batchYear=' . $batchYear : '') . (isset($startYear) && isset($endYear) ? '&startYear=' . $startYear . '&endYear=' . $endYear : '') : '' }}" class="stat-box-link">
                    <div class="stat-box">
                        <div class="stat-icon danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Students Needing Attention
                            @if(isset($batchFilter) && $batchFilter === 'specific' && isset($batchYear))
                                (Batch {{ $batchYear }})
                            @elseif(isset($batchFilter) && $batchFilter === 'range' && isset($startYear) && isset($endYear))
                                (Batches {{ $startYear }}-{{ $endYear }})
                            @endif
                        </h6>
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
                                        <input type="number" id="yearSelect" class="form-control form-control-sm" value="2025" min="1900" max="2100" onchange="window.updateChartByPeriod()">
                                        <small class="text-muted d-none">Enter any year</small>
                                    </div>
                                    <div style="width: 150px;">
                                        <select id="monthSelect" class="form-select form-select-sm" onchange="window.updateChartByPeriod()">
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
                                <label class="form-label mb-0"><i class="fas fa-users me-1 text-primary"></i> Batch Filter:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-filter"></i>
                                        </span>
                                        <select id="batchFilterType" class="form-select" onchange="window.toggleBatchFilterInput()">
                                            <option value="all" selected>All Batches</option>
                                            <option value="specific">Specific Batch</option>
                                            <option value="range">Batch Range</option>
                                        </select>
                                        <!-- Single batch year input (hidden by default) -->
                                        <input type="number" id="batchFilterYear" class="form-control" min="1900" max="2100" value="2025" style="display: none;" placeholder="Enter batch year">
                                        <!-- Batch range inputs (hidden by default) -->
                                        <input type="number" id="batchFilterStartYear" class="form-control" min="1900" max="2100" value="2023" style="display: none;" placeholder="Start year">
                                        <input type="number" id="batchFilterEndYear" class="form-control" min="1900" max="2100" value="2025" style="display: none;" placeholder="End year">
                                        <button class="btn btn-primary" id="applyBatchFilter" onclick="window.applyBatchFilter()" style="display: none;">Apply</button>
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
                    <div id="chartLoading" class="loading-indicator">
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Pass student counts and violation data to JavaScript -->
    <script>
        // Set global variables for student counts that will be used by behavior-charts.js
        window.totalStudents = {{ $totalStudents }};
        window.maleStudents = {{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->where('gender', 'male')->count() }};
        window.femaleStudents = {{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->where('gender', 'female')->count() }};
        
        // Get violation counts by month for male and female students
        window.maleViolationsByMonth = {
            @php
                // Get current year from request or use current year
                $currentYear = request()->input('year', date('Y'));
                
                // Get all months
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                // Get batch filter parameters
                $batchFilter = request()->query('batch', 'all');
                $batchYear = request()->query('batchYear');
                $startYear = request()->query('startYear');
                $endYear = request()->query('endYear');
                
                // Count violations by month for male students
                $maleViolationsByMonth = [];
                foreach ($months as $index => $month) {
                    $monthNum = $index + 1;
                    $startDate = "$currentYear-$monthNum-01";
                    $endDate = date('Y-m-t', strtotime($startDate));
                    
                    // Base query
                    $query = App\Models\Violation::where(function($q) {
                            $q->where('sex', 'male')
                              ->orWhere('sex', 'Male');
                        })
                        ->whereMonth('violation_date', $monthNum)
                        ->whereYear('violation_date', $currentYear)
                        ->where('status', 'active');
                    
                    // Apply batch filter
                    if ($batchFilter === 'specific' && $batchYear) {
                        $query->where('student_id', 'like', $batchYear . '%');
                    } elseif ($batchFilter === 'range' && $startYear && $endYear) {
                        $query->where(function($q) use ($startYear, $endYear) {
                            for ($year = $startYear; $year <= $endYear; $year++) {
                                $q->orWhere('student_id', 'like', $year . '%');
                            }
                        });
                    }
                    
                    $count = $query->count();
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
                    
                    // Base query
                    $query = App\Models\Violation::where(function($q) {
                            $q->where('sex', 'female')
                              ->orWhere('sex', 'Female');
                        })
                        ->whereMonth('violation_date', $monthNum)
                        ->whereYear('violation_date', $currentYear)
                        ->where('status', 'active');
                    
                    // Apply batch filter
                    if ($batchFilter === 'specific' && $batchYear) {
                        $query->where('student_id', 'like', $batchYear . '%');
                    } elseif ($batchFilter === 'range' && $startYear && $endYear) {
                        $query->where(function($q) use ($startYear, $endYear) {
                            for ($year = $startYear; $year <= $endYear; $year++) {
                                $q->orWhere('student_id', 'like', $year . '%');
                            }
                        });
                    }
                    
                    $count = $query->count();
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
                // Get current month from request or use current month
                $selectedMonth = request()->input('month', 'all');
                $currentMonth = ($selectedMonth === 'all') ? date('n') - 1 : (int)$selectedMonth; // 0-indexed for JavaScript
                $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                $monthName = $monthNames[$currentMonth];
                
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
                    
                    // Base query
                    $query = App\Models\Violation::where(function($q) {
                            $q->where('sex', 'male')
                              ->orWhere('sex', 'Male');
                        })
                        ->whereDate('violation_date', '>=', $startDate)
                        ->whereDate('violation_date', '<=', $endDate)
                        ->where('status', 'active');
                    
                    // Apply batch filter
                    if ($batchFilter === 'specific' && $batchYear) {
                        $query->where('student_id', 'like', $batchYear . '%');
                    } elseif ($batchFilter === 'range' && $startYear && $endYear) {
                        $query->where(function($q) use ($startYear, $endYear) {
                            for ($year = $startYear; $year <= $endYear; $year++) {
                                $q->orWhere('student_id', 'like', $year . '%');
                            }
                        });
                    }
                    
                    $count = $query->count();
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
                    
                    // Base query
                    $query = App\Models\Violation::where(function($q) {
                            $q->where('sex', 'female')
                              ->orWhere('sex', 'Female');
                        })
                        ->whereDate('violation_date', '>=', $startDate)
                        ->whereDate('violation_date', '<=', $endDate)
                        ->where('status', 'active');
                    
                    // Apply batch filter
                    if ($batchFilter === 'specific' && $batchYear) {
                        $query->where('student_id', 'like', $batchYear . '%');
                    } elseif ($batchFilter === 'range' && $startYear && $endYear) {
                        $query->where(function($q) use ($startYear, $endYear) {
                            for ($year = $startYear; $year <= $endYear; $year++) {
                                $q->orWhere('student_id', 'like', $year . '%');
                            }
                        });
                    }
                    
                    $count = $query->count();
                    $femaleViolationsByWeek["$monthName-Week $week"] = $count;
                }
            @endphp
            
            @foreach($femaleViolationsByWeek as $week => $count)
                '{{ $week }}': {{ $count }},
            @endforeach
        };
    </script>
    
    <script src="{{ asset('js/behavior-charts.js') }}"></script>
    
    <!-- Add our data fix script to ensure violations display correctly -->
    <script src="{{ asset('js/behavior-data-fix.js') }}"></script>
    
    <!-- Add our chart fix script to ensure chart functionality works correctly -->
    <script src="{{ asset('js/behavior-charts-fix.js') }}"></script>

    <!-- Add Bootstrap JS if not already included -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialize the behavior chart directly when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the chart using the function from behavior-charts.js
            if (typeof window.initBehaviorChart === 'function') {
                window.initBehaviorChart();
            } else {
                console.error('initBehaviorChart function not found. Make sure behavior-charts.js is loaded correctly.');
            }
        });
        
        // Initialize year and month dropdowns
        const yearSelect = document.getElementById('yearSelect');
        const monthSelect = document.getElementById('monthSelect');
        
        // Add event listeners to dropdowns
        yearSelect.addEventListener('change', function() {
            window.updateChartByPeriod();
        });
        
        monthSelect.addEventListener('change', function() {
            window.updateChartByPeriod();
        });

        // Initialize refresh button
        document.getElementById('refresh-behavior').addEventListener('click', function() {
            // Just call the updateChartByPeriod function directly
            window.updateChartByPeriod();
        });
        
        // Initialize batch filter controls
        document.getElementById('batchFilterType').addEventListener('change', function() {
            window.toggleBatchFilterInput();
        });
        
        document.getElementById('applyBatchFilter').addEventListener('click', function() {
            window.applyBatchFilter();
        });
        
        // Set initial batch filter values based on URL parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const batch = urlParams.get('batch');
            
            if (batch) {
                const batchFilterType = document.getElementById('batchFilterType');
                const batchFilterYear = document.getElementById('batchFilterYear');
                const batchFilterStartYear = document.getElementById('batchFilterStartYear');
                const batchFilterEndYear = document.getElementById('batchFilterEndYear');
                
                if (batch === 'specific') {
                    const batchYear = urlParams.get('batchYear');
                    if (batchYear) {
                        batchFilterType.value = 'specific';
                        batchFilterYear.value = batchYear;
                        window.toggleBatchFilterInput();
                        
                        // Store the batch filter settings in window variables
                        window.currentBatchFilter = 'specific';
                        window.batchYear = batchYear;
                    }
                } else if (batch === 'range') {
                    const startYear = urlParams.get('startYear');
                    const endYear = urlParams.get('endYear');
                    if (startYear && endYear) {
                        batchFilterType.value = 'range';
                        batchFilterStartYear.value = startYear;
                        batchFilterEndYear.value = endYear;
                        window.toggleBatchFilterInput();
                        
                        // Store the batch filter settings in window variables
                        window.currentBatchFilter = 'range';
                        window.batchStartYear = startYear;
                        window.batchEndYear = endYear;
                    }
                } else if (batch === 'all') {
                    batchFilterType.value = 'all';
                    window.currentBatchFilter = 'all';
                }
                
                // Trigger chart update with the current batch filter
                setTimeout(function() {
                    window.updateChartByPeriod();
                }, 500);
            }
        });
        
        // Initialize Y-axis scale filter buttons
        document.querySelectorAll('.y-scale-filter').forEach(button => {
            button.addEventListener('click', function() {
                const scale = this.getAttribute('data-scale');
                window.filterDataByYScale(scale);
                
                // Update active state
                document.querySelectorAll('.y-scale-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        
        // These functions have been moved to behavior-charts.js

        // This function has been moved to behavior-charts.js
        
        // This function has been moved to behavior-charts.js

        // Student search functionality
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Search functionality for all students modal
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
        });

        // This function has been moved to behavior-charts.js
        
        // Set up event listeners for the refresh button
        document.getElementById('refreshButton').addEventListener('click', function() {
            // Show loading indicator
            this.classList.add('btn-loading');
            
            // Update chart based on selected year and month
            window.updateChartByPeriod();
            
            // Hide loading indicator after a short delay
            setTimeout(() => {
                this.classList.remove('btn-loading');
            }, 500);
        });
        
        // Set up event listeners for the period buttons
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all period buttons
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get the period from the button's data attribute
                const months = parseInt(this.getAttribute('data-months') || 12);
                
                // Show loading indicator on the refresh button
                const refreshButton = document.getElementById('refreshButton');
                refreshButton.classList.add('btn-loading');
                
                // Generate data for the selected period
                const data = window.generateSampleData(months);
                
                // Update the chart after a short delay to show the loading indicator
                setTimeout(() => {
                    window.updateChart(data);
                    
                    // Hide loading indicator
                    refreshButton.classList.remove('btn-loading');
                    
                    // Show notification
                    window.showNotification('info', `Showing behavior data for the last ${months} months`, 'Time Period Changed!');
                }, 500);
            });
        });
        
        // Set up event listeners for student behavior test buttons
        document.querySelectorAll('.test-behavior-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                window.testStudentBehavior(studentId);
            });
        });
        
        // Set up event listeners for the student behavior modal period buttons
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
    });
    </script>
@endpush