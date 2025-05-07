/**
 * iGotMoney - Expenses Page JavaScript
 * 
 * Handles all interactive functionality for the expenses page
 */

// Wait for DOM content to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Expenses.js: DOM fully loaded');
    
    try {
        // Initialize components with try/catch blocks for better error handling
        initializeExpenseForms();
        initializeTableFilters();
        initializeExpenseActions();
        initializeChartInteractions();
        initializeAnalytics();
        
        // Add animations
        animateElements();
        
        console.log('Expenses.js: All components initialized successfully');
    } catch (error) {
        console.error('Expenses.js: Error during initialization:', error);
    }
});

/**
 * Initialize expense forms
 */
function initializeExpenseForms() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Toggle recurring options in add form
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurring_options');
    
    if (isRecurringCheckbox && recurringOptions) {
        isRecurringCheckbox.addEventListener('change', function() {
            recurringOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Toggle recurring options in edit form
    const editIsRecurringCheckbox = document.getElementById('edit_is_recurring');
    const editRecurringOptions = document.getElementById('edit_recurring_options');
    
    if (editIsRecurringCheckbox && editRecurringOptions) {
        editIsRecurringCheckbox.addEventListener('change', function() {
            editRecurringOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
}

/**
 * Initialize table filters and search
 */
function initializeTableFilters() {
    // Expense search functionality
    const expenseSearch = document.getElementById('expenseSearch');
    if (expenseSearch) {
        expenseSearch.addEventListener('keyup', function() {
            const tableId = this.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            
            if (table) {
                const searchText = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
                
                // Show/hide no data message
                const tableNoData = document.getElementById('tableNoData');
                if (tableNoData) {
                    const visibleRows = table.querySelectorAll('tbody tr[style=""]').length;
                    tableNoData.style.display = visibleRows === 0 ? 'block' : 'none';
                }
            }
        });
    }
    
    // Date range filter
    const dateRangeSelect = document.getElementById('dateRangeSelect');
    const customDateRange = document.getElementById('customDateRange');
    const applyDateFilter = document.getElementById('applyDateFilter');
    
    if (dateRangeSelect && customDateRange) {
        dateRangeSelect.addEventListener('change', function() {
            customDateRange.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    }
    
    if (applyDateFilter) {
        applyDateFilter.addEventListener('click', function() {
            const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
            const selectedRange = dateRangeSelect.value;
            
            let startDate, endDate;
            const now = new Date();
            
            // Calculate date range based on selected option
            switch(selectedRange) {
                case 'all':
                    // Redirect to view all expenses
                    window.location.href = `${basePath}/expenses`;
                    return;
                case 'current-month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                case 'last-month':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'last-3-months':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate());
                    endDate = now;
                    break;
                case 'last-6-months':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 6, now.getDate());
                    endDate = now;
                    break;
                case 'current-year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31);
                    break;
                case 'custom':
                    // Get dates from custom inputs
                    const startInput = document.getElementById('startDate');
                    const endInput = document.getElementById('endDate');
                    
                    if (startInput.value && endInput.value) {
                        startDate = new Date(startInput.value);
                        endDate = new Date(endInput.value);
                    } else {
                        // Show error message if dates not selected
                        alert('Please select both start and end dates for custom range.');
                        return;
                    }
                    break;
                default:
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            }
            
            // Format dates for URL
            const formatDate = date => {
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            // Redirect with date filter
            window.location.href = `${basePath}/expenses?start_date=${formatDate(startDate)}&end_date=${formatDate(endDate)}`;
        });
    }
}

/**
 * Initialize expense actions (edit, delete)
 */
function initializeExpenseActions() {
    // Edit expense action
    const editButtons = document.querySelectorAll('.edit-expense');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            if (expenseId) {
                fetchExpenseDetails(expenseId);
            }
        });
    });
    
    // Delete expense action
    const deleteButtons = document.querySelectorAll('.delete-expense');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            if (expenseId) {
                document.getElementById('delete_expense_id').value = expenseId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
                deleteModal.show();
            }
        });
    });
}

/**
 * Fetch expense details for editing
 */
