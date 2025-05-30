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
    var currentBatch = window.currentBatchFilter;
    
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
            // Show weekly data for the selected month
            var monthIndex = parseInt(selectedMonth);
            var date = new Date(selectedYear, monthIndex, 1);
            var monthName = date.toLocaleString('en-US', { month: 'long' });
            
            // Generate weekly data
            var weeklyData = window.generateMonthData(monthIndex, selectedYear, currentBatch);
            console.log('Generated weekly data for ' + monthName + ':', weeklyData);
            
            // Update the chart with weekly data
            window.updateChart(weeklyData);
            
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            window.showNotification('success', 'Showing weekly data for ' + monthName + ' ' + selectedYear, 'Data Updated');
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
    
    // Update active state of batch filter buttons
    document.querySelectorAll('.batch-filter').forEach(button => {
        if (button.getAttribute('data-batch') === batch) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
    
    // Store the current batch filter
    window.currentBatchFilter = batch;
    
    // Refresh the chart with the new filter
    window.updateChartByPeriod();
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
window.fetchBehaviorData = function(months = 12, showLoading = true, batchFilter = 'all', year = null) {
    // If year is not provided, use the currently selected year
    if (year === null) {
        const yearSelect = document.getElementById('yearSelect');
        if (yearSelect) {
            year = parseInt(yearSelect.value);
        } else {
            year = new Date().getFullYear(); // Default to current year
        }
    }
    
    console.log('Fetching behavior data for year:', year);
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
    
    // Add cache-busting parameter to prevent caching
    const url = '/educator/behavior/data?months=' + months + '&batch=' + batchFilter + '&year=' + year + '&_=' + Date.now();
    
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
        
        // Process the data and update the chart
        window.initBehaviorChart(data);
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
            const monthIndex = parseInt(selectedMonth);
            const monthData = window.generateMonthData(monthIndex, selectedYear, window.currentBatchFilter);
            window.updateChart(monthData);
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
        window.initBehaviorChart();
    }
    
    // Set up event listeners
    window.setupBehaviorChartEventListeners();
    
    // Initialize batch filter buttons
    const batchFilterButtons = document.querySelectorAll('.batch-filter');
    batchFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const batch = this.getAttribute('data-batch');
            window.filterDataByBatch(batch);
        });
    });
    
    // Initialize y-axis scale filter buttons
    const yScaleFilterButtons = document.querySelectorAll('.y-scale-filter');
    yScaleFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scale = this.getAttribute('data-scale');
            window.filterDataByYScale(scale);
        });
    });
});
