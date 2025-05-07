/**
 * iGotMoney - Tax Planning JavaScript
 * Handles functionality for the tax planning page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tax form calculations
    initializeTaxCalculations();
    
    // Initialize year selection
    initializeYearSelection();
    
    // Initialize animations
    initializeAnimations();
    
    // Check for auto-filled success message
    checkAutoFillSuccess();
    
    // Add event listeners for form submission
    setupFormSubmission();
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
    
    // Update tax summary cards
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
    // Get the card value elements
    const cards = document.querySelectorAll('.summary-card-value');
    
    if (cards.length >= 4) {
        // Apply a subtle animation to highlight the changes
        cards.forEach(card => {
            card.classList.add('updating');
            setTimeout(() => {
                card.classList.remove('updating');
            }, 500);
        });
        
        // Update the values with formatted currency
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
        
        // Apply a smooth animation to the chart update
        taxBreakdownChart.options.animation = {
            duration: 800,
            easing: 'easeOutQuart'
        };
        
        // Update chart
        taxBreakdownChart.update();
    }
}

/**
 * Initialize year selection
 * Handles year dropdown selection
 */
function initializeYearSelection() {
    const yearItems = document.querySelectorAll('.dropdown-item');
    yearItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.getAttribute('href');
        });
    });
}

/**
 * Initialize animations
 * Adds subtle animations to the UI elements
 */
function initializeAnimations() {
    // Add staggered fade-in effect to summary cards
    const summaryCards = document.querySelectorAll('.tax-summary-card');
    summaryCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add pulse animation to the Auto-Fill button
    const autoFillBtn = document.getElementById('autoFillTaxInfo');
    if (autoFillBtn) {
        setTimeout(() => {
            autoFillBtn.classList.add('pulse-animation');
            setTimeout(() => {
                autoFillBtn.classList.remove('pulse-animation');
            }, 1500);
        }, 1000);
    }
    
    // Add smooth transition to form inputs
    const formInputs = document.querySelectorAll('.form-control, .form-select');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.closest('.mb-3').classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.closest('.mb-3').classList.remove('focused');
        });
    });
}

/**
 * Setup form submission for loading states
 */
function setupFormSubmission() {
    // Main tax info form submission
    const taxInfoForm = document.getElementById('taxInfoForm');
    if (taxInfoForm) {
        taxInfoForm.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                
                // Reset button state after 30 seconds (failsafe)
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 30000);
            }
        });
    }
    
    // Delete form submission
    const deleteForm = document.getElementById('deleteTaxInfoForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function() {
            const submitButton = document.querySelector('button[form="deleteTaxInfoForm"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                
                // Reset button state after 30 seconds (failsafe)
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 30000);
            }
        });
    }
    
    // Delete tax info button
    const deleteButton = document.getElementById('deleteTaxInfo');
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('deleteTaxId').value = this.getAttribute('data-tax-id');
            var modal = new bootstrap.Modal(document.getElementById('deleteTaxInfoModal'));
            modal.show();
        });
    }
    
    // Scroll to form button
    const scrollToFormBtn = document.getElementById('scrollToForm');
    if (scrollToFormBtn) {
        scrollToFormBtn.addEventListener('click', function() {
            document.querySelector('.tax-form-card').scrollIntoView({ behavior: 'smooth' });
        });
    }
}

/**
 * Handle auto-fill buttons
 */
function setupAutoFillButtons() {
    // Main auto-fill button
    const autoFillBtn = document.getElementById('autoFillTaxInfo');
    if (autoFillBtn) {
        autoFillBtn.addEventListener('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
            modal.show();
        });
    }
    
    // Empty state auto-fill button
    const emptyAutoFillBtn = document.getElementById('autoFillTaxInfoEmpty');
    if (emptyAutoFillBtn) {
        emptyAutoFillBtn.addEventListener('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
            modal.show();
        });
    }
    
    // Auto-fill form submission
    const autoFillForm = document.getElementById('autoFillForm');
    if (autoFillForm) {
        autoFillForm.addEventListener('submit', function() {
            // Show loading state
            const submitButton = document.querySelector('button[form="autoFillForm"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Auto-Filling...';
                
                // Reset button state after 30 seconds (failsafe)
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 30000);
            }
        });
    }
}

// Initialize auto-fill functionality
setupAutoFillButtons();

/**
 * Check for auto-fill success message in URL parameters
 * Shows toast notification if auto-fill was successful
 */
function checkAutoFillSuccess() {
    // Parse URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    const errorParam = urlParams.get('error');
    
    // If we have a success message from auto-fill, highlight the form
    if (successParam && successParam.includes('auto-filled')) {
        // Highlight summary cards with success animation
        document.querySelectorAll('.summary-card-value').forEach(card => {
            card.classList.add('updating');
            setTimeout(() => {
                card.classList.remove('updating');
            }, 1500);
        });
        
        // If chart exists, add highlight animation
        if (typeof taxBreakdownChart !== 'undefined') {
            taxBreakdownChart.options.animation = {
                duration: 1200,
                easing: 'easeOutBounce'
            };
            taxBreakdownChart.update();
        }
        
        // Clean URL by removing success/error parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.toString());
    }
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}