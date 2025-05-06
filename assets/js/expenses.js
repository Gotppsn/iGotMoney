/**
 * iGotMoney - Expenses JavaScript
 * Handles functionality for the expense management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Initialize category highlighting
    initializeCategoryHighlighting();
    
    // Initialize date range filtering
    initializeDateRangeFilter();
    
    // Initialize expense chart
    initializeExpenseChart();
    
    // Initialize expense analytics
    initializeExpenseAnalytics();
    
    // Initialize form validation
    initializeFormValidation();

    // Initialize button event handlers
    initializeButtonHandlers();

    // Initialize recurring options toggle
    initializeRecurringToggle();
    
    // Initialize search functionality
    initializeSearch();
});

/**
 * Initialize category highlighting
 * Highlights rows in the expense table based on category
 */
function initializeCategoryHighlighting() {
    // Get all category options
    const categorySelect = document.getElementById('category_id');
    if (!categorySelect) return;
    
    // Category colors (matching chart colors)
    const categoryColors = {
        1: '#4e73df1a', // Housing - light blue background
        2: '#1cc88a1a', // Utilities - light green background
        3: '#36b9cc1a', // Food - light cyan background
        4: '#f6c23e1a', // Transportation - light yellow background
        5: '#e74a3b1a', // Insurance - light red background
        6: '#6f42c11a', // Healthcare - light purple background
        7: '#fd7e141a', // Debt Payments - light orange background
        8: '#20c9a61a', // Entertainment - light teal background
        9: '#5a5c691a', // Shopping - light gray background
        10: '#8587961a' // Personal Care - light gray-blue background
    };
    
    // Apply highlighting to table rows
    const expenseTable = document.getElementById('expenseTable');
    if (!expenseTable) return;
    
    const rows = expenseTable.querySelectorAll('tbody tr');
    if (!rows.length) return;
    
    rows.forEach(row => {
        const categoryCell = row.querySelector('td:nth-child(2)');
        if (!categoryCell) return;
        
        const categoryName = categoryCell.textContent.trim();
        
        // Find matching category ID
        for (let i = 0; i < categorySelect.options.length; i++) {
            if (categorySelect.options[i].textContent.trim() === categoryName) {
                const categoryId = categorySelect.options[i].value;
                if (categoryColors[categoryId]) {
                    row.style.backgroundColor = categoryColors[categoryId];
                }
                break;
            }
        }
    });
}

/**
 * Initialize date range filter
 * Allows filtering expenses by date range
 */
