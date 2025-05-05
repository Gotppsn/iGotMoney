/**
 * iGotMoney - Income JavaScript
 * Handles functionality for the income management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize frequency based calculation
    initializeFrequencyCalculation();
    
    // Initialize date validation
    initializeDateValidation();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize income summary calculation
    initializeIncomeSummary();
});

/**
 * Initialize frequency based calculation
 * Updates amount label based on frequency selection
 */
function initializeFrequencyCalculation() {
    const frequencySelects = document.querySelectorAll('#frequency, #edit_frequency');
    
    frequencySelects.forEach(select => {
        select.addEventListener('change', function() {
            updateAmountLabel(this);
            updateIncomePreview(this.closest('form'));
        });
        
        // Initialize with current value
        updateAmountLabel(select);
    });
    
    // Add event listeners to amount inputs
    const amountInputs = document.querySelectorAll('#amount, #edit_amount');
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateIncomePreview(this.closest('form'));
        });
    });
}

/**
 * Update amount label based on frequency
 * @param {Element} frequencySelect - The frequency select element
 */
function updateAmountLabel(frequencySelect) {
    const frequency = frequencySelect.value;
    const isAddForm = frequencySelect.id === 'frequency';
    const amountLabel = document.querySelector(`label[for="${isAddForm ? 'amount' : 'edit_amount'}"]`);
    
    if (!amountLabel) return;
    
    let labelText = 'Amount';
    
    switch (frequency) {
        case 'daily':
            labelText = 'Daily Amount';
            break;
        case 'weekly':
            labelText = 'Weekly Amount';
            break;
        case 'bi-weekly':
            labelText = 'Bi-Weekly Amount';
            break;
        case 'monthly':
            labelText = 'Monthly Amount';
            break;
        case 'quarterly':
            labelText = 'Quarterly Amount';
            break;
        case 'annually':
            labelText = 'Annual Amount';
            break;
        case 'one-time':
            labelText = 'One-Time Amount';
            break;
    }
    
    amountLabel.textContent = labelText;
}

/**
 * Update income preview when amount or frequency changes
 * @param {Element} form - The form element
 */
function updateIncomePreview(form) {
    if (!form) return;
    
    const amount = parseFloat(form.querySelector('input[name="amount"]').value) || 0;
    const frequency = form.querySelector('select[name="frequency"]').value;
    
    // Calculate monthly and annual equivalents
    const monthlyEquivalent = calculateMonthlyEquivalent(amount, frequency);
    const annualEquivalent = calculateAnnualEquivalent(amount, frequency);
    
    // Create or update preview element
    let previewElement = form.querySelector('.income-preview');
    if (!previewElement) {
        previewElement = document.createElement('div');
        previewElement.className = 'income-preview mt-3 alert alert-info';
        const amountInput = form.querySelector('input[name="amount"]');
        amountInput.parentNode.parentNode.insertAdjacentElement('afterend', previewElement);
    }
    
    if (amount > 0) {
        previewElement.innerHTML = `
            <div class="d-flex justify-content-between">
                <span>Monthly equivalent:</span>
                <strong>$${monthlyEquivalent.toFixed(2)}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>Annual equivalent:</span>
                <strong>$${annualEquivalent.toFixed(2)}</strong>
            </div>
        `;
        previewElement.style.display = 'block';
    } else {
        previewElement.style.display = 'none';
    }
}

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
    if (!startDateInput || !endDateInput || !endDateInput.value) return;
    
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (endDate < startDate) {
        alert('End date must be after start date.');
        endDateInput.value = '';
    }
}

/**
 * Initialize form validation
 * Validates form inputs before submission
 */
