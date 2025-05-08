@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Custom CSS Styles -->
<style>
    /* Card Styling */
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: none;
    }

    .card h2 {
        color: #333;
        font-size: 1.5rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    /* Violator Info Styling */
    .violator-info {
        transition: transform 0.2s;
    }

    .violator-info:hover {
        transform: translateX(5px);
    }

    /* Badge Styling */
    .badge-danger {
        background-color: #dc3545;
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 15px;
    }

    /* Profile Image Styling */
    .profile-img {
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .profile-img:hover {
        transform: scale(1.05);
    }

    /* Violation Stats Styling */
    .violation-stats {
        margin-top: 15px;
        padding-right: 10px;
    }

    /* Custom Scrollbar Styling */
    .violation-stats::-webkit-scrollbar {
        width: 6px;
    }

    .violation-stats::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .violation-stats::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .violation-stats::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Violation Item Styling */
    .violation-item {
        margin-bottom: 15px;
    }

    .violation-item p {
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .violation-item .count {
        font-weight: bold;
        color: #dc3545;
    }

    /* Progress Bar Styling */
    .progress {
        height: 8px;
        margin-top: 5px;
        border-radius: 4px;
        background-color: #e9ecef;
        overflow: hidden;
    }

    .progress-bar {
        background-color: #0d6efd;
        transition: width 0.6s ease;
    }
</style>

<!-- Dashboard Header -->
<h2 class="mb-5">Dashboard</h2>

<!-- Statistics Cards Row -->
<div class="row g-3">
    <!-- Violations Card -->
    <div class="col-md-6">
        <div class="card">
            <p class="title">Violation <img src="{{ asset('images/warning-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3>{{ $totalViolations }}</h3>
        </div>
    </div>

    <!-- Rewards Card -->
    <div class="col-md-6">
        <div class="card">
            <p class="title">Reward <img src="{{ asset('images/medal-removebg-preview.png') }}" alt="" class="icon"></p>
            <h3>156</h3>
        </div>
    </div>
</div>

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
        <div class="card" style="height: 400px; padding: 20px;">
            <h2>Violation Report</h2>
            <select id="violation-filter" class="form-select mb-3">
                <option value="month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="last_3_months">Last 3 Months</option>
            </select>
            <div class="violation-stats" style="max-height: 300px; overflow-y: auto;">
                <div class="loading text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="violation-list"></div>
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

    // Violation Stats Update Function
    function updateViolationStats(period) {
        const loading = document.querySelector('.loading');
        const violationList = document.getElementById('violation-list');

        loading.classList.remove('d-none');
        violationList.innerHTML = '';

        fetch(`/educator/violation-stats?period=${period}`)
            .then(response => response.json())
            .then(data => {
                const maxCount = Math.max(...data.map(item => item.count));
                const totalViolations = data.reduce((sum, item) => sum + item.count, 0);
                
                // Generate HTML for violation stats
                let violationHtml = `
                    <div class="total-violations mb-3">
                        <h5>Total Violations: <span class="text-danger">${totalViolations}</span></h5>
                    </div>
                `;
                
                violationHtml += data.map(violation => {
                    const percentage = (violation.count / maxCount) * 100;
                    return `
                        <div class="violation-item">
                            <p>
                                <span>${violation.violation_name}</span>
                                <span class="count">${violation.count}</span>
                            </p>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                }).join('');

                violationList.innerHTML = violationHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                violationList.innerHTML = '<p class="text-danger">Error loading violation statistics</p>';
            })
            .finally(() => {
                loading.classList.add('d-none');
            });
    }

    // Initialize violation stats and event listeners
    document.addEventListener("DOMContentLoaded", function () {
        // Initial load of violation stats
        updateViolationStats('month');

        // Add event listener for period filter
        document.getElementById('violation-filter').addEventListener('change', function() {
            updateViolationStats(this.value);
        });
    });
</script>
@endpush
