/**
 * iGotMoney - Dashboard JavaScript
 * Enhanced dashboard functionality with modern interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard components
    initializeDashboard();
    
    // Set up quick actions
    initializeQuickActions();
    
    // Initialize tooltips
    initializeTooltips();
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    // Add animation for progress bars
    animateProgressBars();
    
    // Initialize chart interactions
    initializeChartInteractions();
    
    // Add hover effects to cards
    initializeCardInteractions();
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
                
                // Update chart with new data (in a real app, this would fetch from server)
                updateChartForTimeRange(this.textContent.trim());
            });
        });
    }
}

/**
 * Update chart for selected time range
 * @param {string} timeRange - Selected time range
 */
function updateChartForTimeRange(timeRange) {
    // In a real app, this would fetch data from the server
    // Here we're just simulating different data for different time ranges
    
    if (!window.expenseCategoryChart) return;
    
    // Show loading indicator
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = 0.5;
        
        // Create and append loading spinner
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        spinner.style.position = 'absolute';
        spinner.style.top = '50%';
        spinner.style.left = '50%';
        spinner.style.transform = 'translate(-50%, -50%)';
        spinner.style.fontSize = '1.5rem';
        spinner.style.color = '#4e73df';
        
        chartContainer.appendChild(spinner);
    }
    
    // Simulate API delay
    setTimeout(() => {
        let newData;
        
        switch(timeRange) {
            case 'Last Month':
                newData = generateRandomData(5, 100, 500);
                break;
            case 'Last 3 Months':
                newData = generateRandomData(5, 300, 1500);
                break;
            case 'This Year':
                newData = generateRandomData(5, 1000, 5000);
                break;
            default: // 'This Month'
                // Keep existing data
                if (chartContainer) {
                    chartContainer.style.opacity = 1;
                    chartContainer.removeChild(spinner);
                }
                return;
        }
        
        // Update chart data
        window.expenseCategoryChart.data.datasets[0].data = newData;
        window.expenseCategoryChart.update();
        
        // Update top expenses list with new data
        updateTopExpensesList(newData);
        
        // Remove loading indicator
        if (chartContainer) {
            chartContainer.style.opacity = 1;
            chartContainer.removeChild(spinner);
        }
        
        // Show notification
        showNotification('Chart updated to show ' + timeRange + ' data', 'info');
    }, 800);
}

/**
 * Generate random data for chart simulation
 */
function generateRandomData(count, min, max) {
    const data = [];
    for (let i = 0; i < count; i++) {
        data.push(Math.floor(Math.random() * (max - min + 1)) + min);
    }
    return data;
}

/**
 * Update top expenses list with new data
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
 * Initialize card interactions for hover effects
 */
function initializeCardInteractions() {
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
    // Refresh dashboard button
    const refreshButton = document.getElementById('refreshDashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            // Show loading indicator
            showLoadingOverlay();
            
            // In a real app, this would fetch data via AJAX
            // For demonstration, we're just reloading the page
            setTimeout(() => {
                window.location.reload();
            }, 800);
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
    
    // Generate advice button
    const generateAdviceBtn = document.getElementById('generateAdvice');
    if (generateAdviceBtn) {
        generateAdviceBtn.addEventListener('click', function() {
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            
            // In a real app, this would make an AJAX call
            // For now, just reload the page after a short delay
            setTimeout(() => {
                window.location.href = '?generate_advice=true';
            }, 1000);
        });
    }
}

/**
 * Show loading overlay
 */
function showLoadingOverlay() {
    // Create loading overlay
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
    
    // Remove after 1 second (in a real app, this would be removed after data loads)
    setTimeout(() => {
        if (document.body.contains(loadingOverlay)) {
            document.body.removeChild(loadingOverlay);
        }
    }, 1000);
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