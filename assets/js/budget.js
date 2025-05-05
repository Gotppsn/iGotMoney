/**
 * iGotMoney - Budget JavaScript
 * Handles functionality for the budget management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize budget date validation
    initializeDateValidation();
    
    // Initialize budget category selection
    initializeCategorySelection();
    
    // Initialize budget recommendations
    initializeBudgetRecommendations();
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
        alert('End date must be after start date.');
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
            
            // Submit form is handled in the main script
        }
    });
}

/**
 * Highlight budget recommendations
 * Highlight recommendations that differ from current budgets
 */
function highlightRecommendations() {
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
            const budget = parseFloat(budgetCell.textContent.replace('$', '').replace(',', ''));
            
            currentBudgets[category] = budget;
        }
    });
    
    // Check recommendations against current budgets
    recommendationRows.forEach(row => {
        const categoryCell = row.querySelector('td:first-child');
        const recommendedCell = row.querySelector('td:nth-child(2)');
        
        if (categoryCell && recommendedCell) {
            const category = categoryCell.textContent.trim();
            const recommended = parseFloat(recommendedCell.textContent.replace('$', '').replace(',', ''));
            
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
                    new bootstrap.Tooltip(categoryCell.querySelector('[data-bs-toggle="tooltip"]'));
                }
            }
        }
    });
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