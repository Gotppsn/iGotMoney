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
    
    // Initialize expense chart
    initializeExpenseChart();
    
    // Initialize expense analytics
    initializeExpenseAnalytics();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize button handlers
    initializeButtonHandlers();
    
    // Initialize recurring options toggle
    initializeRecurringToggle();
    
    // Initialize search functionality
    initializeSearch();
    
    // Clear any lingering modal backdrops
    removeModalBackdrops();
});

/**
 * Remove modal backdrops
 */
function removeModalBackdrops() {
    const modalBackdrops = document.querySelectorAll('.modal-backdrop');
    if (modalBackdrops.length) {
        modalBackdrops.forEach(backdrop => backdrop.remove());
    }
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    
    // Remove inline styles
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

/**
 * Initialize category highlighting
 */
function initializeCategoryHighlighting() {
    const categorySelect = document.getElementById('category_id');
    if (!categorySelect) return;
    
    // Category colors
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
 * Initialize expense chart
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
    
    let labels = [];
    let data = [];
    let colors = [];
    
    try {
        labels = JSON.parse(labelsStr);
        data = JSON.parse(dataStr);
        colors = JSON.parse(colorsStr);
    } catch (e) {
        console.error('Error parsing chart data:', e);
    }
    
    // Show no data message if no data
    if (labels.length === 0 || data.length === 0) {
        const chartNoData = document.getElementById('chartNoData');
        if (chartNoData) {
            chartNoData.style.display = 'block';
        }
        return;
    }
    
    // Initialize the chart
    try {
        window.expenseCategoryChart = new Chart(chartCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    hoverBackgroundColor: colors,
                    hoverBorderColor: 'rgba(234, 236, 244, 1)',
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
            }
        });
    } catch (e) {
        console.error('Error creating chart:', e);
    }
}

/**
 * Initialize expense analytics
 */
function initializeExpenseAnalytics() {
    const calculateButton = document.getElementById('calculateAnalytics');
    if (!calculateButton) return;
    
    calculateButton.addEventListener('click', function() {
        const analyticsContent = document.getElementById('analyticsContent');
        if (!analyticsContent) return;
        
        analyticsContent.style.display = 'flex';
        
        // Get all visible expense rows
        const expenseTable = document.getElementById('expenseTable');
        if (!expenseTable) return;
        
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
            try {
                const amount = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('$', '').replace(/,/g, '')) || 0;
                const date = new Date(row.querySelector('td:nth-child(4)').textContent);
                const category = row.querySelector('td:nth-child(2)').textContent;
                const frequency = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                const isRecurring = row.querySelector('td:nth-child(6)').textContent.includes('Yes');
                
                return { amount, date, category, frequency, isRecurring };
            } catch (e) {
                console.error('Error extracting expense data:', e);
                return null;
            }
        }).filter(expense => expense !== null);
        
        // Calculate analytics
        calculateExpenseAnalytics(expenses);
    });
}

/**
 * Calculate expense analytics
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
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
    
    // Reset form when modal is closed
    const addModal = document.getElementById('addExpenseModal');
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addExpenseForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }
            
            const recurringOptions = document.getElementById('recurring_options');
            if (recurringOptions) {
                recurringOptions.style.display = 'none';
            }
            
            // Remove any lingering backdrops
            removeModalBackdrops();
        });
    }
    
    const editModal = document.getElementById('editExpenseModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('editExpenseForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }
            
            const editRecurringOptions = document.getElementById('edit_recurring_options');
            if (editRecurringOptions) {
                editRecurringOptions.style.display = 'none';
            }
            
            // Remove any lingering backdrops
            removeModalBackdrops();
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
        });
    }
}

/**
 * Initialize button handlers
 */
function initializeButtonHandlers() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Handle edit expense button clicks
    document.querySelectorAll('.edit-expense').forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            document.getElementById('edit_expense_id').value = expenseId;
            
            try {
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                editModal.show();
                
                // Load expense data
                fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate form fields
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
                            alert('Failed to load expense data');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching expense data:', error);
                        alert('Error loading expense data');
                    });
            } catch (e) {
                console.error('Error showing modal:', e);
            }
        });
    });

    // Handle delete expense button clicks
    document.querySelectorAll('.delete-expense').forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.getAttribute('data-expense-id');
            document.getElementById('delete_expense_id').value = expenseId;
            
            try {
                // Show modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
                deleteModal.show();
            } catch (e) {
                console.error('Error showing delete modal:', e);
            }
        });
    });
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