function initializeFormValidation() {
    const addForm = document.querySelector('#addIncomeModal form');
    const editForm = document.querySelector('#editIncomeModal form');
    
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            if (!validateIncomeForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            if (!validateIncomeForm(this)) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Validate income form
 * @param {Element} form - The form to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateIncomeForm(form) {
    const nameInput = form.querySelector('input[name="name"]');
    const amountInput = form.querySelector('input[name="amount"]');
    const startDateInput = form.querySelector('input[name="start_date"]');
    
    let isValid = true;
    let errorMessage = '';
    
    // Reset previous error messages
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Validate name
    if (!nameInput.value.trim()) {
        nameInput.classList.add('is-invalid');
        errorMessage = 'Income name is required.';
        isValid = false;
    }
    
    // Validate amount
    if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
        amountInput.classList.add('is-invalid');
        errorMessage = errorMessage || 'Please enter a valid amount greater than zero.';
        isValid = false;
    }
    
    // Validate start date
    if (!startDateInput.value) {
        startDateInput.classList.add('is-invalid');
        errorMessage = errorMessage || 'Start date is required.';
        isValid = false;
    }
    
    if (!isValid && errorMessage) {
        // Show error message
        alert(errorMessage);
    }
    
    return isValid;
}

/**
 * Initialize income summary visualization
 * Adds visual elements to highlight income statistics
 */
function initializeIncomeSummary() {
    const summaryCards = document.querySelectorAll('.dashboard-card.income');
    
    summaryCards.forEach(card => {
        // Get the income value
        const incomeText = card.querySelector('.h5').textContent;
        const incomeValue = parseFloat(incomeText.replace('$', '').replace(/,/g, ''));
        
        // Add a trend indicator if not already present
        if (!card.querySelector('.trend-indicator') && !isNaN(incomeValue)) {
            const trendIndicator = document.createElement('div');
            trendIndicator.className = 'trend-indicator small mt-2';
            
            if (incomeValue > 0) {
                trendIndicator.innerHTML = `
                    <span class="text-success">
                        <i class="fas fa-arrow-up me-1"></i>Active Income
                    </span>
                `;
            } else {
                trendIndicator.innerHTML = `
                    <span class="text-muted">
                        <i class="fas fa-minus me-1"></i>No Income
                    </span>
                `;
            }
            
            card.querySelector('.row').appendChild(trendIndicator);
        }
    });
}

/**
 * Calculate monthly equivalent based on frequency
 * @param {number} amount - The amount
 * @param {string} frequency - The frequency
 * @returns {number} - Monthly equivalent amount
 */
function calculateMonthlyEquivalent(amount, frequency) {
    switch (frequency) {
        case 'daily':
            return amount * 30; // Approximate days in a month
        case 'weekly':
            return amount * 4.33; // Approximate weeks in a month
        case 'bi-weekly':
            return amount * 2.17; // Approximate bi-weeks in a month
        case 'monthly':
            return amount;
        case 'quarterly':
            return amount / 3;
        case 'annually':
            return amount / 12;
        case 'one-time':
            return amount; // One-time income is counted in full for the month it occurs
        default:
            return amount;
    }
}

/**
 * Calculate annual equivalent based on frequency
 * @param {number} amount - The amount
 * @param {string} frequency - The frequency
 * @returns {number} - Annual equivalent amount
 */
function calculateAnnualEquivalent(amount, frequency) {
    switch (frequency) {
        case 'daily':
            return amount * 365;
        case 'weekly':
            return amount * 52;
        case 'bi-weekly':
            return amount * 26;
        case 'monthly':
            return amount * 12;
        case 'quarterly':
            return amount * 4;
        case 'annually':
            return amount;
        case 'one-time':
            return amount; // One-time income is counted in full for the year it occurs
        default:
            return amount;
    }
}

/**
 * Helper function to show a spinner
 * @param {Element} container - The container to add the spinner to
 */
function showSpinner(container) {
    // Create spinner element
    const spinner = document.createElement('div');
    spinner.className = 'text-center py-3';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    
    // Clear container and add spinner
    container.innerHTML = '';
    container.appendChild(spinner);
}

/**
 * Helper function to hide a spinner
 * @param {Element} container - The container with the spinner
 */
function hideSpinner(container) {
    // Find and remove spinner
    const spinner = container.querySelector('.spinner-border');
    if (spinner) {
        spinner.parentNode.remove();
    }
}