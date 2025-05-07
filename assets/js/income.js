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
                document.getElementById('edit_end_date').value = data.income.end_date || '';
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