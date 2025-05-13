document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Expenses JS loaded');
    
    initializeChart();
    initializeEventListeners();
    initializeFormValidation();
    initializeAnimations();
    initializeSearch();
    initializeQuickFilters();
    initializeInsights();
    initializeTooltips();
    initializeTopCategories(); // New function call
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const chartCanvas = document.getElementById('categoryChart');
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }

    try {
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage();
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            showNoDataMessage();
            return;
        }

        const ctx = chartCanvas.getContext('2d');
        window.categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        padding: 30,
                        labels: {
                            boxWidth: 16,
                            boxHeight: 16,
                            padding: 15,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    },
                    doughnutLabel: {
                        labels: [
                            {
                                text: 'Total',
                                font: {
                                    size: '16',
                                    weight: 'bold'
                                }
                            },
                            {
                                text: function() {
                                    const total = chartData.reduce((acc, val) => acc + val, 0);
                                    return currencySymbol + total.toLocaleString();
                                },
                                font: {
                                    size: '24',
                                    weight: 'bold'
                                }
                            }
                        ]
                    }
                }
            },
            plugins: [{
                id: 'doughnutLabel',
                beforeDraw: function(chart) {
                    if (chart.config.options.plugins.doughnutLabel) {
                        // Get ctx from chart
                        const ctx = chart.ctx;
                        
                        // Get options from the center object in options
                        const labels = chart.config.options.plugins.doughnutLabel.labels;
                        if (!labels) return;
                        
                        // Calculate total
                        const total = chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                        
                        // Get width and height
                        const width = chart.chartArea.right - chart.chartArea.left;
                        const height = chart.chartArea.bottom - chart.chartArea.top;
                        
                        // Set the center
                        const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                        const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                        
                        // Draw labels
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        
                        let textY = centerY - 10;
                        for (let i = 0; i < labels.length; i++) {
                            const label = labels[i];
                            const fontSize = label.font.size;
                            const fontWeight = label.font.weight;
                            const text = typeof label.text === 'function' ? label.text() : label.text;
                            
                            ctx.font = `${fontWeight} ${fontSize}px Inter, sans-serif`;
                            ctx.fillStyle = '#0f172a'; // Dark text color
                            
                            // Offset for multiple labels
                            const labelTextY = textY + (i * 24);
                            ctx.fillText(text, centerX, labelTextY);
                        }
                    }
                }
            }]
        });

        const noDataMessage = document.getElementById('chartNoData');
        if (noDataMessage) {
            noDataMessage.style.display = 'none';
        }

    } catch (error) {
        console.error('Error initializing chart:', error);
        showNoDataMessage();
    }
}

function initializeTopCategories() {
    // Get data from the chart
    const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
    const chartDataEl = document.querySelector('meta[name="chart-data"]');
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    
    if (!chartLabelsEl || !chartDataEl || !currencySymbolEl) {
        console.error('Chart data meta tags not found for top categories!');
        return;
    }
    
    try {
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            console.warn('No category data available for top categories');
            return;
        }
        
        // Create an array of [label, data] pairs
        const categories = chartLabels.map((label, i) => [label, chartData[i]]);
        
        // Sort by amount (descending)
        categories.sort((a, b) => b[1] - a[1]);
        
        // Get total for percentage calculations
        const totalAmount = chartData.reduce((acc, val) => acc + val, 0);
        
        // Get the top categories container
        const topCategoriesContainer = document.querySelector('.top-categories-list');
        if (!topCategoriesContainer) {
            console.error('Top categories container not found!');
            return;
        }
        
        // Generate HTML for top categories
        let html = '';
        categories.slice(0, 5).forEach((category, index) => {
            const [name, amount] = category;
            const percentage = ((amount / totalAmount) * 100).toFixed(1);
            
            html += `
                <div class="top-category-item">
                    <div class="top-category-rank">${index + 1}</div>
                    <div class="top-category-info">
                        <h4 class="top-category-name">${name}</h4>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${percentage}%" 
                                 aria-valuenow="${percentage}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="top-category-amount">
                        <span class="amount">${currencySymbol}${amount.toLocaleString()}</span>
                        <span class="percentage">${percentage}%</span>
                    </div>
                </div>
            `;
        });
        
        topCategoriesContainer.innerHTML = html;
        
    } catch (error) {
        console.error('Error initializing top categories:', error);
    }
}

