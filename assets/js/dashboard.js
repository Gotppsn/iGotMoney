/**
 * iGotMoney - Dashboard JavaScript
 * Enhanced dashboard functionality with modern interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard components
    initializeDashboard();
    
    // Set up quick actions
    initializeQuickActions();
    
    // Handle card interactions
    initializeCardInteractions();
    
    // Initialize tooltips
    initializeTooltips();
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    // Initialize refresh timer for auto-refresh
    initializeRefreshTimer();
    
    // Add animation for progress bars
    animateProgressBars();
    
    // Initialize chart interactions
    initializeChartInteractions();
}

/**
 * Initialize refresh timer for auto-refresh
 */
function initializeRefreshTimer() {
    // Refresh dashboard data every 30 minutes
    const refreshInterval = 30 * 60 * 1000; // 30 minutes
    
    setInterval(function() {
        // Show refresh indicator
        const refreshButton = document.getElementById('refreshDashboard');
        if (refreshButton) {
            const icon = refreshButton.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            // Fetch fresh data
            fetchDashboardData(function() {
                // Stop spinner when done
                if (icon) {
                    icon.classList.remove('fa-spin');
                }
                
                // Show notification
                showNotification('Dashboard data updated', 'success');
            });
        }
    }, refreshInterval);
}

/**
 * Animate progress bars on load
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    // Animate progress bars one by one with a slight delay
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const targetWidth = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = targetWidth;
        }, 100 + (index * 50)); // Staggered animation
    });
}

/**
 * Initialize interactions for expense category chart
 */
function initializeChartInteractions() {
    const chartContainer = document.querySelector('.chart-container');
    if (!chartContainer) return;
    
    // Add hover effect to chart container
    chartContainer.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
        this.style.transition = 'transform 0.3s ease';
    });
    
    chartContainer.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    
    // Handle dropdown time range selection
    const timeRangeDropdown = document.getElementById('dropdownTimeRange');
    if (timeRangeDropdown) {
        const timeRangeOptions = document.querySelectorAll('.dropdown-menu a.dropdown-item');
        timeRangeOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update dropdown button text
                timeRangeDropdown.textContent = this.textContent;
                
                // Remove active class from all options
                timeRangeOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Show loading state
                chartContainer.classList.add('loading');
                
                // Simulate fetching data for the selected time range
                setTimeout(() => {
                    // Update chart with new data (in a real app, this would fetch from server)
                    updateChartForTimeRange(this.textContent.trim());
                    
                    // Remove loading state
                    chartContainer.classList.remove('loading');
                    
                    // Show notification
                    showNotification('Chart updated to show ' + this.textContent.trim() + ' data', 'info');
                }, 800);
            });
        });
    }
}

/**
 * Initialize card interactions for hover effects
 */
function initializeCardInteractions() {
    // Add hover effect to financial cards
    const financialCards = document.querySelectorAll('.financial-card');
    financialCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add hover effect to budget and expense items
    const hoverItems = document.querySelectorAll('.budget-item, .expense-item, .financial-goal-item, .advice-item');
    hoverItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--gray-100)';
            this.style.borderRadius = '8px';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
    });
}

/**
 * Initialize tooltips for interactive elements
 */
function initializeTooltips() {
    // Initialize tooltips for milestone markers
    const milestoneMarkers = document.querySelectorAll('.milestone-marker');
    milestoneMarkers.forEach(marker => {
        const title = marker.getAttribute('title');
        if (title) {
            // Create tooltip element
            const tooltip = document.createElement('div');
            tooltip.className = 'milestone-tooltip';
            tooltip.textContent = title;
            tooltip.style.position = 'absolute';
            tooltip.style.display = 'none';
            tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            tooltip.style.color = 'white';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '10';
            tooltip.style.pointerEvents = 'none';
            
            document.body.appendChild(tooltip);
            
            // Show tooltip on hover
            marker.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                tooltip.style.display = 'block';
                tooltip.style.left = rect.left + 'px';
                tooltip.style.top = (rect.top - 30) + 'px';
            });
            
            marker.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        }
    });
}

/**
 * Initialize quick actions
 */
function initializeQuickActions() {
    // Quick add expense button (if exists)
    const quickAddExpenseBtn = document.getElementById('quickAddExpense');
    if (quickAddExpenseBtn) {
        quickAddExpenseBtn.addEventListener('click', function() {
            window.location.href = '/expenses?action=add';
        });
    }
    
    // Quick add income button (if exists)
    const quickAddIncomeBtn = document.getElementById('quickAddIncome');
    if (quickAddIncomeBtn) {
        quickAddIncomeBtn.addEventListener('click', function() {
            window.location.href = '/income?action=add';
        });
    }
    
    // Refresh dashboard button
    const refreshButton = document.getElementById('refreshDashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            fetchDashboardData(() => {
                // In a real app, this would fetch data via AJAX
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            });
        });
    }
    
    // Print dashboard button
    const printButton = document.getElementById('printDashboard');
    if (printButton) {
        printButton.addEventListener('click', function() {
            // Prepare for printing
            document.body.classList.add('printing');
            
            // Print the page
            window.print();
            
            // Remove printing class after print dialog closes
            setTimeout(() => {
                document.body.classList.remove('printing');
            }, 1000);
        });
    }
}

