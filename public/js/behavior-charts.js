/**
 * ScholarSync Behavior Charts
 * This file contains all the functionality for behavior charts in the ScholarSync application.
 */

// Global chart references
window.behaviorChart = null;
window.studentBehaviorChart = null;

// Global variables for filtering
window.currentBatchFilter = 'all'; // Default to showing all students
window.currentYAxisScale = 'auto'; // Default to auto scale for y-axis

// Function to generate monthly data for the behavior chart showing actual violations
window.generateSampleData = function(months, year, batchFilter) {
    console.log('generateSampleData called with months:', months, 'year:', year, 'batch:', batchFilter);
    if (year === undefined) year = new Date().getFullYear();
    if (batchFilter === undefined) batchFilter = window.currentBatchFilter;
    
    var labels = [];
    var menData = [];
    var womenData = [];
    
    // Always show January to December (all 12 months)
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var fullMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    // Get violation data from global variables if available
    // These should be set in the blade template from the server
    var maleViolationCounts = window.maleViolationCounts || {};
    var femaleViolationCounts = window.femaleViolationCounts || {};
    
    console.log('Violation counts from database:', { maleViolationCounts, femaleViolationCounts });
    
    // If months is 12, show all months from January to December
    if (months === 12) {
        // Use all month names
        for (var i = 0; i < monthNames.length; i++) {
            labels.push(monthNames[i]);
            
            // Get actual violation counts from the database
            // Start with 0 for each month and add actual counts if available
            var monthKey = fullMonthNames[i].toLowerCase();
            var maleCount = maleViolationCounts[monthKey] || 0;
            var femaleCount = femaleViolationCounts[monthKey] || 0;
            
            // Apply batch filter if needed
            if (batchFilter === '1') {
                // Filter for 1st year students if that data is available
                maleCount = (maleViolationCounts[monthKey + '_year1'] !== undefined) ? 
                    maleViolationCounts[monthKey + '_year1'] : Math.round(maleCount * 0.6);
                femaleCount = (femaleViolationCounts[monthKey + '_year1'] !== undefined) ? 
                    femaleViolationCounts[monthKey + '_year1'] : Math.round(femaleCount * 0.6);
            } else if (batchFilter === '2') {
                // Filter for 2nd year students if that data is available
                maleCount = (maleViolationCounts[monthKey + '_year2'] !== undefined) ? 
                    maleViolationCounts[monthKey + '_year2'] : Math.round(maleCount * 0.4);
                femaleCount = (femaleViolationCounts[monthKey + '_year2'] !== undefined) ? 
                    femaleViolationCounts[monthKey + '_year2'] : Math.round(femaleCount * 0.4);
            }
            
            menData.push(maleCount);
            womenData.push(femaleCount);
        }
    } else {
        // For fewer months, show the most recent ones
        var currentDate = new Date();
        currentDate.setFullYear(year);
        var currentMonth = currentDate.getMonth();
        
        // Calculate starting month index (go back 'months' number of months)
        for (var i = months - 1; i >= 0; i--) {
            var monthIndex = (currentMonth - i + 12) % 12; // Ensure positive index with modulo
            var monthName = monthNames[monthIndex];
            var fullMonthName = fullMonthNames[monthIndex];
            labels.push(monthName);
            
            // Get actual violation counts from the database
            // Start with 0 for each month and add actual counts if available
            var monthKey = fullMonthName.toLowerCase();
            var maleCount = maleViolationCounts[monthKey] || 0;
            var femaleCount = femaleViolationCounts[monthKey] || 0;
            
            // Apply batch filter if needed
            if (batchFilter === '1') {
                // Filter for 1st year students if that data is available
                maleCount = (maleViolationCounts[monthKey + '_year1'] !== undefined) ? 
                    maleViolationCounts[monthKey + '_year1'] : Math.round(maleCount * 0.6);
                femaleCount = (femaleViolationCounts[monthKey + '_year1'] !== undefined) ? 
                    femaleViolationCounts[monthKey + '_year1'] : Math.round(femaleCount * 0.6);
            } else if (batchFilter === '2') {
                // Filter for 2nd year students if that data is available
                maleCount = (maleViolationCounts[monthKey + '_year2'] !== undefined) ? 
                    maleViolationCounts[monthKey + '_year2'] : Math.round(maleCount * 0.4);
                femaleCount = (femaleViolationCounts[monthKey + '_year2'] !== undefined) ? 
                    femaleViolationCounts[monthKey + '_year2'] : Math.round(femaleCount * 0.4);
            }
            
            menData.push(maleCount);
            womenData.push(femaleCount);
        }
    }
    
    // This is monthly data, not weekly
    return {
        labels: labels,
        men: menData,
        women: womenData,
        title: 'Student Violations by Month',
        isWeeklyView: false // Flag to indicate this is NOT weekly data
    };
};

