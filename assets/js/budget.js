/**
 * iGotMoney - Budget Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize search functionality
    initializeSearch();
    
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
 * Initialize budget charts
 */
function initializeBudgetCharts(budgetData, spentData, categoryLabels) {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Please include it in your page.');
        return;
    }
    
    // Initialize donut chart
    const donutCtx = document.getElementById('budgetDonutChart');
    if (!donutCtx) {
        console.warn('Budget donut chart canvas element not found.');
        return;
    }
    
    // Ensure the canvas has proper dimensions
    const chartContainer = donutCtx.parentElement;
    if (chartContainer) {
        donutCtx.width = chartContainer.clientWidth;
        donutCtx.height = chartContainer.clientHeight || 250;
    }
    
    if (budgetData && budgetData.length > 0) {
        try {
            const totalBudget = budgetData.reduce((a, b) => a + b, 0);
            const totalSpent = spentData.reduce((a, b) => a + b, 0);
            const totalRemaining = Math.max(0, totalBudget - totalSpent);
            
            // Destroy existing chart if it exists
            if (window.budgetChart instanceof Chart) {
                window.budgetChart.destroy();
            }
            
            // Create donut chart
            window.budgetChart = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Spent', 'Remaining'],
                    datasets: [{
                        data: [totalSpent, totalRemaining],
                        backgroundColor: [
                            totalSpent > totalBudget ? '#e74a3b' : '#4e73df',
                            '#1cc88a'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw || 0;
                                    const percentage = ((value / totalBudget) * 100).toFixed(1);
                                    return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error creating chart:', e);
        }
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
 * Select investment category when clicking add investment budget
 */
function selectInvestmentCategory() {
    // Find the investment category option
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        const options = categorySelect.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].text.includes('Investments')) {
                categorySelect.value = options[i].value;
                break;
            }
        }
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

// Make functions globally available
window.selectInvestmentCategory = selectInvestmentCategory;
window.initializeBudgetCharts = initializeBudgetCharts;