/**
 * Fetch dashboard data
 * @param {function} callback - Function to call when fetch is complete
 */
function fetchDashboardData(callback) {
    // In a real implementation, this would make an AJAX request
    // For demonstration, we're just simulating a delay
    
    // Show a loading indicator
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.innerHTML = '<i class="fas fa-circle-notch fa-spin fa-3x" style="color: var(--primary-color);"></i>';
    
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
    
    // Simulate network request
    setTimeout(function() {
        // Remove loading overlay
        document.body.removeChild(loadingOverlay);
        
        // Call callback function
        if (callback && typeof callback === 'function') {
            callback();
        }
    }, 1000);
}

/**
 * Update chart for selected time range
 * @param {string} timeRange - Selected time range
 */
function updateChartForTimeRange(timeRange) {
    // In a real app, this would fetch data from the server
    // Here we're just simulating different data for different time ranges
    
    if (!window.expenseCategoryChart) return;
    
    let newData;
    
    switch(timeRange) {
        case 'Last Month':
            newData = [350, 280, 190, 140, 90];
            break;
        case 'Last 3 Months':
            newData = [950, 780, 620, 450, 320];
            break;
        case 'This Year':
            newData = [3200, 2800, 2100, 1700, 1200];
            break;
        default: // 'This Month'
            // Keep existing data
            return;
    }
    
    // Update chart data
    window.expenseCategoryChart.data.datasets[0].data = newData;
    window.expenseCategoryChart.update();
    
    // Update top expenses list
    updateTopExpensesList(newData);
}

/**
 * Update top expenses list with new data
 * @param {array} newData - New expense data
 */
function updateTopExpensesList(newData) {
    const expenseItems = document.querySelectorAll('.expense-item');
    const totalExpenses = newData.reduce((sum, value) => sum + value, 0);
    
    expenseItems.forEach((item, index) => {
        if (index < newData.length) {
            const amountElement = item.querySelector('.expense-amount');
            const progressBar = item.querySelector('.progress-bar');
            
            if (amountElement) {
                amountElement.textContent = '$' + newData[index].toFixed(2);
            }
            
            if (progressBar) {
                const percentage = (newData[index] / totalExpenses) * 100;
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
            }
        }
    });
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'dashboard-notification';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '4px';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 3px 6px rgba(0,0,0,0.16)';
    notification.style.transform = 'translateX(120%)';
    notification.style.transition = 'transform 0.3s ease';
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#2ecc71';
            notification.style.color = '#fff';
            break;
        case 'warning':
            notification.style.backgroundColor = '#f39c12';
            notification.style.color = '#fff';
            break;
        case 'danger':
            notification.style.backgroundColor = '#e74c3c';
            notification.style.color = '#fff';
            break;
        default: // info
            notification.style.backgroundColor = '#3498db';
            notification.style.color = '#fff';
    }
    
    // Add icon based on type
    let icon;
    switch(type) {
        case 'success':
            icon = 'fa-check-circle';
            break;
        case 'warning':
            icon = 'fa-exclamation-triangle';
            break;
        case 'danger':
            icon = 'fa-exclamation-circle';
            break;
        default: // info
            icon = 'fa-info-circle';
    }
    
    notification.innerHTML = `<i class="fas ${icon} me-2"></i> ${message}`;
    
    // Add close button
    const closeButton = document.createElement('span');
    closeButton.innerHTML = '&times;';
    closeButton.style.marginLeft = '10px';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontWeight = 'bold';
    
    closeButton.addEventListener('click', function() {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
    
    notification.appendChild(closeButton);
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto-hide after duration
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

/**
 * Update financial summary cards with new data
 * @param {object} summary - The summary data
 */
function updateFinancialSummary(summary) {
    // Update monthly income
    updateCardValue('.income-card .card-value', summary.monthly_income);
    
    // Update monthly expenses
    updateCardValue('.expenses-card .card-value', summary.monthly_expenses);
    
    // Update monthly net
    updateCardValue('.savings-card .card-value', summary.monthly_net);
    
    // Update yearly projection
    updateCardValue('.projection-card .card-value', summary.yearly_net);
}

/**
 * Update card value with animation
 * @param {string} selector - Element selector
 * @param {number} newValue - New value
 */
function updateCardValue(selector, newValue) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    // Get current value without formatting
    const currentText = element.textContent;
    const currentValue = parseFloat(currentText.replace(/[^0-9.-]+/g, ''));
    
    // Animate count up/down
    animateValue(element, currentValue, newValue, 1000);
}

/**
 * Animate numeric value change
 * @param {Element} element - DOM element to update
 * @param {number} start - Start value
 * @param {number} end - End value
 * @param {number} duration - Animation duration in milliseconds
 */
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = progress * (end - start) + start;
        element.textContent = '$' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}