/**
 * Direct Form Handler
 * 
 * Handles form submissions via AJAX to prevent page reloads
 * Enhances user experience by providing immediate feedback
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeDirectForms();
});

/**
 * Initialize direct form handling
 */
function initializeDirectForms() {
    // These are the forms we want to handle with AJAX
    const formSelectors = [
        '#addExpenseForm',
        '#editExpenseForm',
        '#deleteExpenseForm'
    ];
    
    formSelectors.forEach(selector => {
        const form = document.querySelector(selector);
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }
    });
}

/**
 * Handle form submission via AJAX
 */
function handleFormSubmit(event) {
    // Prevent default form submission
    event.preventDefault();
    
    const form = event.target;
    
    // Check form validity
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Get form data
    const formData = new FormData(form);
    
    // Get form submission URL
    const submitUrl = form.getAttribute('action');
    
    // Get the base path
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    
    // Determine form action type
    const actionType = formData.get('action'); // 'add', 'edit', or 'delete'
    
    // Send AJAX request
    fetch(submitUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .catch(() => {
        // If not JSON, it's probably a redirect
        return { success: true, message: 'Operation completed' };
    })
    .then(data => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        if (data.success) {
            // Show success message
            showFormNotification(getSuccessMessage(actionType), 'success');
            
            // Close modal if open
            closeModalIfOpen(form.closest('.modal'));
            
            // Reset form
            form.reset();
            form.classList.remove('was-validated');
            
            // Reload data after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error message
            showFormNotification(data.message || 'An error occurred', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        // Show error message
        showFormNotification('An error occurred while processing your request', 'danger');
    });
}

/**
 * Close modal if form is inside one
 */
function closeModalIfOpen(modalElement) {
    if (modalElement && bootstrap) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }
}

/**
 * Get success message based on action type
 */
function getSuccessMessage(actionType) {
    switch (actionType) {
        case 'add':
            return 'Expense added successfully!';
        case 'edit':
            return 'Expense updated successfully!';
        case 'delete':
            return 'Expense deleted successfully!';
        default:
            return 'Operation completed successfully!';
    }
}

/**
 * Show notification for form operations
 */
function showFormNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show direct-form-notification`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.left = '50%';
    notification.style.transform = 'translateX(-50%) translateY(-100px)';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    notification.style.borderRadius = '0.5rem';
    notification.style.transition = 'transform 0.3s ease';
    
    // Add content
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(0)';
    }, 10);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(-100px)';
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}