function initializeInsights() {
    const insightsContainer = document.getElementById('expenseInsights');
    if (!insightsContainer) return;

    const chartDataEl = document.querySelector('meta[name="chart-data"]');
    if (!chartDataEl) return;

    const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
    const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
    const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
    
    if (chartData.length === 0) return;
    
    // Analyze expense data
    const totalExpenses = chartData.reduce((acc, val) => acc + val, 0);
    const maxExpense = Math.max(...chartData);
    const maxExpenseIndex = chartData.indexOf(maxExpense);
    const maxCategory = chartLabels[maxExpenseIndex] || 'Unknown';
    const maxPercentage = ((maxExpense / totalExpenses) * 100).toFixed(1);
    
    // Find patterns
    const insights = [];
    
    // 1. Highest spending category
    insights.push({
        icon: 'fa-arrow-trend-up',
        title: `Top spending category: ${maxCategory}`,
        description: `${maxPercentage}% of your expenses are in ${maxCategory}`,
        color: 'primary'
    });
    
    // 2. Provide a budgeting tip - simulate this with fixed data
    insights.push({
        icon: 'fa-lightbulb',
        title: 'Budgeting tip',
        description: 'Consider setting category budgets to better control spending',
        color: 'warning'
    });
    
    // 3. Spending trend (simulate with fixed data since we don't have historical data)
    const trendUp = Math.random() > 0.5;
    insights.push({
        icon: trendUp ? 'fa-chart-line' : 'fa-chart-line-down',
        title: `${trendUp ? 'Increasing' : 'Decreasing'} trend`,
        description: `Your expenses are ${trendUp ? 'higher' : 'lower'} compared to last month`,
        color: trendUp ? 'danger' : 'success'
    });
    
    // Generate the HTML for the insights
    let html = '';
    insights.forEach(insight => {
        html += `
            <div class="insight-card ${insight.color}">
                <div class="insight-icon">
                    <i class="fas ${insight.icon}"></i>
                </div>
                <div class="insight-content">
                    <h4>${insight.title}</h4>
                    <p>${insight.description}</p>
                </div>
                <div class="insight-action">
                    <button class="insight-btn">View</button>
                </div>
            </div>
        `;
    });
    
    insightsContainer.innerHTML = html;
    
    // Initialize click listeners
    document.querySelectorAll('.insight-btn').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.insight-card');
            const title = card.querySelector('h4').textContent;
            showNotification(`Insight: ${title}`, 'info');
            
            // You could take different actions based on the insight type
            if (title.includes('Top spending')) {
                // Filter to this category
                const categoryName = title.split(': ')[1];
                const categorySelect = document.getElementById('categorySelect');
                if (categorySelect) {
                    // Find the option with this text
                    const options = Array.from(categorySelect.options);
                    const option = options.find(opt => opt.text.includes(categoryName));
                    if (option) {
                        categorySelect.value = option.value;
                        document.getElementById('applyFilter').click();
                    }
                }
            } else if (title.includes('Budgeting tip')) {
                // Open an info modal with budgeting tips
                const modalTitle = 'Budgeting Tips';
                const modalContent = `
                    <p>Here are some tips to better manage your expenses:</p>
                    <ul>
                        <li>Set specific budget limits for each category</li>
                        <li>Track your expenses regularly</li>
                        <li>Use the 50/30/20 rule: 50% needs, 30% wants, 20% savings</li>
                        <li>Identify and cut unnecessary expenses</li>
                        <li>Plan for irregular expenses</li>
                    </ul>
                `;
                showInfoModal(modalTitle, modalContent);
            }
        });
    });
}