// Function to generate weekly data for a specific month using actual violation data
window.generateMonthData = function(monthIndex, year, batchFilter) {
    if (year === undefined) year = new Date().getFullYear();
    if (batchFilter === undefined) batchFilter = window.currentBatchFilter;
    
    var labels = [];
    var menData = [];
    var womenData = [];
    var maleViolatorsData = [];
    var femaleViolatorsData = [];
    
    // Calculate the number of weeks in the month
    var firstDay = new Date(year, monthIndex, 1);
    var lastDay = new Date(year, monthIndex + 1, 0);
    
    // Get the month name for display
    var monthName = firstDay.toLocaleString('en-US', { month: 'long' });
    var monthKey = monthName.toLowerCase();
    
    // Get the day of the week for the first day (0 = Sunday, 1 = Monday, etc.)
    var firstDayOfWeek = firstDay.getDay();
    
    // Calculate the total number of days in the month
    var totalDays = lastDay.getDate();
    
    // Calculate the number of weeks (including partial weeks)
    // A week that spans from the previous month or into the next month counts as a week
    var numWeeks = Math.ceil((totalDays + firstDayOfWeek) / 7);
    
    console.log('Generating weekly data for ' + monthName + ' ' + year + ' with ' + numWeeks + ' weeks');
    
    // Get violation data from global variables if available
    var maleViolationCounts = window.maleViolationCounts || {};
    var femaleViolationCounts = window.femaleViolationCounts || {};
    
    // Get the total violations for this month
    var totalMaleViolations = maleViolationCounts[monthKey] || 0;
    var totalFemaleViolations = femaleViolationCounts[monthKey] || 0;
    
    // Apply batch filter if needed
    if (batchFilter === '1') {
        // Filter for 1st year students if that data is available
        totalMaleViolations = (maleViolationCounts[monthKey + '_year1'] !== undefined) ? 
            maleViolationCounts[monthKey + '_year1'] : Math.round(totalMaleViolations * 0.6);
        totalFemaleViolations = (femaleViolationCounts[monthKey + '_year1'] !== undefined) ? 
            femaleViolationCounts[monthKey + '_year1'] : Math.round(totalFemaleViolations * 0.6);
    } else if (batchFilter === '2') {
        // Filter for 2nd year students if that data is available
        totalMaleViolations = (maleViolationCounts[monthKey + '_year2'] !== undefined) ? 
            maleViolationCounts[monthKey + '_year2'] : Math.round(totalMaleViolations * 0.4);
        totalFemaleViolations = (femaleViolationCounts[monthKey + '_year2'] !== undefined) ? 
            femaleViolationCounts[monthKey + '_year2'] : Math.round(totalFemaleViolations * 0.4);
    }
    
    // Get weekly violation data if available
    var maleWeeklyData = maleViolationCounts[monthKey + '_weekly'] || [];
    var femaleWeeklyData = femaleViolationCounts[monthKey + '_weekly'] || [];
    
    // Check if we have the new data structure with violator information
    var hasMaleViolatorInfo = maleWeeklyData.counts !== undefined && maleWeeklyData.violators !== undefined;
    var hasFemaleViolatorInfo = femaleWeeklyData.counts !== undefined && femaleWeeklyData.violators !== undefined;
    
    // Generate weekly data for the month
    for (var week = 1; week <= numWeeks; week++) {
        // Calculate the date range for this week
        var weekStartDay = (week - 1) * 7 - firstDayOfWeek + 1;
        var weekEndDay = Math.min(weekStartDay + 6, totalDays);
        
        // Create a descriptive week label
        var weekLabel = 'Week ' + week;
        labels.push(weekLabel);
        
        // Use actual weekly violation data if available, otherwise distribute the monthly total
        var maleCount, femaleCount;
        var maleViolators = [], femaleViolators = [];
        
        if (hasMaleViolatorInfo && maleWeeklyData.counts.length >= week) {
            // Use actual weekly data with violator information
            maleCount = maleWeeklyData.counts[week-1];
            maleViolators = maleWeeklyData.violators[week-1] || [];
        } else if (maleWeeklyData.length >= week && maleWeeklyData[week-1] !== undefined) {
            // Use actual weekly count data (old format)
            maleCount = maleWeeklyData[week-1];
        } else {
            // Distribute violations across weeks based on week position
            var weekPosition = week / numWeeks; // 0.0 to 1.0 representing position in month
            var distributionFactor;
            
            // Create a bell curve distribution with more violations in the middle of the month
            if (weekPosition < 0.5) {
                distributionFactor = 1.5 * weekPosition + 0.25; // 0.25 to 1.0
            } else {
                distributionFactor = 1.5 * (1 - weekPosition) + 0.25; // 1.0 to 0.25
            }
            
            // Calculate this week's portion of the total violations
            maleCount = Math.round(totalMaleViolations * distributionFactor / numWeeks);
        }
        
        if (hasFemaleViolatorInfo && femaleWeeklyData.counts.length >= week) {
            // Use actual weekly data with violator information
            femaleCount = femaleWeeklyData.counts[week-1];
            femaleViolators = femaleWeeklyData.violators[week-1] || [];
        } else if (femaleWeeklyData.length >= week && femaleWeeklyData[week-1] !== undefined) {
            // Use actual weekly count data (old format)
            femaleCount = femaleWeeklyData[week-1];
        } else {
            // Distribute violations across weeks based on week position
            var weekPosition = week / numWeeks;
            var distributionFactor;
            
            if (weekPosition < 0.5) {
                distributionFactor = 1.5 * weekPosition + 0.25;
            } else {
                distributionFactor = 1.5 * (1 - weekPosition) + 0.25;
            }
            
            femaleCount = Math.round(totalFemaleViolations * distributionFactor / numWeeks);
        }
        
        menData.push(maleCount);
        womenData.push(femaleCount);
        maleViolatorsData.push(maleViolators);
        femaleViolatorsData.push(femaleViolators);
    }
    
    // Make sure to return the data in the expected format
    var result = {
        labels: labels,
        men: menData,
        women: womenData,
        maleViolators: maleViolatorsData,
        femaleViolators: femaleViolatorsData,
        title: monthName + ' ' + year + ' - Weekly View',
        isWeeklyView: true  // Flag to indicate this is weekly data
    };
    
    console.log('Returning weekly data:', result);
    return result;
};

