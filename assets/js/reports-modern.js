/**
 * Modern Reports Page JavaScript
 * Enhances the reports page with smooth animations and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Reports JS loaded');
    
    // Initialize all components
    initializeReportCards();
    initializeEventListeners();
    initializeCharts();
    initializeDateRangeSelection();
    initializePrintButton();
    handleResponsiveTables();
});

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
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Form submission
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            // Add loading state
            const button = this.querySelector('.btn-primary');
            if (button) {
                const originalContent = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
                button.disabled = true;
            }
        });
    }
    
    // Date range changes
    const dateRangeSelect = document.getElementById('date_range');
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            document.getElementById('start_date_container').style.display = isCustom ? '' : 'none';
            document.getElementById('end_date_container').style.display = isCustom ? '' : 'none';
            
            if (isCustom) {
                const startDate = document.getElementById('start_date');
                if (startDate) {
                    startDate.focus();
                }
            }
        });
    }
}

/**
 * Initialize charts with modern styling
 */
function initializeCharts() {
    // Set default Chart.js options for consistent styling
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', system-ui, -apple-system, sans-serif";
        Chart.defaults.animation.duration = 1000;
        Chart.defaults.animation.easing = 'easeOutQuart';
        
        // Initialize all charts on the page
        document.querySelectorAll('canvas').forEach(canvas => {
            // Chart initialization is handled by the PHP script
            // This is for any additional chart customization
        });
    }
}

/**
 * Initialize date range selection functionality
 */
function initializeDateRangeSelection() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        // Ensure end date is not before start date
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
function initializePrintButton() {
    const printButton = document.querySelector('button[onclick="window.print()"]');
    
    if (printButton) {
        // Remove inline onclick and add proper event listener
        printButton.removeAttribute('onclick');
        printButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add print-specific class to body
            document.body.classList.add('printing');
            
            // Trigger print
            window.print();
            
            // Remove print class after a delay
            setTimeout(() => {
                document.body.classList.remove('printing');
            }, 1000);
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
 * Add loading state to report cards
 */
function addLoadingState(card) {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="spinner"></div>';
    card.style.position = 'relative';
    card.appendChild(loadingOverlay);
    
    return loadingOverlay;
}

/**
 * Remove loading state from report cards
 */
function removeLoadingState(loadingOverlay) {
    if (loadingOverlay && loadingOverlay.parentNode) {
        loadingOverlay.parentNode.removeChild(loadingOverlay);
    }
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

/**
 * Animate progress bars (for budget reports)
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.budget-bar-fill');
    
    progressBars.forEach((bar, index) => {
        const percentage = bar.getAttribute('data-percentage') || bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = percentage;
        }, 100 + (index * 50));
    });
}

/**
 * Handle chart exports
 */
function exportChart(chartId, format = 'png') {
    const canvas = document.getElementById(chartId);
    if (canvas) {
        const url = canvas.toDataURL(`image/${format}`);
        const link = document.createElement('a');
        link.download = `report-chart-${chartId}.${format}`;
        link.href = url;
        link.click();
    }
}

/**
 * Initialize smooth scrolling for internal links
 */
function initializeSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Display notification messages
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

// Initialize animations after DOM is fully loaded
window.addEventListener('load', function() {
    // Animate progress bars if they exist
    animateProgressBars();
    
    // Initialize smooth scrolling
    initializeSmoothScroll();
});