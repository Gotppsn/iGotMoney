/**
 * iGotMoney - Modern Reports Page JavaScript
 * Enhances the reports page with smooth animations and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on page load
    animateReportCards();
    
    // Add event listeners for date range selection
    initDateRangeSelection();
    
    // Add print button functionality
    initPrintButton();
    
    // Add responsive table handling
    handleResponsiveTables();
});

/**
 * Animate report cards with staggered timing
 */
function animateReportCards() {
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
 * Initialize date range selection functionality
 */
function initDateRangeSelection() {
    const dateRangeSelector = document.getElementById('date_range');
    const startDateContainer = document.getElementById('start_date_container');
    const endDateContainer = document.getElementById('end_date_container');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (!dateRangeSelector) return;
    
    // Show/hide custom date fields based on selection
    dateRangeSelector.addEventListener('change', function() {
        if (this.value === 'custom') {
            startDateContainer.style.display = '';
            endDateContainer.style.display = '';
            
            // Focus on the start date input
            setTimeout(() => {
                if (startDateInput) startDateInput.focus();
            }, 100);
        } else {
            startDateContainer.style.display = 'none';
            endDateContainer.style.display = 'none';
        }
    });
    
    // Ensure end date is not before start date
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && this.value > endDateInput.value) {
                endDateInput.value = this.value;
            }
            
            // Set minimum date for end date input
            endDateInput.min = this.value;
        });
        
        // Initialize min value for end date
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
        }
    }
}

/**
 * Initialize print button functionality
 */
function initPrintButton() {
    const printButton = document.querySelector('button[onclick="window.print()"]');
    
    if (printButton) {
        printButton.addEventListener('click', function(e) {
            // Allow the default window.print() to still happen
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
        wrapper.appendChild(fadeRight);
        
        // Check if table is wider than container
        if (wrapper.scrollWidth > wrapper.clientWidth) {
            wrapper.classList.add('has-overflow');
            fadeRight.style.opacity = '1';
        } else {
            wrapper.classList.remove('has-overflow');
            fadeRight.style.opacity = '0';
        }
        
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
        window.addEventListener('resize', function() {
            if (wrapper.scrollWidth > wrapper.clientWidth) {
                wrapper.classList.add('has-overflow');
                fadeRight.style.opacity = '1';
            } else {
                wrapper.classList.remove('has-overflow');
                fadeRight.style.opacity = '0';
            }
        });
    });
}

/**
 * Format currency with proper locale
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
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
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
}