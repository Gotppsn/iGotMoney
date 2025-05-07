/**
 * iGotMoney - Investments Page JavaScript
 * Enhanced investments functionality with modern interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeInvestmentForms();
    initializeDataTables();
    initializeSearchFilter();
    initializeInvestmentActions();
    initializeTooltips();
    animateElements();
});

/**
 * Get base path from meta tag
 */
function getBasePath() {
    const metaTag = document.querySelector('meta[name="base-path"]');
    return metaTag ? metaTag.content : '';
}

/**
 * Initialize investment forms with validation and calculations
 */
function initializeInvestmentForms() {
    // Add investment form calculator
    initInvestmentCalculator('purchase_price', 'quantity', 'current_price', 'investment-calculator');
    
    // Form validation for add investment
    const addForm = document.querySelector('#addInvestmentModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
    
    // Form validation for edit investment
    const editForm = document.querySelector('#editInvestmentModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
}

/**
 * Initialize the investment calculator for form
 */
function initInvestmentCalculator(priceId, quantityId, currentPriceId, calculatorId) {
    const purchasePrice = document.getElementById(priceId);
    const quantity = document.getElementById(quantityId);
    const currentPrice = document.getElementById(currentPriceId);
    const calculator = document.getElementById(calculatorId);
    
    if (!purchasePrice || !quantity || !currentPrice || !calculator) return;
    
    const initialInvestment = calculator.querySelector('#initial-investment') || calculator.querySelector('[id$="initial-investment"]');
    const currentValue = calculator.querySelector('#current-value') || calculator.querySelector('[id$="current-value"]');
    const gainLoss = calculator.querySelector('#gain-loss') || calculator.querySelector('[id$="gain-loss"]');
    const gainLossContainer = calculator.querySelector('#gain-loss-container') || calculator.querySelector('[id$="gain-loss-container"]');
    
    if (!initialInvestment || !currentValue) return;
    
    const updateCalculator = () => {
        const price = parseFloat(purchasePrice.value) || 0;
        const qty = parseFloat(quantity.value) || 0;
        const current = parseFloat(currentPrice.value) || price;
        
        const invested = price * qty;
        const currentVal = current * qty;
        const gainLossVal = currentVal - invested;
        const gainLossPercent = invested > 0 ? (gainLossVal / invested) * 100 : 0;
        
        initialInvestment.textContent = '$' + invested.toFixed(2);
        currentValue.textContent = '$' + currentVal.toFixed(2);
        
        if (gainLoss && gainLossContainer) {
            if (current !== price) {
                gainLossContainer.classList.remove('d-none');
                gainLoss.textContent = '$' + Math.abs(gainLossVal).toFixed(2) + ' (' + gainLossPercent.toFixed(2) + '%)';
                gainLoss.className = gainLossVal >= 0 ? 'text-success' : 'text-danger';
            } else {
                gainLossContainer.classList.add('d-none');
            }
        }
        
        if (invested > 0) {
            calculator.classList.remove('d-none');
        } else {
            calculator.classList.add('d-none');
        }
    };
    
    purchasePrice.addEventListener('input', updateCalculator);
    quantity.addEventListener('input', updateCalculator);
    currentPrice.addEventListener('input', updateCalculator);
    
    // Initial calculation
    updateCalculator();
}

/**
 * Initialize DataTables for investment tables
 */
function initializeDataTables() {
    // Check if jQuery is defined and DataTable exists
    if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.DataTable === 'function') {
        try {
            const table = $('#investmentTable');
            if (table.length > 0) {
                table.DataTable({
                    responsive: true,
                    searching: true,
                    paging: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search investments...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ investments",
                        infoEmpty: "Showing 0 to 0 of 0 investments",
                        infoFiltered: "(filtered from _MAX_ total investments)",
                        zeroRecords: "No matching investments found",
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>'
                        }
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    order: [[7, 'desc']] // Order by gain/loss column
                });
            }
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    }
}

/**
 * Initialize search filter for investment table
 */
function initializeSearchFilter() {
    const searchInput = document.getElementById('investmentSearch');
    if (!searchInput) return;
    
    // Check if DataTables is active on the table
    let usingDataTables = false;
    
    if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.DataTable === 'function') {
        try {
            const dtInstance = $('#investmentTable').DataTable();
            if (dtInstance) {
                usingDataTables = true;
            }
        } catch (e) {
            // DataTables not initialized on this table
            usingDataTables = false;
        }
    }
    
    // If we're not using DataTables, implement manual search
    if (!usingDataTables) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#investmentTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
}