function fetchExpenseDetails(expenseId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Populate form fields
                document.getElementById('edit_expense_id').value = data.expense.expense_id;
                
                // Safely set values with error handling
                const categoryField = document.getElementById('edit_category_id');
                if (categoryField) categoryField.value = data.expense.category_id;
                
                const descriptionField = document.getElementById('edit_description');
                if (descriptionField) descriptionField.value = data.expense.description;
                
                const amountField = document.getElementById('edit_amount');
                if (amountField) amountField.value = data.expense.amount;
                
                const dateField = document.getElementById('edit_expense_date');
                if (dateField) dateField.value = data.expense.expense_date;
                
                const isRecurring = data.expense.is_recurring === '1' || data.expense.is_recurring === 1;
                
                const recurringField = document.getElementById('edit_is_recurring');
                if (recurringField) recurringField.checked = isRecurring;
                
                const recurringOptions = document.getElementById('edit_recurring_options');
                if (recurringOptions) recurringOptions.style.display = isRecurring ? 'block' : 'none';
                
                if (isRecurring) {
                    const frequencyField = document.getElementById('edit_frequency');
                    if (frequencyField) frequencyField.value = data.expense.frequency;
                }
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                editModal.show();
            } else {
                console.error('Error fetching expense details:', data.message);
                showNotification('Failed to fetch expense details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while fetching expense details', 'danger');
        });
}

/**
 * Initialize chart interactions
 */
function initializeChartInteractions() {
    // Chart period dropdown handler
    const chartPeriodOptions = document.querySelectorAll('.chart-period');
    
    chartPeriodOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update dropdown button text
            const dropdownButton = document.getElementById('chartPeriodDropdown');
            if (dropdownButton) {
                dropdownButton.textContent = this.textContent.trim();
            }
            
            // Remove active class from all options
            chartPeriodOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to selected option
            this.classList.add('active');
            
            // Update chart with new data for selected period
            updateChartForPeriod(this.getAttribute('data-period'));
        });
    });
}

/**
 * Update chart for different time periods
 */