function initializeDateRangeFilter() {
    // Handle date range selection
    const dateRangeSelect = document.getElementById('dateRangeSelect');
    const customDateRange = document.getElementById('customDateRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const applyButton = document.getElementById('applyDateFilter');
    
    if (!dateRangeSelect || !customDateRange || !startDateInput || !endDateInput || !applyButton) return;
    
    dateRangeSelect.addEventListener('change', function() {
        customDateRange.style.display = this.value === 'custom' ? 'block' : 'none';
        
        if (this.value !== 'custom') {
            // Set default date range based on selection
            const { startDate, endDate } = getDateRangeFromOption(this.value);
            startDateInput.value = startDate ? formatDateForInput(startDate) : '';
            endDateInput.value = endDate ? formatDateForInput(endDate) : '';
        }
    });
    
    // Initialize with current month
    const { startDate, endDate } = getDateRangeFromOption('current-month');
    startDateInput.value = formatDateForInput(startDate);
    endDateInput.value = formatDateForInput(endDate);
    
    // Apply date filter
    applyButton.addEventListener('click', function() {
        const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
        const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }
        
        if (startDate > endDate) {
            alert('Start date must be before end date.');
            return;
        }
        
        // Save selected period for analytics
        window.selectedPeriod = dateRangeSelect.value;
        window.selectedStartDate = startDateInput.value;
        window.selectedEndDate = endDateInput.value;
        
        // Show loading indicator
        const tableBody = document.querySelector('#expenseTable tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading expenses...</p>
                    </td>
                </tr>
            `;
        }
        
        // Fetch expenses from server with selected date range
        const basePath = document.querySelector('meta[name="base-path"]') ? 
            document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
        
        fetch(`${basePath}/expenses?action=get_expenses_by_date&start_date=${startDateInput.value}&end_date=${endDateInput.value}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update table with filtered data
                    updateExpenseTable(data.expenses);
                    
                    // Update chart with filtered data
                    updateChartWithData(data.expenses);
                    
                    // Close dropdown
                    const dropdownMenu = document.querySelector('#dateFilterDropdown + .dropdown-menu');
                    if (dropdownMenu) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(document.getElementById('dateFilterDropdown'));
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                    
                    // Show notification
                    showNotification(`Showing expenses from ${formatDateForDisplay(startDate)} to ${formatDateForDisplay(endDate)}`, 'info');
                } else {
                    showNotification('Failed to load expenses: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching expenses:', error);
                showNotification('An error occurred while loading expenses.', 'danger');
                
                // Clear table
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-danger mb-0">
                                    Failed to load expenses. Please try again.
                                </div>
                            </td>
                        </tr>
                    `;
                }
            });
    });
}

/**
 * Update expense table with filtered data
 * @param {Array} expenses - Array of expense objects
 */
function updateExpenseTable(expenses) {
    const tableBody = document.querySelector('#expenseTable tbody');
    if (!tableBody) return;
    
    if (expenses.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <p>No expenses found for the selected period.</p>
                </td>
            </tr>
        `;
        
        // Show "no data" message
        const tableNoData = document.getElementById('tableNoData');
        if (tableNoData) {
            tableNoData.style.display = 'block';
        }
        return;
    }
    
    // Hide "no data" message
    const tableNoData = document.getElementById('tableNoData');
    if (tableNoData) {
        tableNoData.style.display = 'none';
    }
    
    // Generate table rows
    let rowsHTML = '';
    
    expenses.forEach(expense => {
        // Format date
        const expenseDate = new Date(expense.expense_date);
        const formattedDate = formatDateForDisplay(expenseDate);
        
        // Format frequency
        const frequency = expense.frequency.replace(/-/g, ' ');
        const capitalizedFrequency = frequency.charAt(0).toUpperCase() + frequency.slice(1);
        
        rowsHTML += `
            <tr>
                <td>${escapeHTML(expense.description)}</td>
                <td>${escapeHTML(expense.category_name)}</td>
                <td>$${parseFloat(expense.amount).toFixed(2)}</td>
                <td>${formattedDate}</td>
                <td>${capitalizedFrequency}</td>
                <td>
                    <span class="badge ${expense.is_recurring == 1 ? 'bg-info' : 'bg-secondary'}">
                        ${expense.is_recurring == 1 ? 'Yes' : 'No'}
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-info edit-expense" data-expense-id="${expense.expense_id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-expense" data-expense-id="${expense.expense_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = rowsHTML;
    
    // Reinitialize button handlers for new rows
    initializeButtonHandlers();
    
    // Reapply category highlighting
    initializeCategoryHighlighting();
}

/**
 * Initialize expense chart
 * Creates and configures the expense category chart
 */
function initializeExpenseChart() {
    const chartCanvas = document.getElementById('expenseCategoryChart');
    if (!chartCanvas) return;
    
    // Get chart data from meta tags
    const labelsStr = document.querySelector('meta[name="chart-labels"]') ? 
        document.querySelector('meta[name="chart-labels"]').getAttribute('content') : '[]';
    
    const dataStr = document.querySelector('meta[name="chart-data"]') ? 
        document.querySelector('meta[name="chart-data"]').getAttribute('content') : '[]';
    
    const colorsStr = document.querySelector('meta[name="chart-colors"]') ? 
        document.querySelector('meta[name="chart-colors"]').getAttribute('content') : '[]';
    
    const labels = JSON.parse(labelsStr);
    const data = JSON.parse(dataStr);
    const colors = JSON.parse(colorsStr);
    
    // Create hover colors (darker)
    const hoverColors = colors.map(color => {
        return adjustColor(color, -20);
    });
    
    // Initialize the chart
    window.expenseCategoryChart = new Chart(chartCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                hoverBackgroundColor: hoverColors,
                hoverBorderColor: 'rgba(234, 236, 244, 1)',
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%',
        }
    });
    
    // Show/hide no data message
    const chartNoData = document.getElementById('chartNoData');
    if (chartNoData) {
        chartNoData.style.display = labels.length > 0 ? 'none' : 'block';
    }
    
    // Handle chart period dropdown
    const chartPeriodLinks = document.querySelectorAll('.chart-period');
    chartPeriodLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update dropdown button text
            const periodText = this.textContent;
            const dropdownButton = document.getElementById('chartPeriodDropdown');
            if (dropdownButton) {
                dropdownButton.innerHTML = `<i class="fas fa-calendar-alt me-1"></i> ${periodText}`;
            }
            
            // Get date range based on period
            const period = this.getAttribute('data-period');
            const { startDate, endDate } = getDateRangeFromOption(period);
            
            // Update chart for date range
            if (startDate && endDate) {
                // Update chart via AJAX
                const basePath = document.querySelector('meta[name="base-path"]') ? 
                    document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
                
                const formattedStartDate = formatDateForInput(startDate);
                const formattedEndDate = formatDateForInput(endDate);
                
                fetch(`${basePath}/expenses?action=get_expenses_by_date&start_date=${formattedStartDate}&end_date=${formattedEndDate}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update chart
                            updateChartWithData(data.expenses);
                            
                            // Update top expenses
                            updateTopExpenses(data.expenses);
                        } else {
                            console.error('Failed to load expense data:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching expense data:', error);
                    });
            }
        });
    });
}