/**
 * Initialize investment action buttons
 */
function initializeInvestmentActions() {
    // Edit investment button
    document.querySelectorAll('.edit-investment').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const investmentId = this.getAttribute('data-investment-id');
            if (!investmentId) return;
            
            const editForm = document.getElementById('editInvestmentForm');
            const editFooter = document.getElementById('editInvestmentFooter');
            const editIdInput = document.getElementById('edit_investment_id');
            
            if (!editForm || !editFooter || !editIdInput) {
                showNotification('Error: Form elements not found!', 'danger');
                return;
            }
            
            // Show modal with loading state
            const modal = new bootstrap.Modal(document.getElementById('editInvestmentModal'));
            modal.show();
            
            // Hide form elements while loading
            editForm.classList.add('d-none');
            editFooter.classList.add('d-none');
            
            // Fetch investment data with proper base path
            const basePath = getBasePath();
            fetch(basePath + '/investments?action=get_investment&investment_id=' + investmentId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Populate form fields if they exist
                        if (document.getElementById('edit_investment_id')) 
                            document.getElementById('edit_investment_id').value = data.investment.investment_id;
                        if (document.getElementById('edit_type_id')) 
                            document.getElementById('edit_type_id').value = data.investment.type_id;
                        if (document.getElementById('edit_name')) 
                            document.getElementById('edit_name').value = data.investment.name;
                        if (document.getElementById('edit_ticker_symbol')) 
                            document.getElementById('edit_ticker_symbol').value = data.investment.ticker_symbol || '';
                        if (document.getElementById('edit_purchase_date')) 
                            document.getElementById('edit_purchase_date').value = data.investment.purchase_date;
                        if (document.getElementById('edit_purchase_price')) 
                            document.getElementById('edit_purchase_price').value = data.investment.purchase_price;
                        if (document.getElementById('edit_quantity')) 
                            document.getElementById('edit_quantity').value = data.investment.quantity;
                        if (document.getElementById('edit_current_price')) 
                            document.getElementById('edit_current_price').value = data.investment.current_price;
                        if (document.getElementById('edit_notes')) 
                            document.getElementById('edit_notes').value = data.investment.notes || '';
                        
                        // Show form elements
                        if (editForm) editForm.classList.remove('d-none');
                        if (editFooter) editFooter.classList.remove('d-none');
                        
                        // Initialize calculator
                        initInvestmentCalculator('edit_purchase_price', 'edit_quantity', 'edit_current_price', 'edit-investment-calculator');
                    } else {
                        // Show error notification
                        showNotification('Failed to load investment data: ' + (data.message || 'Unknown error'), 'danger');
                        modal.hide();
                    }
                })
                .catch(error => {
                    console.error('Error fetching investment data:', error);
                    showNotification('An error occurred while loading investment data: ' + error.message, 'danger');
                    modal.hide();
                });
        });
    });
    
    // Delete investment button
    document.querySelectorAll('.delete-investment').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const investmentId = this.getAttribute('data-investment-id');
            if (!investmentId) return;
            
            const deleteIdInput = document.getElementById('delete_investment_id');
            if (deleteIdInput) {
                deleteIdInput.value = investmentId;
                
                // Show modal
                const deleteModal = document.getElementById('deleteInvestmentModal');
                if (deleteModal) {
                    try {
                        const modal = new bootstrap.Modal(deleteModal);
                        modal.show();
                    } catch (error) {
                        console.error('Error showing delete modal:', error);
                        showNotification('Error showing delete modal', 'danger');
                    }
                } else {
                    console.error('Delete modal element not found');
                    showNotification('Delete modal not found', 'danger');
                }
            } else {
                console.error('Delete investment ID input not found');
                showNotification('Delete form elements not found', 'danger');
            }
        });
    });
    
    // Update price button
    document.querySelectorAll('.update-price').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click event
            e.preventDefault(); // Prevent default button behavior
            
            const investmentId = this.getAttribute('data-investment-id');
            const currentPrice = this.getAttribute('data-current-price');
            
            if (!investmentId || !currentPrice) return;
            
            const updateIdInput = document.getElementById('update_investment_id');
            const updatePriceInput = document.getElementById('update_current_price');
            
            if (updateIdInput && updatePriceInput) {
                updateIdInput.value = investmentId;
                updatePriceInput.value = currentPrice;
                
                // Show modal
                const priceModal = document.getElementById('updatePriceModal');
                if (priceModal) {
                    try {
                        const modal = new bootstrap.Modal(priceModal);
                        modal.show();
                    } catch (error) {
                        console.error('Error showing update price modal:', error);
                        showNotification('Error showing update price modal', 'danger');
                    }
                } else {
                    console.error('Update price modal not found');
                    showNotification('Update price modal not found', 'danger');
                }
            } else {
                console.error('Update price form elements not found');
                showNotification('Update price form elements not found', 'danger');
            }
        });
    });
}

