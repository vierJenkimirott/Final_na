/**
 * ScholarSync Behavior Charts
 * This file contains all the functionality for behavior charts in the ScholarSync application.
 */

// Global chart reference
let behaviorChart;

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Get the period from the button's data attribute
            const months = parseInt(this.getAttribute('data-months') || 12);
            
            // Show loading indicator on the refresh button
            const refreshBtn = document.getElementById('refresh-behavior');
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';
                refreshBtn.disabled = true;
            }
            
            // Update the chart with the selected time period
            fetchBehaviorData(months, true);
            
            // Reset refresh button after data is loaded
            setTimeout(() => {
                if (refreshBtn) {
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Data';
                    refreshBtn.disabled = false;
                }
            }, 1000);
        });
    });
    
    // Set up print chart functionality
    const printChartBtn = document.getElementById('print-chart');
    if (printChartBtn) {
        printChartBtn.addEventListener('click', function() {
            const canvas = document.getElementById('behaviorChart');
            if (canvas) {
                // Create a printable version
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Behavior Report - ScholarSync</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .header { text-align: center; margin-bottom: 20px; }
                            .chart-container { text-align: center; margin: 20px 0; }
                            .footer { margin-top: 30px; font-size: 12px; text-align: center; color: #666; }
                            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                            th { background-color: #f2f2f2; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>Student Behavior Report</h1>
                            <p>Generated on ${new Date().toLocaleString()}</p>
                        </div>
                        <div class="chart-container">
                            <img src="${canvas.toDataURL('image/png')}" width="800">
                        </div>
                        <table>
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                            <tr>
                                <td>Average Score</td>
                                <td>${document.getElementById('average-score').textContent}</td>
                            </tr>
                            <tr>
                                <td>Men Average</td>
                                <td>${document.getElementById('men-avg').textContent}</td>
                            </tr>
                            <tr>
                                <td>Women Average</td>
                                <td>${document.getElementById('women-avg').textContent}</td>
                            </tr>
                            <tr>
                                <td>Excellent Behavior</td>
                                <td>${document.getElementById('excellent-count').textContent}</td>
                            </tr>
                            <tr>
                                <td>Needs Improvement</td>
                                <td>${document.getElementById('needs-improvement-count').textContent}</td>
                            </tr>
                            <tr>
                                <td>Critical Cases</td>
                                <td>${document.getElementById('critical-count').textContent}</td>
                            </tr>
                        </table>
                        <div class="footer">
                            <p>ScholarSync Behavior Monitoring System</p>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            }
        });
    }
    
    // Set up refresh button
    const refreshBtn = document.getElementById('refresh-behavior');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Show loading indicator
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            this.disabled = true;
            
            // Get current time period from active period button
            const activeBtn = document.querySelector('.period-btn.active');
            const months = activeBtn ? parseInt(activeBtn.getAttribute('data-months')) : 12;
            
            // Refresh the data
            fetchBehaviorData(months, true);
            
            // Reset button after a short delay
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Data';
                this.disabled = false;
            }, 1000);
        });
    }
    
    // Initial data fetch with default 12 months
    fetchBehaviorData(12);
    
    // Set up periodic updates
    setInterval(checkForUpdates, 60000); // Check every minute
    
    // Set up real-time updates for violations
    setupRealTimeUpdates();
});

/**
 * Fetch behavior data from the server
 * @param {number} months - Number of months to fetch data for
 * @param {boolean} forceRefresh - Whether to force a refresh of the data
 */
