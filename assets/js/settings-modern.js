/**
 * Modern Settings Page JavaScript
 * Handles all interactive features for the settings page
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Settings JS initialized');
    
    // Initialize tab navigation
    initializeTabNavigation();
    
    // Initialize other components
    initializeFormValidation();
    initializeResetSettings();
    initializeCurrencyPreview();
});

/**
 * Tab navigation initialization
 * Direct implementation without Bootstrap dependency
 */
function initializeTabNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    // Ensure first tab is visible by default
    if (tabPanes.length > 0) {
        // First hide all panes
        tabPanes.forEach(pane => {
            pane.style.display = 'none';
            pane.classList.remove('active');
        });
        
        // Show the first one
        tabPanes[0].style.display = 'block';
        tabPanes[0].classList.add('active');
        
        // Make sure first nav link is active
        if (navLinks.length > 0) {
            navLinks[0].classList.add('active');
        }
    }
    
    // Add click handlers to all tab links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get target tab
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            
            if (!targetPane) {
                console.error('Target pane not found:', targetId);
                return;
            }
            
            // Update active state for links
            navLinks.forEach(nav => {
                nav.classList.remove('active');
            });
            this.classList.add('active');
            
            // Hide all panes
            tabPanes.forEach(pane => {
                pane.style.display = 'none';
                pane.classList.remove('active');
            });
            
            // Show the target pane
            targetPane.style.display = 'block';
            targetPane.classList.add('active');
            targetPane.style.animation = 'fadeIn 0.3s ease both';
            
            console.log('Tab activated:', targetId);
        });
    });
}

/**
 * Form validation with visual feedback
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Find and highlight invalid fields
                const invalidFields = form.querySelectorAll(':invalid');
                if (invalidFields.length > 0) {
                    // Scroll to first invalid field
                    invalidFields[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    
                    // Focus the first invalid field
                    setTimeout(() => {
                        invalidFields[0].focus();
                    }, 500);
                    
                    // Add shake animation
                    invalidFields.forEach(field => {
                        field.classList.add('shake');
                        setTimeout(() => {
                            field.classList.remove('shake');
                        }, 600);
                    });
                }
            } else {
                // Add loading state to submit button
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    const originalContent = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitButton.disabled = true;
                    
                    // Reset button after 3s (failsafe)
                    setTimeout(() => {
                        submitButton.innerHTML = originalContent;
                        submitButton.disabled = false;
                    }, 3000);
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Password matching validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        // Check on confirm password input
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Check when primary password changes
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
 * Reset settings functionality with modal confirmation
 */
function initializeResetSettings() {
    const resetButton = document.getElementById('resetSettings');
    const resetModal = document.getElementById('resetSettingsModal');
    
    if (resetButton && resetModal) {
        resetButton.addEventListener('click', function() {
            // Try to use Bootstrap modal if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(resetModal);
                modal.show();
            } else {
                // Manual fallback if Bootstrap JS isn't available
                resetModal.style.display = 'block';
                resetModal.classList.add('show');
                document.body.classList.add('modal-open');
                
                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
                
                // Close modal handlers
                const closeButtons = resetModal.querySelectorAll('[data-bs-dismiss="modal"], .modal-close, .btn-secondary');
                closeButtons.forEach(button => {
                    button.addEventListener('click', closeModal);
                });
                
                // Close on modal background click
                resetModal.addEventListener('click', function(e) {
                    if (e.target === resetModal) {
                        closeModal();
                    }
                });
                
                function closeModal() {
                    resetModal.style.display = 'none';
                    resetModal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    
                    // Remove backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        document.body.removeChild(backdrop);
                    }
                }
            }
        });
    }
}

/**
 * Currency preview functionality
 */
function initializeCurrencyPreview() {
    const currencySelect = document.getElementById('currency');
    const previewValues = document.querySelectorAll('.preview-value');
    
    if (currencySelect && previewValues.length) {
        // Update on change
        currencySelect.addEventListener('change', function() {
            updateCurrencySymbol(this.value);
        });
        
        function updateCurrencySymbol(currencyCode) {
            const symbols = {
                'USD': '$',
                'EUR': '€',
                'GBP': '£',
                'JPY': '¥',
                'CAD': 'C$',
                'AUD': 'A$',
                'CNY': '¥',
                'INR': '₹',
                'BRL': 'R$',
                'MXN': 'Mex$',
                'THB': '฿'
            };
            
            const symbol = symbols[currencyCode] || '$';
            
            // Update all preview values with new currency symbol
            previewValues.forEach(element => {
                const value = element.textContent.replace(/^[^\d]*/, '');
                element.textContent = `${symbol}${value}`;
            });
        }
    }
}

// Add animation and utility styles
const styleElement = document.createElement('style');
styleElement.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }
    
    .shake {
        animation: shake 0.5s ease-in-out;
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .nav-link {
        position: relative;
        z-index: 10;
    }
    
    .tab-pane.active {
        display: block !important;
    }
`;
document.head.appendChild(styleElement);

// Success message toast notification
function showSuccessMessage(message = 'Settings saved successfully!') {
    const notification = document.createElement('div');
    notification.className = 'success-notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--success-color, #10b981);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove notification after a delay
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Check for success parameter in URL and show success message
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success')) {
    showSuccessMessage();
}