// Function to update the chart with new data
window.updateChart = function(data) {
    try {
        console.log('Updating chart with data and scale:', window.currentYAxisScale);
        
        // Get the selected year
        let selectedYear = new Date().getFullYear(); // Default to current year
        const yearSelect = document.getElementById('yearSelect');
        if (yearSelect) {
            selectedYear = parseInt(yearSelect.value);
        }
        
        console.log('updateChart called');
        var canvas = document.getElementById('behaviorChart');
        if (!canvas) {
            console.error('Chart canvas not found!');
            return;
        }
        
        console.log('Canvas found:', canvas);
        
        // Make sure canvas is visible
        canvas.style.display = 'block';
        
        // Hide loading indicator if it exists
        var loadingElement = document.getElementById('chartLoading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // Log the data to help with debugging
        console.log('Updating chart with data:', data);
    
        // Validate that we have the required data properties
        if (!data || !data.labels || !data.men || !data.women) {
            console.error('Invalid chart data format:', data);
            window.showNotification('danger', 'Invalid chart data format', 'Error');
            window.showChartError(canvas, 'Invalid chart data format');
            return;
        }
    
        // Check if this is weekly data
        var isWeeklyView = data.isWeeklyView || false;
        console.log('Is weekly view:', isWeeklyView);
        
        var ctx = canvas.getContext('2d');
        
        // We don't need gradients for bar charts as we'll use solid colors
        // But we'll keep the variable names for compatibility
        
        // Destroy existing chart if it exists
        if (window.behaviorChart) {
            window.behaviorChart.destroy();
        }
        
        // Create new chart instance
        window.behaviorChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Male Violations',
                    data: data.men,
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    hoverBorderColor: '#fff',
                    barPercentage: 0.8,
                    categoryPercentage: 0.9
                },
                {
                    label: 'Female Violations',
                    data: data.women,
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderColor: 'rgba(231, 74, 59, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(231, 74, 59, 1)',
                    hoverBorderColor: '#fff',
                    barPercentage: 0.8,
                    categoryPercentage: 0.9
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Student Violations by Month (' + selectedYear + ')',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: false // Hide default legend as we're using custom legend below chart
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                // Basic label showing count
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + ' violations';
                                }
                                return label;
                            },
                            afterLabel: function(context) {
                                // Check if we have violator information
                                if (!data.isWeeklyView || !context.parsed.y) {
                                    return null; // No violator info for monthly view or zero violations
                                }
                                
                                // Get the violators for this week
                                var weekIndex = context.dataIndex;
                                var violators = [];
                                
                                // Determine if this is male or female data
                                if (context.datasetIndex === 0) { // Male dataset
                                    violators = data.maleViolators && data.maleViolators[weekIndex] ? data.maleViolators[weekIndex] : [];
                                } else { // Female dataset
                                    violators = data.femaleViolators && data.femaleViolators[weekIndex] ? data.femaleViolators[weekIndex] : [];
                                }
                                
                                // If no violator details, return null
                                if (!violators.length) {
                                    return null;
                                }
                                
                                // Format violator information with names prominently displayed
                                var tooltipLines = [];
                                tooltipLines.push('Violators:');
                                violators.forEach(function(violator) {
                                    if (violator.name) {
                                        // Show name prominently, then date and violation type if available
                                        var line = '• ' + violator.name;
                                        if (violator.date) {
                                            line += ' (' + violator.date + ')';
                                        }
                                        if (violator.violation_type) {
                                            line += ' - ' + violator.violation_type;
                                        }
                                        tooltipLines.push(line);
                                    }
                                });
                                
                                return tooltipLines;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: function() {
                            // Use the selected y-axis scale
                            if (window.currentYAxisScale === 'auto') {
                                // Calculate max based on actual data values
                                try {
                                    const maxMen = Math.max(...data.men);
                                    const maxWomen = Math.max(...data.women);
                                    const maxValue = Math.max(maxMen, maxWomen, 5); // At least 5 for small values
                                    
                                    // Round up to the nearest 5
                                    return Math.ceil(maxValue / 5) * 5;
                                } catch (e) {
                                    console.warn('Error calculating auto scale:', e);
                                    return 10; // Default to 10 if there's an error
                                }
                            } else {
                                // Use the specified scale
                                return parseInt(window.currentYAxisScale);
                            }
                        }(),
                        grid: {
                            drawBorder: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            stepSize: function() {
                                // Adjust step size based on max value or current scale
                                let max;
                                
                                if (window.currentYAxisScale === 'auto') {
                                    // Calculate based on data
                                    const maxMen = Math.max(...data.men);
                                    const maxWomen = Math.max(...data.women);
                                    max = Math.ceil(Math.max(maxMen, maxWomen, 5) / 5) * 5;
                                } else {
                                    // Use the specified scale
                                    max = parseInt(window.currentYAxisScale);
                                }
                                
                                if (max <= 10) return 1;
                                if (max <= 20) return 2;
                                if (max <= 50) return 5;
                                return 10;
                            }(),
                            font: {
                                size: 12
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Violations',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: true,
                            drawBorder: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        },
                        title: {
                            display: true,
                            text: 'Time Period',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
        
        // Update the custom legend
        window.updateCustomLegend();
    } catch (error) {
        console.error('Error updating chart:', error);
        if (canvas) {
            window.showChartError(canvas, 'Failed to update chart: ' + error.message);
        }
        
        // Show notification
        window.showNotification('danger', 'Failed to update chart: ' + error.message, 'Error');
    }
};

// Function to initialize the behavior chart
window.initBehaviorChart = function(data) {
    try {
        console.log('initBehaviorChart called');
        var canvas = document.getElementById('behaviorChart');
        console.log('Canvas element exists:', canvas !== null);
        
        if (!canvas) {
            console.error('Canvas element not found! Cannot initialize chart.');
            // Try to find the chart container and show an error
            var container = document.querySelector('.chart-container');
            if (container) {
                var errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Error:</strong> Chart canvas not found!';
                container.appendChild(errorDiv);
            }
            return;
        }
        
        // If no data is provided, generate sample data for 12 months (default)
        if (!data) {
            console.log('No data provided, generating sample data');
            data = window.generateSampleData(12);
        }
        
        console.log('Data for chart:', data);
        
        // Update the chart immediately instead of using setTimeout
        try {
            console.log('Calling updateChart directly');
            window.updateChart(data);
        } catch (e) {
            console.error('Error initializing chart:', e);
            window.showChartError(canvas, 'Failed to initialize chart: ' + e.message);
        }
    } catch (outerError) {
        console.error('Fatal error initializing behavior chart:', outerError);
    }
};

// Function to update chart based on selected year and month
window.updateChartByPeriod = function() {
    console.log('updateChartByPeriod called');
    
    // Get the select elements
    var yearSelect = document.getElementById('yearSelect');
    var monthSelect = document.getElementById('monthSelect');
    
    if (!yearSelect || !monthSelect) {
        console.error('Year or month select elements not found');
        return;
    }
    
    // Get the selected values
    var selectedYear = parseInt(yearSelect.value);
    var selectedMonth = monthSelect.value;
    var currentBatch = window.currentBatchFilter || window.getCurrentBatchFilter() || 'all';
    
    console.log('Selected year: ' + selectedYear + ', Selected month: ' + selectedMonth + ', Batch: ' + currentBatch);
    
    // Update the refresh button to show loading state
    var refreshButton = document.getElementById('refresh-behavior');
    if (refreshButton) {
        refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
        refreshButton.disabled = true;
    }
    
    // Show loading indicator
    var loadingElement = document.getElementById('chartLoading');
    if (loadingElement) {
        loadingElement.style.display = 'flex';
    }
    
    // Show loading notification
    window.showNotification('info', 'Loading behavior data...', 'Please wait');
    
    try {
        if (selectedMonth === 'all') {
            // Show all months (monthly view)
            var months = 12; // Show full year
            
            // Fetch actual data from the server if available
            if (typeof window.fetchBehaviorData === 'function') {
                // This will handle loading indicator
                window.fetchBehaviorData(months, true, currentBatch, selectedYear);
            } else {
                // Fallback to sample data if fetch function not available
                var monthlyData = window.generateSampleData(months, selectedYear, currentBatch);
                console.log('Generated monthly data:', monthlyData);
                window.updateChart(monthlyData);
                
                // Hide loading indicator
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }
                
                const batchText = currentBatch === 'all' ? 'all students' : currentBatch + ' year students';
                window.showNotification('success', `Showing ${batchText} monthly data for ${selectedYear}`, 'Data Updated');
            }
        } else {
            // Show data for the selected month
            var monthIndex = parseInt(selectedMonth);
            var date = new Date(selectedYear, monthIndex, 1);
            var monthName = date.toLocaleString('en-US', { month: 'long' });

            // Fetch actual data from the server for the specific month
            if (typeof window.fetchBehaviorData === 'function') {
                // This will handle loading indicator and fetch real data
                window.fetchBehaviorData(1, true, currentBatch, selectedYear, selectedMonth);
            } else {
                // Fallback to generated weekly data if fetch function not available
                var weeklyData = window.generateMonthData(monthIndex, selectedYear, currentBatch);
                console.log('Generated weekly data for ' + monthName + ':', weeklyData);

                // Update the chart with weekly data
                window.updateChart(weeklyData);

                // Hide loading indicator
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }

                window.showNotification('success', 'Showing data for ' + monthName + ' ' + selectedYear, 'Data Updated');
            }
        }
    } catch (error) {
        console.error('Error updating chart:', error);
        window.showNotification('danger', 'Failed to update chart data: ' + error.message, 'Error');
        
        // Hide loading indicator
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    } finally {
        // Reset the refresh button
        if (refreshButton) {
            setTimeout(function() {
                refreshButton.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Data';
                refreshButton.disabled = false;
            }, 500);
        }
    }
};

