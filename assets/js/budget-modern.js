document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Budget JS loaded');
    
    // Initialize all components
    initializeGauge();
    initializeProgressBars();
    initializeEventListeners();
    initializeFormValidation();
    initializeSearch();
    initializeRecommendations();
});

function initializeGauge() {
    const gauges = document.querySelectorAll('.modern-gauge');
    gauges.forEach(gauge => {
        const percentage = parseFloat(gauge.dataset.percentage) || 0;
        const progress = gauge.querySelector('.gauge-progress');
        
        if (progress) {
            // Animate gauge on load
            setTimeout(() => {
                progress.style.strokeDashoffset = 251.2 * (1 - percentage / 100);
            }, 500);
        }
    });
}

function initializeProgressBars() {
    // Animate category progress bars
    const progressFills = document.querySelectorAll('.progress-fill');
    progressFills.forEach((fill, index) => {
        const width = fill.style.width;
        fill.style.width = '0%';
        
        setTimeout(() => {
            fill.style.width = width;
        }, 100 + (index * 50));
    });
    
    // Animate budget progress bars in table
    const budgetProgressBars = document.querySelectorAll('.budget-progress .progress-bar');
    budgetProgressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 300 + (index * 30));
    });
}

function initializeEventListeners() {
    // Edit budget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const budgetId = button.getAttribute('data-budget-id');
            if (budgetId) {
                loadBudgetForEdit(budgetId);
            }
        }
    });

    // Delete budget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const budgetId = button.getAttribute('data-budget-id');
            if (budgetId) {
                document.getElementById('delete_budget_id').value = budgetId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteBudgetModal'));
                deleteModal.show();
            }
        }
    });

    // Adopt recommendation buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.adopt-recommendation')) {
            e.preventDefault();
            const button = e.target.closest('.adopt-recommendation');
            adoptRecommendation(button);
        }
    });

    // Adopt all recommendations
    const adoptAllBtn = document.getElementById('adoptAllRecommendations');
    if (adoptAllBtn) {
        adoptAllBtn.addEventListener('click', function() {
            const confirmMessage = document.querySelector('meta[name="confirm-adopt-all"]')?.getAttribute('content') || 
                'Are you sure you want to adopt all budget recommendations? This will create budget entries for all recommended categories.';
            
            if (confirm(confirmMessage)) {
                document.getElementById('generateBudgetForm').submit();
            }
        });
    }
}

function loadBudgetForEdit(budgetId) {
    const basePath = document.querySelector('meta[name="base-path"]')?.getAttribute('content') || '';
    
    fetch(`${basePath}/budget?action=get_budget&budget_id=${budgetId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_budget_id').value = data.budget.budget_id;
            document.getElementById('edit_category_id').value = data.budget.category_id;
            document.getElementById('edit_amount').value = data.budget.amount;
            document.getElementById('edit_start_date').value = data.budget.start_date;
            document.getElementById('edit_end_date').value = data.budget.end_date;
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
            editModal.show();
        } else {
            const errorMsg = document.querySelector('meta[name="error-load-budget"]')?.getAttribute('content') || 
                'Failed to load budget data';
            showNotification(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = document.querySelector('meta[name="error-load-budget-general"]')?.getAttribute('content') || 
            'An error occurred while loading budget data';
        showNotification(errorMsg, 'error');
    });
}

function adoptRecommendation(button) {
    const categoryId = button.getAttribute('data-category-id');
    const amount = button.getAttribute('data-amount');
    
    // Set values in the add budget form
    document.getElementById('category_id').value = categoryId;
    document.getElementById('amount').value = amount;
    
    // Show the add budget modal
    const addModal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
    addModal.show();
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

function initializeSearch() {
    const searchInput = document.getElementById('categorySearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.budget-table tbody tr');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const categoryName = row.querySelector('.category-text').textContent.toLowerCase();
                if (categoryName.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const noDataMessage = document.querySelector('.table-empty');
            const tableBody = document.querySelector('.table-responsive');
            
            if (visibleRows === 0 && tableRows.length > 0) {
                if (tableBody) tableBody.style.display = 'none';
                if (noDataMessage) {
                    noDataMessage.style.display = 'block';
                    
                    // Get translation from meta tags
                    const noMatchingBudgetsText = document.querySelector('meta[name="no-matching-budgets"]')?.getAttribute('content') || 
                        'No matching budgets found';
                    const tryAdjustingText = document.querySelector('meta[name="try-adjusting-search"]')?.getAttribute('content') || 
                        'Try adjusting your search term';
                        
                    noDataMessage.querySelector('h4').textContent = noMatchingBudgetsText;
                    noDataMessage.querySelector('p').textContent = tryAdjustingText;
                    const emptyActions = noDataMessage.querySelector('.empty-actions');
                    if (emptyActions) emptyActions.style.display = 'none';
                }
            } else {
                if (tableBody) tableBody.style.display = 'block';
                if (noDataMessage && tableRows.length > 0) noDataMessage.style.display = 'none';
            }
        });
    }
}

function initializeRecommendations() {
    // Animate recommendation progress bars
    const recommendationBars = document.querySelectorAll('.recommendation-progress .progress-bar');
    recommendationBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 800 + (index * 100));
    });
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add styles
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
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Select investment category when clicking add investment budget
function selectInvestmentCategory() {
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

// Make function globally available
window.selectInvestmentCategory = selectInvestmentCategory;