function showInfoModal(title, content) {
    // Create modal dynamically
    const modalId = 'infoModal';
    let modal = document.getElementById(modalId);
    
    if (!modal) {
        // Create new modal if it doesn't exist
        const modalHTML = `
            <div class="modal fade modern-modal" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="modal-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Append to body
        const div = document.createElement('div');
        div.innerHTML = modalHTML;
        document.body.appendChild(div.firstChild);
        
        modal = document.getElementById(modalId);
    } else {
        // Update existing modal
        modal.querySelector('.modal-title').textContent = title;
        modal.querySelector('.modal-body').innerHTML = content;
    }
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

function initializeQuickFilters() {
    const container = document.getElementById('quickFilters');
    if (!container) return;
    
    // Get current date info
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();
    
    // Define quick filters
    const filters = [
        {
            label: 'This Month',
            icon: 'fa-calendar-day',
            filter: { month: currentMonth, year: currentYear }
        },
        {
            label: 'Last Month',
            icon: 'fa-calendar-week',
            filter: { 
                month: currentMonth === 1 ? 12 : currentMonth - 1, 
                year: currentMonth === 1 ? currentYear - 1 : currentYear 
            }
        },
        {
            label: 'This Year',
            icon: 'fa-calendar',
            filter: { year: currentYear }
        },
        {
            label: 'All Time',
            icon: 'fa-infinity',
            filter: { }
        }
    ];
    
    // Create chips
    let html = '';
    filters.forEach(filter => {
        html += `
            <div class="filter-chip" data-month="${filter.filter.month || 0}" data-year="${filter.filter.year || 0}" data-category="0">
                <i class="fas ${filter.icon}"></i>
                <span>${filter.label}</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Add event listeners
    container.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const month = this.dataset.month;
            const year = this.dataset.year;
            const category = this.dataset.category;
            
            // Set form values
            const monthSelect = document.getElementById('monthSelect');
            const yearSelect = document.getElementById('yearSelect');
            const categorySelect = document.getElementById('categorySelect');
            
            if (monthSelect) monthSelect.value = month;
            if (yearSelect) yearSelect.value = year;
            if (categorySelect) categorySelect.value = category;
            
            // Submit form
            document.getElementById('applyFilter').click();
            
            // Highlight the active filter
            container.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Set active filter based on current URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const urlMonth = urlParams.get('month');
    const urlYear = urlParams.get('year');
    
    if (urlMonth || urlYear) {
        // Find matching filter
        container.querySelectorAll('.filter-chip').forEach(chip => {
            const chipMonth = chip.dataset.month;
            const chipYear = chip.dataset.year;
            
            if ((urlMonth && chipMonth === urlMonth && (!urlYear || chipYear === urlYear)) || 
                (urlYear && chipYear === urlYear && (!urlMonth || chipMonth === urlMonth))) {
                chip.classList.add('active');
            }
        });
    } else {
        // Default to "This Month"
        container.querySelector('.filter-chip').classList.add('active');
    }
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function showNoDataMessage() {
    const chartContainer = document.querySelector('.chart-container');
    const noDataMessage = document.getElementById('chartNoData');
    
    if (chartContainer) {
        chartContainer.style.display = 'none';
    }
    
    if (noDataMessage) {
        noDataMessage.style.display = 'block';
    }
}

function initializeEventListeners() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const expenseId = button.getAttribute('data-expense-id');
            if (expenseId) {
                loadExpenseForEdit(expenseId);
            }
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const expenseId = button.getAttribute('data-expense-id');
            if (expenseId) {
                document.getElementById('delete_expense_id').value = expenseId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
                deleteModal.show();
            }
        }
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.clone')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.clone');
            const expenseId = button.getAttribute('data-expense-id');
            if (expenseId) {
                loadExpenseForClone(expenseId);
            }
        }
    });

    const chartPeriodSelect = document.getElementById('chartPeriodSelect');
    if (chartPeriodSelect) {
        chartPeriodSelect.addEventListener('change', function() {
            updateChartData(this.value);
        });
    }

    // Handle recurring expense checkbox for ADD form
    const recurringCheckbox = document.getElementById('is_recurring');
    if (recurringCheckbox) {
        recurringCheckbox.addEventListener('change', function() {
            const frequencyField = document.getElementById('frequency');
            const frequencyGroup = document.getElementById('frequency_group');
            if (frequencyField) {
                frequencyField.disabled = !this.checked;
                if (frequencyGroup) {
                    frequencyGroup.style.opacity = this.checked ? '1' : '0.5';
                }
                if (!this.checked) {
                    frequencyField.value = 'one-time';
                } else {
                    frequencyField.value = 'monthly'; // Default to monthly for recurring
                }
            }
        });
    }
    
    // Handle recurring expense checkbox for EDIT form
    const recurringEditCheckbox = document.getElementById('edit_is_recurring');
    if (recurringEditCheckbox) {
        recurringEditCheckbox.addEventListener('change', function() {
            const frequencyField = document.getElementById('edit_frequency');
            const frequencyGroup = document.getElementById('edit_frequency_group');
            if (frequencyField) {
                frequencyField.disabled = !this.checked;
                if (frequencyGroup) {
                    frequencyGroup.style.opacity = this.checked ? '1' : '0.5';
                }
                if (!this.checked) {
                    frequencyField.value = 'one-time';
                } else if (frequencyField.value === 'one-time') {
                    frequencyField.value = 'monthly'; // Default to monthly for recurring
                }
            }
        });
    }

    const resetFilter = document.getElementById('resetFilter');
    if (resetFilter) {
        resetFilter.addEventListener('click', function(e) {
            e.preventDefault();
            const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
            window.location.href = `${basePath}/expenses`;
        });
    }
    
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(filterForm);
            const month = formData.get('month');
            const year = formData.get('year');
            const category = formData.get('category');
            
            const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
            const params = new URLSearchParams();
            
            if (month && month !== '0') params.append('month', month);
            if (year && year !== '0') params.append('year', year);
            if (category && category !== '0') params.append('category', category);
            
            const queryString = params.toString();
            const url = queryString ? `${basePath}/expenses?${queryString}` : `${basePath}/expenses`;
            
            window.location.href = url;
        });
    }
    
    const filterMenu = document.querySelector('.filter-menu');
    if (filterMenu) {
        filterMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Add event listeners for bulk actions
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.expense-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionVisibility();
        });
    }
    
    // Add listeners for individual checkboxes
    const checkboxes = document.querySelectorAll('.expense-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionVisibility);
    });
    
    // Bulk delete button
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedIds = getSelectedExpenseIds();
            if (selectedIds.length > 0) {
                const confirmMessage = `Are you sure you want to delete ${selectedIds.length} expense(s)?`;
                if (confirm(confirmMessage)) {
                    // In a real implementation, we would submit these IDs to the server
                    showNotification(`${selectedIds.length} expenses selected for deletion.`, 'info');
                    // You would normally submit a form or make an AJAX request here
                }
            }
        });
    }
    
    // Category filter buttons
    document.querySelectorAll('.category-filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categorySelect = document.getElementById('categorySelect');
            if (categorySelect) {
                categorySelect.value = categoryId;
                document.getElementById('applyFilter').click();
            }
        });
    });
    
    // Initialize amount range slider
    const amountRange = document.getElementById('amountRange');
    const amountValue = document.getElementById('amountValue');
    if (amountRange && amountValue) {
        amountRange.addEventListener('input', function() {
            const value = this.value;
            const currencySymbol = document.querySelector('meta[name="currency-symbol"]').getAttribute('content') || '$';
            amountValue.textContent = `${currencySymbol}${value}`;
        });
    }
}