/**
 * Adjust color brightness
 * @param {string} color - Hex color
 * @param {number} amount - Amount to adjust (positive = lighter, negative = darker)
 * @returns {string} - Adjusted color
 */
function adjustColor(color, amount) {
    // Convert hex to RGB
    let r = parseInt(color.substring(1, 3), 16);
    let g = parseInt(color.substring(3, 5), 16);
    let b = parseInt(color.substring(5, 7), 16);
    
    // Adjust colors
    r = Math.max(0, Math.min(255, r + amount));
    g = Math.max(0, Math.min(255, g + amount));
    b = Math.max(0, Math.min(255, b + amount));
    
    // Convert back to hex
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

/**
 * Update chart with data
 * @param {Array} expenses - Array of expense objects
 */
function updateChartWithData(expenses) {
    if (!window.expenseCategoryChart) return;
    
    // Group expenses by category
    const categories = {};
    
    expenses.forEach(expense => {
        const categoryName = expense.category_name;
        
        if (!categories[categoryName]) {
            categories[categoryName] = 0;
        }
        
        categories[categoryName] += parseFloat(expense.amount);
    });
    
    // Convert to arrays for chart
    const categoryNames = Object.keys(categories);
    const categoryTotals = categoryNames.map(name => categories[name]);
    
    // Get chart colors
    const chartColorsStr = document.querySelector('meta[name="chart-colors"]') ? 
        document.querySelector('meta[name="chart-colors"]').getAttribute('content') : '[]';
    
    const chartColors = JSON.parse(chartColorsStr);
    
    // Ensure we have enough colors
    while (chartColors.length < categoryNames.length) {
        const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);
        chartColors.push(randomColor);
    }
    
    // Create hover colors
    const hoverColors = chartColors.map(color => adjustColor(color, -20));
    
    // Update chart data
    window.expenseCategoryChart.data.labels = categoryNames;
    window.expenseCategoryChart.data.datasets[0].data = categoryTotals;
    window.expenseCategoryChart.data.datasets[0].backgroundColor = chartColors.slice(0, categoryNames.length);
    window.expenseCategoryChart.data.datasets[0].hoverBackgroundColor = hoverColors.slice(0, categoryNames.length);
    
    // Update chart
    window.expenseCategoryChart.update();
    
    // Show/hide no data message
    const chartNoData = document.getElementById('chartNoData');
    if (chartNoData) {
        chartNoData.style.display = categoryNames.length > 0 ? 'none' : 'block';
    }
    
    // Update top expenses
    updateTopExpenses(expenses);
}

/**
 * Update top expenses
 * @param {Array} expenses - Array of expense objects
 */
