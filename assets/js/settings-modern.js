document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Settings JS loaded');
    
    // Initialize all components
    initializeTabNavigation();
    initializeRangeSlider();
    initializeFormValidation();
    initializeResetSettings();
    initializeToggles();
    initializeAnimations();
});

function initializeTabNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the target tab
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            
            if (!targetPane) return;
            
            // Remove active classes
            navLinks.forEach(nav => nav.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active classes
            this.classList.add('active');
            targetPane.classList.add('active');
            
            // Add animation
            targetPane.style.animation = 'fadeIn 0.3s ease both';
        });
    });
}

function initializeRangeSlider() {
    const rangeInput = document.getElementById('budget_alert_threshold');
    const rangeValue = document.getElementById('threshold_value');
    
    if (rangeInput && rangeValue) {
        // Update value display on input
        rangeInput.addEventListener('input', function() {
            rangeValue.textContent = this.value + '%';
            
            // Add visual feedback
            const percent = (this.value - this.min) / (this.max - this.min);
            const hue = percent * 120; // 0 (red) to 120 (green)
            rangeValue.style.color = `hsl(${hue}, 70%, 50%)`;
        });
        
        // Initialize the color on load
        const percent = (rangeInput.value - rangeInput.min) / (rangeInput.max - rangeInput.min);
        const hue = percent * 120;
        rangeValue.style.color = `hsl(${hue}, 70%, 50%)`;
    }
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Add shake animation to invalid fields
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                });
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Real-time validation for password fields
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

function initializeResetSettings() {
    const resetButton = document.getElementById('resetSettings');
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('resetSettingsModal'));
            modal.show();
        });
    }
}

function initializeToggles() {
    // Initialize all toggle switches
    const toggleFields = document.querySelectorAll('.toggle-field');
    
    toggleFields.forEach(field => {
        const input = field.querySelector('input[type="checkbox"]');
        const slider = field.querySelector('.toggle-slider');
        
        // Add click handler to the entire field
        field.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                input.checked = !input.checked;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        // Add visual feedback
        input.addEventListener('change', function() {
            if (this.checked) {
                slider.style.backgroundColor = 'var(--primary-color)';
            } else {
                slider.style.backgroundColor = 'var(--gray-300)';
            }
        });
    });
    
    // Handle disabled checkboxes
    const disabledCheckboxes = document.querySelectorAll('.disabled-checkbox input');
    disabledCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.preventDefault();
            showTooltip(this, 'This setting is always enabled');
        });
    });
}

function initializeAnimations() {
    // Add smooth scrolling for navigation
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('data-bs-target');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offset = targetElement.offsetTop - 100;
                window.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add animation to cards when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.settings-card').forEach(card => {
        observer.observe(card);
    });
}

function showTooltip(element, message) {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = message;
    tooltip.style.cssText = `
        position: absolute;
        background: var(--gray-800);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position the tooltip
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    tooltip.style.left = `${rect.left + (rect.width - tooltipRect.width) / 2}px`;
    tooltip.style.top = `${rect.top - tooltipRect.height - 8}px`;
    
    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
    
    // Remove tooltip after delay
    setTimeout(() => {
        tooltip.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(tooltip);
        }, 200);
    }, 2000);
}

// Add shake animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }
    
    .shake {
        animation: shake 0.5s ease-in-out;
    }
    
    .animate-in {
        animation: fadeIn 0.6s ease both;
    }
`;
document.head.appendChild(style);

// Handle form submissions with loading states
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        
        if (submitButton) {
            const originalContent = submitButton.innerHTML;
            
            // Add loading state
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitButton.disabled = true;
            
            // Reset button after form submission (this is a fallback)
            setTimeout(() => {
                submitButton.innerHTML = originalContent;
                submitButton.disabled = false;
            }, 3000);
        }
    });
});

// Add visual feedback for successful saves
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
        background: var(--success-color);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius-sm);
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Hide and remove notification
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add this to handle successful form submissions
if (window.location.search.includes('success=1')) {
    showSuccessMessage();
}