/**
 * Initialize tooltips for interactive elements
 */
function initializeTooltips() {
    // Check if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip === 'function') {
        try {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    boundary: document.body
                });
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    } else {
        // Add title attribute to buttons that should have tooltips
        document.querySelectorAll('.btn-icon').forEach(btn => {
            if (!btn.getAttribute('title')) {
                const icon = btn.querySelector('i');
                if (icon) {
                    if (icon.classList.contains('fa-edit')) {
                        btn.setAttribute('title', 'Edit Investment');
                    } else if (icon.classList.contains('fa-trash')) {
                        btn.setAttribute('title', 'Delete Investment');
                    } else if (icon.classList.contains('fa-sync-alt')) {
                        btn.setAttribute('title', 'Update Price');
                    }
                }
            }
        });
    }
}

/**
 * Animate elements for better user experience
 */
function animateElements() {
    // Add animation to cards
    const cards = document.querySelectorAll('.card');
    
    cards.forEach((card, index) => {
        card.style.setProperty('--index', index);
        setTimeout(() => {
            card.classList.add('fade-in');
        }, 100);
    });
    
    // Add hover effect to table rows
    const tableRows = document.querySelectorAll('.investments-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--primary-lighter, #f8f9fa)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Check if notification container exists, if not create it
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.backgroundColor = getColorForType(type);
    notification.style.color = '#fff';
    notification.style.padding = '12px 20px';
    notification.style.borderRadius = '8px';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    notification.style.marginBottom = '10px';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(50px)';
    notification.style.transition = 'all 0.3s ease';
    notification.style.maxWidth = '350px';
    notification.style.wordBreak = 'break-word';
    
    // Add icon
    let iconClass = 'info-circle';
    if (type === 'success') iconClass = 'check-circle';
    if (type === 'warning') iconClass = 'exclamation-triangle';
    if (type === 'danger') iconClass = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${iconClass}" style="margin-right: 10px;"></i>
        <div style="flex: 1;">${message}</div>
        <button type="button" style="background: transparent; border: none; color: white; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Close button
    const closeBtn = notification.querySelector('button');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => removeNotification(notification));
    }
    
    // Auto remove after duration
    setTimeout(() => removeNotification(notification), duration);
    
    function removeNotification(el) {
        if (!el) return;
        el.style.opacity = '0';
        el.style.transform = 'translateX(50px)';
        setTimeout(() => {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, 300);
    }
    
    function getColorForType(type) {
        switch(type) {
            case 'success': return 'rgba(46, 204, 113, 0.9)';
            case 'warning': return 'rgba(243, 156, 18, 0.9)';
            case 'danger': return 'rgba(231, 76, 60, 0.9)';
            default: return 'rgba(52, 152, 219, 0.9)'; // info
        }
    }
}