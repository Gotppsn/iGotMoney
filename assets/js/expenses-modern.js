document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Expenses JS loaded');
    
    initializeChart();
    initializeEventListeners();
    initializeFormValidation();
    initializeAnimations();
    initializeSearch();
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
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage();
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        
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
                                return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
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
            if (frequencyField) {
                frequencyField.disabled = !this.checked;
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
            if (frequencyField) {
                frequencyField.disabled = !this.checked;
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
            if (frequencyField) {
                frequencyField.value = data.expense.frequency || 'one-time';
                frequencyField.disabled = !isRecurring;
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

function updateChartData(period) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
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
                window.categoryChart.update();
            }
            
            updateCategoriesList(sortedCategories, data.analytics.total_amount);
            
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

function updateCategoriesList(categories, totalAmount) {
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
                    <span class="amount">$${amount.toLocaleString()}</span>
                    <span class="percentage">${percentage}%</span>
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
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
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