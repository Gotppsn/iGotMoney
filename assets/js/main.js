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