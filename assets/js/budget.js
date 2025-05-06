/**
 * iGotMoney - Budget JavaScript
 * Handles functionality for the budget management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    console.log('Initializing budget.js with base path:', basePath);
    
    // Initialize budget date validation
    initializeDateValidation();
    
    // Initialize budget category selection
    initializeCategorySelection();
    
    // Initialize budget recommendations
    initializeBudgetRecommendations();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize budget chart
    initializeBudgetChart();
    
    // Initialize button handlers
    initializeButtonHandlers();
});

/**
 * Initialize date validation
 * Ensures end date is after start date
 */
function initializeDateValidation() {
    const startDateInputs = document.querySelectorAll('#start_date, #edit_start_date');
    const endDateInputs = document.querySelectorAll('#end_date, #edit_end_date');
    
    startDateInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            validateDates(this, endDateInputs[index]);
        });
    });
    
    endDateInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            validateDates(startDateInputs[index], this);
        });
    });
}

/**
 * Validate start and end dates
 * @param {Element} startDateInput - The start date input element
 * @param {Element} endDateInput - The end date input element
 */
function validateDates(startDateInput, endDateInput) {
    if (!startDateInput || !endDateInput) return;
    
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (endDate < startDate) {
        showNotification('End date must be after start date.', 'warning');
        endDateInput.value = '';
    }
}

/**
 * Initialize category selection
 * Prevents duplicate categories and provides suggestions
 */
