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

        /* Student behavior modal styles */
        .student-name-link {
            color: #4e73df;
            text-decoration: none;
            font-weight: 500;
        }

        .student-name-link:hover {
            text-decoration: underline;
            color: #2e59d9;
        }

        .loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 10;
        }

        .loading-indicator .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4e73df;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        .error-message {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 10;
        }

        .error-message i {
            font-size: 40px;
            color: #e74a3b;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                                    @foreach(\App\Models\User::whereHas('roles', function($q) { $q->where('name', 'student'); })->get() as $student)
                                    <tr>
                                        <td>
                                            <a href="{{ route('educator.view-student-behavior', ['student_id' => $student->student_id ?? $student->id]) }}" class="student-name-link" data-student-id="{{ $student->student_id ?? $student->id }}">
                                                {{ $student->name }}
                                            </a>
                                        </td>
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

    <!-- Add Bootstrap JS if not already included -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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
        // Add a global test function
        window.testStudentBehavior = function(studentId) {
            console.log('Testing student behavior with ID:', studentId);

            // Create a simple chart with hardcoded data
            const chartCanvas = document.getElementById('studentBehaviorChart');
            const ctx = chartCanvas.getContext('2d');

            // Create a simple chart
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Behavior Score',
                        data: [100, 95, 90, 85, 80, 75],
                        borderColor: '#4bc0c0',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Show the modal
            const modal = document.getElementById('studentBehaviorModal');
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        };

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
                    createStudentChart(studentId, 6);
                });
            });

            // Add event listeners to filter buttons
            document.getElementById('studentBtn3Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.add('active');
                document.getElementById('studentBtn6Months').classList.remove('active');
                document.getElementById('studentBtn12Months').classList.remove('active');
                createStudentChart(studentId, 3);
            });

            document.getElementById('studentBtn6Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.remove('active');
                document.getElementById('studentBtn6Months').classList.add('active');
                document.getElementById('studentBtn12Months').classList.remove('active');
                createStudentChart(studentId, 6);
            });

            document.getElementById('studentBtn12Months').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                document.getElementById('studentBtn3Months').classList.remove('active');
                document.getElementById('studentBtn6Months').classList.remove('active');
                document.getElementById('studentBtn12Months').classList.add('active');
                createStudentChart(studentId, 12);
            });

            // Add event listener to retry button
            document.getElementById('student-retry-button').addEventListener('click', function() {
                const studentId = document.getElementById('studentBehaviorId').textContent.replace('Student ID: ', '');
                let months = 6;
                if (document.getElementById('studentBtn3Months').classList.contains('active')) months = 3;
                if (document.getElementById('studentBtn12Months').classList.contains('active')) months = 12;
                createStudentChart(studentId, months);
            });
        });

        // Function to create student chart
        function createStudentChart(studentId, months) {
            // Show loading indicator
            document.getElementById('student-chart-loading').style.display = 'flex';
            document.getElementById('student-error-message').style.display = 'none';

            // Get chart element
            const chartCanvas = document.getElementById('studentBehaviorChart');

            // Destroy existing chart if it exists
            if (window.studentBehaviorChart) {
                window.studentBehaviorChart.destroy();
                window.studentBehaviorChart = null;
            }

            // Fetch data from server
            fetch(`/educator/student-behavior-data/${studentId}?months=${months}&_=${Date.now()}`)
                .then(response => response.json())
                .then(data => {
                    // Hide loading indicator
                    document.getElementById('student-chart-loading').style.display = 'none';

                    // Update violation count
                    const violationCount = document.getElementById('student-violation-count');
                    if (data.violationsCount > 0) {
                        violationCount.textContent = data.violationsCount;
                        violationCount.style.display = 'inline-block';
                    } else {
                        violationCount.style.display = 'none';
                    }

                    // Create chart
                    const ctx = chartCanvas.getContext('2d');

                    // Create gradient
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(75, 192, 192, 0.6)');
                    gradient.addColorStop(1, 'rgba(75, 192, 192, 0.1)');

                    // Create chart
                    window.studentBehaviorChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Behavior Score',
                                data: data.scoreData,
                                borderColor: '#4bc0c0',
                                borderWidth: 3,
                                backgroundColor: gradient,
                                pointBackgroundColor: '#4bc0c0',
                                pointBorderColor: '#fff',
                                pointRadius: 5,
                                pointHoverRadius: 8,
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `Score: ${context.raw}/100`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        stepSize: 10,
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Behavior Score'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Month'
                                    }
                                }
                            },
                            animation: {
                                duration: 1000
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading student behavior data:', error);
                    document.getElementById('student-chart-loading').style.display = 'none';
                    document.getElementById('student-error-message').style.display = 'flex';
                });
        }
    </script>
@endpush