// Update the custom legend to match the chart data
window.updateCustomLegend = function() {
    // The legend is already in the HTML with the correct styling
    // We just need to make sure it's visible
    var legendContainer = document.querySelector('.d-flex.justify-content-center.mt-4');
    if (legendContainer) {
        legendContainer.style.display = 'flex';
    }
};

// Function to filter data by batch year
window.filterDataByBatch = function(batch) {
    console.log('Filtering data by batch:', batch);

    // Store the current batch filter
    window.currentBatchFilter = batch;

    // Update active state of batch filter buttons (if they exist)
    document.querySelectorAll('.batch-filter').forEach(button => {
        if (button.getAttribute('data-batch') === batch) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });

    window.updateBatchFilterSelection(batch);
    // Refresh the behavior chart with the new filter - only if we're on the behavior page
    // Check if necessary elements exist before calling updateChartByPeriod
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    
    if (yearSelect && monthSelect) {
        // We're on the behavior page, update chart
        window.updateChartByPeriod();
    } else {
        // We're on the dashboard page, call dashboard-specific functions
        console.log('On dashboard page, updating batch filtering');
        
        // Call dashboard-specific functions
        window.dashboardFilterByBatch(batch);
    }
};

// Function to handle dashboard-specific batch filtering
window.dashboardFilterByBatch = function(batch) {
    console.log('Dashboard-specific batch filtering for:', batch);
    
    // Get the total students count element
    let studentCountElement = document.getElementById('total-students-count');
    // Get the total violations count element
    let violationsCountElement = document.getElementById('total-violations-count');
    
    if (studentCountElement) {
        // Show loading spinner
        studentCountElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        // Make an AJAX request to get students by batch
        fetch(`/educator/students-by-batch?batch=${batch}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Students response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Students data received:', data);
            // Update the student count - check if data.count exists and is not undefined
            if (data && data.success && data.count !== undefined) {
                studentCountElement.textContent = data.count.toString();
                console.log('Updated student count to:', data.count);
            } else if (data && data.count !== undefined) {
                studentCountElement.textContent = data.count.toString();
                console.log('Updated student count to:', data.count);
            } else {
                console.error('Invalid response format for student count:', data);
                studentCountElement.textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error fetching students by batch:', error);
            studentCountElement.textContent = '0'; // Fallback
        });
    }
        
    if (violationsCountElement) {
        // Show loading spinner
        violationsCountElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        // Make an AJAX request to get violations count by batch
        fetch(`/educator/violations/count?batch=${batch}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Violations response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Violations data received:', data);
            // Update the violations count - check if data.count exists and is not undefined
            if (data && data.success && data.count !== undefined) {
                violationsCountElement.textContent = data.count.toString();
                console.log('Updated violations count to:', data.count);
            } else if (data && data.count !== undefined) {
                violationsCountElement.textContent = data.count.toString();
                console.log('Updated violations count to:', data.count);
            } else {
                console.error('Invalid response format for violations count:', data);
                violationsCountElement.textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error fetching violations count by batch:', error);
            violationsCountElement.textContent = '0'; // Fallback
        });
    }
        
    // Update batch-specific sections visibility
    window.updateBatchSpecificSections(batch);
        
    // Find all student section cards
    const allBatchSections = document.querySelectorAll('.col-md-6');
    let batch2025Section, batch2026Section;
        
    // Iterate through each section to find the batch-specific ones
    allBatchSections.forEach(section => {
        const headerElement = section.querySelector('.card-header h5');
        if (headerElement) {
            const headerText = headerElement.textContent;
            if (headerText.includes('Batch 2025')) {
                batch2025Section = section;
            } else if (headerText.includes('Batch 2026')) {
                batch2026Section = section;
            }
        }
    });
        
    // Update visibility based on selected batch
    if (batch2025Section && batch2026Section) {
        if (batch === 'all') {
            // Show both sections
            batch2025Section.style.display = '';
            batch2026Section.style.display = '';
        } else if (batch === '2025') {
            // Show only 2025 section
            batch2025Section.style.display = '';
            batch2026Section.style.display = 'none';
        } else if (batch === '2026') {
            // Show only 2026 section
            batch2025Section.style.display = 'none';
            batch2026Section.style.display = '';
        }
    } else {
        console.warn('Could not find batch-specific student sections');
    }
    
    // Notification removed - no toast for batch filtering
};