function initializeCategorySelection() {
    const categorySelects = document.querySelectorAll('#category_id, #edit_category_id');
    
    categorySelects.forEach(select => {
        select.addEventListener('change', function() {
            const selectedCategory = this.value;
            const isEdit = this.id === 'edit_category_id';
            const modalId = isEdit ? 'editBudgetModal' : 'addBudgetModal';
            
            // Check if budget already exists for this category
            const existingBudgets = document.querySelectorAll('table tbody tr');
            let categoryExists = false;
            let currentBudgetId = null;
            
            if (isEdit) {
                currentBudgetId = document.getElementById('edit_budget_id').value;
            }
            
            existingBudgets.forEach(row => {
                const categoryCell = row.querySelector('td:first-child');
                const categoryText = categoryCell ? categoryCell.textContent.trim() : '';
                const actionCell = row.querySelector('td:last-child');
                const editButton = actionCell ? actionCell.querySelector('.edit-budget') : null;
                const budgetId = editButton ? editButton.getAttribute('data-budget-id') : null;
                
                // Skip checking if this is the budget we're currently editing
                if (isEdit && budgetId === currentBudgetId) {
                    return;
                }
                
                // Check if category exists in table
                if (categoryText && select.options[select.selectedIndex].text === categoryText) {
                    categoryExists = true;
                }
            });
            
            if (categoryExists) {
                // Show warning
                let warningElement = document.getElementById(`${modalId}-warning`);
                
                if (!warningElement) {
                    warningElement = document.createElement('div');
                    warningElement.id = `${modalId}-warning`;
                    warningElement.className = 'alert alert-warning mt-3';
                    warningElement.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        A budget for this category already exists. Creating another budget may cause conflicts.
                    `;
                    
                    const amountInput = document.getElementById(isEdit ? 'edit_amount' : 'amount');
                    amountInput.parentNode.parentNode.insertAdjacentElement('afterend', warningElement);
                }
            } else {
                // Remove warning if exists
                const warningElement = document.getElementById(`${modalId}-warning`);
                if (warningElement) {
                    warningElement.remove();
                }
            }
            
            // Suggest budget amount based on income (simplified)
            const suggestionElement = document.getElementById(`${modalId}-suggestion`);
            const amountInput = document.getElementById(isEdit ? 'edit_amount' : 'amount');
            
            // Remove any existing suggestion
            if (suggestionElement) {
                suggestionElement.remove();
            }
            
            // Get category name
            const categoryName = select.options[select.selectedIndex].text;
            
            // Make suggestion based on category (simplified)
            let suggestionText = '';
            let suggestedAmount = 0;
            
            // Check if we have recommendation data
            const recommendations = document.querySelectorAll('.adopt-recommendation');
            let categoryFound = false;
            
            recommendations.forEach(rec => {
                if (rec.getAttribute('data-category-id') === selectedCategory) {
                    suggestedAmount = parseFloat(rec.getAttribute('data-amount'));
                    categoryFound = true;
                }
            });
            
            if (!categoryFound) {
                // Fallback to general suggestions
                switch(categoryName) {
                    case 'Housing':
                        suggestionText = '30% of your income is the recommended housing budget.';
                        break;
                    case 'Food':
                        suggestionText = '10-15% of your income is typical for food expenses.';
                        break;
                    case 'Transportation':
                        suggestionText = '10-15% of your income is typical for transportation costs.';
                        break;
                    case 'Utilities':
                        suggestionText = '5-10% of your income is typical for utilities.';
                        break;
                    case 'Debt Payments':
                        suggestionText = 'Aim to keep debt payments under 20% of your income.';
                        break;
                    case 'Savings':
                        suggestionText = 'Try to save at least 10-20% of your income.';
                        break;
                    default:
                        suggestionText = '';
                }
            } else {
                suggestionText = `Suggested amount: $${suggestedAmount.toFixed(2)} based on your income.`;
            }
            
            // Add suggestion if we have text
            if (suggestionText) {
                const newSuggestion = document.createElement('div');
                newSuggestion.id = `${modalId}-suggestion`;
                newSuggestion.className = 'form-text text-info';
                newSuggestion.innerHTML = `<i class="fas fa-lightbulb me-1"></i> ${suggestionText}`;
                
                // If we found a recommended amount, add a button to use it
                if (categoryFound) {
                    newSuggestion.innerHTML += ` <button type="button" class="btn btn-sm btn-outline-info use-suggestion" data-amount="${suggestedAmount.toFixed(2)}">Use Suggestion</button>`;
                }
                
                amountInput.parentNode.insertAdjacentElement('afterend', newSuggestion);
                
                // Add event listener to "Use Suggestion" button
                const useSuggestionBtn = document.querySelector(`#${modalId}-suggestion .use-suggestion`);
                if (useSuggestionBtn) {
                    useSuggestionBtn.addEventListener('click', function() {
                        const amount = this.getAttribute('data-amount');
                        amountInput.value = amount;
                    });
                }
            }
        });
    });
}

/**
 * Initialize budget recommendations
 * Handle recommendation adoption and highlighting
 */
function initializeBudgetRecommendations() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Highlight recommendations that differ significantly from current budgets
    highlightRecommendations();
    
    // Add bulk adoption feature
    const adoptAllBtn = document.getElementById('adoptAllRecommendations');
    if (!adoptAllBtn) return;
    
    adoptAllBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to adopt all recommended budgets? This will replace your existing budgets.')) {
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
            this.disabled = true;
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'generate_plan');
            formData.append('replace_existing', '1');
            
            // Convert FormData to URL encoded string
            const params = new URLSearchParams();
            for (const pair of formData.entries()) {
                params.append(pair[0], pair[1]);
            }
            
            // Add AJAX handling
            const xhr = new XMLHttpRequest();
            xhr.open('POST', basePath + '/budget', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification(response.message, 'success');
                            
                            // Reload page after a short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification(response.message || 'An error occurred', 'danger');
                            adoptAllBtn.innerHTML = '<i class="fas fa-check"></i> Adopt All Recommendations';
                            adoptAllBtn.disabled = false;
                        }
                    } catch (e) {
                        showNotification('Invalid response from server', 'danger');
                        adoptAllBtn.innerHTML = '<i class="fas fa-check"></i> Adopt All Recommendations';
                        adoptAllBtn.disabled = false;
                    }
                } else {
                    showNotification('Failed to process request: ' + xhr.status, 'danger');
                    adoptAllBtn.innerHTML = '<i class="fas fa-check"></i> Adopt All Recommendations';
                    adoptAllBtn.disabled = false;
                }
            };
            xhr.onerror = function() {
                showNotification('Network error occurred', 'danger');
                adoptAllBtn.innerHTML = '<i class="fas fa-check"></i> Adopt All Recommendations';
                adoptAllBtn.disabled = false;
            };
            
            xhr.send(params.toString());
        }
    });
    
    // Handle individual recommendation adoption
    document.querySelectorAll('.adopt-recommendation').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const amount = this.getAttribute('data-amount');
            
            // Show loading spinner
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adopting...';
            this.disabled = true;
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('category_id', categoryId);
            formData.append('amount', amount);
            formData.append('start_date', new Date().toISOString().split('T')[0]);
            formData.append('end_date', new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0]);
            
            // Convert FormData to URL encoded string
            const params = new URLSearchParams();
            for (const pair of formData.entries()) {
                params.append(pair[0], pair[1]);
            }
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', basePath + '/budget', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification(response.message || 'Budget added successfully!', 'success');
                            
                            // Reload page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification(response.message || 'Failed to add budget', 'danger');
                            this.innerHTML = '<i class="fas fa-check"></i> Adopt';
                            this.disabled = false;
                        }
                    } catch (e) {
                        showNotification('Invalid response from server', 'danger');
                        this.innerHTML = '<i class="fas fa-check"></i> Adopt';
                        this.disabled = false;
                    }
                } else {
                    showNotification('Request failed with status ' + xhr.status, 'danger');
                    this.innerHTML = '<i class="fas fa-check"></i> Adopt';
                    this.disabled = false;
                }
            };
            
            xhr.onerror = () => {
                showNotification('Network error occurred', 'danger');
                this.innerHTML = '<i class="fas fa-check"></i> Adopt';
                this.disabled = false;
            };
            
            xhr.send(params.toString());
        });
    });
}

