/**
 * Centralized toast notification system
 */

// Toast types and their corresponding icons
const TOAST_TYPES = {
    success: {
        icon: 'fas fa-check-circle',
        title: 'Success'
    },
    error: {
        icon: 'fas fa-exclamation-circle',
        title: 'Error'
    },
    info: {
        icon: 'fas fa-info-circle',
        title: 'Information'
    },
    warning: {
        icon: 'fas fa-exclamation-triangle',
        title: 'Warning'
    }
};

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, error, info, warning)
 * @param {number} duration - Duration in milliseconds (default: 3000)
 */
function showToast(message, type = 'info', duration = 3000) {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        console.error('Toast container not found');
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const toastType = TOAST_TYPES[type] || TOAST_TYPES.info;
    
    toast.innerHTML = `
        <i class="${toastType.icon}"></i>
        <div class="toast-message">
            <strong>${toastType.title}:</strong> ${message}
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto-remove after duration
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out forwards';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

/**
 * Show a success toast notification
 * @param {string} message - The success message
 */
function showSuccessToast(message) {
    showToast(message, 'success');
}

/**
 * Show an error toast notification
 * @param {string} message - The error message
 */
function showErrorToast(message) {
    showToast(message, 'error');
}

/**
 * Show an info toast notification
 * @param {string} message - The info message
 */
function showInfoToast(message) {
    showToast(message, 'info');
}

/**
 * Show a warning toast notification
 * @param {string} message - The warning message
 */
function showWarningToast(message) {
    showToast(message, 'warning');
}

// Export functions for use in other files
window.showToast = showToast;
window.showSuccessToast = showSuccessToast;
window.showErrorToast = showErrorToast;
window.showInfoToast = showInfoToast;
window.showWarningToast = showWarningToast; 