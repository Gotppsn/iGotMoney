/**
 * iGotMoney - Income JavaScript
 * Handles functionality for the income management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize frequency based calculation
    initializeFrequencyCalculation();
    
    // Initialize date validation
    initializeDateValidation();
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
        });
        
        // Initialize with current value
        updateAmountLabel(select);
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