function loadExpenseForEdit(expenseId) {
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
            
            const frequencyField = document.getElementById('edit_frequency');
            const frequencyGroup = document.getElementById('edit_frequency_group');
            if (frequencyField) {
                frequencyField.value = data.expense.frequency || 'one-time';
                frequencyField.disabled = !isRecurring;
                if (frequencyGroup) {
                    frequencyGroup.style.opacity = isRecurring ? '1' : '0.5';
                }
            }
            
            const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
            editModal.show();
        } else {
            showNotification('Failed to load expense data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while loading expense data', 'error');
    });
}

function loadExpenseForClone(expenseId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fill the add form with the expense data
            document.getElementById('category_id').value = data.expense.category_id;
            document.getElementById('description').value = data.expense.description + ' (Copy)';
            document.getElementById('amount').value = data.expense.amount;
            document.getElementById('expense_date').value = new Date().toISOString().split('T')[0]; // Set to today
            
            const isRecurring = data.expense.is_recurring === '1' || data.expense.is_recurring === 1;
            document.getElementById('is_recurring').checked = isRecurring;
            
            const frequencyField = document.getElementById('frequency');
            const frequencyGroup = document.getElementById('frequency_group');
            if (frequencyField) {
                frequencyField.value = data.expense.frequency || 'one-time';
                frequencyField.disabled = !isRecurring;
                if (frequencyGroup) {
                    frequencyGroup.style.opacity = isRecurring ? '1' : '0.5';
                }
            }
            
            const addModal = new bootstrap.Modal(document.getElementById('addExpenseModal'));
            addModal.show();
        } else {
            showNotification('Failed to load expense data for cloning', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while loading expense data', 'error');
    });
}

