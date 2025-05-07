/**
 * iGotMoney - Budget Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize chart if available
    initializeChart();
    
    // Animate progress bars
    animateProgressBars();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Handle edit and delete operations
    initializeEditOperations();
    
    // Handle recommendation adoption
    initializeRecommendationAdoption();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[title]');
    [...tooltipTriggerList].forEach(el => {
        if (bootstrap && bootstrap.Tooltip) {
            new bootstrap.Tooltip(el);
        }
    });
}

/**
 * Initialize table search functionality
 */
function initializeSearch() {
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('budgetCategoriesTable');
            
            if (table) {
                const tbody = table.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const categoryName = row.querySelector('td:first-child').textContent.toLowerCase();
                    
                    if (categoryName.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
}

/**
 * Initialize budget vs actual chart
 */
function initializeChart() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Please include it in your page.');
        const chartContainer = document.querySelector('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="alert alert-warning">Chart cannot be displayed: Chart.js library not available</div>';
        }
        return;
    }
    
    // Get chart canvas
    const ctx = document.getElementById('budgetVsActualChart');
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }
    
    try {
        // Get chart data from HTML attributes
        const labels = JSON.parse(ctx.getAttribute('data-labels') || '[]');
        const budgetData = JSON.parse(ctx.getAttribute('data-budget') || '[]');
        const spentData = JSON.parse(ctx.getAttribute('data-spent') || '[]');
        
        // Create chart
        const chartConfig = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Budget',
                        data: budgetData,
                        backgroundColor: 'rgba(78, 115, 223, 0.8)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Spent',
                        data: spentData,
                        backgroundColor: 'rgba(231, 74, 59, 0.8)',
                        borderColor: 'rgba(231, 74, 59, 1)',
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                return label + ': $' + value.toLocaleString();
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        };
        
        window.budgetVsActualChart = new Chart(ctx, chartConfig);
        
        // Handle period selector
        const periodOptions = document.querySelectorAll('.dropdown-menu .dropdown-item');
        periodOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update dropdown button text
                const dropdownButton = document.getElementById('chartPeriodDropdown');
                if (dropdownButton) {
                    dropdownButton.innerHTML = '<i class="fas fa-calendar-alt me-1"></i> ' + this.textContent;
                }
                
                // Remove active class from all options
                periodOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Update chart - normally would fetch new data here
                // This is just a simulation
                simulateChartUpdate(this.textContent.trim());
            });
        });
        
    } catch (e) {
        console.error('Error initializing chart:', e);
        const chartContainer = document.querySelector('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="alert alert-danger">Error initializing chart: ' + e.message + '</div>';
        }
    }
}

/**
 * Simulate chart update with different time ranges
 */
function simulateChartUpdate(timeRange) {
    if (!window.budgetVsActualChart) return;
    
    // Show loading indicator
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = 0.5;
        
        // Create and append loading spinner
        const spinner = document.createElement('div');
        spinner.className = 'position-absolute top-50 start-50 translate-middle';
        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        
        chartContainer.style.position = 'relative';
        chartContainer.appendChild(spinner);
    }
    
    // Simulate API delay
    setTimeout(() => {
        // Generate random data based on time range
        const chartLabels = window.budgetVsActualChart.data.labels;
        let budgetData = [];
        let spentData = [];
        
        switch(timeRange) {
            case 'Last Month':
                budgetData = chartLabels.map(() => Math.floor(Math.random() * 400) + 200);
                spentData = budgetData.map(val => Math.floor(val * (Math.random() * 0.5 + 0.5)));
                break;
            case 'Last 3 Months':
                budgetData = chartLabels.map(() => Math.floor(Math.random() * 800) + 400);
                spentData = budgetData.map(val => Math.floor(val * (Math.random() * 0.6 + 0.4)));
                break;
            case 'This Year':
                budgetData = chartLabels.map(() => Math.floor(Math.random() * 1500) + 800);
                spentData = budgetData.map(val => Math.floor(val * (Math.random() * 0.7 + 0.3)));
                break;
            default: // 'This Month' - use current data
                break;
        }
        
        // Only update if we have new data
        if (budgetData.length > 0 && timeRange !== 'This Month') {
            window.budgetVsActualChart.data.datasets[0].data = budgetData;
            window.budgetVsActualChart.data.datasets[1].data = spentData;
            window.budgetVsActualChart.update();
        }
        
        // Remove loading indicator
        if (chartContainer) {
            chartContainer.style.opacity = 1;
            const spinner = chartContainer.querySelector('.position-absolute');
            if (spinner) {
                chartContainer.removeChild(spinner);
            }
        }
    }, 800);
}

/**
 * Animate progress bars
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const width = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = width;
        }, 100 + (index * 50));
    });
    
    // Animate gauge
    const gaugeFill = document.querySelector('.gauge-fill');
    if (gaugeFill) {
        setTimeout(() => {
            const rotation = gaugeFill.style.transform;
            if (rotation) {
                gaugeFill.style.transform = rotation;
            }
        }, 300);
    }
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
        }, false);
    });
}

/**
 * Initialize edit operations
 */
function initializeEditOperations() {
    // Handle edit budget
    const editBtns = document.querySelectorAll('.edit-budget');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const budgetId = this.getAttribute('data-budget-id');
            
            // Fetch budget data via AJAX
            fetch(`${BASE_PATH}/budget?action=get_budget&budget_id=${budgetId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const budget = data.budget;
                        
                        // Populate the edit form
                        document.getElementById('edit_budget_id').value = budget.budget_id;
                        document.getElementById('edit_category_id').value = budget.category_id;
                        document.getElementById('edit_amount').value = budget.amount;
                        document.getElementById('edit_start_date').value = budget.start_date;
                        document.getElementById('edit_end_date').value = budget.end_date;
                        
                        // Show the modal
                        const editModal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
                        editModal.show();
                    } else {
                        showNotification('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error fetching budget data:', error);
                    showNotification('An error occurred while fetching budget data.', 'danger');
                });
        });
    });
    
    // Handle delete budget
    const deleteBtns = document.querySelectorAll('.delete-budget');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const budgetId = this.getAttribute('data-budget-id');
            document.getElementById('delete_budget_id').value = budgetId;
            
            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteBudgetModal'));
            deleteModal.show();
        });
    });
}

/**
 * Initialize recommendation adoption
 */
function initializeRecommendationAdoption() {
    // Handle adopt recommendation
    const adoptBtns = document.querySelectorAll('.adopt-recommendation');
    adoptBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const amount = this.getAttribute('data-amount');
            
            // Set values in the add budget form
            document.getElementById('category_id').value = categoryId;
            document.getElementById('amount').value = amount;
            
            // Show the add budget modal
            const addModal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
            addModal.show();
        });
    });
    
    // Handle adopt all recommendations
    const adoptAllBtn = document.getElementById('adoptAllRecommendations');
    if (adoptAllBtn) {
        adoptAllBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to adopt all budget recommendations? This will create budget entries for all recommended categories.')) {
                // Submit the generate budget form with replace_existing=1
                document.getElementById('generateBudgetForm').submit();
            }
        });
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Check if notification container exists, if not create it
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '1rem';
        notificationContainer.style.right = '1rem';
        notificationContainer.style.zIndex = '9999';
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    notificationContainer.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notificationContainer.removeChild(notification);
        }, 300);
    }, 5000);
}