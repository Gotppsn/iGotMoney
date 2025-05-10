document.addEventListener('DOMContentLoaded', function() {
    console.log('Expenses.js: DOM fully loaded');
    
    // Prevent multiple initializations
    if (window.expensesInitialized) {
        console.log('Expenses.js: Already initialized, skipping...');
        return;
    }
    
    window.expensesInitialized = true;
    
    try {
        initializeExpenseForms();
        initializeTableFilters();
        initializeExpenseActions();
        initializeChartInteractions();
        initializeAnalytics();
        initializeChart();
        animateElements();
        
        console.log('Expenses.js: All components initialized successfully');
    } catch (error) {
        console.error('Expenses.js: Error during initialization:', error);
    }
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const chartCanvas = document.getElementById('expenseCategoryChart');
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }
    
    // Check if chart already exists and destroy it
    const existingChart = Chart.getChart(chartCanvas);
    if (existingChart) {
        console.log('Destroying existing chart instance');
        existingChart.destroy();
    }
    
    // Clean up any global reference
    if (window.expenseCategoryChart) {
        if (typeof window.expenseCategoryChart.destroy === 'function') {
            window.expenseCategoryChart.destroy();
        }
        window.expenseCategoryChart = null;
    }
    
    // Set global Chart.js defaults
    Chart.defaults.font.family = '"Inter", "Roboto", "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#718096';
    Chart.defaults.responsive = true;
    
    try {
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            const chartNoData = document.getElementById('chartNoData');
            if (chartNoData) {
                chartNoData.style.display = 'block';
            }
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        
        console.log('Chart data loaded:', { chartLabels, chartData, chartColors });
        
        if (chartLabels.length > 0 && chartData.length > 0) {
            const ctx = chartCanvas.getContext('2d');
            
            // Create new chart instance
            window.expenseCategoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        data: chartData,
                        backgroundColor: chartColors,
                        borderWidth: 0,
                        hoverOffset: 10,
                        borderRadius: 3
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return ` ${label}: $${value.toFixed(2)} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
            
            console.log('Chart initialized successfully');
            
            const chartNoData = document.getElementById('chartNoData');
            if (chartNoData) {
                chartNoData.style.display = 'none';
            }
        } else {
            console.log('No chart data available');
            const chartNoData = document.getElementById('chartNoData');
            if (chartNoData) {
                chartNoData.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error initializing chart:', error);
        const chartNoData = document.getElementById('chartNoData');
        if (chartNoData) {
            chartNoData.style.display = 'block';
        }
    }
}

function initializeExpenseForms() {
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
    
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurring_options');
    
    if (isRecurringCheckbox && recurringOptions) {
        isRecurringCheckbox.addEventListener('change', function() {
            recurringOptions.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                const frequencySelect = document.getElementById('frequency');
                if (frequencySelect) {
                    frequencySelect.value = 'monthly';
                }
            }
        });
    }
    
    const editIsRecurringCheckbox = document.getElementById('edit_is_recurring');
    const editRecurringOptions = document.getElementById('edit_recurring_options');
    
    if (editIsRecurringCheckbox && editRecurringOptions) {
        editIsRecurringCheckbox.addEventListener('change', function() {
            editRecurringOptions.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                const editFrequencySelect = document.getElementById('edit_frequency');
                if (editFrequencySelect) {
                    editFrequencySelect.value = 'monthly';
                }
            }
        });
    }
}

function initializeTableFilters() {
    const expenseSearch = document.getElementById('expenseSearch');
    if (expenseSearch) {
        expenseSearch.addEventListener('keyup', function() {
            const tableId = this.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            
            if (table) {
                const searchText = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                let visibleRows = 0;
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchText)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const tableContainer = table.closest('.table-responsive');
                const tableNoData = document.getElementById('tableNoData');
                
                if (tableContainer && tableNoData) {
                    if (rows.length === 0) {
                        tableContainer.style.display = 'none';
                        tableNoData.style.display = 'block';
                    } else if (visibleRows === 0) {
                        tableContainer.style.display = 'block';
                        tableNoData.style.display = 'none';
                        
                        let noResultsRow = table.querySelector('.no-results');
                        if (!noResultsRow) {
                            const tbody = table.querySelector('tbody');
                            noResultsRow = document.createElement('tr');
                            noResultsRow.className = 'no-results';
                            noResultsRow.innerHTML = '<td colspan="7" class="text-center py-4">No matching expenses found</td>';
                            tbody.appendChild(noResultsRow);
                        }
                    } else {
                        tableContainer.style.display = 'block';
                        tableNoData.style.display = 'none';
                        
                        const noResultsRow = table.querySelector('.no-results');
                        if (noResultsRow) {
                            noResultsRow.remove();
                        }
                    }
                }
            }
        });
    }
    
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
            
            switch(selectedRange) {
                case 'all':
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
                    const startInput = document.getElementById('startDate');
                    const endInput = document.getElementById('endDate');
                    
                    if (startInput.value && endInput.value) {
                        startDate = new Date(startInput.value);
                        endDate = new Date(endInput.value);
                    } else {
                        showNotification('Please select both start and end dates for custom range.', 'warning');
                        return;
                    }
                    break;
                default:
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            }
            
            const formatDate = date => {
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            window.location.href = `${basePath}/expenses?start_date=${formatDate(startDate)}&end_date=${formatDate(endDate)}`;
        });
    }
}

function initializeExpenseActions() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-expense')) {
            e.preventDefault();
            const button = e.target.closest('.edit-expense');
            const expenseId = button.getAttribute('data-expense-id');
            if (expenseId) {
                fetchExpenseDetails(expenseId);
            }
        }
        
        if (e.target.closest('.delete-expense')) {
            e.preventDefault();
            const button = e.target.closest('.delete-expense');
            const expenseId = button.getAttribute('data-expense-id');
            if (expenseId) {
                document.getElementById('delete_expense_id').value = expenseId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
                deleteModal.show();
            }
        }
    });
}

function fetchExpenseDetails(expenseId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_expense_id').value = data.expense.expense_id;
                document.getElementById('edit_category_id').value = data.expense.category_id;
                document.getElementById('edit_description').value = data.expense.description;
                document.getElementById('edit_amount').value = data.expense.amount;
                document.getElementById('edit_expense_date').value = data.expense.expense_date;
                
                const isRecurring = data.expense.is_recurring === '1' || data.expense.is_recurring === 1;
                document.getElementById('edit_is_recurring').checked = isRecurring;
                document.getElementById('edit_recurring_options').style.display = isRecurring ? 'block' : 'none';
                
                if (isRecurring) {
                    document.getElementById('edit_frequency').value = data.expense.frequency;
                }
                
                const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                editModal.show();
            } else {
                showNotification('Failed to fetch expense details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while fetching expense details', 'danger');
        });
}

function initializeChartInteractions() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.chart-period')) {
            e.preventDefault();
            const option = e.target.closest('.chart-period');
            
            const dropdownButton = document.getElementById('chartPeriodDropdown');
            if (dropdownButton) {
                dropdownButton.innerHTML = '<i class="fas fa-calendar-alt me-1"></i> ' + option.textContent.trim();
            }
            
            document.querySelectorAll('.chart-period').forEach(opt => opt.classList.remove('active'));
            
            option.classList.add('active');
            
            updateChartForPeriod(option.getAttribute('data-period'));
        }
    });
}

function updateChartForPeriod(period) {
    const chartContainer = document.querySelector('.chart-container');
    const chartNoData = document.getElementById('chartNoData');
    
    if (!chartContainer) return;
    
    chartContainer.style.opacity = 0.5;
    const spinner = document.createElement('div');
    spinner.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
    spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    chartContainer.appendChild(spinner);
    
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
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
            startDate = new Date(2000, 0, 1);
            endDate = new Date(now.getFullYear() + 10, 11, 31);
            break;
        default:
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    }
    
    const formatDate = date => {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    fetch(`${basePath}/expenses?action=get_expenses_by_date&start_date=${formatDate(startDate)}&end_date=${formatDate(endDate)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            spinner.remove();
            chartContainer.style.opacity = 1;
            
            if (data.success) {
                if (data.expenses.length > 0) {
                    const categoryTotals = {};
                    
                    data.expenses.forEach(expense => {
                        const category = expense.category_name;
                        if (!categoryTotals[category]) {
                            categoryTotals[category] = 0;
                        }
                        categoryTotals[category] += parseFloat(expense.amount);
                    });
                    
                    const sortedCategories = Object.entries(categoryTotals)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 10);
                    
                    const labels = sortedCategories.map(item => item[0]);
                    const values = sortedCategories.map(item => item[1]);
                    
                    if (window.expenseCategoryChart && typeof window.expenseCategoryChart.update === 'function') {
                        window.expenseCategoryChart.data.labels = labels;
                        window.expenseCategoryChart.data.datasets[0].data = values;
                        window.expenseCategoryChart.update();
                        
                        chartContainer.style.display = 'block';
                        if (chartNoData) {
                            chartNoData.style.display = 'none';
                        }
                    }
                    
                    updateTopExpensesList(sortedCategories, data.total);
                } else {
                    chartContainer.style.display = 'none';
                    if (chartNoData) {
                        chartNoData.style.display = 'block';
                    }
                }
            } else {
                console.error('Error fetching data for chart:', data.message);
                chartContainer.style.display = 'none';
                if (chartNoData) {
                    chartNoData.style.display = 'block';
                }
            }
        })
        .catch(error => {
            spinner.remove();
            chartContainer.style.opacity = 1;
            
            console.error('Error:', error);
            chartContainer.style.display = 'none';
            if (chartNoData) {
                chartNoData.style.display = 'block';
            }
        });
}