function updateChartForPeriod(period) {
    const chartContainer = document.querySelector('.chart-container');
    const chartNoData = document.getElementById('chartNoData');
    
    if (chartContainer) {
        // Show loading indicator
        chartContainer.style.opacity = 0.5;
        const spinner = document.createElement('div');
        spinner.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        chartContainer.appendChild(spinner);
        
        // Get base path
        const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
        
        // Calculate date range based on period
        let startDate, endDate;
        const now = new Date();
        
        switch(period) {
            case 'last-month':
                startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                break;
            case 'quarter':
                const quarter = Math.floor(now.getMonth() / 3);
                startDate = new Date(now.getFullYear(), quarter * 3, 1);
                endDate = new Date(now.getFullYear(), (quarter + 1) * 3, 0);
                break;
            case 'last-3-months':
                startDate = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate());
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case 'current-year':
                startDate = new Date(now.getFullYear(), 0, 1);
                endDate = new Date(now.getFullYear(), 11, 31);
                break;
            case 'all':
                startDate = new Date(2000, 0, 1); // Far back enough to include all data
                endDate = new Date(now.getFullYear() + 10, 11, 31); // Far ahead enough to include all data
                break;
            default: // current-month
                startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        }
        
        // Format dates for API
        const formatDate = date => {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        // Fetch data for the specified period
        fetch(`${basePath}/expenses?action=get_expenses_by_date&start_date=${formatDate(startDate)}&end_date=${formatDate(endDate)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                // Remove spinner
                spinner.remove();
                chartContainer.style.opacity = 1;
                
                if (data.success) {
                    if (data.expenses.length > 0) {
                        // Process data for chart
                        const categoryTotals = {};
                        
                        // Calculate totals by category
                        data.expenses.forEach(expense => {
                            const category = expense.category_name;
                            if (!categoryTotals[category]) {
                                categoryTotals[category] = 0;
                            }
                            categoryTotals[category] += parseFloat(expense.amount);
                        });
                        
                        // Sort categories by amount
                        const sortedCategories = Object.entries(categoryTotals)
                            .sort((a, b) => b[1] - a[1])
                            .slice(0, 10); // Limit to top 10 categories
                        
                        // Prepare chart data
                        const labels = sortedCategories.map(item => item[0]);
                        const values = sortedCategories.map(item => item[1]);
                        
                        // Update chart
                        if (window.expenseCategoryChart) {
                            window.expenseCategoryChart.data.labels = labels;
                            window.expenseCategoryChart.data.datasets[0].data = values;
                            window.expenseCategoryChart.update();
                            
                            // Show chart and hide no data message
                            chartContainer.style.display = 'block';
                            chartNoData.style.display = 'none';
                        }
                        
                        // Also update the top expenses list
                        updateTopExpensesList(sortedCategories, data.total);
                        
                        showNotification(`Chart updated to ${period.replace('-', ' ')} view`, 'info');
                    } else {
                        // Show no data message
                        chartContainer.style.display = 'none';
                        chartNoData.style.display = 'block';
                    }
                } else {
                    console.error('Error fetching data for chart:', data.message);
                    // Show no data message
                    chartContainer.style.display = 'none';
                    chartNoData.style.display = 'block';
                }
            })
            .catch(error => {
                // Remove spinner
                spinner.remove();
                chartContainer.style.opacity = 1;
                
                console.error('Error:', error);
                // Show no data message
                chartContainer.style.display = 'none';
                chartNoData.style.display = 'block';
            });
    }
}

/**
 * Update top expenses list with new data
 */
function updateTopExpensesList(categories, totalAmount) {
    const topExpensesContainer = document.querySelector('.chart-card:nth-child(2) .card-body');
    
    if (topExpensesContainer && categories.length > 0) {
        // Create HTML content
        let html = '';
        
        // Add top 5 categories or fewer if less available
        const displayCategories = categories.slice(0, 5);
        
        displayCategories.forEach(([category, amount]) => {
            const percentage = (amount / totalAmount) * 100;
            let colorClass = 'bg-info';
            
            if (percentage > 30) {
                colorClass = 'bg-danger';
            } else if (percentage > 20) {
                colorClass = 'bg-warning';
            } else if (percentage > 10) {
                colorClass = 'bg-primary';
            }
            
            html += `
            <div class="expense-item">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="expense-category">${category}</span>
                    <span class="expense-amount">$${amount.toFixed(2)}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar ${colorClass}" role="progressbar" 
                        style="width: ${Math.min(100, percentage)}%" 
                        aria-valuenow="${percentage}" 
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>`;
        });
        
        // If no categories, show empty state
        if (categories.length === 0) {
            html = `
            <div class="text-center py-4 empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h4>No expenses found</h4>
                <p>No expense data available for the selected period</p>
            </div>`;
        }
        
        // Update the content with animation
        topExpensesContainer.style.opacity = 0;
        setTimeout(() => {
            topExpensesContainer.innerHTML = html;
            topExpensesContainer.style.opacity = 1;
        }, 300);
    }
}

/**
 * Initialize analytics functionality
 */
function initializeAnalytics() {
    const calculateAnalyticsBtn = document.getElementById('calculateAnalytics');
    const analyticsContent = document.getElementById('analyticsContent');
    const analyticsPlaceholder = document.getElementById('analyticsPlaceholder');
    
    if (calculateAnalyticsBtn && analyticsContent && analyticsPlaceholder) {
        calculateAnalyticsBtn.addEventListener('click', function() {
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Calculating...';
            analyticsPlaceholder.style.display = 'none';
            
            // Fetch analytics data
            fetchAnalyticsData();
        });
    }
}

/**
 * Fetch analytics data
 */
function fetchAnalyticsData() {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    const analyticsContent = document.getElementById('analyticsContent');
    const calculateAnalyticsBtn = document.getElementById('calculateAnalytics');
    
    fetch(`${basePath}/expenses?action=get_expense_analytics&period=current-month`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset button state
                calculateAnalyticsBtn.disabled = false;
                calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Recalculate';
                
                // Populate analytics content
                displayAnalytics(data.analytics);
                
                // Show analytics content with animation
                analyticsContent.style.opacity = 0;
                analyticsContent.style.display = 'flex';
                setTimeout(() => {
                    analyticsContent.style.opacity = 1;
                }, 50);
                
                showNotification('Analytics calculated successfully', 'success');
            } else {
                console.error('Error fetching analytics:', data.message);
                showNotification('Failed to fetch analytics', 'danger');
                
                // Reset button state
                calculateAnalyticsBtn.disabled = false;
                calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Calculate';
                
                // Show placeholder
                document.getElementById('analyticsPlaceholder').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
            
            // Reset button state
            calculateAnalyticsBtn.disabled = false;
            calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Calculate';
            
            // Show placeholder
            document.getElementById('analyticsPlaceholder').style.display = 'block';
        });
}

/**
 * Display analytics data
 */
function displayAnalytics(analytics) {
    const analyticsContent = document.getElementById('analyticsContent');
    
    // Format date range
    const startDate = new Date(analytics.start_date);
    const endDate = new Date(analytics.end_date);
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    const formattedStartDate = startDate.toLocaleDateString('en-US', dateOptions);
    const formattedEndDate = endDate.toLocaleDateString('en-US', dateOptions);
    
    // Create HTML content
    let html = `
    <div class="col-12 mb-3">
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Showing analytics for period: <strong>${formattedStartDate}</strong> to <strong>${formattedEndDate}</strong>
            (${analytics.days_in_period} days)
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="analytics-item h-100">
            <h6><i class="fas fa-money-bill-wave me-2"></i>Total Expenses</h6>
            <div class="value">$${analytics.total_amount.toFixed(2)}</div>
            <div class="description">${analytics.expense_count} expense entries</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="analytics-item h-100">
            <h6><i class="fas fa-calendar-day me-2"></i>Daily Average</h6>
            <div class="value">$${analytics.daily_average.toFixed(2)}</div>
            <div class="description">Average expense per day</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="analytics-item h-100">
            <h6><i class="fas fa-chart-line me-2"></i>Monthly Projection</h6>
            <div class="value">$${analytics.projected_monthly.toFixed(2)}</div>
            <div class="description">Projected monthly total</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="analytics-item h-100">
            <h6><i class="fas fa-chart-pie me-2"></i>Highest Category</h6>
            <div class="value">${analytics.highest_category}</div>
            <div class="description">$${analytics.highest_amount.toFixed(2)}</div>
        </div>
    </div>`;
    
    // Add category breakdown if available
    if (Object.keys(analytics.category_totals).length > 0) {
        html += `
        <div class="col-12 mt-2">
            <h6 class="mb-3">Category Breakdown</h6>
            <div class="row">`;
        
        // Get sorted categories
        const categories = Object.entries(analytics.category_totals);
        categories.sort((a, b) => b[1] - a[1]);
        
        // Add each category
        categories.forEach(([category, amount]) => {
            const percentage = ((amount / analytics.total_amount) * 100).toFixed(1);
            html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="expense-item px-3 py-2 rounded">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="expense-category">${category}</span>
                        <span class="expense-amount">$${amount.toFixed(2)}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                            style="width: ${percentage}%" 
                            aria-valuenow="${percentage}" 
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="text-end mt-1">
                        <small class="text-muted">${percentage}% of total</small>
                    </div>
                </div>
            </div>`;
        });
        
        html += `
            </div>
        </div>`;
    }
    
    // Update the content
    analyticsContent.innerHTML = html;
    
    // Add animation to items
    setTimeout(() => {
        const items = analyticsContent.querySelectorAll('.analytics-item, .expense-item');
        items.forEach((item, index) => {
            item.style.opacity = 0;
            item.style.transform = 'translateY(10px)';
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                item.style.opacity = 1;
                item.style.transform = 'translateY(0)';
            }, 50 * index);
        });
    }, 100);
}

/**
 * Add animations to page elements
 */
function animateElements() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const width = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = width;
        }, 100 + (index * 50));
    });
    
    // Animate card entrance
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            card.style.opacity = 1;
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // First, remove any existing notifications
    const existingNotifications = document.querySelectorAll('.expense-notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `expense-notification alert alert-${type} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.maxWidth = '300px';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(50px)';
    notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    
    // Set notification content
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove after timeout
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(50px)';
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}