function updateChartData(period) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = '0.5';
    }
    
    fetch(`${basePath}/expenses?action=get_expense_analytics&period=${period}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.analytics && data.analytics.category_totals && Object.keys(data.analytics.category_totals).length > 0) {
            const categoryTotals = data.analytics.category_totals;
            
            const sortedCategories = Object.entries(categoryTotals)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 10);
            
            const labels = sortedCategories.map(item => item[0]);
            const values = sortedCategories.map(item => item[1]);
            
            const colors = [
                '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
                '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
            ];
            
            if (window.categoryChart) {
                window.categoryChart.data.labels = labels;
                window.categoryChart.data.datasets[0].data = values;
                window.categoryChart.data.datasets[0].backgroundColor = colors.slice(0, values.length);
                
                // Update the tooltip to use the correct currency symbol
                window.categoryChart.options.plugins.tooltip.callbacks.label = function(context) {
                    const label = context.label || '';
                    const value = context.parsed;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                };
                
                window.categoryChart.update();
            }
            
            updateCategoriesList(sortedCategories, data.analytics.total_amount, currencySymbol);
            
            // Also update the period title
            updatePeriodTitle(period);
            
            const noDataMessage = document.getElementById('chartNoData');
            if (noDataMessage) {
                noDataMessage.style.display = 'none';
            }
            if (chartContainer) {
                chartContainer.style.display = 'block';
            }
        } else {
            showNoDataMessage();
        }
        
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        showNotification('Failed to update chart data', 'error');
    });
}

function updatePeriodTitle(period) {
    const titleElement = document.getElementById('chartPeriodTitle');
    if (!titleElement) return;
    
    let title = 'Expenses';
    switch (period) {
        case 'current-month':
            title = 'This Month';
            break;
        case 'last-month':
            title = 'Last Month';
            break;
        case 'last-3-months':
            title = 'Last 3 Months';
            break;
        case 'current-year':
            title = 'This Year';
            break;
        case 'all':
            title = 'All Time';
            break;
    }
    
    titleElement.textContent = title;
}

function updateCategoriesList(categories, totalAmount, currencySymbol) {
    const listContainer = document.querySelector('.categories-list-content');
    
    if (!listContainer || categories.length === 0) return;
    
    let html = '';
    categories.forEach(([category, amount], index) => {
        const percentage = ((amount / totalAmount) * 100).toFixed(1);
        html += `
            <div class="category-item">
                <div class="category-rank">${index + 1}</div>
                <div class="category-info">
                    <h4 class="category-name">${category}</h4>
                    <div class="category-bar">
                        <div class="category-bar-fill" 
                             style="width: 0%"
                             data-percentage="${percentage}">
                        </div>
                    </div>
                </div>
                <div class="category-amount">
                    <span class="amount">${currencySymbol}${amount.toLocaleString()}</span>
                    <span class="percentage">${percentage}%</span>
                </div>
                <div class="category-actions">
                    <button class="category-filter-btn" title="Filter by this category" data-category-id="${getCategoryIdByName(category)}">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
    
    setTimeout(() => {
        const bars = listContainer.querySelectorAll('.category-bar-fill');
        bars.forEach(bar => {
            const percentage = bar.getAttribute('data-percentage');
            bar.style.width = percentage + '%';
        });
    }, 100);
    
    // Add event listeners to category filter buttons
    listContainer.querySelectorAll('.category-filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categorySelect = document.getElementById('categorySelect');
            if (categorySelect) {
                categorySelect.value = categoryId;
                document.getElementById('applyFilter').click();
            }
        });
    });
}

