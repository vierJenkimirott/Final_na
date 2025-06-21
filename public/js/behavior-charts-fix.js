/**
 * Fix for behavior charts
 * This script fixes issues with the behavior chart functionality
 */

// Set default values for global variables
window.currentYAxisScale = window.currentYAxisScale || 'auto';
window.currentBatchFilter = window.currentBatchFilter || 'all';

// Override the initBehaviorChart function to add better error handling
const originalInitBehaviorChart = window.initBehaviorChart;
window.initBehaviorChart = function(data) {
    try {
        console.log('Enhanced initBehaviorChart called');
        
        // If no data is provided, generate sample data for 12 months (default)
        if (!data) {
            console.log('No data provided, generating sample data');
            data = window.generateSampleData(12);
        }
        
        // Ensure data is in the correct format
        if (!data.labels || !Array.isArray(data.labels)) {
            console.warn('Labels missing or not an array, creating default labels');
            data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        }
        
        if (!data.men || !Array.isArray(data.men)) {
            console.warn('Men data missing or not an array, creating empty array');
            data.men = Array(data.labels.length).fill(0);
        }
        
        if (!data.women || !Array.isArray(data.women)) {
            console.warn('Women data missing or not an array, creating empty array');
            data.women = Array(data.labels.length).fill(0);
        }
        
        console.log('Data for chart:', data);
        
        // Call the original function with the fixed data
        if (originalInitBehaviorChart) {
            originalInitBehaviorChart(data);
        } else {
            // Fallback to updateChart if original function is not available
            window.updateChart(data);
        }
    } catch (error) {
        console.error('Error in enhanced initBehaviorChart:', error);
        
        // Create a basic chart with empty data as fallback
        const fallbackData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            men: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            women: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        };
        
        try {
            window.updateChart(fallbackData);
        } catch (e) {
            console.error('Failed to create fallback chart:', e);
        }
    }
};

// Override the fetchBehaviorData function to add better error handling
const originalFetchBehaviorData = window.fetchBehaviorData;
window.fetchBehaviorData = function(months = 12, showLoading = true, batchFilter = 'all', year = null) {
    try {
        // If year is not provided, use the currently selected year
        if (year === null) {
            const yearSelect = document.getElementById('yearSelect');
            if (yearSelect) {
                year = parseInt(yearSelect.value);
            } else {
                year = new Date().getFullYear(); // Default to current year
            }
        }
        
        console.log('Enhanced fetchBehaviorData called for year:', year);
        
        // Show loading indicator
        const loadingElement = document.getElementById('chartLoading');
        if (loadingElement) {
            loadingElement.style.display = 'flex';
        }
        
        // Call the original function
        if (originalFetchBehaviorData) {
            originalFetchBehaviorData(months, showLoading, batchFilter, year);
        } else {
            // Fallback to sample data if original function is not available
            const sampleData = window.generateSampleData(months, year, batchFilter);
            window.initBehaviorChart(sampleData);
            
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error in enhanced fetchBehaviorData:', error);
        
        // Create a basic chart with empty data as fallback
        const fallbackData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            men: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            women: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        };
        
        window.initBehaviorChart(fallbackData);
        
        // Hide loading indicator
        const loadingElement = document.getElementById('chartLoading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }
};

// Initialize the chart when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM content loaded, initializing behavior chart with fix');
    
    // Initialize behavior chart if the canvas exists
    const behaviorChartCanvas = document.getElementById('behaviorChart');
    if (behaviorChartCanvas) {
        console.log('Found behavior chart canvas, initializing chart with timeout');
        
        // Use setTimeout to ensure the canvas is fully rendered
        setTimeout(function() {
            try {
                window.fetchBehaviorData();
            } catch (e) {
                console.error('Error initializing chart:', e);
                
                // Create a basic chart with empty data as fallback
                const fallbackData = {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    men: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    women: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                };
                
                window.initBehaviorChart(fallbackData);
            }
        }, 300);
    }
});