/**
 * iGotMoney - Income Management JavaScript
 * Handles all income page functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize event handlers
    initializeEventHandlers();
    
    // Add animation to cards
    animateElements();
});

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Fetch all forms that need validation
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop through forms and prevent submission if invalid
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Initialize event handlers
 */
function initializeEventHandlers() {
    // Search functionality
    const searchInput = document.getElementById('incomeSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
    
    // Edit income buttons - Direct inline handlers for compatibility
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-income')) {
            const button = e.target.closest('.edit-income');
            const incomeId = button.getAttribute('data-income-id');
            handleEditIncome(incomeId);
        }
        
        if (e.target.closest('.delete-income')) {
            const button = e.target.closest('.delete-income');
            const incomeId = button.getAttribute('data-income-id');
            handleDeleteIncome(incomeId);
        }
    });
    
    // Modal focus handling
    const addIncomeModal = document.getElementById('addIncomeModal');
    if (addIncomeModal) {
        addIncomeModal.addEventListener('shown.bs.modal', function() {
            document.getElementById('name').focus();
        });
    }
    
    // Handle end date input changes with debugging
    const endDateInputs = document.querySelectorAll('#end_date, #edit_end_date');
    endDateInputs.forEach(input => {
        input.addEventListener('change', function() {
            console.log('End date changed:', this.value);
            
            // Allow empty values for optional end date
            if (!this.value) {
                console.log('End date cleared');
                return;
            }
            
            // Validate the date
            const selectedDate = new Date(this.value);
            if (isNaN(selectedDate.getTime())) {
                console.log('Invalid date format');
                this.value = '';
                return;
            }
            
            // Check if date is reasonable (not before 1900)
            if (selectedDate < new Date('1900-01-01')) {
                console.log('Date too far in the past');
                alert('Please select a valid date after January 1, 1900');
                this.value = '';
                return;
            }
            
            console.log('Valid end date:', this.value);
        });
    });
}

/**
 * Handle search functionality
 */
function handleSearch() {
    const searchTerm = this.value.toLowerCase();
    const tableId = this.getAttribute('data-table-search');
    const table = document.getElementById(tableId);
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let hasVisibleRows = false;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const shouldShow = text.includes(searchTerm);
        
        if (shouldShow) {
            row.style.display = '';
            hasVisibleRows = true;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show or hide empty state message
    const cardBody = table.closest('.card-body');
    let emptyStateMsg = cardBody.querySelector('.empty-search-message');
    
    if (!hasVisibleRows && searchTerm !== '') {
        if (!emptyStateMsg) {
            emptyStateMsg = document.createElement('div');
            emptyStateMsg.className = 'text-center py-4 empty-search-message';
            emptyStateMsg.innerHTML = `
                <div class="text-muted">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <p>No results found for "${searchTerm}"</p>
                </div>
            `;
            cardBody.appendChild(emptyStateMsg);
        } else {
            emptyStateMsg.style.display = '';
            emptyStateMsg.querySelector('p').innerText = `No results found for "${searchTerm}"`;
        }
    } else if (emptyStateMsg) {
        emptyStateMsg.style.display = 'none';
    }
}

/**
 * Handle edit income button click
 * @param {string} incomeId - The ID of the income to edit
 */
function handleEditIncome(incomeId) {
    // Set income ID in edit form
    document.getElementById('edit_income_id').value = incomeId;
    
    // Get base path for API calls
    const basePath = document.querySelector('meta[name="base-path"]')?.getAttribute('content') || '';
    
    // Show loading spinner in modal body
    const editModal = document.getElementById('editIncomeModal');
    const modalBody = editModal.querySelector('.modal-body');
    const originalContent = modalBody.innerHTML;
    
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading income data...</p>
        </div>
    `;
    
    // Show modal
    const bsModal = new bootstrap.Modal(editModal);
    bsModal.show();
    
    // Fetch income data
    fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Restore original content
                modalBody.innerHTML = originalContent;
                
                // Populate form fields
                document.getElementById('edit_income_id').value = data.income.income_id;
                document.getElementById('edit_name').value = data.income.name;
                document.getElementById('edit_amount').value = data.income.amount;
                document.getElementById('edit_frequency').value = data.income.frequency;
                document.getElementById('edit_start_date').value = data.income.start_date;
                
                // Handle end date properly
                const endDateInput = document.getElementById('edit_end_date');
                if (data.income.end_date && data.income.end_date !== '0000-00-00' && data.income.end_date !== null) {
                    // Validate the date
                    const parsedDate = new Date(data.income.end_date);
                    if (!isNaN(parsedDate.getTime()) && parsedDate > new Date('1900-01-01')) {
                        endDateInput.value = data.income.end_date;
                    } else {
                        endDateInput.value = '';
                    }
                } else {
                    endDateInput.value = '';
                }
                
                document.getElementById('edit_is_active').checked = data.income.is_active == 1;
            } else {
                // Show error message in modal
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Failed to load income data: ${data.message}
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching income data:', error);
            
            // Show error message in modal
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    An error occurred while loading income data. Please try again.
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            `;
        });
}

/**
 * Handle delete income button click
 * @param {string} incomeId - The ID of the income to delete
 */
function handleDeleteIncome(incomeId) {
    // Set income ID in delete form
    document.getElementById('delete_income_id').value = incomeId;
    
    // Show modal
    const deleteModal = document.getElementById('deleteIncomeModal');
    const bsModal = new bootstrap.Modal(deleteModal);
    bsModal.show();
}

/**
 * Animate elements on page load
 */
function animateElements() {
    // Add staggered animation to table rows
    const tableRows = document.querySelectorAll('.income-table tbody tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        row.style.transitionDelay = `${index * 0.05}s`;
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 100);
    });
    
    // Add animation to summary cards
    const cards = document.querySelectorAll('.income-summary-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.transitionDelay = `${index * 0.1}s`;
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
}

/**
 * Format number as currency
 * @param {number} value - The value to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

/**
 * Validate date input
 * @param {string} dateString - The date string to validate
 * @returns {boolean} Whether the date is valid
 */
function isValidDate(dateString) {
    if (!dateString) return true; // Empty is valid for optional fields
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return false;
    
    // Check if date is reasonable (not before 1900 or too far in future)
    const year = date.getFullYear();
    if (year < 1900 || year > 2100) return false;
    
    return true;
}

/**
 * Clear form validation
 * @param {HTMLFormElement} form - The form to clear validation for
 */
function clearFormValidation(form) {
    form.classList.remove('was-validated');
    const inputs = form.querySelectorAll('.is-invalid');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });
}