// Function to filter data by batch range in the behavior monitoring page
window.filterDataByBatchRange = function(startYear, endYear) {
    console.log(`Filtering behavior data by batch range: ${startYear} to ${endYear}`);
    
    // Show loading indicator
    const chartLoading = document.getElementById('chartLoading');
    if (chartLoading) {
        chartLoading.style.display = 'flex';
    }
    
    // Update the chart based on the selected batch range
    // This will make an AJAX request to get the filtered data
    fetch(`/educator/behavior/data?startBatch=${startYear}&endBatch=${endYear}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Filtered behavior data received for batch range:', data);
        
        // Update the chart with the new data
        if (window.behaviorChart) {
            // Update male and female data points
            window.behaviorChart.data.datasets[0].data = data.maleData || [];
            window.behaviorChart.data.datasets[1].data = data.femaleData || [];
            
            // Update chart title to reflect the selected batch range
            window.behaviorChart.options.plugins.title.text = `Student Violations by Month (Batches ${startYear}-${endYear})`;
            
            window.behaviorChart.update();
        }
        
        // Hide loading indicator
        if (chartLoading) {
            chartLoading.style.display = 'none';
        }
        
        // Update chart title in the DOM if it exists
        const chartTitle = document.querySelector('.chart-title');
        if (chartTitle) {
            chartTitle.textContent = `Student Violations by Month (Batches ${startYear}-${endYear})`;
        }
        
        // Notification removed - no toast for batch range filtering
    })
    .catch(error => {
        console.error('Error fetching filtered behavior data for batch range:', error);
        
        // Hide loading indicator
        if (chartLoading) {
            chartLoading.style.display = 'none';
        }
        
        // Show error notification
        window.showNotification('error', 'Failed to load filtered behavior data for batch range. Please try again.', 'Error');
    });
};

// Function to get current batch filter value from either dashboard or behavior page
window.getCurrentBatchFilter = function() {
    // Try behavior page dropdown first
    const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
    if (behaviorBatchSelect) {
        return behaviorBatchSelect.value;
    }

    // Try dashboard dropdown
    const dashboardBatchSelect = document.getElementById('batchSelect');
    if (dashboardBatchSelect) {
        return dashboardBatchSelect.value;
    }

    // Fallback to 'all'
    return 'all';
};

// Function to update batch filter dropdown selection (for both pages)
window.updateBatchFilterSelection = function(batch) {
    // Update behavior page dropdown if it exists
    const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
    if (behaviorBatchSelect) {
        behaviorBatchSelect.value = batch;
    }

    // Update dashboard dropdown if it exists
    const dashboardBatchSelect = document.getElementById('batchSelect');
    if (dashboardBatchSelect) {
        dashboardBatchSelect.value = batch;
    }
};

// Function to filter data by batch in the behavior monitoring page
window.filterDataByBatch = function(batch) {
    console.log('Filtering behavior data by batch:', batch);
    // --- Unified class (batch) filtering logic ---
    window.currentBatchFilter = batch;
    // Update dropdown/button active states
    if (typeof window.updateBatchFilterSelection === 'function') {
        window.updateBatchFilterSelection(batch);
    }
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    if (yearSelect && monthSelect) {
        window.updateChartByPeriod();
    } else {
        window.fetchBehaviorData(12, true, window.currentBatchFilter);
    }
    // Return early so legacy AJAX logic below is skipped
    return;
    
    // Show loading indicator
    const chartLoading = document.getElementById('chartLoading');
    if (chartLoading) {
        chartLoading.style.display = 'flex';
    }
    
    // Update the chart based on the selected batch
    // This will make an AJAX request to get the filtered data
    fetch(`/educator/behavior/data?batch=${batch}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Filtered behavior data received:', data);
        
        // Update the chart with the new data
        if (window.behaviorChart) {
            // Update male and female data points
            window.behaviorChart.data.datasets[0].data = data.maleData || [];
            window.behaviorChart.data.datasets[1].data = data.femaleData || [];
            
            // Update chart title to reflect the selected batch
            window.behaviorChart.options.plugins.title.text = `Student Violations by Month ${batch === 'all' ? '' : '(' + batch + ')'}`;
            
            window.behaviorChart.update();
        }
        
        // Hide loading indicator
        if (chartLoading) {
            chartLoading.style.display = 'none';
        }
        
        // Update chart title in the DOM if it exists
        const chartTitle = document.querySelector('.chart-title');
        if (chartTitle) {
            chartTitle.textContent = `Student Violations by Month ${batch === 'all' ? '' : '(' + batch + ')'}`;
        }
        
        // Notification removed - no toast for batch filtering
    })
    .catch(error => {
        console.error('Error fetching filtered behavior data:', error);
        
        // Hide loading indicator
        if (chartLoading) {
            chartLoading.style.display = 'none';
        }
        
        // Show error notification
        window.showNotification('error', 'Failed to load filtered behavior data. Please try again.', 'Error');
    });
};

// Function to show toast notifications on the dashboard
window.showToast = function(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type === 'info' ? 'primary' : type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto-remove the toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toastContainer.removeChild(toast);
        }, 300);
    }, 3000);
};

