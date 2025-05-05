/**
 * iGotMoney - Tax Planning JavaScript
 * Handles functionality for the tax planning page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tax form calculations
    initializeTaxCalculations();
    
    // Initialize year selection
    initializeYearSelection();
});

/**
 * Initialize tax calculations
 * Updates tax estimates based on form inputs
 */
function initializeTaxCalculations() {
    const taxForm = document.getElementById('taxInfoForm');
    if (!taxForm) return;
    
    // Form input elements
    const filingStatusEl = document.getElementById('filing_status');
    const estimatedIncomeEl = document.getElementById('estimated_income');
    const deductionsEl = document.getElementById('deductions');
    const creditsEl = document.getElementById('credits');
    const taxPaidToDateEl = document.getElementById('tax_paid_to_date');
    
    // Add input event listeners to all form fields
    [filingStatusEl, estimatedIncomeEl, deductionsEl, creditsEl, taxPaidToDateEl].forEach(el => {
        if (el) {
            el.addEventListener('change', calculateTaxEstimates);
            el.addEventListener('input', calculateTaxEstimates);
        }
    });
    
    // Initial calculation
    calculateTaxEstimates();
}

/**
 * Calculate tax estimates based on form inputs
 * Makes an AJAX request to get updated tax information
 */
function calculateTaxEstimates() {
    const filingStatus = document.getElementById('filing_status').value;
    const estimatedIncome = parseFloat(document.getElementById('estimated_income').value) || 0;
    const deductions = parseFloat(document.getElementById('deductions').value) || 0;
    const credits = parseFloat(document.getElementById('credits').value) || 0;
    const taxPaidToDate = parseFloat(document.getElementById('tax_paid_to_date').value) || 0;
    const taxYear = document.querySelector('input[name="tax_year"]').value;
    
    // Calculate taxable income
    const taxableIncome = Math.max(0, estimatedIncome - deductions);
    
    // Calculate tax based on filing status (simplified version)
    let tax = 0;
    
    if (filingStatus === 'single') {
        // 2024 tax brackets for single filers (simplified)
        if (taxableIncome <= 11000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 44725) {
            tax = 1100 + (taxableIncome - 11000) * 0.12;
        } else if (taxableIncome <= 95375) {
            tax = 5147 + (taxableIncome - 44725) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 16290 + (taxableIncome - 95375) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 37104 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 578125) {
            tax = 52832 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 174238.25 + (taxableIncome - 578125) * 0.37;
        }
    } else if (filingStatus === 'married_joint') {
        // 2024 tax brackets for married filing jointly (simplified)
        if (taxableIncome <= 22000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 89450) {
            tax = 2200 + (taxableIncome - 22000) * 0.12;
        } else if (taxableIncome <= 190750) {
            tax = 10294 + (taxableIncome - 89450) * 0.22;
        } else if (taxableIncome <= 364200) {
            tax = 32580 + (taxableIncome - 190750) * 0.24;
        } else if (taxableIncome <= 462500) {
            tax = 74208 + (taxableIncome - 364200) * 0.32;
        } else if (taxableIncome <= 693750) {
            tax = 105664 + (taxableIncome - 462500) * 0.35;
        } else {
            tax = 186601.5 + (taxableIncome - 693750) * 0.37;
        }
    } else if (filingStatus === 'married_separate') {
        // 2024 tax brackets for married filing separately (simplified)
        if (taxableIncome <= 11000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 44725) {
            tax = 1100 + (taxableIncome - 11000) * 0.12;
        } else if (taxableIncome <= 95375) {
            tax = 5147 + (taxableIncome - 44725) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 16290 + (taxableIncome - 95375) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 37104 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 346875) {
            tax = 52832 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 93300.75 + (taxableIncome - 346875) * 0.37;
        }
    } else if (filingStatus === 'head_of_household') {
        // 2024 tax brackets for head of household (simplified)
        if (taxableIncome <= 15700) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 59850) {
            tax = 1570 + (taxableIncome - 15700) * 0.12;
        } else if (taxableIncome <= 95350) {
            tax = 6868 + (taxableIncome - 59850) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 14678 + (taxableIncome - 95350) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 35498 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 578100) {
            tax = 51226 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 172623.5 + (taxableIncome - 578100) * 0.37;
        }
    }
    
    // Apply tax credits
    tax = Math.max(0, tax - credits);
    
    // Calculate remaining tax
    const remainingTax = Math.max(0, tax - taxPaidToDate);
    
    // Calculate effective tax rate
    const effectiveTaxRate = estimatedIncome > 0 ? (tax / estimatedIncome) * 100 : 0;
    
    // Update tax summary cards (would normally be done with AJAX)
    updateTaxSummaryCards(estimatedIncome, tax, remainingTax, effectiveTaxRate);
}

/**
 * Update tax summary cards with calculated values
 * @param {number} estimatedIncome - Estimated annual income
 * @param {number} taxLiability - Calculated tax liability
 * @param {number} remainingTax - Remaining tax owed
 * @param {number} effectiveTaxRate - Effective tax rate percentage
 */
function updateTaxSummaryCards(estimatedIncome, taxLiability, remainingTax, effectiveTaxRate) {
    // This function would update the UI with the calculated tax values
    // For demonstration purposes, we're just logging the values
    console.log({
        estimatedIncome: formatCurrency(estimatedIncome),
        taxLiability: formatCurrency(taxLiability),
        remainingTax: formatCurrency(remainingTax),
        effectiveTaxRate: effectiveTaxRate.toFixed(2) + '%'
    });
    
    // In a real implementation, we would update the DOM elements
    const cards = document.querySelectorAll('.dashboard-card .h5');
    if (cards.length >= 4) {
        cards[0].textContent = formatCurrency(estimatedIncome);
        cards[1].textContent = formatCurrency(taxLiability);
        cards[2].textContent = formatCurrency(remainingTax);
        cards[3].textContent = effectiveTaxRate.toFixed(2) + '%';
    }
    
    // Update chart if it exists
    updateTaxChart(estimatedIncome, taxLiability);
}

/**
 * Update tax breakdown chart with new values
 * @param {number} estimatedIncome - Estimated annual income
 * @param {number} taxLiability - Calculated tax liability
 */
function updateTaxChart(estimatedIncome, taxLiability) {
    // Check if chart exists in the global scope
    if (typeof taxBreakdownChart !== 'undefined') {
        // Calculate net income
        const netIncome = estimatedIncome - taxLiability;
        
        // Update chart data
        taxBreakdownChart.data.datasets[0].data = [netIncome, taxLiability];
        
        // Update chart
        taxBreakdownChart.update();
    }
}

/**
 * Initialize year selection
 * Handles year dropdown selection
 */
function initializeYearSelection() {
    const yearDropdown = document.getElementById('yearDropdown');
    if (!yearDropdown) return;
    
    const yearItems = document.querySelectorAll('.dropdown-item');
    yearItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.getAttribute('href');
        });
    });
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}