/**
 * Highlight budget recommendations
 * Highlight recommendations that differ from current budgets
 */
function highlightRecommendations() {
    // Get all recommendation rows
    const recommendationRows = document.querySelectorAll('table tbody tr');
    const budgetTable = document.querySelector('.card-body table');
    
    if (!recommendationRows.length || !budgetTable) return;
    
    // Get current budgets
    const currentBudgets = {};
    const budgetRows = budgetTable.querySelectorAll('tbody tr');
    
    budgetRows.forEach(row => {
        const categoryCell = row.querySelector('td:first-child');
        const budgetCell = row.querySelector('td:nth-child(2)');
        
        if (categoryCell && budgetCell) {
            const category = categoryCell.textContent.trim();
            const budget = parseFloat(budgetCell.textContent.replace('$', '').replace(/,/g, ''));
            
            currentBudgets[category] = budget;
        }
    });
    
    // Check recommendations against current budgets
    document.querySelectorAll('.adopt-recommendation').forEach(button => {
        const row = button.closest('tr');
        if (!row) return;
        
        const categoryCell = row.querySelector('td:first-child');
        const recommendedCell = row.querySelector('td:nth-child(2)');
        
        if (categoryCell && recommendedCell) {
            const category = categoryCell.textContent.trim();
            const recommended = parseFloat(button.getAttribute('data-amount'));
            
            if (currentBudgets[category]) {
                const current = currentBudgets[category];
                const difference = Math.abs(recommended - current);
                const percentDifference = (difference / current) * 100;
                
                // Highlight significant differences
                if (percentDifference > 20) {
                    row.classList.add('table-warning');
                    
                    // Add explanation tooltip
                    categoryCell.innerHTML += `
                        <span class="ms-2 badge bg-warning" data-bs-toggle="tooltip" title="This recommendation differs by ${percentDifference.toFixed(0)}% from your current budget">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    `;
                    
                    // Initialize tooltip
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        new bootstrap.Tooltip(categoryCell.querySelector('[data-bs-toggle="tooltip"]'));
                    }
                }
            }
        }
    });
}

/**
 * Initialize form validation
 * Validates form inputs before submission
 */
function initializeFormValidation() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    console.log('Base path for forms:', basePath);
    
    // Add form submit handlers
    const addForm = document.querySelector('#addBudgetModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateBudgetForm(this)) {
                submitFormWithAjax(this);
            }
        });
    }
    
    // Handle edit form using button click instead of form submit
    const editForm = document.querySelector('#editBudgetModal form');
    const saveEditButton = document.querySelector('#saveEditBudget');
    
    if (editForm && saveEditButton) {
        saveEditButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (validateBudgetForm(editForm)) {
                submitFormWithAjax(editForm);
            }
        });
    } else if (editForm) {
        // Fallback for original button structure
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateBudgetForm(this)) {
                submitFormWithAjax(this);
            }
        });
    }
    
    const deleteForm = document.querySelector('#deleteBudgetModal form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            submitFormWithAjax(this);
        });
    }
}

/**
 * Initialize button handlers
 * Sets up event listeners for edit and delete buttons
 */