function fetchBehaviorData(months = 6, forceRefresh = false) {
    console.log('Fetching behavior data for', months, 'months', forceRefresh ? '(forced refresh)' : '');
    // Store the current months selection in a data attribute on the body for debugging
    document.body.dataset.currentMonths = months;
    
    // Show loading indicator
    const canvas = document.getElementById('behaviorChart');
    if (!canvas) {
        console.error('Behavior chart canvas not found');
        return;
    }
    
    const container = canvas.parentElement;
    
    // Add loading overlay if it doesn't exist
    let loadingOverlay = container.querySelector('.chart-loading');
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'chart-loading';
        loadingOverlay.innerHTML = '<div class="spinner"></div><p>Loading behavior data...</p>';
        container.appendChild(loadingOverlay);
    } else {
        loadingOverlay.style.display = 'flex';
    }
    
    // Hide any previous error messages
    const errorElement = container.querySelector('.chart-error');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
    
    // Show canvas if it was hidden
    canvas.style.display = 'block';
    
    // Make the API request with a cache-busting parameter to prevent caching
    fetch(`/educator/behavior-data?months=${months}&_=${Date.now()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load behavior data');
        }
        return response.json();
    })
    .then(data => {
        // Hide loading indicator
        loadingOverlay.style.display = 'none';
        
        // Log the received data for debugging
        console.log('Received behavior data:', data);
        console.log('Months requested:', data.monthsRequested);
        console.log('Number of labels:', data.labels ? data.labels.length : 0);
        
        // Update stat cards with real data
        updateStatCards(data);
        
        // Initialize or update chart
        initBehaviorChart(data);
        
        // Update last updated timestamp
        const lastUpdatedElement = document.querySelector('.last-updated');
        if (lastUpdatedElement && data.lastUpdated) {
            lastUpdatedElement.innerHTML = `<i class="fas fa-clock me-1"></i> Last updated: ${data.lastUpdated}`;
        }
        
        // Show success notification with months info
        const monthsText = data.monthsRequested || document.body.dataset.currentMonths || 6;
        showNotification(`Behavior data for ${monthsText} months loaded successfully`);
    })
    .catch(error => {
        // Hide loading indicator
        loadingOverlay.style.display = 'none';
        
        // Show error message
        console.error('Error fetching behavior data:', error);
        showChartError(canvas, 'Failed to load behavior data. Please try again later.');
        
        // If no data available, use sample data for demonstration
        const sampleData = generateSampleData(months);
        initBehaviorChart(sampleData);
    });
}

/**
 * Generate sample data for the behavior chart
 * @param {number} months - Number of months to generate data for
 * @returns {Object} - Chart data object
 */
function generateSampleData(months) {
    const labels = [];
    const boysData = [];
    const girlsData = [];
    
    // Get month names for the past X months
    const currentDate = new Date();
    for (let i = months - 1; i >= 0; i--) {
        const date = new Date(currentDate);
        date.setMonth(currentDate.getMonth() - i);
        const monthName = date.toLocaleString('default', { month: 'long' });
        labels.push(monthName);
        
        // Generate random data between 70 and 100
        const boysScore = Math.floor(Math.random() * 30) + 70;
        const girlsScore = Math.floor(Math.random() * 30) + 70;
        
        boysData.push(boysScore);
        girlsData.push(girlsScore);
    }
    
    return {
        labels: labels,
        boys: boysData,
        girls: girlsData,
        totalStudents: 120,
        studentsNeedingAttention: 15
    };
}

/**
 * Update the stat cards with real data
 * @param {Object} data - The behavior data from the server
 */
function updateStatCards(data) {
    // Update total students card
    const totalStudentsElement = document.querySelector('.total-students');
    if (totalStudentsElement && data.totalStudents) {
        totalStudentsElement.textContent = data.totalStudents;
    }
    
    // Update students needing attention card
    const attentionStudentsElement = document.querySelector('.attention-students');
    if (attentionStudentsElement && data.studentsNeedingAttention) {
        attentionStudentsElement.textContent = data.studentsNeedingAttention;
    }
}

/**
 * Calculate and update analytics data based on behavior scores
 * @param {Object} data - The behavior data from the server
 */
function updateAnalytics(data) {
    if (!data.men || !data.women) return;
    
    // Calculate average scores
    const menScores = data.men || [];
    const womenScores = data.women || [];
    const allScores = [...menScores, ...womenScores];
    
    // Calculate average score
    const averageScore = Math.round(allScores.reduce((sum, score) => sum + score, 0) / allScores.length);
    const menAverage = Math.round(menScores.reduce((sum, score) => sum + score, 0) / menScores.length);
    const womenAverage = Math.round(womenScores.reduce((sum, score) => sum + score, 0) / womenScores.length);
    
    // Calculate score distributions
    const excellentCount = allScores.filter(score => score >= 90).length;
    const goodCount = allScores.filter(score => score >= 70 && score < 90).length;
    const needsImprovementCount = allScores.filter(score => score >= 50 && score < 70).length;
    const criticalCount = allScores.filter(score => score < 50).length;
    
    // Calculate percentages
    const excellentPercentage = Math.round((excellentCount / allScores.length) * 100);
    const needsImprovementPercentage = Math.round((needsImprovementCount / allScores.length) * 100);
    const criticalPercentage = Math.round((criticalCount / allScores.length) * 100);
    
    // Update UI elements
    document.getElementById('average-score').textContent = averageScore;
    document.getElementById('excellent-count').textContent = excellentPercentage + '%';
    document.getElementById('needs-improvement-count').textContent = needsImprovementPercentage + '%';
    document.getElementById('critical-count').textContent = criticalPercentage + '%';
    document.getElementById('men-avg').textContent = menAverage + ' pts';
    document.getElementById('women-avg').textContent = womenAverage + ' pts';
    
    // Calculate trend (comparing last two months)
    if (menScores.length >= 2 && womenScores.length >= 2) {
        const lastMonthMen = menScores[menScores.length - 1];
        const prevMonthMen = menScores[menScores.length - 2];
        const lastMonthWomen = womenScores[womenScores.length - 1];
        const prevMonthWomen = womenScores[womenScores.length - 2];
        
        const lastMonthAvg = (lastMonthMen + lastMonthWomen) / 2;
        const prevMonthAvg = (prevMonthMen + prevMonthWomen) / 2;
        
        // Calculate percentage change
        const percentChange = ((lastMonthAvg - prevMonthAvg) / prevMonthAvg) * 100;
        const roundedChange = Math.abs(Math.round(percentChange * 10) / 10);
        
        // Update trend indicator
        const trendElement = document.getElementById('score-trend');
        if (trendElement) {
            if (percentChange > 0) {
                trendElement.className = 'small text-success mt-1';
                trendElement.innerHTML = `<i class="fas fa-arrow-up me-1"></i>${roundedChange}% from last period`;
            } else if (percentChange < 0) {
                trendElement.className = 'small text-danger mt-1';
                trendElement.innerHTML = `<i class="fas fa-arrow-down me-1"></i>${roundedChange}% from last period`;
            } else {
                trendElement.className = 'small text-muted mt-1';
                trendElement.innerHTML = `<i class="fas fa-equals me-1"></i>No change from last period`;
            }
        }
    }
}

/**
 * Check for behavior data updates from the server
 */
function checkForUpdates() {
    // Get last update timestamp
    const lastUpdated = document.querySelector('.last-updated');
    const timestamp = lastUpdated ? new Date(lastUpdated.textContent.replace('Last updated: ', '')).getTime() : null;
    
    if (!timestamp) return;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Check for updates with cache-busting parameter
    fetch(`/educator/check-behavior-updates?_=${Date.now()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-Last-Update': timestamp,
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.hasUpdates) {
            // Show notification
            showNotification('New behavior data is available. Refreshing chart...');
            
            // Get current time period
            const timeSelect = document.getElementById('timeSelect');
            const months = timeSelect ? parseInt(timeSelect.value) : 6;
            
            // Refresh data
            fetchBehaviorData(months);
        }
    })
    .catch(error => {
        console.error('Error checking for updates:', error);
    });
}

