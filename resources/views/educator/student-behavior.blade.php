@extends('layouts.educator')

@section('title', 'Student Behavior - ' . $student->name)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/behavior.css') }}">
<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #4bc0c0;
        color: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .notification.show { opacity: 1; }
    .retry-button {
        display: none;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: #4bc0c0;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* Custom filter buttons styling to match student page */
    .filter-buttons {
        margin-bottom: 15px;
        text-align: center;
    }

    .filter-buttons button,
    .filter-buttons .btn-filter {
        padding: 8px 15px;
        margin: 0 5px;
        background-color: #f0f0f0 !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        color: #333 !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .filter-buttons button:hover,
    .filter-buttons .btn-filter:hover {
        background-color: #e0e0e0 !important;
    }

    .filter-buttons button.active,
    .filter-buttons .btn-filter.active {
        background-color: #4bc0c0 !important;
        color: white !important;
        border-color: #4bc0c0 !important;
        box-shadow: 0 2px 5px rgba(75, 192, 192, 0.3) !important;
    }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2>Behavior Report</h2>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="goBackToStudentList()">
                <i class="fas fa-arrow-left me-1"></i> Back to Student List
            </button>
            <a href="{{ route('educator.behavior') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chart-line me-1"></i> Behavior Monitoring
            </a>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">{{ $student->name }}</h4>
                    <p class="text-muted mb-0">Student ID: {{ $student->student_id }}</p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end">
                        <div class="me-3">
                            <span class="d-block text-muted small">Violations</span>
                            <span class="badge {{ $violationCount > 0 ? 'bg-danger' : 'bg-success' }} px-2 py-1">{{ $violationCount }}</span>
                        </div>
                        <div class="me-3">
                            <span class="d-block text-muted small">Status</span>
                            <span class="badge {{ $violationCount > 0 ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                                {{ $violationCount > 0 ? 'Needs Attention' : 'Good Standing' }}
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Current Score</span>
                            <span id="current-score" class="fw-bold">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Period Filters -->
    <div class="filter-buttons">
        <button id="btn12Months" type="button" style="padding: 8px 15px; margin: 0 5px; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: 500; color: #333;">12 Months</button>
        <button id="btn6Months" type="button" style="padding: 8px 15px; margin: 0 5px; background-color: #4bc0c0; border: 1px solid #4bc0c0; border-radius: 4px; cursor: pointer; font-weight: 500; color: white; box-shadow: 0 2px 5px rgba(75, 192, 192, 0.3);">6 Months</button>
        <button id="btn3Months" type="button" style="padding: 8px 15px; margin: 0 5px; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: 500; color: #333;">3 Months</button>
    </div>

    <!-- Chart Container -->
    <div class="chart-container">
        <canvas id="behaviorChart"></canvas>
        <div id="chart-loading" class="loading-indicator" style="display: flex;">
            <div class="spinner"></div>
            <p>Loading behavior data...</p>
        </div>
        <div id="error-message" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <p>Could not load behavior data</p>
            <button id="retry-button" class="btn btn-sm btn-primary">Retry</button>
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
            <span class="badge badge-pill badge-danger ml-2" style="display: none;" id="violation-count">0</span>
        </p>
    </div>

    <!-- Additional Info -->
    <div class="row mt-4">
        <div class="col-md-6 mx-auto">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Violation History</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Last Violation:</span>
                        <span id="last-violation">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // ===== INITIALIZATION =====
    const ctx = document.getElementById('behaviorChart').getContext('2d');
    const chartContainer = document.querySelector('.chart-container');
    const errorMessage = document.getElementById('error-message');
    const retryButton = document.getElementById('retry-button');
    const violationCountEl = document.getElementById('violation-count');
    const currentScoreEl = document.getElementById('current-score');
    const lastViolationEl = document.getElementById('last-violation');

    // State variables
    let currentMonths = 6;
    let behaviorChart = null;
    let loadingElement = document.getElementById('chart-loading');

    // Set initial button states
    updateButtonStates();

    // ===== FUNCTIONS =====

    // Function to go back to student list
    function goBackToStudentList() {
        // Navigate to behavior page with a URL parameter to trigger modal
        window.location.href = '{{ route("educator.behavior") }}?openStudentList=1';
    }

    // Update filter button states
    function updateButtonStates() {
        // First remove active class from all buttons
        document.querySelectorAll('.filter-buttons button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Then add active class to the selected button
        if (currentMonths === 3) {
            document.getElementById('btn3Months').classList.add('active');
        } else if (currentMonths === 6) {
            document.getElementById('btn6Months').classList.add('active');
        } else if (currentMonths === 12) {
            document.getElementById('btn12Months').classList.add('active');
        }
    }

    // Show loading indicator
    function showLoading() {
        errorMessage.style.display = 'none';
        retryButton.style.display = 'none';

        if (loadingElement) {
            loadingElement.style.display = 'flex';
        }
    }

    // Hide loading indicator
    function hideLoading() {
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }

    // Show error message
    function showError(message) {
        errorMessage.style.display = 'flex';
        retryButton.style.display = 'block';
    }

    // Create chart configuration
    function createChartConfig(data) {
        // Create gradient for background
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(75, 192, 192, 0.2)');
        gradient.addColorStop(1, 'rgba(255, 99, 132, 0.1)');

        // Find months with violations (score < 100)
        const violationMonths = [];
        data.scoreData.forEach((score, index) => {
            if (score < 100) {
                violationMonths.push({
                    month: data.labels[index],
                    score: score,
                    index: index
                });
            }
        });

        console.log('Months with violations:', violationMonths);

        return {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Behavior Score',
                    data: data.scoreData,
                    borderColor: '#4bc0c0',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: function(context) {
                        const score = context.raw;
                        if (score >= 90) return '#4bc0c0';
                        if (score >= 70) return '#36a2eb';
                        if (score >= 50) return '#ffcd56';
                        if (score >= 30) return '#ff9f40';
                        return '#ff6384';
                    },
                    pointBorderColor: '#fff',
                    pointRadius: function(context) {
                        // Make violation points larger
                        const score = context.raw;
                        return score < 100 ? 8 : 6;
                    },
                    pointHoverRadius: function(context) {
                        const score = context.raw;
                        return score < 100 ? 10 : 8;
                    },
                    pointStyle: function(context) {
                        // Use a different point style for violations
                        const score = context.raw;
                        return score < 100 ? 'rectRot' : 'circle';
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return `Score: ${context.raw}/100`; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            callback: function(value) { return value + '%'; }
                        },
                        title: { display: true, text: 'Behavior Score' }
                    },
                    x: {
                        title: { display: true, text: 'Month' }
                    }
                },
                animation: { duration: 1000 }
            }
        };
    }

    // Load behavior data
    function loadBehaviorData(months) {
        showLoading();

        // Reset summary info
        currentScoreEl.textContent = 'Loading...';
        lastViolationEl.textContent = 'Loading...';

        fetch(`/educator/student-behavior-data/{{ $student->student_id }}?months=${months}&_=${Date.now()}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                hideLoading();

                // Log the data received from the server
                console.log('Behavior data received:', data);

                // Check if we have violations count
                if (data.violationsCount) {
                    console.log(`Found ${data.violationsCount} violations for this student`);

                    // Add a small indicator to the page showing violation count
                    if (violationCountEl) {
                        violationCountEl.textContent = data.violationsCount;
                        violationCountEl.style.display = 'inline-block';
                    }
                }

                // Update student info with overall current score
                if (data.currentScore !== undefined) {
                    const currentScore = data.currentScore;

                    // Update current score with proper color coding
                    if (currentScore >= 90) {
                        currentScoreEl.innerHTML = `<span class="text-success">${currentScore}/100</span>`;
                    } else if (currentScore >= 70) {
                        currentScoreEl.innerHTML = `<span class="text-warning">${currentScore}/100</span>`;
                    } else {
                        currentScoreEl.innerHTML = `<span class="text-danger">${currentScore}/100</span>`;
                    }
                } else {
                    currentScoreEl.innerHTML = '<span class="text-muted">No data</span>';
                }

                // Last violation
                if (data.violationsCount > 0) {
                    lastViolationEl.textContent = data.lastViolationDate || 'Date not available';
                } else {
                    lastViolationEl.innerHTML = '<span class="text-success">No violations</span>';
                }

                // Destroy existing chart if it exists
                if (behaviorChart) behaviorChart.destroy();

                // Create new chart
                behaviorChart = new Chart(ctx, createChartConfig(data));
            })
            .catch(error => {
                console.error('Error loading behavior data:', error);
                hideLoading();
                showError();

                // Reset summary info on error
                currentScoreEl.textContent = 'Error loading data';
                lastViolationEl.textContent = 'Error loading data';
            });
    }

    // ===== EVENT LISTENERS =====

    // Filter buttons
    document.getElementById('btn3Months').addEventListener('click', () => {
        if (currentMonths !== 3) {
            currentMonths = 3;

            // Update button styles directly
            document.getElementById('btn3Months').style.backgroundColor = '#4bc0c0';
            document.getElementById('btn3Months').style.borderColor = '#4bc0c0';
            document.getElementById('btn3Months').style.color = 'white';
            document.getElementById('btn3Months').style.boxShadow = '0 2px 5px rgba(75, 192, 192, 0.3)';

            document.getElementById('btn6Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn6Months').style.borderColor = '#ddd';
            document.getElementById('btn6Months').style.color = '#333';
            document.getElementById('btn6Months').style.boxShadow = 'none';

            document.getElementById('btn12Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn12Months').style.borderColor = '#ddd';
            document.getElementById('btn12Months').style.color = '#333';
            document.getElementById('btn12Months').style.boxShadow = 'none';

            loadBehaviorData(currentMonths);
        }
    });

    document.getElementById('btn6Months').addEventListener('click', () => {
        if (currentMonths !== 6) {
            currentMonths = 6;

            // Update button styles directly
            document.getElementById('btn6Months').style.backgroundColor = '#4bc0c0';
            document.getElementById('btn6Months').style.borderColor = '#4bc0c0';
            document.getElementById('btn6Months').style.color = 'white';
            document.getElementById('btn6Months').style.boxShadow = '0 2px 5px rgba(75, 192, 192, 0.3)';

            document.getElementById('btn3Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn3Months').style.borderColor = '#ddd';
            document.getElementById('btn3Months').style.color = '#333';
            document.getElementById('btn3Months').style.boxShadow = 'none';

            document.getElementById('btn12Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn12Months').style.borderColor = '#ddd';
            document.getElementById('btn12Months').style.color = '#333';
            document.getElementById('btn12Months').style.boxShadow = 'none';

            loadBehaviorData(currentMonths);
        }
    });

    document.getElementById('btn12Months').addEventListener('click', () => {
        if (currentMonths !== 12) {
            currentMonths = 12;

            // Update button styles directly
            document.getElementById('btn12Months').style.backgroundColor = '#4bc0c0';
            document.getElementById('btn12Months').style.borderColor = '#4bc0c0';
            document.getElementById('btn12Months').style.color = 'white';
            document.getElementById('btn12Months').style.boxShadow = '0 2px 5px rgba(75, 192, 192, 0.3)';

            document.getElementById('btn3Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn3Months').style.borderColor = '#ddd';
            document.getElementById('btn3Months').style.color = '#333';
            document.getElementById('btn3Months').style.boxShadow = 'none';

            document.getElementById('btn6Months').style.backgroundColor = '#f0f0f0';
            document.getElementById('btn6Months').style.borderColor = '#ddd';
            document.getElementById('btn6Months').style.color = '#333';
            document.getElementById('btn6Months').style.boxShadow = 'none';

            loadBehaviorData(currentMonths);
        }
    });

    // Retry button
    retryButton.addEventListener('click', () => loadBehaviorData(currentMonths));

    // ===== INITIALIZATION =====

    // Initial load
    loadBehaviorData(6);
</script>
@endpush