function getCategoryIdByName(categoryName) {
    // Try to find the category ID from the select dropdown
    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect) {
        for (let i = 0; i < categorySelect.options.length; i++) {
            if (categorySelect.options[i].text === categoryName) {
                return categorySelect.options[i].value;
            }
        }
    }
    return '0'; // Default to all categories if not found
}

function getSelectedExpenseIds() {
    const checkboxes = document.querySelectorAll('.expense-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

function updateBulkActionVisibility() {
    const selectedCount = document.querySelectorAll('.expense-checkbox:checked').length;
    const bulkActions = document.getElementById('bulkActions');
    
    if (bulkActions) {
        bulkActions.style.display = selectedCount > 0 ? 'flex' : 'none';
        const countEl = bulkActions.querySelector('.selected-count');
        if (countEl) {
            countEl.textContent = selectedCount;
        }
    }
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Highlight the first invalid field
                const invalidField = form.querySelector(':invalid');
                if (invalidField) {
                    invalidField.focus();
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                // Check this input's validity
                const isValid = this.checkValidity();
                
                // Get the feedback element
                const feedbackEl = this.nextElementSibling;
                if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                    // Set custom message if needed
                    if (!isValid) {
                        if (this.validity.valueMissing) {
                            feedbackEl.textContent = `${this.name.replace('_', ' ')} is required`;
                        } else if (this.validity.typeMismatch) {
                            feedbackEl.textContent = `Please enter a valid ${this.type}`;
                        } else if (this.validity.rangeUnderflow) {
                            feedbackEl.textContent = `Value must be at least ${this.min}`;
                        }
                    }
                }
            });
        });
    });
}

function initializeAnimations() {
    const categoryBars = document.querySelectorAll('.category-bar-fill');
    categoryBars.forEach((bar, index) => {
        setTimeout(() => {
            const percentage = bar.getAttribute('data-percentage') || bar.style.width.replace('%', '');
            bar.style.width = percentage + '%';
        }, 100 + (index * 50));
    });
    
    // Animate stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('show');
        }, 100 + (index * 100));
    });
    
    // Animate table rows
    const tableRows = document.querySelectorAll('.expenses-table tbody tr');
    tableRows.forEach((row, index) => {
        setTimeout(() => {
            row.classList.add('show');
        }, 300 + (index * 50));
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('expenseSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.expenses-table tbody tr');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const noDataMessage = document.getElementById('tableNoData');
            const tableBody = document.querySelector('.table-responsive');
            
            if (visibleRows === 0 && tableRows.length > 0) {
                if (tableBody) tableBody.style.display = 'none';
                if (noDataMessage) {
                    noDataMessage.style.display = 'block';
                    noDataMessage.querySelector('h4').textContent = 'No matching expenses found';
                    noDataMessage.querySelector('p').textContent = 'Try adjusting your search term';
                    noDataMessage.querySelector('.btn-add-first').style.display = 'none';
                }
            } else {
                if (tableBody) tableBody.style.display = 'block';
                if (noDataMessage && tableRows.length > 0) noDataMessage.style.display = 'none';
            }
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    notification.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}