function initializeButtonHandlers() {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    console.log('Initializing button handlers for budget actions');
    
    // Handle edit budget button clicks
    document.querySelectorAll('.edit-budget').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Edit budget button clicked', this.getAttribute('data-budget-id'));
            const budgetId = this.getAttribute('data-budget-id');
            document.getElementById('edit_budget_id').value = budgetId;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
            modal.show();
            
            // Fetch budget data
            fetchBudgetData(budgetId);
        });
    });
    
    document.querySelectorAll('.delete-budget').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Delete budget button clicked', this.getAttribute('data-budget-id'));
            const budgetId = this.getAttribute('data-budget-id');
            document.getElementById('delete_budget_id').value = budgetId;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('deleteBudgetModal'));
            modal.show();
        });
    });
}

/**
 * Validate budget form
 * @param {HTMLFormElement} form - The form to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateBudgetForm(form) {
    // Reset previous validation
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Get form inputs
    const categoryId = form.querySelector('[name="category_id"]');
    const amount = form.querySelector('[name="amount"]');
    const startDate = form.querySelector('[name="start_date"]');
    const endDate = form.querySelector('[name="end_date"]');
    
    let isValid = true;
    
    // Validate category
    if (!categoryId.value) {
        categoryId.classList.add('is-invalid');
        showValidationError(categoryId, 'Please select a category');
        isValid = false;
    }
    
    // Validate amount
    if (!amount.value || parseFloat(amount.value) <= 0) {
        amount.classList.add('is-invalid');
        showValidationError(amount, 'Please enter an amount greater than zero');
        isValid = false;
    }
    
    // Validate start date
    if (!startDate.value) {
        startDate.classList.add('is-invalid');
        showValidationError(startDate, 'Please select a start date');
        isValid = false;
    }
    
    // Validate end date
    if (!endDate.value) {
        endDate.classList.add('is-invalid');
        showValidationError(endDate, 'Please select an end date');
        isValid = false;
    }
    
    // Validate date range
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end < start) {
            endDate.classList.add('is-invalid');
            showValidationError(endDate, 'End date must be after start date');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Show validation error message
 * @param {HTMLElement} element - The element with error
 * @param {string} message - Error message
 */
function showValidationError(element, message) {
    // Create or update feedback element
    let feedback = element.nextElementSibling;
    
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        element.parentNode.insertBefore(feedback, element.nextSibling);
    }
    
    feedback.textContent = message;
}

/**
 * Submit form with AJAX
 * @param {HTMLFormElement} form - The form to submit
 */
