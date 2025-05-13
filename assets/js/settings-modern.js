/**
 * Modern Settings Page JavaScript
 * Handles all interactive features for the settings page
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Settings JS initialized');
    
    // Initialize tabs with direct DOM selectors
    setupTabs();
    
    // Initialize form validation
    setupFormValidation();
    
    // Set up reset settings modal
    setupResetModal();
    
    // Fix currency preview
    fixCurrencyDisplay();
});

/**
 * Simple tab navigation that doesn't rely on data attributes
 */
function setupTabs() {
    // Get all tab triggers and content sections using direct selectors
    const profileTab = document.getElementById('profile-tab');
    const securityTab = document.getElementById('security-tab');
    const preferencesTab = document.getElementById('preferences-tab');
    
    const profilePane = document.getElementById('profile');
    const securityPane = document.getElementById('security');
    const preferencesPane = document.getElementById('preferences');
    
    if (!profileTab || !securityTab || !preferencesTab || 
        !profilePane || !securityPane || !preferencesPane) {
        console.error('Tab elements not found');
        return;
    }
    
    // Helper function to activate a tab
    function activateTab(tab, pane) {
        // Deactivate all tabs
        [profileTab, securityTab, preferencesTab].forEach(t => {
            t.classList.remove('active');
        });
        
        // Hide all panes
        [profilePane, securityPane, preferencesPane].forEach(p => {
            p.style.display = 'none';
            p.classList.remove('active');
        });
        
        // Activate the selected tab
        tab.classList.add('active');
        pane.style.display = 'block';
        pane.classList.add('active');
        
        // If preferences tab is activated, ensure currency display is fixed
        if (pane === preferencesPane) {
            setTimeout(fixCurrencyDisplay, 100);
        }
    }
    
    // Set up click handlers for each tab
    profileTab.addEventListener('click', function(e) {
        e.preventDefault();
        activateTab(profileTab, profilePane);
        console.log('Profile tab activated');
    });
    
    securityTab.addEventListener('click', function(e) {
        e.preventDefault();
        activateTab(securityTab, securityPane);
        console.log('Security tab activated');
    });
    
    preferencesTab.addEventListener('click', function(e) {
        e.preventDefault();
        activateTab(preferencesTab, preferencesPane);
        console.log('Preferences tab activated');
    });
    
    // Ensure the first tab is active by default
    activateTab(profileTab, profilePane);
}

/**
 * Fix currency display elements to prevent NaN
 */
function fixCurrencyDisplay() {
    console.log('Fixing currency display');
    const previewContainer = document.querySelector('.currency-preview');
    if (!previewContainer) {
        console.log('Currency preview container not found');
        return;
    }
    
    const previewItems = previewContainer.querySelectorAll('.preview-item');
    if (!previewItems.length) {
        console.log('Preview items not found');
        return;
    }
    
    // Apply default values to ensure we never show NaN
    previewItems.forEach((item, index) => {
        const valueElement = item.querySelector('.preview-value');
        if (valueElement) {
            // Get current symbol from the first character if present
            let currentText = valueElement.textContent || '';
            let symbol = currentText.charAt(0);
            if (!/[฿$€£¥₹R]/.test(symbol)) {
                symbol = '฿'; // Default to Thai Baht
            }
            
            // Default values based on index
            let value;
            if (index === 0) {
                value = '1,000.00'; // Income
            } else if (index === 1) {
                value = '250.50';   // Expenses
            } else {
                value = '750.00';   // Budget
            }
            
            // Set the value with the symbol
            valueElement.textContent = symbol + value;
        }
    });
    
    // Set up currency selector behavior
    const currencySelect = document.getElementById('currency');
    if (currencySelect) {
        // Update when currency changes
        currencySelect.addEventListener('change', function() {
            updateCurrencySymbol(this.value);
        });
        
        // Initial update
        updateCurrencySymbol(currencySelect.value);
    }
    
    function updateCurrencySymbol(currencyCode) {
        const symbols = {
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'JPY': '¥',
            'THB': '฿',
            'CNY': '¥',
            'AUD': 'A$',
            'CAD': 'C$',
            'INR': '₹',
            'BRL': 'R$',
            'MXN': 'Mex$'
        };
        
        const symbol = symbols[currencyCode] || '฿';
        
        previewItems.forEach((item, index) => {
            const valueElement = item.querySelector('.preview-value');
            if (valueElement) {
                // Default amounts for each type
                let amount;
                if (index === 0) {
                    amount = 1000;
                } else if (index === 1) {
                    amount = 250.5;
                } else {
                    amount = 750;
                }
                
                // Format based on currency type
                let formattedValue;
                if (currencyCode === 'JPY' || currencyCode === 'CNY') {
                    // No decimals for Yen and Yuan
                    formattedValue = Math.round(amount).toLocaleString();
                } else if (currencyCode === 'EUR') {
                    // European format
                    formattedValue = amount.toLocaleString('de-DE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    valueElement.textContent = symbol + ' ' + formattedValue;
                    return;
                } else {
                    // Standard format with 2 decimal places
                    formattedValue = amount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
                
                valueElement.textContent = symbol + formattedValue;
            }
        });
    }
}

/**
 * Form validation setup
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Find invalid fields
                const invalidFields = form.querySelectorAll(':invalid');
                if (invalidFields.length > 0) {
                    // Scroll to and focus first invalid field
                    invalidFields[0].scrollIntoView({ behavior: 'smooth' });
                    setTimeout(() => {
                        invalidFields[0].focus();
                    }, 500);
                }
            } else {
                // Add loading state to submit button
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitButton.disabled = true;
                }
            }
            
            form.classList.add('was-validated');
        });
    });
    
    // Password matching validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        newPassword.addEventListener('input', function() {
            if (confirmPassword.value && this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    }
}

/**
 * Reset settings modal functionality
 */
function setupResetModal() {
    const resetButton = document.getElementById('resetSettings');
    const resetModal = document.getElementById('resetSettingsModal');
    
    if (!resetButton || !resetModal) {
        return;
    }
    
    resetButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Simple show/hide for the modal without Bootstrap dependency
        resetModal.style.display = 'block';
        resetModal.classList.add('show');
        
        // Add backdrop manually
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
        document.body.classList.add('modal-open');
        
        // Close button functionality
        const closeButtons = resetModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-secondary, .modal-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', closeModal);
        });
        
        // Close on outside click
        resetModal.addEventListener('click', function(e) {
            if (e.target === resetModal) {
                closeModal();
            }
        });
    });
    
    function closeModal() {
        resetModal.style.display = 'none';
        resetModal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        }
    }
}

// Add success message handler
window.addEventListener('load', function() {
    // Show success message if success parameter is in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success alert-dismissible fade show';
        successMessage.innerHTML = 'Settings updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        
        // Insert at top of content
        const contentContainer = document.querySelector('.settings-content');
        if (contentContainer) {
            contentContainer.insertBefore(successMessage, contentContainer.firstChild);
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                successMessage.classList.remove('show');
                setTimeout(() => {
                    if (successMessage.parentNode) {
                        successMessage.parentNode.removeChild(successMessage);
                    }
                }, 500);
            }, 3000);
        }
    }
});