function updateTopExpenses(expenses) {
    const topExpensesContent = document.getElementById('topExpensesContent');
    if (!topExpensesContent) return;
    
    // Group expenses by category
    const categories = {};
    
    expenses.forEach(expense => {
        const categoryName = expense.category_name;
        
        if (!categories[categoryName]) {
            categories[categoryName] = 0;
        }
        
        categories[categoryName] += parseFloat(expense.amount);
    });
    
    // Convert to array and sort
    const categoryData = Object.entries(categories)
        .map(([name, total]) => ({ name, total }))
        .sort((a, b) => b.total - a.total)
        .slice(0, 5); // Top 5 categories
    
    if (categoryData.length === 0) {
        topExpensesContent.innerHTML = '<p>No expense data available for the selected period.</p>';
        return;
    }
    
    // Calculate total expenses
    const totalExpenses = categoryData.reduce((sum, category) => sum + category.total, 0);
    
    let html = '';
    
    categoryData.forEach(category => {
        const percentage = (category.total / totalExpenses) * 100;
        let colorClass = 'bg-info';
        
        if (percentage > 30) {
            colorClass = 'bg-danger';
        } else if (percentage > 20) {
            colorClass = 'bg-warning';
        } else if (percentage > 10) {
            colorClass = 'bg-primary';
        }
        
        html += `
            <h4 class="small font-weight-bold">
                ${escapeHTML(category.name)}
                <span class="float-end">$${category.total.toFixed(2)}</span>
            </h4>
            <div class="progress mb-4">
                <div class="progress-bar ${colorClass}" role="progressbar" 
                    style="width: ${Math.min(100, percentage)}%" 
                    aria-valuenow="${percentage}" 
                    aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        `;
    });
    
    topExpensesContent.innerHTML = html;
}

/**
 * Initialize expense analytics
 * Calculates and displays expense analytics
 */
function initializeExpenseAnalytics() {
    const calculateButton = document.getElementById('calculateAnalytics');
    if (!calculateButton) return;
    
    calculateButton.addEventListener('click', function() {
        const analyticsContent = document.getElementById('analyticsContent');
        if (!analyticsContent) return;
        
        // Show loading state
        analyticsContent.style.display = 'block';
        analyticsContent.innerHTML = `
            <div class="col-12 text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Calculating expense analytics...</p>
            </div>
        `;
        
        // Get current date filter state
        const period = window.selectedPeriod || 'current-month';
        const startDate = window.selectedStartDate || formatDateForInput(getDateRangeFromOption('current-month').startDate);
        const endDate = window.selectedEndDate || formatDateForInput(getDateRangeFromOption('current-month').endDate);
        
        // Get base path
        const basePath = document.querySelector('meta[name="base-path"]') ? 
            document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
        
        // Fetch analytics data from server
        fetch(`${basePath}/expenses?action=get_expense_analytics&period=${period}&start_date=${startDate}&end_date=${endDate}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show analytics
                    displayAnalytics(data.analytics);
                } else {
                    throw new Error(data.message || 'Failed to get analytics');
                }
            })
            .catch(error => {
                console.error('Error calculating analytics:', error);
                
                // Show error message
                analyticsContent.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to calculate analytics. Please try again.
                        </div>
                    </div>
                `;
            });
    });
}

/**
 * Display analytics
 * @param {Object} analytics - Analytics data
 */
