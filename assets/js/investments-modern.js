document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Investments JS loaded');
    
    // Initialize all components
    initializeCharts();
    initializeEventListeners();
    initializeInvestmentCalculator();
    initializeAnimations();
    initializeSearch();
    initializeFormValidation();
});

function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    initializeAllocationChart();
    initializeRiskChart();
}

function initializeAllocationChart() {
    const chartCanvas = document.getElementById('allocationChart');
    if (!chartCanvas) {
        console.error('Allocation chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const allocationLabelsEl = document.querySelector('meta[name="allocation-labels"]');
        const allocationDataEl = document.querySelector('meta[name="allocation-data"]');
        const allocationColorsEl = document.querySelector('meta[name="allocation-colors"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!allocationLabelsEl || !allocationDataEl || !allocationColorsEl) {
            console.error('Allocation chart data meta tags not found!');
            showNoDataMessage('allocationChart');
            return;
        }
        
        const allocationLabels = JSON.parse(allocationLabelsEl.getAttribute('content') || '[]');
        const allocationData = JSON.parse(allocationDataEl.getAttribute('content') || '[]');
        const allocationColors = JSON.parse(allocationColorsEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (allocationLabels.length === 0 || allocationData.length === 0) {
            showNoDataMessage('allocationChart');
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.allocationChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: allocationLabels,
                datasets: [{
                    data: allocationData,
                    backgroundColor: allocationColors,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        padding: 30,
                        labels: {
                            boxWidth: 16,
                            boxHeight: 16,
                            padding: 15,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Error initializing allocation chart:', error);
        showNoDataMessage('allocationChart');
    }
}

function initializeRiskChart() {
    const chartCanvas = document.getElementById('riskChart');
    if (!chartCanvas) {
        console.error('Risk chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const riskLabelsEl = document.querySelector('meta[name="risk-labels"]');
        const riskDataEl = document.querySelector('meta[name="risk-data"]');
        const riskColorsEl = document.querySelector('meta[name="risk-colors"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!riskLabelsEl || !riskDataEl || !riskColorsEl) {
            console.error('Risk chart data meta tags not found!');
            showNoDataMessage('riskChart');
            return;
        }
        
        const riskLabels = JSON.parse(riskLabelsEl.getAttribute('content') || '[]');
        const riskData = JSON.parse(riskDataEl.getAttribute('content') || '[]');
        const riskColors = JSON.parse(riskColorsEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (riskLabels.length === 0 || riskData.length === 0) {
            showNoDataMessage('riskChart');
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.riskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: riskLabels,
                datasets: [{
                    data: riskData,
                    backgroundColor: riskColors,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        padding: 30,
                        labels: {
                            boxWidth: 16,
                            boxHeight: 16,
                            padding: 15,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Error initializing risk chart:', error);
        showNoDataMessage('riskChart');
    }
}

function showNoDataMessage(chartId) {
    const chartContainer = document.getElementById(chartId)?.parentElement;
    if (chartContainer) {
        // Get translated message from the page
        const noDataMessage = document.querySelector('.empty-state p')?.textContent || 'No investment data available';
        
        chartContainer.innerHTML = `
            <div class="no-data-message">
                <i class="fas fa-chart-pie"></i>
                <p>${noDataMessage}</p>
            </div>
        `;
    }
}

function initializeEventListeners() {
    // Edit investment buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const investmentId = button.getAttribute('data-investment-id');
            if (investmentId) {
                loadInvestmentForEdit(investmentId);
            }
        }
    });

    // Delete investment buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const investmentId = button.getAttribute('data-investment-id');
            if (investmentId) {
                document.getElementById('delete_investment_id').value = investmentId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteInvestmentModal'));
                deleteModal.show();
            }
        }
    });

    // Update price buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.update')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.update');
            const investmentId = button.getAttribute('data-investment-id');
            const currentPrice = button.getAttribute('data-current-price');
            if (investmentId) {
                document.getElementById('update_investment_id').value = investmentId;
                document.getElementById('update_current_price').value = currentPrice || '';
                const updateModal = new bootstrap.Modal(document.getElementById('updatePriceModal'));
                updateModal.show();
            }
        }
    });
}

function loadInvestmentForEdit(investmentId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/investments?action=get_investment&investment_id=${investmentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_investment_id').value = data.investment.investment_id;
            document.getElementById('edit_type_id').value = data.investment.type_id;
            document.getElementById('edit_name').value = data.investment.name;
            document.getElementById('edit_ticker_symbol').value = data.investment.ticker_symbol || '';
            document.getElementById('edit_purchase_date').value = data.investment.purchase_date;
            document.getElementById('edit_purchase_price').value = data.investment.purchase_price;
            document.getElementById('edit_quantity').value = data.investment.quantity;
            document.getElementById('edit_current_price').value = data.investment.current_price;
            document.getElementById('edit_notes').value = data.investment.notes || '';
            
            // Update calculator
            updateCalculator('edit');
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editInvestmentModal'));
            editModal.show();
        } else {
            // Get error message element text or fallback
            const errorMessage = document.querySelector('.alert-danger')?.textContent || 'Failed to load investment data';
            showNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Get error message from the page or fallback
        const errorMessage = document.querySelector('.alert-danger')?.textContent || 'An error occurred while loading investment data';
        showNotification(errorMessage, 'error');
    });
}

function initializeInvestmentCalculator() {
    // Add form calculator
    const purchasePrice = document.getElementById('purchase_price');
    const quantity = document.getElementById('quantity');
    const currentPrice = document.getElementById('current_price');
    
    if (purchasePrice && quantity) {
        purchasePrice.addEventListener('input', () => updateCalculator('add'));
        quantity.addEventListener('input', () => updateCalculator('add'));
        currentPrice?.addEventListener('input', () => updateCalculator('add'));
    }
    
    // Edit form calculator
    const editPurchasePrice = document.getElementById('edit_purchase_price');
    const editQuantity = document.getElementById('edit_quantity');
    const editCurrentPrice = document.getElementById('edit_current_price');
    
    if (editPurchasePrice && editQuantity && editCurrentPrice) {
        editPurchasePrice.addEventListener('input', () => updateCalculator('edit'));
        editQuantity.addEventListener('input', () => updateCalculator('edit'));
        editCurrentPrice.addEventListener('input', () => updateCalculator('edit'));
    }
}

function updateCalculator(form) {
    // Get currency symbol from meta tag
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
    
    const prefix = form === 'edit' ? 'edit_' : '';
    const purchasePrice = parseFloat(document.getElementById(prefix + 'purchase_price')?.value) || 0;
    const quantity = parseFloat(document.getElementById(prefix + 'quantity')?.value) || 0;
    const currentPrice = parseFloat(document.getElementById(prefix + 'current_price')?.value) || purchasePrice;
    
    const initialInvestment = purchasePrice * quantity;
    const currentValue = currentPrice * quantity;
    const gainLoss = currentValue - initialInvestment;
    const gainLossPercent = initialInvestment > 0 ? (gainLoss / initialInvestment) * 100 : 0;
    
    const calculatorEl = document.getElementById(prefix + 'investment_calculator');
    if (calculatorEl) {
        document.getElementById(prefix + 'initial_investment').textContent = `${currencySymbol}${initialInvestment.toFixed(2).toLocaleString()}`;
        document.getElementById(prefix + 'current_value').textContent = `${currencySymbol}${currentValue.toFixed(2).toLocaleString()}`;
        
        const gainLossContainer = document.getElementById(prefix + 'gain_loss_container');
        if (gainLossContainer) {
            const gainLossEl = document.getElementById(prefix + 'gain_loss');
            const gainLossPercentEl = document.getElementById(prefix + 'gain_loss_percent');
            
            gainLossEl.textContent = `${currencySymbol}${Math.abs(gainLoss).toFixed(2).toLocaleString()}`;
            gainLossEl.className = gainLoss >= 0 ? 'calculator-value text-success' : 'calculator-value text-danger';
            
            gainLossPercentEl.textContent = `(${gainLoss >= 0 ? '+' : ''}${gainLossPercent.toFixed(2)}%)`;
            gainLossPercentEl.className = gainLoss >= 0 ? 'text-success' : 'text-danger';
            
            gainLossContainer.style.display = currentPrice !== purchasePrice ? 'block' : 'none';
        }
        
        calculatorEl.style.display = initialInvestment > 0 ? 'block' : 'none';
    }
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

function initializeAnimations() {
    // Animate cards on load
    const cards = document.querySelectorAll('.summary-card, .chart-card, .performers-card, .table-card, .tips-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('investmentSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.investments-table tbody tr');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const noDataMessage = document.getElementById('tableNoData');
            const tableBody = document.querySelector('.table-responsive');

            // Get translated messages from hidden elements or data attributes in the DOM
            let noMatchingInvestments = 'No matching investments found';
            let tryAdjustingSearch = 'Try adjusting your search term';
            
            // Try to get translations from page elements that might have them
            const noMatchingEl = document.querySelector('[data-translation="no_matching_investments"]');
            const adjustingSearchEl = document.querySelector('[data-translation="try_adjusting_search"]');
            
            if (noMatchingEl) noMatchingInvestments = noMatchingEl.textContent;
            if (adjustingSearchEl) tryAdjustingSearch = adjustingSearchEl.textContent;
            
            if (visibleRows === 0 && tableRows.length > 0) {
                if (tableBody) tableBody.style.display = 'none';
                if (noDataMessage) {
                    noDataMessage.style.display = 'block';
                    noDataMessage.querySelector('h4').textContent = noMatchingInvestments;
                    noDataMessage.querySelector('p').textContent = tryAdjustingSearch;
                }
            } else {
                if (tableBody) tableBody.style.display = 'block';
                if (noDataMessage && tableRows.length > 0) noDataMessage.style.display = 'none';
            }
        });
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}