/**
 * Initialize all event listeners for the behavior chart and controls
 */
function initEventListeners() {
    // Time period selection
    document.querySelectorAll('.time-period-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const months = parseInt(this.dataset.months);
            
            // Update active state
            document.querySelectorAll('.time-period-option').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
            
            // Fetch data for the selected time period
            fetchBehaviorData(months);
            
            // Show notification
            showNotification(`Showing behavior data for the last ${months} months`);
        });
    });

    // Download chart as image
    document.getElementById('downloadChart')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!behaviorChart) return;
        
        // Create a temporary link
        const link = document.createElement('a');
        link.download = 'behavior-chart-' + new Date().toISOString().split('T')[0] + '.png';
        link.href = behaviorChart.toBase64Image();
        link.click();
        
        // Show notification
        showNotification('Chart downloaded successfully');
    });
    
    // Toggle data labels
    document.getElementById('toggleDataLabels')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!behaviorChart) return;
        
        // Toggle data labels plugin
        const showDataLabels = !behaviorChart.options.plugins.datalabels.display;
        behaviorChart.options.plugins.datalabels.display = showDataLabels;
        behaviorChart.update();
        
        // Update the button text to reflect the current state
        this.innerHTML = showDataLabels ? 
            '<i class="fas fa-tags me-2"></i> Hide Data Labels' : 
            '<i class="fas fa-tags me-2"></i> Show Data Labels';
            
        // Show notification
        showNotification(`Data labels ${showDataLabels ? 'enabled' : 'disabled'}`);
    });
    
    // Reset zoom
    document.getElementById('resetZoom')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!behaviorChart) return;
        
        behaviorChart.resetZoom();
        showNotification('Chart zoom reset');
    });
    
    // Full screen chart
    document.getElementById('fullScreenChart')?.addEventListener('click', function(e) {
        e.preventDefault();
        const chartContainer = document.querySelector('.behavior-report');
        if (!chartContainer) return;
        
        if (!document.fullscreenElement) {
            // Enter fullscreen
            if (chartContainer.requestFullscreen) {
                chartContainer.requestFullscreen();
            } else if (chartContainer.webkitRequestFullscreen) {
                chartContainer.webkitRequestFullscreen();
            } else if (chartContainer.msRequestFullscreen) {
                chartContainer.msRequestFullscreen();
            }
            this.innerHTML = '<i class="fas fa-compress me-2"></i> Exit Full Screen';
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            this.innerHTML = '<i class="fas fa-expand me-2"></i> Full Screen';
        }
    });

    // Refresh data button (in header)
    document.getElementById('refreshData')?.addEventListener('click', function() {
        const months = parseInt(document.getElementById('monthSelector')?.value || 6);
        fetchBehaviorData(months, true);
        showNotification('Refreshing behavior data...');
    });
    
    // Chart refresh button (below chart)
    document.getElementById('refreshChart')?.addEventListener('click', function() {
        const months = parseInt(document.getElementById('monthSelector')?.value || 6);
        fetchBehaviorData(months);
        showNotification('Refreshing chart...');
    });
    
    // Sample data generation code removed
}