function updateTopExpensesList(categories, totalAmount) {
    const topExpensesContainer = document.querySelector('.chart-card:nth-child(2) .card-body');
    
    if (topExpensesContainer && categories.length > 0) {
        let html = '';
        
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
        
        topExpensesContainer.style.opacity = 0;
        setTimeout(() => {
            topExpensesContainer.innerHTML = html;
            topExpensesContainer.style.opacity = 1;
        }, 300);
    }
}

function initializeAnalytics() {
    const calculateAnalyticsBtn = document.getElementById('calculateAnalytics');
    
    if (calculateAnalyticsBtn) {
        calculateAnalyticsBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Calculating...';
            
            const analyticsPlaceholder = document.getElementById('analyticsPlaceholder');
            if (analyticsPlaceholder) {
                analyticsPlaceholder.style.display = 'none';
            }
            
            fetchAnalyticsData();
        });
    }
}

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
                calculateAnalyticsBtn.disabled = false;
                calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Recalculate';
                
                displayAnalytics(data.analytics);
                
                analyticsContent.style.opacity = 0;
                analyticsContent.style.display = 'flex';
                setTimeout(() => {
                    analyticsContent.style.opacity = 1;
                }, 50);
                
                showNotification('Analytics calculated successfully', 'success');
            } else {
                console.error('Error fetching analytics:', data.message);
                showNotification('Failed to fetch analytics', 'danger');
                
                calculateAnalyticsBtn.disabled = false;
                calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Calculate';
                
                const analyticsPlaceholder = document.getElementById('analyticsPlaceholder');
                if (analyticsPlaceholder) {
                    analyticsPlaceholder.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
            
            calculateAnalyticsBtn.disabled = false;
            calculateAnalyticsBtn.innerHTML = '<i class="fas fa-calculator me-1"></i> Calculate';
            
            const analyticsPlaceholder = document.getElementById('analyticsPlaceholder');
            if (analyticsPlaceholder) {
                analyticsPlaceholder.style.display = 'block';
            }
        });
}

function displayAnalytics(analytics) {
    const analyticsContent = document.getElementById('analyticsContent');
    
    const startDate = new Date(analytics.start_date);
    const endDate = new Date(analytics.end_date);
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    const formattedStartDate = startDate.toLocaleDateString('en-US', dateOptions);
    const formattedEndDate = endDate.toLocaleDateString('en-US', dateOptions);
    
    let html = `
    <div class="col-12 mb-3">
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Showing analytics for: <strong>${formattedStartDate}</strong> to <strong>${formattedEndDate}</strong>
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
    
    if (Object.keys(analytics.category_totals).length > 0) {
        html += `
        <div class="col-12 mt-2">
            <h6 class="mb-3">Category Breakdown</h6>
            <div class="row">`;
        
        const categories = Object.entries(analytics.category_totals);
        categories.sort((a, b) => b[1] - a[1]);
        
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
    
    analyticsContent.innerHTML = html;
    
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

function animateElements() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const width = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = width;
        }, 100 + (index * 50));
    });
    
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

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.expense-notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
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
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(50px)';
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}