// Function to update dashboard data based on batch filter
window.updateDashboardData = function(batch) {
    // Get CSRF token for secure request
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Update total students violation count using the proper endpoint
    const countElement = document.getElementById('total-violations-count');

    if (countElement) {
        // Apply a loading state
        const originalText = countElement.textContent;
        countElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        // Use the violations/count endpoint which is now available
        fetch(`/educator/violations/count?batch=${batch}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                countElement.textContent = data.count;
            } else {
                throw new Error(data.message || 'Failed to get violation count');
            }
        })
        .catch(error => {
            console.error('Error updating violation count:', error);
            countElement.textContent = originalText; // Restore original on error
        });
    }
    
    // Update violation status overview chart using the API endpoint that exists
    fetch(`/api/violation-stats-by-batch?batch=${batch}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update violation status chart if it exists
        const violationStatusChart = window.violationStatusChart;
        if (violationStatusChart) {
            violationStatusChart.data.datasets[0].data = [data.violatorCount, data.nonViolatorCount];
            violationStatusChart.update();
        }
        
        // Update the stats display
        document.querySelectorAll('.stat-card').forEach(card => {
            const h3Element = card.querySelector('h3');
            if (!h3Element) return;
            
            if (card.textContent.includes('Violators') && !card.textContent.includes('Non-Violators')) {
                h3Element.textContent = data.violatorCount;
            } else if (card.textContent.includes('Non-Violators')) {
                h3Element.textContent = data.nonViolatorCount;
            }
        });
    })
    .catch(error => {
        console.error('Error updating violation status chart:', error);
    });
    
    // Update violation report for the selected period
    const period = document.getElementById('violation-filter')?.value || 'all';
    
    fetch(`/api/violation-stats?period=${period}&batch=${batch}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const violationList = document.getElementById('violation-list');
        if (violationList) {
            // Apply a loading state
            violationList.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Simulate a delay for the loading effect
            setTimeout(() => {
                violationList.innerHTML = '';
                
                if (data.length > 0) {
                    const maxCount = Math.max(...data.map(item => item.count));
                    data.forEach(violation => {
                        const violationItem = document.createElement('div');
                        violationItem.className = 'violation-item';
                        violationItem.innerHTML = `
                            <div class="violation-text">${violation.violation_name}</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(violation.count / maxCount) * 100}%;"></div>
                            </div>
                        `;
                        violationList.appendChild(violationItem);
                    });
                } else {
                    violationList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <h5>No Violations</h5>
                            <p class="text-muted">No violations recorded for this period.</p>
                        </div>
                    `;
                }
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error fetching violation report data:', error);
        if (violationList) {
            violationList.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Error loading violation data
                </div>
            `;
        }
    });
    
    // Update student tabs based on batch filter
    fetch(`/educator/students-by-batch?batch=${batch}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Apply batch filter to the existing tabs
        const violatorsTab = document.getElementById('violators');
        const nonViolatorsTab = document.getElementById('non-violators');
        
        if (violatorsTab) {
            // Apply a loading state
            violatorsTab.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Filter the existing DOM elements instead of fetching new data
            setTimeout(() => {
                // Implementation for updating violators tab
                // This would typically involve DOM manipulation based on the batch filter
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error filtering student data:', error);
    });
    
    // Notification removed - no toast for filter changes
};

// Function to update violation report based on period and batch
window.updateViolationReport = function(period, batch) {
    // Get CSRF token for secure request
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // If batch is not provided, use the current batch filter
    if (batch === undefined) {
        batch = window.getCurrentBatchFilter();
    }
    
    fetch(`/api/violation-stats?period=${period}&batch=${batch}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const violationList = document.getElementById('violation-list');
        if (violationList) {
            // Apply a loading state
            violationList.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Simulate a delay for the loading effect
            setTimeout(() => {
                violationList.innerHTML = '';
                
                if (data.length > 0) {
                    const maxCount = Math.max(...data.map(item => item.count));
                    data.forEach(violation => {
                        const violationItem = document.createElement('div');
                        violationItem.className = 'violation-item';
                        violationItem.innerHTML = `
                            <div class="violation-text">${violation.violation_name}</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(violation.count / maxCount) * 100}%;"></div>
                            </div>
                        `;
                        violationList.appendChild(violationItem);
                    });
                    
                    // Ensure the container is scrollable
                    const reportList = document.querySelector('.violation-report-list');
                    if (reportList) {
                        reportList.style.overflowY = 'auto';
                    }
                } else {
                    violationList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <h5>No Violations</h5>
                            <p class="text-muted">No violations recorded for this period.</p>
                        </div>
                    `;
                }
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error fetching violation report data:', error);
        if (violationList) {
            violationList.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Error loading violation data
                </div>
            `;
        }
    });
};

// Function to update student tabs based on batch filter
window.updateStudentTabs = function(batch) {
    // Get CSRF token for secure request
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Update non-compliant and compliant students tabs
    // Since we don't have a specific endpoint, use the students-by-batch endpoint
    fetch(`/educator/students-by-batch?batch=${batch}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Apply batch filter to the existing tabs
        const violatorsTab = document.getElementById('violators');
        const nonViolatorsTab = document.getElementById('non-violators');
        
        if (violatorsTab) {
            // Apply a loading state
            violatorsTab.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Filter the existing DOM elements instead of fetching new data
            setTimeout(() => {
                // For demonstration purposes, we'll show a filtered view
                // In a real implementation, you would create proper endpoints
                const studentElements = document.querySelectorAll('#violators .d-flex.align-items-center');
                let visibleCount = 0;
                
                if (studentElements.length > 0) {
                    violatorsTab.innerHTML = '';
                    
                    studentElements.forEach(element => {
                        const studentId = element.querySelector('.text-muted.small')?.textContent;
                        // If batch is 'all' or the student ID starts with the batch number, show it
                        if (batch === 'all' || (studentId && studentId.startsWith(batch))) {
                            violatorsTab.appendChild(element.cloneNode(true));
                            visibleCount++;
                        }
                    });
                    
                    if (visibleCount === 0) {
                        violatorsTab.innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No violation records found for ${batch === 'all' ? 'any batch' : 'batch ' + batch}.
                            </div>
                        `;
                    }
                } else {
                    violatorsTab.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No violation records found.
                        </div>
                    `;
                }
            }, 500);
        }
        
        if (nonViolatorsTab) {
            // Apply a loading state
            nonViolatorsTab.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Filter the existing DOM elements
            setTimeout(() => {
                const studentElements = document.querySelectorAll('#non-violators .d-flex.align-items-center');
                let visibleCount = 0;
                
                if (studentElements.length > 0) {
                    nonViolatorsTab.innerHTML = '';
                    
                    studentElements.forEach(element => {
                        const studentId = element.querySelector('.text-muted.small')?.textContent;
                        // If batch is 'all' or the student ID starts with the batch number, show it
                        if (batch === 'all' || (studentId && studentId.startsWith(batch))) {
                            nonViolatorsTab.appendChild(element.cloneNode(true));
                            visibleCount++;
                        }
                    });
                    
                    if (visibleCount === 0) {
                        nonViolatorsTab.innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No compliant students found for ${batch === 'all' ? 'any batch' : 'batch ' + batch}.
                            </div>
                        `;
                    }
                } else {
                    nonViolatorsTab.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No compliant students found.
                        </div>
                    `;
                }
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error filtering student data:', error);
    });
    
    // Notification removed - no toast for filter changes
};

// Function to filter data by y-axis scale
window.filterDataByYScale = function(scale) {
    console.log('Filtering data by y-axis scale:', scale);
    
    // Update active state of y-scale filter buttons
    document.querySelectorAll('.y-scale-filter').forEach(button => {
        if (button.getAttribute('data-scale') === scale) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
    
    // Store the current y-axis scale
    window.currentYAxisScale = scale;
    
    try {
        // Refresh the chart with the new scale
        if (window.behaviorChart && window.behaviorChart.data) {
            // Get current data from the chart
            const currentData = {
                labels: window.behaviorChart.data.labels,
                men: window.behaviorChart.data.datasets[0].data,
                women: window.behaviorChart.data.datasets[1].data
            };
            
            // Update the chart with the current data but new scale
            window.updateChart(currentData);
        } else {
            console.log('Chart not initialized yet, refreshing data');
            // If no chart exists yet, refresh the data
            window.updateChartByPeriod();
        }
    } catch (error) {
        console.error('Error updating chart scale:', error);
        window.showNotification('danger', 'Failed to update chart scale: ' + error.message, 'Error');
    }
};

// Function to fetch behavior data from server
window.fetchBehaviorData = function(months = 12, showLoading = true, batchFilter = 'all', year = null, month = 'all') {
    // If year is not provided, use the currently selected year
    if (year === null) {
        const yearSelect = document.getElementById('yearSelect');
        if (yearSelect) {
            year = parseInt(yearSelect.value);
        } else {
            year = new Date().getFullYear(); // Default to current year
        }
    }

    console.log('Fetching behavior data for year:', year, 'month:', month);
    if (months === undefined) months = 12;
    if (batchFilter === undefined) batchFilter = window.currentBatchFilter;

    console.log('Fetching behavior data for', months, 'months with batch filter:', batchFilter);

    // Show loading indicator
    const loadingElement = document.getElementById('chartLoading');
    if (loadingElement) {
        loadingElement.style.display = 'flex';
    }

    // Get CSRF token for secure request
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Build URL with parameters
    let url = '/educator/behavior/data?batch=' + batchFilter + '&year=' + year;
    if (month !== 'all') {
        url += '&month=' + month;
    }
    url += '&_=' + Date.now(); // Add cache-busting parameter
    
    // Make AJAX request to get behavior data
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load behavior data: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Received behavior data:', data);
        
        // Hide loading indicator
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // If no data is returned, generate sample data
        if (!data || !data.labels || !data.men || !data.women) {
            console.warn('No valid data returned from server, using sample data');
            data = window.generateSampleData(months);
        } else {
            // Store the violation counts in global variables for use in generateSampleData
            // This allows us to reuse the data when filtering by batch
            window.maleViolationCounts = data.maleViolationCounts || {};
            window.femaleViolationCounts = data.femaleViolationCounts || {};
            console.log('Stored violation counts from server:', { 
                maleViolationCounts: window.maleViolationCounts, 
                femaleViolationCounts: window.femaleViolationCounts 
            });
        }
        
        // If a single month was requested and the server did not return weekly view data, generate weekly breakdown on the client side.
        if (months === 1 && (!data || !data.isWeeklyView)) {
            try {
                const monthIdx = parseInt(month, 10);
                const fallbackWeekly = window.generateMonthData(monthIdx, year, batchFilter);
                window.initBehaviorChart(fallbackWeekly);
            } catch (e) {
                console.warn('Fallback weekly generation failed, using server data as-is', e);
                window.initBehaviorChart(data);
            }
        } else {
            // Process the data and update the chart normally
            window.initBehaviorChart(data);
        }
    })
    .catch(error => {
        console.error('Error fetching behavior data:', error);
        
        // Hide loading indicator
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // Show error notification
        window.showNotification('danger', error.message, 'Error');
        
        // Use sample data as fallback
        console.log('Using sample data as fallback');
        const sampleData = window.generateSampleData(months);
        window.initBehaviorChart(sampleData);
    });
};

/**
 * Show a notification to the user
 * @param {string} type - The type of notification (success, info, warning, danger)
 * @param {string} message - The notification message
 * @param {string} title - The notification title
 */
window.showNotification = function(type, message, title) {
    if (title === undefined) title = 'Notification';
    // Create notifications container if it doesn't exist
    var notificationsContainer = document.querySelector('.behavior-notifications');
    if (!notificationsContainer) {
        notificationsContainer = document.createElement('div');
        notificationsContainer.className = 'behavior-notifications';
        notificationsContainer.style.position = 'fixed';
        notificationsContainer.style.top = '20px';
        notificationsContainer.style.right = '20px';
        notificationsContainer.style.zIndex = '9999';
        notificationsContainer.style.width = '300px';
        document.body.appendChild(notificationsContainer);
    }
    
    // Create notification element
    var notification = document.createElement('div');
    notification.className = 'behavior-toast toast show';
    notification.style.opacity = '0';
    notification.style.backgroundColor = '#fff';
    notification.style.borderRadius = '0.5rem';
    notification.style.boxShadow = '0 4px 24px rgba(44,62,80,0.15)';
    notification.style.overflow = 'hidden';
    notification.style.marginBottom = '1rem';
    notification.style.animation = 'slideIn 0.3s ease forwards';
    
    // Set notification color based on type
    var bgColor, iconClass;
    switch(type) {
        case 'success':
            bgColor = '#10ac84';
            iconClass = 'fas fa-check-circle';
            break;
        case 'info':
            bgColor = '#2e86de';
            iconClass = 'fas fa-info-circle';
            break;
        case 'warning':
            bgColor = '#ff9f43';
            iconClass = 'fas fa-exclamation-triangle';
            break;
        case 'danger':
            bgColor = '#ee5253';
            iconClass = 'fas fa-exclamation-circle';
            break;
        default:
            bgColor = '#2e86de';
            iconClass = 'fas fa-info-circle';
    }
    
    // Create notification content
    notification.innerHTML = `
        <div style="display: flex; align-items: center; padding: 1rem; border-left: 4px solid ${bgColor};">
            <div style="margin-right: 0.75rem; color: ${bgColor};">
                <i class="${iconClass}" style="font-size: 1.5rem;"></i>
            </div>
            <div style="flex: 1;">
                <h5 style="margin: 0 0 0.25rem; color: #2c3e50; font-weight: 600;">${title}</h5>
                <p style="margin: 0; color: #7f8c8d; font-size: 0.875rem;">${message}</p>
            </div>
            <button type="button" style="background: none; border: none; color: #95a5a6; cursor: pointer; font-size: 1rem; padding: 0.25rem;" onclick="this.parentNode.parentNode.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to notifications container
    notificationsContainer.appendChild(notification);
    
    // Make visible with animation
    setTimeout(function() {
        notification.style.opacity = '1';
    }, 10);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        notification.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
};

/**
 * Show an error message when the chart fails to load
 * @param {HTMLElement} canvas - The canvas element
 * @param {string} errorMessage - The error message to display
 */
window.showChartError = function(canvas, errorMessage) {
    // Create error element
    var errorElement = document.createElement('div');
    errorElement.className = 'chart-error-message';
    errorElement.innerHTML = '<div class="alert alert-danger">' +
        '<i class="fas fa-exclamation-triangle me-2"></i>' +
        '<strong>Chart Error:</strong> ' + errorMessage +
        '</div>' +
        '<button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="window.fetchBehaviorData()">' +
            '<i class="fas fa-sync"></i> Try Again' +
        '</button>';
    errorElement.style.display = 'block';
    
    // Add to container
    canvas.parentElement.appendChild(errorElement);
    
    // Hide canvas
    canvas.style.display = 'none';
};

// Function to set up event listeners for the behavior chart
window.setupBehaviorChartEventListeners = function() {
    console.log('Setting up behavior chart event listeners');
    
    // Set up event listeners for y-axis scale filter buttons
    const yScaleFilterButtons = document.querySelectorAll('.y-scale-filter');
    if (yScaleFilterButtons.length > 0) {
        console.log('Found y-scale filter buttons:', yScaleFilterButtons.length);
        yScaleFilterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const scale = this.getAttribute('data-scale');
                window.filterDataByYScale(scale);
            });
        });
    } else {
        console.log('No y-scale filter buttons found');
    }

    // Add event listeners for year and month selectors to update chart when changed
    const yearSelect = document.getElementById('yearSelect');
    if (yearSelect && !yearSelect.hasAttribute('data-listener-added')) {
        yearSelect.addEventListener('change', function() {
            window.updateChartByPeriod();
        });
        yearSelect.setAttribute('data-listener-added', 'true');
    }

    const monthSelect = document.getElementById('monthSelect');
    if (monthSelect && !monthSelect.hasAttribute('data-listener-added')) {
        monthSelect.addEventListener('change', function() {
            window.updateChartByPeriod();
        });
        monthSelect.setAttribute('data-listener-added', 'true');
    }
};

// Function to update chart based on selected time period
window.updateChartByPeriod = function() {
    try {
        console.log('updateChartByPeriod called');
        
        // Get the selected year and month
        const yearSelect = document.getElementById('yearSelect');
        const monthSelect = document.getElementById('monthSelect');
        
        if (!yearSelect || !monthSelect) {
            console.error('Year or month select elements not found');
            return;
        }
        
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = monthSelect.value;
        
        console.log('Selected year:', selectedYear, 'Selected month:', selectedMonth);
        
        // Validate year input
        if (isNaN(selectedYear)) {
            window.showNotification('warning', 'Please enter a valid year', 'Invalid Year');
            return;
        }
        
        // If a specific month is selected, generate weekly data for that month
        if (selectedMonth !== 'all') {
            // Fetch weekly data for selected month & year from server
            window.fetchBehaviorData(1, true, window.currentBatchFilter, selectedYear, selectedMonth);
            return;
        } else {
            // Otherwise, fetch data for the entire year
            window.fetchBehaviorData(12, true, window.currentBatchFilter, selectedYear);
        }
    } catch (error) {
        console.error('Error in updateChartByPeriod:', error);
        window.showNotification('danger', 'Failed to update chart: ' + error.message, 'Error');
    }
};

// Document ready event listener
document.addEventListener('DOMContentLoaded', function() {
    // Initialize behavior chart if the canvas exists
    const behaviorChartCanvas = document.getElementById('behaviorChart');
    if (behaviorChartCanvas) {
        // Automatically fetch and display real behavior data on page load
        window.fetchBehaviorData();
    }
    
    // Set up event listeners
    window.setupBehaviorChartEventListeners();
    
    // Initialize batch filter buttons (for behavior page)
    const batchFilterButtons = document.querySelectorAll('.batch-filter');
    batchFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const batch = this.getAttribute('data-batch');
            window.filterDataByBatch(batch);
        });
    });

    // Initialize batch filter dropdown (for dashboard page)
    const batchSelect = document.getElementById('batchSelect');
    if (batchSelect && !batchSelect.hasAttribute('data-listener-added')) {
        batchSelect.addEventListener('change', function() {
            const batch = this.value;
            window.filterDataByBatch(batch);
        });
        batchSelect.setAttribute('data-listener-added', 'true');
    }

    // Initialize batch filter dropdown (for behavior page)
    const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
    if (behaviorBatchSelect && !behaviorBatchSelect.hasAttribute('data-listener-added')) {
        behaviorBatchSelect.addEventListener('change', function() {
            const batch = this.value;
            window.filterDataByBatch(batch);
        });
        behaviorBatchSelect.setAttribute('data-listener-added', 'true');
    }
    
    // Initialize y-axis scale filter buttons
    const yScaleFilterButtons = document.querySelectorAll('.y-scale-filter');
    yScaleFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scale = this.getAttribute('data-scale');
            window.filterDataByYScale(scale);
        });
    });
});
// Function to update chart based on selected time period
window.updateChartByPeriod = function() {
    try {
        console.log('updateChartByPeriod called');
        
        // Get the selected year and month
        const yearSelect = document.getElementById('yearSelect');
        const monthSelect = document.getElementById('monthSelect');
        
        if (!yearSelect || !monthSelect) {
            console.error('Year or month select elements not found');
            return;
        }
        
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = monthSelect.value;
        
        console.log('Selected year:', selectedYear, 'Selected month:', selectedMonth);
        
        // Validate year input
        if (isNaN(selectedYear)) {
            window.showNotification('warning', 'Please enter a valid year', 'Invalid Year');
            return;
        }
        
        // If a specific month is selected, generate weekly data for that month
        if (selectedMonth !== 'all') {
            // Fetch weekly data for selected month & year from server
            window.fetchBehaviorData(1, true, window.currentBatchFilter, selectedYear, selectedMonth);
            return;
        } else {
            // Otherwise, fetch data for the entire year
            window.fetchBehaviorData(12, true, window.currentBatchFilter, selectedYear);
        }
    } catch (error) {
        console.error('Error in updateChartByPeriod:', error);
        window.showNotification('danger', 'Failed to update chart: ' + error.message, 'Error');
    }
};

// Document ready event listener
document.addEventListener('DOMContentLoaded', function() {
    // Initialize behavior chart if the canvas exists
    const behaviorChartCanvas = document.getElementById('behaviorChart');
    if (behaviorChartCanvas) {
        // Automatically fetch and display real behavior data on page load
        window.fetchBehaviorData();
    }
    
    // Set up event listeners
    window.setupBehaviorChartEventListeners();
    
    // Initialize batch filter buttons (for behavior page)
    const batchFilterButtons = document.querySelectorAll('.batch-filter');
    batchFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const batch = this.getAttribute('data-batch');
            window.filterDataByBatch(batch);
        });
    });

    // Initialize batch filter dropdown (for dashboard page)
    const batchSelect = document.getElementById('batchSelect');
    if (batchSelect && !batchSelect.hasAttribute('data-listener-added')) {
        batchSelect.addEventListener('change', function() {
            const batch = this.value;
            window.filterDataByBatch(batch);
        });
        batchSelect.setAttribute('data-listener-added', 'true');
    }

    // Initialize batch filter dropdown (for behavior page)
    const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
    if (behaviorBatchSelect && !behaviorBatchSelect.hasAttribute('data-listener-added')) {
        behaviorBatchSelect.addEventListener('change', function() {
            const batch = this.value;
            window.filterDataByBatch(batch);
        });
        behaviorBatchSelect.setAttribute('data-listener-added', 'true');
    }
    
    // Initialize y-axis scale filter buttons
    const yScaleFilterButtons = document.querySelectorAll('.y-scale-filter');
    yScaleFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scale = this.getAttribute('data-scale');
            window.filterDataByYScale(scale);
        });
    });
});

// Check if violation stats is scrollable and add indicator
function checkScrollable() {
    const violationStats = document.querySelector('.violation-stats');
    if (violationStats) {
        // Check if content is scrollable
        if (violationStats.scrollHeight > violationStats.clientHeight) {
            violationStats.classList.add('scrollable');
        } else {
            violationStats.classList.remove('scrollable');
        }
    }
}

// Call this function after the violation list is populated
document.addEventListener('DOMContentLoaded', function() {
    // Check if scrollable after the violation list is loaded
    setTimeout(checkScrollable, 1000);
    
    // Add event listener to the violation filter to check again after filtering
    const violationFilter = document.getElementById('violation-filter');
    if (violationFilter) {
        violationFilter.addEventListener('change', function() {
            setTimeout(checkScrollable, 1000);
        });
    }
});