/**
 * Initialize the behavior chart with data
 * @param {Object} data - The behavior data from the server
 */
function initBehaviorChart(data) {
    const ctx = document.getElementById('behaviorChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (behaviorChart) {
        behaviorChart.destroy();
    }
    
    // Register zoom plugin if not already registered
    if (!Chart.helpers.getRelativePosition) {
        Chart.register(ChartZoom);
    }
    
    // Create enhanced gradient fills for datasets
    const menGradient = ctx.createLinearGradient(0, 0, 0, 400);
    menGradient.addColorStop(0, 'rgba(78, 115, 223, 0.6)');
    menGradient.addColorStop(0.5, 'rgba(78, 115, 223, 0.2)');
    menGradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)');
    
    const womenGradient = ctx.createLinearGradient(0, 0, 0, 400);
    womenGradient.addColorStop(0, 'rgba(231, 74, 59, 0.6)');
    womenGradient.addColorStop(0.5, 'rgba(231, 74, 59, 0.2)');
    womenGradient.addColorStop(1, 'rgba(231, 74, 59, 0.05)');
    
    // Create point colors based on behavior score with increased visibility
    const menPointColors = data.men?.map(score => {
        if (score >= 90) return '#1cc88a'; // Excellent - green
        if (score >= 70) return '#4e73df'; // Good - blue
        if (score >= 50) return '#f6c23e'; // Needs improvement - yellow
        return '#e74a3b'; // Critical - red
    }) || [];
    
    const womenPointColors = data.women?.map(score => {
        if (score >= 90) return '#1cc88a'; // Excellent - green
        if (score >= 70) return '#4e73df'; // Good - blue
        if (score >= 50) return '#f6c23e'; // Needs improvement - yellow
        return '#e74a3b'; // Critical - red
    }) || [];
    
    // Make sure we have data to display
    if (!data.labels || data.labels.length === 0) {
        // Generate monthly labels if none provided
        data.labels = [];
        const today = new Date();
        // Use the requested months if available, otherwise default to 12
        const monthsToShow = data.monthsRequested || 12;
        console.log('Generating labels for', monthsToShow, 'months');
        
        // Generate labels for all months in order (January to December)
        if (monthsToShow === 12) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            data.labels = monthNames;
        } else {
            // For other time periods, use relative months
            for (let i = 0; i < monthsToShow; i++) {
                const d = new Date(today);
                d.setMonth(today.getMonth() - (monthsToShow - 1) + i);
                data.labels.push(d.toLocaleString('default', { month: 'long' }));
            }
        }
    }
    
    // Ensure we have data points
    if (!data.men || data.men.length === 0) {
        data.men = [85, 82, 80, 78, 83, 85];
    }
    
    if (!data.women || data.women.length === 0) {
        data.women = [88, 86, 84, 87, 90, 92];
    }
    
    // Calculate analytics data
    updateAnalytics(data);
    
    // Initialize chart with enhanced visuals
    behaviorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Men',
                    data: data.men || [],
                    borderColor: '#4e73df',
                    borderWidth: 3,
                    backgroundColor: menGradient,
                    pointBackgroundColor: menPointColors,
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#4e73df',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Women',
                    data: data.women || [],
                    borderColor: '#e74a3b',
                    borderWidth: 3,
                    backgroundColor: womenGradient,
                    pointBackgroundColor: womenPointColors,
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#e74a3b',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            // Add zoom plugin options
            plugins: {
                zoom: {
                    pan: {
                        enabled: true,
                        mode: 'xy'
                    },
                    zoom: {
                        wheel: {
                            enabled: true,
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: 'xy',
                    }
                },
                // Add data labels plugin options
                datalabels: {
                    display: false, // Hidden by default, can be toggled
                    backgroundColor: function(context) {
                        return context.dataset.backgroundColor;
                    },
                    borderRadius: 4,
                    color: 'white',
                    font: {
                        weight: 'bold',
                        size: 10
                    },
                    padding: 6
                },
                // Enhanced tooltip
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' points';
                                
                                // Add score interpretation
                                const score = context.parsed.y;
                                if (score >= 90) {
                                    label += ' (Excellent)';
                                } else if (score >= 70) {
                                    label += ' (Good)';
                                } else if (score >= 50) {
                                    label += ' (Needs Improvement)';
                                } else {
                                    label += ' (Critical)';
                                }
                            }
                            return label;
                        },
                        // Add footer with additional information
                        footer: function(tooltipItems) {
                            return 'Click and drag to pan, scroll to zoom';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        drawBorder: false,
                        color: "rgba(0, 0, 0, 0.1)"
                    },
                    ticks: {
                        maxTicksLimit: 24, // Increased to ensure all months show
                        padding: 10,
                        color: "#4e73df",
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        autoSkip: false // Prevent skipping labels
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        maxTicksLimit: 11,
                        stepSize: 10,
                        padding: 10,
                        color: "#4e73df",
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        callback: function(value) {
                            return value + ' pts';
                        }
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)",
                        borderColor: "#dddfeb",
                        borderWidth: 1,
                        drawBorder: true
                    },
                    // Reverse the axis to make the chart go down
                    reverse: false
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        title: function(tooltipItems) {
                            return tooltipItems[0].label + ' Behavior Report';
                        },
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' points';
                                
                                // Add score interpretation with color coding
                                const score = context.parsed.y;
                                if (score >= 90) {
                                    label += ' <span style="color:#1cc88a;font-weight:bold;">(Excellent)</span>';
                                } else if (score >= 70) {
                                    label += ' <span style="color:#4e73df;font-weight:bold;">(Good)</span>';
                                } else if (score >= 50) {
                                    label += ' <span style="color:#f6c23e;font-weight:bold;">(Needs Improvement)</span>';
                                } else {
                                    label += ' <span style="color:#e74a3b;font-weight:bold;">(Critical)</span>';
                                }
                            }
                            return label;
                        },
                        afterLabel: function(context) {
                            // Add deduction information
                            const score = context.parsed.y;
                            const deduction = 100 - score;
                            if (deduction > 0) {
                                return `Total deductions: -${deduction} points`;
                            }
                            return '';
                        },
                        footer: function(tooltipItems) {
                            // Add violation severity information
                            const score = tooltipItems[0].parsed.y;
                            if (score < 100) {
                                let severityInfo = [];
                                if (score < 80) {
                                    severityInfo.push('Potential violations include:');
                                    if (score < 50) severityInfo.push('• Very High severity violations (-20 pts each)');
                                    if (score < 70) severityInfo.push('• High severity violations (-15 pts each)');
                                    if (score < 90) severityInfo.push('• Medium severity violations (-10 pts each)');
                                    severityInfo.push('• Low severity violations (-5 pts each)');
                                }
                                return severityInfo;
                            }
                            return ['Perfect behavior record'];
                        }
                    },
                    useHTML: true
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            elements: {
                line: {
                    tension: 0.4
                }
            }
        }
    });
}

