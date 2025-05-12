/**
 * Reports Page JavaScript
 * Enhanced for Thai language support
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Reports JS loaded');
    
    // Initialize date range selector
    initializeDateRange();
    
    // Initialize all other components
    initializeReportCards();
    initializeCharts();
    initializePrintButton();
    handleResponsiveTables();
});

/**
 * Initialize date range selection
 */
function initializeDateRange() {
    const dateRangeSelector = document.getElementById('date_range');
    if (dateRangeSelector) {
        dateRangeSelector.addEventListener('change', function() {
            const startDateContainer = document.getElementById('start_date_container');
            const endDateContainer = document.getElementById('end_date_container');
            
            if (this.value === 'custom') {
                startDateContainer.style.display = '';
                endDateContainer.style.display = '';
                
                // Focus on the start date field
                const startDateInput = document.getElementById('start_date');
                if (startDateInput) {
                    startDateInput.focus();
                }
            } else {
                startDateContainer.style.display = 'none';
                endDateContainer.style.display = 'none';
            }
        });
    }
    
    // Set up date validation
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        // Set min/max attributes to ensure logical date selection
        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && new Date(this.value) > new Date(endDateInput.value)) {
                endDateInput.value = this.value;
            }
            
            // Set minimum date for end date input
            endDateInput.min = this.value;
        });
        
        // Initialize min value for end date if start date has value
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
        }
    }
}

/**
 * Animate report cards with staggered timing
 */
function initializeReportCards() {
    const reportCards = document.querySelectorAll('.report-content-card');
    
    reportCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100)); // Staggered animation
    });
}

/**
 * Initialize charts with localized labels
 * Support for Thai language in charts
 */
function initializeCharts() {
    // Set global Chart.js options
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', system-ui, -apple-system, sans-serif";
        Chart.defaults.animation.duration = 1000;
        Chart.defaults.animation.easing = 'easeOutQuart';
        
        // Custom options for handling Thai text
        const thaiLangConfig = {
            font: {
                family: "'Noto Sans Thai', system-ui, sans-serif",
                size: 12
            },
            padding: 8  // Add slightly more padding for Thai text
        };
        
        // Check if current language is Thai
        const isThaiLanguage = document.documentElement.lang === 'th';
        
        if (isThaiLanguage) {
            // Apply Thai-specific configurations
            Chart.defaults.font = {
                ...Chart.defaults.font,
                ...thaiLangConfig.font
            };
            
            // Adjust tooltip padding for Thai text
            Chart.defaults.plugins.tooltip.padding = thaiLangConfig.padding;
        }
    }
}

/**
 * Initialize print button functionality
 * Handles Thai fonts properly for printing
 */
function initializePrintButton() {
    const printButton = document.querySelector('button[onclick="window.print()"]');
    
    if (printButton) {
        // Remove inline onclick and add proper event listener
        printButton.removeAttribute('onclick');
        printButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add print-specific class to body
            document.body.classList.add('printing');
            
            // Check if Thai language is used
            const isThaiLanguage = document.documentElement.lang === 'th';
            
            // For Thai language, ensure fonts are loaded before printing
            if (isThaiLanguage) {
                // Make sure Thai fonts are loaded before printing
                document.fonts.ready.then(() => {
                    window.print();
                    
                    // Remove print class after a delay
                    setTimeout(() => {
                        document.body.classList.remove('printing');
                    }, 1000);
                });
            } else {
                // For non-Thai languages, print normally
                window.print();
                
                // Remove print class after a delay
                setTimeout(() => {
                    document.body.classList.remove('printing');
                }, 1000);
            }
        });
    }
}

/**
 * Handle responsive tables on smaller screens
 */
function handleResponsiveTables() {
    const tableWrappers = document.querySelectorAll('.table-responsive');
    
    tableWrappers.forEach(wrapper => {
        // Add fade indicators for scrollable tables on mobile
        const fadeRight = document.createElement('div');
        fadeRight.className = 'table-fade-right';
        fadeRight.style.cssText = `
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 30px;
            background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,1));
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 5;
        `;
        wrapper.style.position = 'relative';
        wrapper.appendChild(fadeRight);
        
        // Check if table is wider than container
        function checkTableOverflow() {
            if (wrapper.scrollWidth > wrapper.clientWidth) {
                wrapper.classList.add('has-overflow');
                fadeRight.style.opacity = '1';
            } else {
                wrapper.classList.remove('has-overflow');
                fadeRight.style.opacity = '0';
            }
        }
        
        // Initial check
        checkTableOverflow();
        
        // Show/hide fade based on scroll position
        wrapper.addEventListener('scroll', function() {
            const maxScrollLeft = this.scrollWidth - this.clientWidth;
            
            if (this.scrollLeft >= maxScrollLeft - 5) {
                fadeRight.style.opacity = '0';
            } else {
                fadeRight.style.opacity = '1';
            }
        });
        
        // Listen for window resize
        window.addEventListener('resize', checkTableOverflow);
    });
}

/**
 * Format currency with proper locale
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    const language = document.documentElement.lang || 'en';
    
    // For Thai language, use Thai locale
    if (language === 'th') {
        return new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: 'THB',
            minimumFractionDigits: 2
        }).format(amount);
    }
    
    // Default to English/USD
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(amount);
}

/**
 * Format percentage with proper locale
 * @param {number} value - Percentage value
 * @returns {string} Formatted percentage string
 */
function formatPercentage(value) {
    const language = document.documentElement.lang || 'en';
    
    // For Thai language, use Thai locale
    if (language === 'th') {
        return new Intl.NumberFormat('th-TH', {
            style: 'percent',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value / 100);
    }
    
    // Default to English
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
}

/**
 * Handle export functionality for reports
 * @param {string} format - Export format (csv, pdf)
 */
function exportReport(format) {
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.append('export', format);
    
    window.location.href = `${window.location.pathname}?${currentParams.toString()}`;
}

/**
 * Display notification messages
 * @param {string} message - Message text
 * @param {string} type - Message type (info, success, warning, error)
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        background-color: ${type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        max-width: 300px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    `;
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}