function submitFormWithAjax(form) {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Get submit button
    const submitButton = form.querySelector('button[type="submit"]') || 
                          form.querySelector('button:last-child');
    const originalButtonText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Create form data
    const formData = new FormData(form);
    
    // Convert FormData to URL encoded string
    const params = new URLSearchParams();
    for (const pair of formData.entries()) {
        params.append(pair[0], pair[1]);
    }
    
    // Send AJAX request
    const xhr = new XMLHttpRequest();
    
    // Use absolute URL with base path - IMPORTANT FIX
    const url = basePath + '/budget';
    console.log('Submitting form to:', url);
    
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Close modal if we're in one
                    const modal = form.closest('.modal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                    
                    // Show success message
                    showNotification(response.message || 'Operation completed successfully', 'success');
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    showNotification(response.message || 'An error occurred', 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            } catch (e) {
                // Invalid JSON response
                console.error('Invalid server response:', xhr.responseText);
                showNotification('Invalid response from server', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        } else {
            // Error response
            console.error('Server error:', xhr.status);
            showNotification('Server error: ' + xhr.status, 'danger');
            
            // Reset button
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    };
    
    xhr.onerror = function() {
        console.error('Request failed');
        showNotification('Network error occurred', 'danger');
        
        // Reset button
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    };
    
    xhr.send(params.toString());
}

/**
 * Fetch budget data for editing
 * @param {string} budgetId - Budget ID to fetch
 */
function fetchBudgetData(budgetId) {
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Get form and modal elements
    const form = document.querySelector('#editBudgetModal form');
    const modal = document.getElementById('editBudgetModal');
    
    if (!form || !modal) return;
    
    // Set budget ID to form
    document.getElementById('edit_budget_id').value = budgetId;
    
    // Create and send request
    const xhr = new XMLHttpRequest();
    
    // Use proper URL with base path
    const url = `${basePath}/budget?action=get_budget&budget_id=${budgetId}`;
    console.log('Fetching budget data from:', url);
    
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                // Try to parse the response as JSON
                const responseText = xhr.responseText;
                console.log('Raw response:', responseText);
                
                const response = JSON.parse(responseText);
                console.log('Budget data received:', response);
                
                if (response && response.success === true && response.budget) {
                    // Restore the form fields to ensure they exist
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                ${Array.from(document.getElementById('category_id').options).map(option => 
                                    `<option value="${option.value}">${option.textContent}</option>`
                                ).join('')}
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_amount" class="form-label">Budget Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    `;
                    
                    // Populate form fields
                    document.getElementById('edit_category_id').value = response.budget.category_id;
                    document.getElementById('edit_amount').value = response.budget.amount;
                    document.getElementById('edit_start_date').value = response.budget.start_date;
                    document.getElementById('edit_end_date').value = response.budget.end_date;
                    
                    // Trigger category change event to update suggestions
                    const event = new Event('change');
                    document.getElementById('edit_category_id').dispatchEvent(event);
                } else {
                    // Response doesn't have the expected structure
                    console.error('Unexpected response structure:', response);
                    showNotification('Invalid budget data structure received', 'danger');
                    
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            } catch (e) {
                // Error parsing the JSON response
                console.error('Error parsing response:', e, 'Response text:', xhr.responseText);
                showNotification('Error processing server response', 'danger');
                
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        } else {
            // Non-200 status code
            console.error('Server error:', xhr.status, xhr.responseText);
            showNotification('Server error: ' + xhr.status, 'danger');
            
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('Request failed to connect');
        showNotification('Network error occurred', 'danger');
        
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    };
    
    xhr.send();
}

/**
 * Initialize budget chart
 * Creates and configures the budget vs actual chart
 */
function initializeBudgetChart() {
    const chartCanvas = document.getElementById('budgetVsActualChart');
    if (!chartCanvas) {
        console.error('Budget chart canvas not found');
        return;
    }
    
    // The chart initialization is now handled in the page_scripts section
    // This function is kept to maintain compatibility with existing code
    
    // Add a safety check for chart initialization errors
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not available. Make sure it is properly loaded.');
        const chartContainer = chartCanvas.closest('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Chart cannot be displayed: Chart.js library not available
                </div>
            `;
        }
    }
    
    console.log('Chart initialized successfully');
}

/**
 * Show notification
 * @param {string} message - Message to display
 * @param {string} type - Message type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Style the notification
    notification.style.position = 'fixed';
    notification.style.top = '1rem';
    notification.style.right = '1rem';
    notification.style.zIndex = '1050';
    notification.style.minWidth = '250px';
    notification.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
    
    // Add to document
    document.body.appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 150);
    }, duration);
}

/**
 * Calculate budget allocation
 * Suggests appropriate budget allocation based on income
 * @param {number} income - Monthly income
 * @param {string} category - Budget category
 * @returns {number} - Suggested budget amount
 */
function calculateBudgetAllocation(income, category) {
    let percentage = 0;
    
    switch(category) {
        case 'Housing':
            percentage = 0.3; // 30%
            break;
        case 'Food':
            percentage = 0.12; // 12%
            break;
        case 'Transportation':
            percentage = 0.1; // 10%
            break;
        case 'Utilities':
            percentage = 0.08; // 8%
            break;
        case 'Insurance':
            percentage = 0.1; // 10%
            break;
        case 'Healthcare':
            percentage = 0.05; // 5%
            break;
        case 'Debt Payments':
            percentage = 0.15; // 15%
            break;
        case 'Entertainment':
            percentage = 0.05; // 5%
            break;
        case 'Shopping':
            percentage = 0.05; // 5%
            break;
        case 'Personal Care':
            percentage = 0.03; // 3%
            break;
        case 'Education':
            percentage = 0.02; // 2%
            break;
        case 'Investments':
            percentage = 0.1; // 10%
            break;
        case 'Gifts & Donations':
            percentage = 0.03; // 3%
            break;
        case 'Travel':
            percentage = 0.05; // 5%
            break;
        case 'Miscellaneous':
            percentage = 0.02; // 2%
            break;
        default:
            percentage = 0.05; // 5% default
    }
    
    return income * percentage;
}