/**
 * Set up the time period selector functionality
 */
function setupTimePeriodSelector() {
    const timePeriodSelector = document.getElementById('time-period');
    if (!timePeriodSelector) {
        console.warn('Time period selector not found');
        return;
    }
    
    // Add event listener to the dropdown
    timePeriodSelector.addEventListener('change', function() {
        const months = parseInt(this.value);
        console.log(`Time period changed to ${months} months`);
        fetchBehaviorData(months, true);
        showNotification(`Showing behavior data for the last ${months} month${months > 1 ? 's' : ''}`);
        
        // Update any UI elements that depend on the time period
        document.body.dataset.currentTimePeriod = months;
    });
}

/**
 * Set up real-time updates for the behavior chart when new violations are logged
 */
function setupRealTimeUpdates() {
    console.log('Setting up real-time updates for behavior chart');
    
    // Add event listener for violation form submission
    document.addEventListener('violation:created', function(e) {
        console.log('Violation created event detected:', e.detail);
        
        // Show notification about the new violation
        showNotification('New violation logged. Updating behavior chart...');
        
        // Get current time period selection
        const months = parseInt(document.getElementById('time-period')?.value || 12);
        
        // Refresh the behavior data
        fetchBehaviorData(months, true);
    });
    
    // Listen for form submissions on the add violation form
    const addViolationForm = document.getElementById('addViolationForm');
    if (addViolationForm) {
        addViolationForm.addEventListener('submit', function(e) {
            // We don't prevent default as we want the form to submit normally
            // Just store in localStorage that we should check for updates when returning to behavior page
            localStorage.setItem('behavior_update', JSON.stringify({
                timestamp: Date.now(),
                sex: document.querySelector('[name="sex"]')?.value || 'Unknown',
                severity: document.querySelector('[name="severity"]')?.value || 'Unknown'
            }));
        });
    }
    
    // Check if we're returning from adding a violation
    const behaviorUpdate = localStorage.getItem('behavior_update');
    if (behaviorUpdate) {
        try {
            const updateData = JSON.parse(behaviorUpdate);
            const timeSinceUpdate = Date.now() - updateData.timestamp;
            
            // If the update was recent (within last 10 seconds), refresh the chart
            if (timeSinceUpdate < 10000) {
                console.log('Detected recent violation addition:', updateData);
                showNotification(`New ${updateData.severity} violation logged. Updating chart...`);
                
                // Get current time period selection
                const months = parseInt(document.getElementById('time-period')?.value || 12);
                
                // Refresh the behavior data
                fetchBehaviorData(months, true);
            }
            
            // Clear the update data
            localStorage.removeItem('behavior_update');
        } catch (err) {
            console.error('Error parsing behavior update data:', err);
            localStorage.removeItem('behavior_update');
        }
    }
    
    // Set up AJAX response interceptor to detect violation creation
    const originalFetch = window.fetch;
    window.fetch = function() {
        return originalFetch.apply(this, arguments)
            .then(response => {
                // Clone the response so we can read it multiple times
                const clone = response.clone();
                
                // Check if this is a violation creation response
                if (arguments[0].toString().includes('/violations') && arguments[1]?.method === 'POST') {
                    clone.json().then(data => {
                        if (data.success && data.data?.shouldUpdateBehaviorChart) {
                            console.log('Violation created via AJAX, triggering update');
                            document.dispatchEvent(new CustomEvent('violation:created', {
                                detail: data.data
                            }));
                        }
                    }).catch(err => console.error('Error parsing response:', err));
                }
                
                return response;
            });
    };
    
    // Check if Echo is available (Laravel Echo for WebSockets)
    if (typeof window.Echo !== 'undefined') {
        // Listen for violation created events
        window.Echo.channel('behavior-updates')
            .listen('ViolationCreated', (e) => {
                console.log('New violation detected via Echo:', e);
                
                // Dispatch the same event as our AJAX interceptor
                document.dispatchEvent(new CustomEvent('violation:created', {
                    detail: e
                }));
            });
    } else {
        // Fallback to polling if Echo is not available
        console.log('Echo not available, using AJAX interceptor for updates');
        
        // Set up a polling mechanism as an additional fallback
        setInterval(function() {
            // Check if we're on the behavior page
            if (document.getElementById('behaviorChart')) {
                console.log('Polling for behavior updates');
                const months = parseInt(document.getElementById('time-period')?.value || 12);
                fetchBehaviorData(months, false);
            }
        }, 30000); // Poll every 30 seconds
    }
}

/**
 * Show a notification to the user
 * @param {string} message - The notification message
 */
function showNotification(message) {
    // Create notifications container if it doesn't exist
    let notificationsContainer = document.querySelector('.behavior-notifications');
    if (!notificationsContainer) {
        notificationsContainer = document.createElement('div');
        notificationsContainer.className = 'behavior-notifications';
        document.body.appendChild(notificationsContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'behavior-notification alert alert-info alert-dismissible fade show';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    notificationsContainer.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

/**
 * Show an error message when the chart fails to load
 * @param {HTMLElement} canvas - The canvas element
 * @param {string} errorMessage - The error message to display
 */
function showChartError(canvas, errorMessage) {
    const container = canvas.parentElement;
    
    // Create error message element
    let errorElement = container.querySelector('.chart-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'chart-error alert alert-danger';
        container.appendChild(errorElement);
    }
    
    // Set error message
    errorElement.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> ${errorMessage}
        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="fetchBehaviorData()">
            <i class="fas fa-sync"></i> Try Again
        </button>
    `;
    errorElement.style.display = 'block';
    
    // Hide canvas
    canvas.style.display = 'none';
}
