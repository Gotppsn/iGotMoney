/**
 * iGotMoney - Expenses JavaScript
 * Handles functionality for the expense management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize category highlighting
    initializeCategoryHighlighting();
    
    // Initialize date range filtering
    initializeDateRangeFilter();
    
    // Initialize expense analytics
    initializeExpenseAnalytics();
    
    // Initialize form validation
    initializeFormValidation();
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
    if (expenseTable) {
        const rows = expenseTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const categoryCell = row.querySelector('td:nth-child(2)');
            if (categoryCell) {
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
            }
        });
    }
}

/**
 * Initialize date range filter
 * Allows filtering expenses by date range
 */
function initializeDateRangeFilter() {
    // Create filter elements
    const cardHeader = document.querySelector('.card-header .input-group');
    if (!cardHeader) return;
    
    const filterContainer = document.createElement('div');
    filterContainer.className = 'date-filter-container ms-3';
    filterContainer.innerHTML = `
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-calendar me-1"></i> Date Filter
            </button>
            <div class="dropdown-menu p-3" style="width: 250px;">
                <div class="mb-2">
                    <label class="form-label">Date Range</label>
                    <select class="form-select form-select-sm" id="dateRangeSelect">
                        <option value="all">All Time</option>
                        <option value="current-month" selected>Current Month</option>
                        <option value="last-month">Last Month</option>
                        <option value="last-3-months">Last 3 Months</option>
                        <option value="last-6-months">Last 6 Months</option>
                        <option value="current-year">Current Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div id="customDateRange" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="startDate">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="endDate">
                    </div>
                </div>
                <div class="d-grid">
                    <button class="btn btn-sm btn-primary" id="applyDateFilter">Apply</button>
                </div>
            </div>
        </div>
    `;
    
    cardHeader.insertAdjacentElement('afterend', filterContainer);
    
    // Handle date range selection
    const dateRangeSelect = document.getElementById('dateRangeSelect');
    const customDateRange = document.getElementById('customDateRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const applyButton = document.getElementById('applyDateFilter');
    
    if (dateRangeSelect) {
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
    }
    
    // Apply date filter
    if (applyButton) {
        applyButton.addEventListener('click', function() {
            const table = document.getElementById('expenseTable');
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
            const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
            
            if (startDate) startDate.setHours(0, 0, 0, 0);
            if (endDate) endDate.setHours(23, 59, 59, 999);
            
            rows.forEach(row => {
                const dateCell = row.querySelector('td:nth-child(4)');
                if (dateCell) {
                    const expenseDate = new Date(dateCell.textContent);
                    
                    // Apply filter
                    if (
                        (startDate && expenseDate < startDate) || 
                        (endDate && expenseDate > endDate)
                    ) {
                        row.style.display = 'none';
                    } else {
                        row.style.display = '';
                    }
                }
            });
            
            // Close dropdown
            document.body.click();
        });
    }
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
            endDate.setMonth(11, 31);
            break;
        case 'all':
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
    return date.toISOString().split('T')[0];
}

/**
 * Initialize expense analytics
 * Calculates and displays expense analytics
 */
function initializeExpenseAnalytics() {
    const expenseTable = document.getElementById('expenseTable');
    if (!expenseTable) return;
    
    // Create analytics container
    const cardBody = document.querySelector('.card-body');
    if (!cardBody) return;
    
    const analyticsContainer = document.createElement('div');
    analyticsContainer.className = 'expense-analytics mt-4';
    analyticsContainer.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 font-weight-bold">Expense Analytics</h6>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="calculateAnalytics">
                    <i class="fas fa-calculator me-1"></i> Calculate
                </button>
            </div>
        </div>
        <div class="row" id="analyticsContent" style="display: none;">
            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Average Daily Expense</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgDailyExpense">$0.00</div>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="highestExpense">$0.00</div>
                                <div class="small" id="highestExpenseCategory"></div>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="projectedMonthly">$0.00</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add analytics container after table
    cardBody.appendChild(analyticsContainer);
    
    // Calculate analytics button
    const calculateButton = document.getElementById('calculateAnalytics');
    if (calculateButton) {
        calculateButton.addEventListener('click', function() {
            const analyticsContent = document.getElementById('analyticsContent');
            analyticsContent.style.display = 'flex';
            
            // Get all visible expense rows
            const rows = Array.from(expenseTable.querySelectorAll('tbody tr'))
                .filter(row => row.style.display !== 'none');
            
            if (rows.length === 0) {
                document.getElementById('avgDailyExpense').textContent = '$0.00';
                document.getElementById('highestExpense').textContent = '$0.00';
                document.getElementById('highestExpenseCategory').textContent = '';
                document.getElementById('projectedMonthly').textContent = '$0.00';
                return;
            }
            
            // Extract expense data
            const expenses = rows.map(row => {
                const amount = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace(', ', '').replace(',', ''));
                const date = new Date(row.querySelector('td:nth-child(4)').textContent);
                const category = row.querySelector('td:nth-child(2)').textContent;
                const frequency = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                const isRecurring = row.querySelector('td:nth-child(6)').textContent.includes('Yes');
                
                return { amount, date, category, frequency, isRecurring };
            });
            
            // Calculate analytics
            calculateExpenseAnalytics(expenses);
        });
    }
}

