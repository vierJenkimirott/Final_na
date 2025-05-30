@extends('layouts.student')

@section('title', 'Behavior Report')

@section('css')
<link rel="stylesheet" href="{{ asset('css/student-violation.css') }}">
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
</style>
@endsection

@section('content')
<div class="container">
    <!-- Header Section -->
    <h4 class="text-muted mb-1">Student: {{ auth()->user()->name }}</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>My Behavior</h2>
        <button id="refreshBtn" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    
    <!-- Time Period Filters -->
    <div class="filter-buttons">
        <button id="btn12Months" class="{{ $months == 12 ? 'active' : '' }}">12 Months</button>
        <button id="btn6Months" class="{{ $months == 6 ? 'active' : '' }}">6 Months</button>
        <button id="btn3Months" class="{{ $months == 3 ? 'active' : '' }}">3 Months</button>
    </div>
    
    <!-- Chart Container -->
    <div class="chart-container">
        <canvas id="behaviorChart"></canvas>
    </div>
    <div id="error-message" style="display: none; color: red; text-align: center;"></div>
    <button id="retry-button" class="retry-button">Retry Loading Chart</button>
    
    <!-- Legend & Info -->
    <div class="behavior-legend" style="margin-top: 20px; text-align: center;">
        <div style="display: inline-block;">
            <span style="display: inline-block; width: 20px; height: 20px; background-color: #4bc0c0; margin-right: 5px;"></span> 
            Behavior Score (100 = Perfect, 0 = Poor)
        </div>
    </div>

    
    <!-- Notification -->
    <div id="notification" class="notification">
        New violation detected! Your behavior score has been updated.
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
    
    // State variables
    let currentMonths = {{ $months }};
    let lastUpdateTimestamp = {{ $timestamp ?? 0 }};
    let behaviorChart = null;
    let loadingElement = null;
    
    // Set initial button states
    updateButtonStates();
    
    // ===== FUNCTIONS =====
    
    // Update filter button states
    function updateButtonStates() {
        document.getElementById('btn3Months').classList.toggle('active', currentMonths === 3);
        document.getElementById('btn6Months').classList.toggle('active', currentMonths === 6);
        document.getElementById('btn12Months').classList.toggle('active', currentMonths === 12);
    }
    
    // Show loading indicator
    function showLoading() {
        errorMessage.style.display = 'none';
        retryButton.style.display = 'none';
        
        if (!loadingElement) {
            loadingElement = document.createElement('div');
            loadingElement.id = 'chart-loading';
            loadingElement.className = 'loading-indicator';
            loadingElement.innerHTML = '<div class="spinner"></div><p>Loading behavior data...</p>';
            chartContainer.appendChild(loadingElement);
        }
    }
    
    // Hide loading indicator
    function hideLoading() {
        if (loadingElement) {
            loadingElement.remove();
            loadingElement = null;
        }
    }
    
    // Show error message
    function showError(message) {
        errorMessage.textContent = message || 'Failed to load behavior data. Please try again.';
        errorMessage.style.display = 'block';
        retryButton.style.display = 'block';
    }
    
    // Show notification
    function showViolationNotification() {
        const notification = document.getElementById('notification');
        notification.classList.add('show');
        setTimeout(() => notification.classList.remove('show'), 5000);
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
    function loadBehaviorData(months, showNotification = false) {
        showLoading();
        
        fetch(`/student/behavior-data?months=${months}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                hideLoading();
                lastUpdateTimestamp = data.lastUpdate || Math.floor(Date.now() / 1000);
                
                // Log the data received from the server
                console.log('Behavior data received:', data);
                
                // Check if we have violations count
                if (data.violationsCount) {
                    console.log(`Found ${data.violationsCount} violations for this student`);
                    
                    // Add a small indicator to the page showing violation count
                    const violationCountEl = document.getElementById('violation-count');
                    if (violationCountEl) {
                        violationCountEl.textContent = data.violationsCount;
                        violationCountEl.style.display = 'inline-block';
                    }
                }
                
                // Destroy existing chart if it exists
                if (behaviorChart) behaviorChart.destroy();
                
                // Create new chart
                behaviorChart = new Chart(ctx, createChartConfig(data));
                
                // Show notification if requested
                if (showNotification) showViolationNotification();
            })
            .catch(error => {
                console.error('Error loading behavior data:', error);
                hideLoading();
                showError();
            });
    }
    
    // Check for updates
    function checkForUpdates() {
        fetch(`/student/check-violation-updates?lastCheck=${lastUpdateTimestamp}`)
            .then(response => response.json())
            .then(data => {
                if (data.hasUpdates) {
                    loadBehaviorData(currentMonths, true);
                    localStorage.setItem('behaviorUpdated', 'true');
                }
                
                if (data.lastUpdate > lastUpdateTimestamp) {
                    lastUpdateTimestamp = data.lastUpdate;
                }
            })
            .catch(error => console.error('Error checking for updates:', error));
    }
    
    // ===== EVENT LISTENERS =====
    
    // Filter buttons
    document.getElementById('btn3Months').addEventListener('click', () => {
        if (currentMonths !== 3) {
            currentMonths = 3;
            loadBehaviorData(currentMonths);
            updateButtonStates();
        }
    });
    
    document.getElementById('btn6Months').addEventListener('click', () => {
        if (currentMonths !== 6) {
            currentMonths = 6;
            loadBehaviorData(currentMonths);
            updateButtonStates();
        }
    });
    
    document.getElementById('btn12Months').addEventListener('click', () => {
        if (currentMonths !== 12) {
            currentMonths = 12;
            loadBehaviorData(currentMonths);
            updateButtonStates();
        }
    });
    
    // Retry button
    retryButton.addEventListener('click', () => loadBehaviorData(currentMonths));
    
    // Page focus
    window.addEventListener('focus', () => loadBehaviorData(currentMonths));
    
    // ===== INITIALIZATION =====
    
    // Initial load
    loadBehaviorData(currentMonths);
    
    // Check for updates from localStorage
    if (localStorage.getItem('behaviorUpdated') === 'true') {
        localStorage.removeItem('behaviorUpdated');
        showViolationNotification();
    }
    
    // Set up update checking
    setTimeout(checkForUpdates, 1000);
    setInterval(checkForUpdates, 5000);
</script>
@endpush
