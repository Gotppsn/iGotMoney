/**
 * iGotMoney - Form Submission Utilities
 * Handles AJAX form submissions and prevents form issues
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AJAX form submissions
    initializeAjaxForms();
});

/**
 * Initialize AJAX form submissions for modal forms
 */
function initializeAjaxForms() {
    const forms = document.querySelectorAll('form[data-ajax="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            // Prevent default form submission
            event.preventDefault();
            
            // Check if form is valid
            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return;
            }
            
            // Get submit button
            const submitButton = this.querySelector('button[type="submit"]');
            if (!submitButton) return;
            
            // Show loading state
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Create FormData
            const formData = new FormData(this);
            
            // Get the correct form action URL - ensure it has the proper path
            const actionUrl = this.getAttribute('action');
            console.log('Submitting form to:', actionUrl);
            
            // Make AJAX request
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // Handle response
                if (data.success) {
                    // Show success message
                    showNotification(data.message || 'Operation completed successfully', 'success');
                    
                    // Close modal if exists
                    const modal = this.closest('.modal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                    
                    // Reload page after delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    showNotification(data.message || 'An error occurred', 'danger');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // Show error message
                showNotification('A network error occurred. Please try again: ' + error.message, 'danger');
            });
        });
    });
}

/**
 * Show notification
 * @param {string} message - Message to display
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Check if notification container exists
    let container = document.getElementById('notification-container');
    
    if (!container) {
        // Create container
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.width = '300px';
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.marginBottom = '10px';
    notification.style.boxShadow = '0 0.25rem 0.75rem rgba(0, 0, 0, 0.1)';
    
    // Add content
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    try {
        // Initialize Bootstrap alert (safely)
        const bsAlert = new bootstrap.Alert(notification);
        
        // Auto close after duration
        setTimeout(() => {
            try {
                bsAlert.close();
            } catch (e) {
                // Fallback if bootstrap alert fails
                notification.remove();
            }
        }, duration);
        
        // Remove from DOM after closing animation
        notification.addEventListener('closed.bs.alert', () => {
            notification.remove();
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        });
    } catch (e) {
        console.error('Error initializing Bootstrap alert:', e);
        
        // Fallback if bootstrap alert fails
        setTimeout(() => {
            notification.remove();
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        }, duration);
    }
}