function displayAnalytics(analytics) {
    const analyticsContent = document.getElementById('analyticsContent');
    if (!analyticsContent) return;
    
    // Update the analytics UI
    analyticsContent.innerHTML = `
        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Daily Expense</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgDailyExpense">$${analytics.daily_average.toFixed(2)}</div>
                            <div class="small text-muted">Based on ${analytics.days_in_period} days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Highest Expense</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="highestExpense">$${analytics.highest_amount.toFixed(2)}</div>
                            <div class="small" id="highestExpenseCategory">${analytics.highest_category}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Projected Monthly Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="projectedMonthly">$${analytics.projected_monthly.toFixed(2)}</div>
                            <div class="small text-muted">Based on daily average</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Calculate expense analytics
 * @param {Array} expenses - Array of expense objects
 */
function calculateExpenseAnalytics(expenses) {
    // Calculate total expense amount
    const totalAmount = expenses.reduce((sum, expense) => sum + parseFloat(expense.amount), 0);
    
    // Find date range
    const dates = expenses.map(expense => new Date(expense.expense_date));
    const minDate = dates.length ? new Date(Math.min.apply(null, dates)) : new Date();
    const maxDate = dates.length ? new Date(Math.max.apply(null, dates)) : new Date();
    
    // Calculate days in range
    const daysDiff = Math.max(1, Math.round((maxDate - minDate) / (1000 * 60 * 60 * 24)) + 1);
    
    // Calculate average daily expense
    const avgDaily = totalAmount / daysDiff;
    
    // Find highest expense
    const highestExpense = expenses.length ? expenses.reduce((max, expense) => 
        parseFloat(expense.amount) > parseFloat(max.amount) ? expense : max, expenses[0]) : { amount: 0, category_name: 'N/A' };
    
    // Calculate projected monthly total
    const daysInMonth = 30;
    const projectedMonthly = avgDaily * daysInMonth;
    
    return {
        totalAmount,
        daysDiff,
        avgDaily,
        highestExpense,
        projectedMonthly
    };
}

/**
 * Initialize form validation
 * Validates expense forms before submission
 */
function initializeFormValidation() {
    // Add form validation
    const addForm = document.getElementById('addExpenseForm');
    const editForm = document.getElementById('editExpenseForm');
    
    if (addForm) {
        addForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
    
    // Reset form when modal is closed
    const addModal = document.getElementById('addExpenseModal');
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function() {
            if (addForm) {
                addForm.reset();
                addForm.classList.remove('was-validated');
            }
            
            const recurringOptions = document.getElementById('recurring_options');
            if (recurringOptions) {
                recurringOptions.style.display = 'none';
            }
        });
    }
    
    const editModal = document.getElementById('editExpenseModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            if (editForm) {
                editForm.reset();
                editForm.classList.remove('was-validated');
            }
            
            const editRecurringOptions = document.getElementById('edit_recurring_options');
            if (editRecurringOptions) {
                editRecurringOptions.style.display = 'none';
            }
        });
    }
}

/**
 * Initialize recurring options toggle
 */
function initializeRecurringToggle() {
    // Toggle recurring options when checkbox is clicked
    const isRecurringCheckbox = document.getElementById('is_recurring');
    if (isRecurringCheckbox) {
        isRecurringCheckbox.addEventListener('change', function() {
            const recurringOptions = document.getElementById('recurring_options');
            if (recurringOptions) {
                recurringOptions.style.display = this.checked ? 'block' : 'none';
            }
            
            // Set default frequency for one-time expenses
            const frequencySelect = document.getElementById('frequency');
            if (frequencySelect) {
                if (!this.checked) {
                    frequencySelect.value = 'one-time';
                } else {
                    frequencySelect.value = 'monthly';
                }
            }
        });
    }

    // Toggle edit recurring options when checkbox is clicked
    const editIsRecurringCheckbox = document.getElementById('edit_is_recurring');
    if (editIsRecurringCheckbox) {
        editIsRecurringCheckbox.addEventListener('change', function() {
            const editRecurringOptions = document.getElementById('edit_recurring_options');
            if (editRecurringOptions) {
                editRecurringOptions.style.display = this.checked ? 'block' : 'none';
            }
            
            // Set frequency value
            const editFrequencySelect = document.getElementById('edit_frequency');
            if (editFrequencySelect) {
                if (!this.checked) {
                    editFrequencySelect.value = 'one-time';
                }
            }
        });
    }
}

/**
 * Initialize button handlers
 * Sets up event listeners for edit and delete buttons
 */
function initializeButtonHandlers() {
    // Get base path
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Handle edit expense button clicks
    document.querySelectorAll('.edit-expense').forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            document.getElementById('edit_expense_id').value = expenseId;
            
            // Reset form validation
            const form = document.getElementById('editExpenseForm');
            if (form) {
                form.classList.remove('was-validated');
            }
            
            // Show modal
            const editModal = document.getElementById('editExpenseModal');
            if (editModal) {
                try {
                    const modal = new bootstrap.Modal(editModal);
                    modal.show();
                    
                    // Show loading state
                    editModal.querySelector('.modal-body').innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading expense data...</p>
                        </div>
                    `;
                    
                    // Fetch expense data
                    fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Restore modal content
                                editModal.querySelector('.modal-body').innerHTML = `
                                    <div class="mb-3">
                                        <label for="edit_category_id" class="form-label">Category</label>
                                        <select class="form-select" id="edit_category_id" name="category_id" required>
                                            <option value="">Select a category</option>
                                            ${Array.from(document.getElementById('category_id').options).map(option => 
                                                `<option value="${option.value}">${option.textContent}</option>`
                                            ).join('')}
                                        </select>
                                        <div class="invalid-feedback">Please select a category.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="edit_description" name="description" required>
                                        <div class="invalid-feedback">Please provide a description.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_amount" class="form-label">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_expense_date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                                        <div class="invalid-feedback">Please select a date.</div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="edit_is_recurring" name="is_recurring">
                                        <label class="form-check-label" for="edit_is_recurring">Recurring Expense</label>
                                    </div>
                                    
                                    <div id="edit_recurring_options" style="display: none;">
                                        <div class="mb-3">
                                            <label for="edit_frequency" class="form-label">Frequency</label>
                                            <select class="form-select" id="edit_frequency" name="frequency">
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="bi-weekly">Bi-Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="annually">Annually</option>
                                            </select>
                                        </div>
                                    </div>
                                `;
                                
                                // Reinitialize recurring toggle
                                const editIsRecurringCheckbox = document.getElementById('edit_is_recurring');
                                if (editIsRecurringCheckbox) {
                                    editIsRecurringCheckbox.addEventListener('change', function() {
                                        const editRecurringOptions = document.getElementById('edit_recurring_options');
                                        if (editRecurringOptions) {
                                            editRecurringOptions.style.display = this.checked ? 'block' : 'none';
                                        }
                                    });
                                }
                                
                                // Populate form fields
                                document.getElementById('edit_expense_id').value = data.expense.expense_id;
                                document.getElementById('edit_category_id').value = data.expense.category_id;
                                document.getElementById('edit_description').value = data.expense.description;
                                document.getElementById('edit_amount').value = data.expense.amount;
                                document.getElementById('edit_expense_date').value = data.expense.expense_date;
                                document.getElementById('edit_is_recurring').checked = data.expense.is_recurring == 1;
                                document.getElementById('edit_frequency').value = data.expense.frequency;
                                
                                // Show/hide recurring options
                                document.getElementById('edit_recurring_options').style.display = 
                                    data.expense.is_recurring == 1 ? 'block' : 'none';
                            } else {
                                // Show error
                                showNotification('Failed to load expense data: ' + data.message, 'danger');
                                modal.hide();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching expense data:', error);
                            showNotification('An error occurred while loading expense data.', 'danger');
                            modal.hide();
                        });
                } catch (e) {
                    console.error('Error showing modal:', e);
                    showNotification('Could not open edit form. Please try again.', 'danger');
                }
            }
        });
    });

    // Handle delete expense button clicks
    document.querySelectorAll('.delete-expense').forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            const deleteExpenseIdInput = document.getElementById('delete_expense_id');
            if (deleteExpenseIdInput) {
                deleteExpenseIdInput.value = expenseId;
            }
            
            // Show modal
            const deleteModal = document.getElementById('deleteExpenseModal');
            if (deleteModal) {
                try {
                    const modal = new bootstrap.Modal(deleteModal);
                    modal.show();
                } catch (e) {
                    console.error('Error showing delete modal:', e);
                    showNotification('Could not open delete confirmation. Please try again.', 'danger');
                }
            }
        });
    });
    
    // Handle delete form submission
    const deleteForm = document.getElementById('deleteExpenseForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const expenseId = document.getElementById('delete_expense_id').value;
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            // Submit form with AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
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
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteExpenseModal'));
                modal.hide();
                
                if (data.success) {
                    // Show success notification
                    showNotification(data.message, 'success');
                    
                    // Reload page to refresh data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error notification
                    showNotification(data.message, 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error deleting expense:', error);
                
                // Show error notification
                showNotification('An error occurred while deleting the expense.', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
    
    // Handle add form submission
    const addForm = document.getElementById('addExpenseForm');
    if (addForm) {
        addForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            // Submit form with AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
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
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addExpenseModal'));
                modal.hide();
                
                if (data.success) {
                    // Show success notification
                    showNotification(data.message, 'success');
                    
                    // Reload page to refresh data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error notification
                    showNotification(data.message, 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error adding expense:', error);
                
                // Show error notification
                showNotification('An error occurred while adding the expense.', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
    
    // Handle edit form submission
    const editForm = document.getElementById('editExpenseForm');
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Submit form with AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
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
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editExpenseModal'));
                modal.hide();
                
                if (data.success) {
                    // Show success notification
                    showNotification(data.message, 'success');
                    
                    // Reload page to refresh data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error notification
                    showNotification(data.message, 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error updating expense:', error);
                
                // Show error notification
                showNotification('An error occurred while updating the expense.', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.getElementById('expenseSearch');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableId = this.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        let visibleRows = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            row.style.display = isVisible ? '' : 'none';
            
            if (isVisible) {
                visibleRows++;
            }
        });
        
        // Show "no data" message if no rows visible
        const tableNoData = document.getElementById('tableNoData');
        if (tableNoData) {
            tableNoData.style.display = visibleRows > 0 ? 'none' : 'block';
        }
    });
}

/**
 * Get date range from option
 * @param {string} option - Date range option
 * @returns {Object} - Start and end dates
 */
function getDateRangeFromOption(option) {
    const now = new Date();
    const startDate = new Date();
    const endDate = new Date();
    
    switch (option) {
        case 'current-month':
            startDate.setDate(1);
            endDate.setMonth(now.getMonth() + 1, 0);
            break;
        case 'last-month':
            startDate.setMonth(now.getMonth() - 1, 1);
            endDate.setDate(0);
            break;
        case 'last-3-months':
            startDate.setMonth(now.getMonth() - 3, 1);
            endDate.setMonth(now.getMonth() + 1, 0);
            break;
        case 'last-6-months':
            startDate.setMonth(now.getMonth() - 6, 1);
            endDate.setMonth(now.getMonth() + 1, 0);
            break;
        case 'current-year':
            startDate.setMonth(0, 1);
            startDate.setHours(0, 0, 0, 0);
            endDate.setMonth(11, 31);
            endDate.setHours(23, 59, 59, 999);
            break;
        case 'quarter':
            // Current quarter
            const currentQuarter = Math.floor(now.getMonth() / 3);
            startDate.setMonth(currentQuarter * 3, 1);
            endDate.setMonth(currentQuarter * 3 + 3, 0);
            break;
        case 'all':
            startDate.setFullYear(2000, 0, 1); // Far past date
            endDate.setFullYear(now.getFullYear() + 10, 11, 31); // Far future date
            break;
        case 'custom':
            // Let user choose dates
            return { startDate: null, endDate: null };
        default:
            return { startDate: null, endDate: null };
    }
    
    return { startDate, endDate };
}

/**
 * Format date for input field
 * @param {Date} date - Date to format
 * @returns {string} - Formatted date string (YYYY-MM-DD)
 */
function formatDateForInput(date) {
    if (!date) return '';
    
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}

/**
 * Format date for display
 * @param {Date} date - Date to format
 * @returns {string} - Formatted date string (MMM D, YYYY)
 */
function formatDateForDisplay(date) {
    if (!date) return '';
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

/**
 * Escape HTML special characters
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show notification
 * @param {string} message - Message to display
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Check if notification container exists
    let container = document.getElementById('notification-container');
    
    if (!container) {
        // Create container
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.width = '300px';
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.marginBottom = '10px';
    notification.style.boxShadow = '0 0.25rem 0.75rem rgba(0, 0, 0, 0.1)';
    
    // Add content
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Initialize Bootstrap alert
    const bsAlert = new bootstrap.Alert(notification);
    
    // Auto close after duration
    setTimeout(() => {
        bsAlert.close();
    }, duration);
    
    // Remove from DOM after closing animation
    notification.addEventListener('closed.bs.alert', function() {
        notification.remove();
        
        // Remove container if empty
        if (container.children.length === 0) {
            container.remove();
        }
    });
}