/**
 * Calculate expense analytics
 * @param {Array} expenses - Array of expense objects
 */
function calculateExpenseAnalytics(expenses) {
    // Calculate total expense amount
    const totalAmount = expenses.reduce((sum, expense) => sum + expense.amount, 0);
    
    // Find date range
    const dates = expenses.map(expense => expense.date);
    const minDate = new Date(Math.min.apply(null, dates));
    const maxDate = new Date(Math.max.apply(null, dates));
    
    // Calculate days in range
    const daysDiff = Math.max(1, Math.round((maxDate - minDate) / (1000 * 60 * 60 * 24)) + 1);
    
    // Calculate average daily expense
    const avgDaily = totalAmount / daysDiff;
    
    // Find highest expense
    const highestExpense = expenses.reduce((max, expense) => 
        expense.amount > max.amount ? expense : max, expenses[0]);
    
    // Calculate projected monthly total
    const daysInMonth = 30;
    const projectedMonthly = avgDaily * daysInMonth;
    
    // Update analytics display
    document.getElementById('avgDailyExpense').textContent = '$' + avgDaily.toFixed(2);
    document.getElementById('highestExpense').textContent = '$' + highestExpense.amount.toFixed(2);
    document.getElementById('highestExpenseCategory').textContent = highestExpense.category;
    document.getElementById('projectedMonthly').textContent = '$' + projectedMonthly.toFixed(2);
}

/**
 * Initialize form validation
 * Validates expense forms before submission
 */
function initializeFormValidation() {
    // Add form validation
    const forms = document.querySelectorAll('#addExpenseModal form, #editExpenseModal form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateExpenseForm(this)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
}

/**
 * Validate expense form
 * @param {HTMLFormElement} form - Form to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateExpenseForm(form) {
    let isValid = true;
    
    // Get form fields
    const categoryId = form.querySelector('[name="category_id"]');
    const description = form.querySelector('[name="description"]');
    const amount = form.querySelector('[name="amount"]');
    const expenseDate = form.querySelector('[name="expense_date"]');
    const isRecurring = form.querySelector('[name="is_recurring"]');
    const frequency = form.querySelector('[name="frequency"]');
    
    // Reset previous errors
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    // Validate category
    if (!categoryId.value) {
        categoryId.classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate description
    if (!description.value.trim()) {
        description.classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate amount
    if (!amount.value || parseFloat(amount.value) <= 0) {
        amount.classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate date
    if (!expenseDate.value) {
        expenseDate.classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate frequency if recurring
    if (isRecurring.checked && (!frequency.value || frequency.value === 'one-time')) {
        frequency.classList.add('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Show notification
 * Displays a temporary notification message
 * @param {string} message - The message to display
 * @param {string} type - The notification type (success, info, warning, danger)
 * @param {number} duration - The duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '300px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Remove after duration
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300); // Wait for fade out
    }, duration);
}