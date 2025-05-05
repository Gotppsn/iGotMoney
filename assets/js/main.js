/**
 * iGotMoney - Main JavaScript File
 * General functionality used across the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize modals
    initializeModals();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize alerts auto-close
    initializeAlerts();
    
    // Initialize responsive sidebar
    initializeSidebar();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap modals
 */
function initializeModals() {
    // Handle data-attributes for modals
    document.addEventListener('click', function(e) {
        if (e.target && e.target.getAttribute('data-bs-toggle') === 'modal') {
            const targetModalId = e.target.getAttribute('data-bs-target');
            const modal = new bootstrap.Modal(document.querySelector(targetModalId));
            modal.show();
        }
    });
    
    // Handle form resets when modal is closed
    const modalList = [].slice.call(document.querySelectorAll('.modal'));
    modalList.forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => form.reset());
        });
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Initialize data tables
 */
function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        const searchInput = document.querySelector('[data-table-search="' + table.id + '"]');
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchText = this.value.toLowerCase();
                
                Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
            });
        }
    });
}

/**
 * Initialize alerts auto-close
 */
function initializeAlerts() {
    const autoCloseAlerts = document.querySelectorAll('.alert-dismissible:not(.alert-persistent)');
    
    autoCloseAlerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
}

/**
 * Initialize responsive sidebar
 */
function initializeSidebar() {
    const toggleButton = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('#sidebar');
    
    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('d-md-block');
            sidebar.classList.toggle('d-none');
        });
    }
}

/**
 * Format currency
 * @param {number} amount - The amount to format
 * @param {string} currencyCode - Currency code (default: USD)
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount, currencyCode = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currencyCode
    }).format(amount);
}

/**
 * Format date
 * @param {string} dateString - The date string to format
 * @param {string} format - Format type (default: 'short')
 * @returns {string} Formatted date string
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    
    switch (format) {
        case 'full':
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        case 'medium':
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        case 'short':
        default:
            return date.toLocaleDateString('en-US');
    }
}

/**
 * Calculate percentage
 * @param {number} value - The current value
 * @param {number} total - The total value
 * @param {number} decimals - Number of decimal places (default: 0)
 * @returns {number} Calculated percentage
 */
function calculatePercentage(value, total, decimals = 0) {
    if (total === 0) return 0;
    const percentage = (value / total) * 100;
    return parseFloat(percentage.toFixed(decimals));
}

/**
 * Show loading spinner
 * @param {HTMLElement} container - The container to add the spinner to
 * @param {string} size - Size of the spinner (sm, md, lg)
 */
function showSpinner(container, size = 'md') {
    const spinner = document.createElement('div');
    spinner.className = `spinner-border text-primary spinner-border-${size}`;
    spinner.setAttribute('role', 'status');
    
    const span = document.createElement('span');
    span.className = 'visually-hidden';
    span.textContent = 'Loading...';
    
    spinner.appendChild(span);
    
    // Clear container and add spinner
    container.innerHTML = '';
    container.appendChild(spinner);
}

/**
 * Hide loading spinner
 * @param {HTMLElement} container - The container to remove the spinner from
 */
function hideSpinner(container) {
    const spinner = container.querySelector('.spinner-border');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Generic AJAX request function
 * @param {string} url - The URL to send the request to
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {Object} data - Data to send with the request
 * @param {function} successCallback - Function to call on success
 * @param {function} errorCallback - Function to call on error
 */
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                response = xhr.responseText;
            }
            successCallback(response);
        } else {
            errorCallback(xhr.statusText);
        }
    };
    
    xhr.onerror = function() {
        errorCallback('Network error');
    };
    
    xhr.send(JSON.stringify(data));
}

/**
 * Show notification
 * @param {string} message - The message to display
 * @param {string} type - Type of notification (success, danger, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '350px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 150);
    }, duration);
}