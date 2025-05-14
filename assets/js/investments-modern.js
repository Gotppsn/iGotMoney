document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Investments JS loaded');
    
    // Initialize all components
    initializeCharts();
    initializeEventListeners();
    initializeInvestmentCalculator();
    initializeROICalculator();
    initializeTooltips();
    initializeTableSorting();
    initializeViewToggle();
    initializeFilterControls();
    initializeFormValidation();
    initializeAnimations();
    initializeSearch();
});

// Global variables
const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
let translations = {};

// Try to parse translations from meta tag
try {
    const translationsEl = document.querySelector('meta[name="js-translations"]');
    if (translationsEl) {
        translations = JSON.parse(translationsEl.getAttribute('content') || '{}');
    }
} catch (e) {
    console.error('Error parsing translations:', e);
}

// Get translation helper
function __(key) {
    return translations[key] || key;
}

// Initialize charts
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    initializeAllocationChart();
    initializeRiskChart();
}

// Initialize allocation chart
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
        
        if (!allocationLabelsEl || !allocationDataEl || !allocationColorsEl) {
            console.error('Allocation chart data meta tags not found!');
            showNoDataMessage('allocationChart');
            return;
        }
        
        const allocationLabels = JSON.parse(allocationLabelsEl.getAttribute('content') || '[]');
        const allocationData = JSON.parse(allocationDataEl.getAttribute('content') || '[]');
        const allocationColors = JSON.parse(allocationColorsEl.getAttribute('content') || '[]');
        
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

// Initialize risk chart
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
        
        if (!riskLabelsEl || !riskDataEl || !riskColorsEl) {
            console.error('Risk chart data meta tags not found!');
            showNoDataMessage('riskChart');
            return;
        }
        
        const riskLabels = JSON.parse(riskLabelsEl.getAttribute('content') || '[]');
        const riskData = JSON.parse(riskDataEl.getAttribute('content') || '[]');
        const riskColors = JSON.parse(riskColorsEl.getAttribute('content') || '[]');
        
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

// Show no data message when chart has no data
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

// Initialize event listeners
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
                loadInvestmentForDelete(investmentId);
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
                loadInvestmentForUpdate(investmentId);
                const updateModal = new bootstrap.Modal(document.getElementById('updatePriceModal'));
                updateModal.show();
            }
        }
    });
    
    // Quick view buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.quick-view')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.quick-view');
            const investmentId = button.getAttribute('data-investment-id');
            if (investmentId) {
                loadInvestmentForQuickView(investmentId);
                const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                quickViewModal.show();
            }
        }
    });
    
    // Quick view modal buttons
    document.getElementById('qv-edit')?.addEventListener('click', function() {
        const investmentId = this.getAttribute('data-investment-id');
        if (investmentId) {
            const quickViewModal = bootstrap.Modal.getInstance(document.getElementById('quickViewModal'));
            quickViewModal.hide();
            
            setTimeout(() => {
                loadInvestmentForEdit(investmentId);
                const editModal = new bootstrap.Modal(document.getElementById('editInvestmentModal'));
                editModal.show();
            }, 500);
        }
    });
    
    document.getElementById('qv-update-price')?.addEventListener('click', function() {
        const investmentId = this.getAttribute('data-investment-id');
        if (investmentId) {
            const quickViewModal = bootstrap.Modal.getInstance(document.getElementById('quickViewModal'));
            quickViewModal.hide();
            
            setTimeout(() => {
                loadInvestmentForUpdate(investmentId);
                const updateModal = new bootstrap.Modal(document.getElementById('updatePriceModal'));
                updateModal.show();
            }, 500);
        }
    });
    
    // Monitor URL for form submissions
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check for success messages in URL
        if (urlParams.has('success')) {
            const successMsg = urlParams.get('success');
            if (successMsg === 'add') {
                showToast('success', 'Success', __('add_success') || 'Investment added successfully!');
            } else if (successMsg === 'edit') {
                showToast('success', 'Success', __('edit_success') || 'Investment updated successfully!');
            } else if (successMsg === 'delete') {
                showToast('success', 'Success', __('delete_success') || 'Investment deleted successfully!');
            } else if (successMsg === 'update') {
                showToast('success', 'Success', __('update_success') || 'Price updated successfully!');
            }
            
            // Clean the URL
            const url = new URL(window.location);
            url.searchParams.delete('success');
            window.history.replaceState({}, '', url);
        }
    });
}

// Load investment data for edit modal
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
            
            const typeSelect = document.getElementById('edit_type_id');
            if (typeSelect) {
                typeSelect.value = data.investment.type_id;
                
                // Set risk level for calculator
                const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                if (selectedOption) {
                    const riskLevel = selectedOption.getAttribute('data-risk');
                    if (riskLevel) {
                        updateRiskLevel('edit', riskLevel);
                    }
                }
            }
            
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
            showToast('error', 'Error', data.message || 'Failed to load investment data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'An error occurred while loading investment data');
    });
}

// Load investment data for delete modal
function loadInvestmentForDelete(investmentId) {
    document.getElementById('delete_investment_id').value = investmentId;
    
    // Try to get investment name and value from the table
    try {
        const row = document.querySelector(`tr[data-investment-id="${investmentId}"]`);
        if (row) {
            const name = row.querySelector('.investment-name')?.textContent || 'This investment';
            const value = row.querySelector('td:nth-child(7)')?.textContent || '';
            
            document.getElementById('delete-investment-name').textContent = name;
            document.getElementById('delete-investment-value').textContent = `Current value: ${value}`;
        } else {
            // Try grid view
            const card = document.querySelector(`.investment-card[data-investment-id="${investmentId}"]`);
            if (card) {
                const name = card.querySelector('.investment-name')?.textContent || 'This investment';
                const value = card.querySelector('.detail-value')?.textContent || '';
                
                document.getElementById('delete-investment-name').textContent = name;
                document.getElementById('delete-investment-value').textContent = `Current value: ${value}`;
            }
        }
    } catch (e) {
        console.error('Error loading delete data:', e);
    }
}

// Load investment data for update price modal
function loadInvestmentForUpdate(investmentId) {
    document.getElementById('update_investment_id').value = investmentId;
    
    // Try to get investment details from the table
    try {
        const row = document.querySelector(`tr[data-investment-id="${investmentId}"]`);
        if (row) {
            const name = row.querySelector('.investment-name')?.textContent || 'This investment';
            const currentPrice = row.querySelector('td:nth-child(6)')?.textContent || '';
            const quantity = parseFloat(row.getAttribute('data-quantity') || 
                           row.querySelector('td:nth-child(5)')?.getAttribute('data-value') || 0);
            
            document.getElementById('update-investment-name').textContent = name;
            document.getElementById('update-investment-current').textContent = `Current price: ${currentPrice}`;
            
            // Get current price value
            const priceMatch = currentPrice.match(/[\d,.]+/);
            let currentPriceValue = 0;
            if (priceMatch) {
                currentPriceValue = parseFloat(priceMatch[0].replace(/,/g, ''));
            }
            
            document.getElementById('update_current_price').value = currentPriceValue;
            document.getElementById('update_current_price').setAttribute('data-quantity', quantity);
            
            // Listen for input changes to update the estimated new value
            document.getElementById('update_current_price').oninput = updatePriceChangeIndicator;
            
            // Initial calculation
            updatePriceChangeIndicator();
        } else {
            // Try grid view
            const card = document.querySelector(`.investment-card[data-investment-id="${investmentId}"]`);
            if (card) {
                // Implementation for grid view if needed
            }
        }
    } catch (e) {
        console.error('Error loading update price data:', e);
    }
}

// Update price change indicator
function updatePriceChangeIndicator() {
    const newPriceInput = document.getElementById('update_current_price');
    if (!newPriceInput) return;
    
    const newPrice = parseFloat(newPriceInput.value) || 0;
    const currentPrice = parseFloat(newPriceInput.getAttribute('data-original-price') || 0) || newPrice;
    const quantity = parseFloat(newPriceInput.getAttribute('data-quantity') || 1);
    
    const priceChange = ((newPrice - currentPrice) / Math.max(0.01, currentPrice)) * 100;
    const indicator = document.getElementById('price-change-indicator');
    
    if (indicator) {
        const priceChangeEl = indicator.querySelector('.price-change');
        const percentageEl = indicator.querySelector('#price-change-percentage');
        const newValueEl = indicator.querySelector('#new-value');
        
        if (priceChangeEl && percentageEl && newValueEl) {
            // Update values
            percentageEl.textContent = `${Math.abs(priceChange).toFixed(2)}%`;
            newValueEl.textContent = formatMoney(newPrice * quantity);
            
            // Update styles
            if (priceChange > 0) {
                priceChangeEl.className = 'price-change positive';
                priceChangeEl.innerHTML = `<i class="fas fa-arrow-up"></i> ${percentageEl.outerHTML}`;
            } else if (priceChange < 0) {
                priceChangeEl.className = 'price-change negative';
                priceChangeEl.innerHTML = `<i class="fas fa-arrow-down"></i> ${percentageEl.outerHTML}`;
            } else {
                priceChangeEl.className = 'price-change';
                priceChangeEl.innerHTML = percentageEl.outerHTML;
            }
        }
    }
}

// Load investment data for quick view modal
function loadInvestmentForQuickView(investmentId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/investments?action=get_investment&investment_id=${investmentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const inv = data.investment;
            const purchaseValue = inv.purchase_price * inv.quantity;
            const currentValue = inv.current_price * inv.quantity;
            const gainLoss = currentValue - purchaseValue;
            const percentGainLoss = (purchaseValue > 0) ? (gainLoss / purchaseValue) * 100 : 0;
            const priceChange = (inv.purchase_price > 0) ? 
                              ((inv.current_price - inv.purchase_price) / inv.purchase_price) * 100 : 0;
            
            // Set button data attributes for actions
            document.getElementById('qv-edit').setAttribute('data-investment-id', inv.investment_id);
            document.getElementById('qv-update-price').setAttribute('data-investment-id', inv.investment_id);
            
            // Fill in the investment details
            document.getElementById('qv-name').textContent = inv.name;
            document.getElementById('qv-ticker').textContent = inv.ticker_symbol || '';
            document.getElementById('qv-icon').className = inv.ticker_symbol ? 'fas fa-chart-line' : 'fas fa-landmark';
            
            // Performance
            const performanceValue = document.getElementById('qv-performance-value');
            performanceValue.textContent = `${percentGainLoss >= 0 ? '+' : ''}${percentGainLoss.toFixed(2)}%`;
            performanceValue.className = `indicator-value ${percentGainLoss >= 0 ? 'positive' : 'negative'}`;
            
            // Basic info
            document.getElementById('qv-type').textContent = data.type_name || 'N/A';
            document.getElementById('qv-risk').textContent = data.risk_level || 'N/A';
            document.getElementById('qv-date').textContent = formatDate(inv.purchase_date);
            
            // Calculate holding period
            const purchaseDate = new Date(inv.purchase_date);
            const today = new Date();
            const holdingPeriod = getHoldingPeriod(purchaseDate, today);
            document.getElementById('qv-period').textContent = holdingPeriod;
            
            // Value information
            document.getElementById('qv-purchase-price').textContent = formatMoney(inv.purchase_price);
            document.getElementById('qv-current-price').textContent = formatMoney(inv.current_price);
            document.getElementById('qv-quantity').textContent = formatNumber(inv.quantity);
            
            const priceChangeEl = document.getElementById('qv-price-change');
            priceChangeEl.textContent = `${priceChange >= 0 ? '+' : ''}${priceChange.toFixed(2)}%`;
            priceChangeEl.className = `detail-value ${priceChange >= 0 ? 'positive' : 'negative'}`;
            
            document.getElementById('qv-initial').textContent = formatMoney(purchaseValue);
            document.getElementById('qv-current').textContent = formatMoney(currentValue);
            
            const gainLossEl = document.getElementById('qv-gain-loss');
            gainLossEl.innerHTML = `${gainLoss >= 0 ? '+' : ''}${formatMoney(gainLoss)} (${percentGainLoss >= 0 ? '+' : ''}${percentGainLoss.toFixed(2)}%)`;
            gainLossEl.className = `detail-value ${gainLoss >= 0 ? 'positive' : 'negative'}`;
            
            // Notes
            const notesSection = document.getElementById('qv-notes-section');
            const notesContent = document.getElementById('qv-notes');
            
            if (inv.notes && inv.notes.trim()) {
                notesContent.textContent = inv.notes;
                notesSection.style.display = 'block';
            } else {
                notesSection.style.display = 'none';
            }
        } else {
            showToast('error', 'Error', data.message || 'Failed to load investment data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'An error occurred while loading investment data');
    });
}

// Calculate holding period
function getHoldingPeriod(startDate, endDate) {
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 30) {
        return `${diffDays} ${__('days')}`;
    } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        return `${months} ${__('months')}`;
    } else {
        const years = Math.floor(diffDays / 365);
        const remainingMonths = Math.floor((diffDays % 365) / 30);
        return remainingMonths > 0 ? 
            `${years} ${__('years')}, ${remainingMonths} ${__('months')}` : 
            `${years} ${__('years')}`;
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

// Format number with commas
function formatNumber(number) {
    if (typeof number !== 'number') {
        number = parseFloat(number) || 0;
    }
    
    if (number % 1 === 0) {
        return number.toLocaleString();
    } else {
        const decimalPlaces = countDecimals(number);
        return number.toLocaleString(undefined, { 
            minimumFractionDigits: Math.min(decimalPlaces, 6),
            maximumFractionDigits: Math.min(decimalPlaces, 6)
        });
    }
}

// Count decimal places
function countDecimals(num) {
    if (Math.floor(num) === num) return 0;
    return num.toString().split('.')[1].length || 0;
}

// Format money
function formatMoney(amount) {
    if (typeof amount !== 'number') {
        amount = parseFloat(amount) || 0;
    }
    return `${currencySymbol}${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Initialize Investment Calculator
function initializeInvestmentCalculator() {
    // Add form calculator
    const typeSelect = document.getElementById('type_id');
    const purchasePrice = document.getElementById('purchase_price');
    const quantity = document.getElementById('quantity');
    const currentPrice = document.getElementById('current_price');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const riskLevel = selectedOption.getAttribute('data-risk');
            updateRiskLevel('', riskLevel);
            updateCalculator('');
        });
    }
    
    if (purchasePrice && quantity) {
        purchasePrice.addEventListener('input', () => updateCalculator(''));
        quantity.addEventListener('input', () => updateCalculator(''));
        currentPrice?.addEventListener('input', () => updateCalculator(''));
    }
    
    // Edit form calculator
    const editTypeSelect = document.getElementById('edit_type_id');
    const editPurchasePrice = document.getElementById('edit_purchase_price');
    const editQuantity = document.getElementById('edit_quantity');
    const editCurrentPrice = document.getElementById('edit_current_price');
    
    if (editTypeSelect) {
        editTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const riskLevel = selectedOption.getAttribute('data-risk');
            updateRiskLevel('edit', riskLevel);
            updateCalculator('edit');
        });
    }
    
    if (editPurchasePrice && editQuantity && editCurrentPrice) {
        editPurchasePrice.addEventListener('input', () => updateCalculator('edit'));
        editQuantity.addEventListener('input', () => updateCalculator('edit'));
        editCurrentPrice.addEventListener('input', () => updateCalculator('edit'));
    }
}

// Update risk level in calculator
function updateRiskLevel(prefix, riskLevel) {
    if (!riskLevel) return;
    
    const riskLevelEl = document.getElementById(prefix + '_risk_level');
    if (riskLevelEl) {
        riskLevelEl.textContent = riskLevel;
        riskLevelEl.className = 'risk-level'; // Reset classes
        
        // Add appropriate class based on risk level
        const lowerRisk = riskLevel.toLowerCase();
        if (lowerRisk.includes('very low')) {
            riskLevelEl.classList.add('very-low');
        } else if (lowerRisk.includes('low')) {
            riskLevelEl.classList.add('low');
        } else if (lowerRisk.includes('moderate')) {
            riskLevelEl.classList.add('moderate');
        } else if (lowerRisk.includes('high')) {
            riskLevelEl.classList.add('high');
        } else if (lowerRisk.includes('very high')) {
            riskLevelEl.classList.add('very-high');
        }
    }
}

// Update investment calculator
function updateCalculator(prefix) {
    prefix = prefix || ''; // Handle empty prefix
    
    const purchasePrice = parseFloat(document.getElementById(prefix + 'purchase_price')?.value) || 0;
    const quantity = parseFloat(document.getElementById(prefix + 'quantity')?.value) || 0;
    const currentPrice = parseFloat(document.getElementById(prefix + 'current_price')?.value) || purchasePrice;
    
    const initialInvestment = purchasePrice * quantity;
    const currentValue = currentPrice * quantity;
    const gainLoss = currentValue - initialInvestment;
    const gainLossPercent = initialInvestment > 0 ? (gainLoss / initialInvestment) * 100 : 0;
    
    const calculatorEl = document.getElementById(prefix + 'investment_calculator');
    if (calculatorEl) {
        document.getElementById(prefix + 'initial_investment').textContent = formatMoney(initialInvestment);
        document.getElementById(prefix + 'current_value').textContent = formatMoney(currentValue);
        
        const gainLossContainer = document.getElementById(prefix + 'gain_loss_container');
        if (gainLossContainer) {
            const gainLossEl = document.getElementById(prefix + 'gain_loss');
            const gainLossPercentEl = document.getElementById(prefix + 'gain_loss_percent');
            
            gainLossEl.textContent = formatMoney(Math.abs(gainLoss));
            gainLossPercentEl.textContent = `(${gainLoss >= 0 ? '+' : '-'}${Math.abs(gainLossPercent).toFixed(2)}%)`;
            
            gainLossEl.className = gainLoss >= 0 ? 'calculator-value text-success' : 'calculator-value text-danger';
            gainLossPercentEl.className = gainLoss >= 0 ? 'text-success' : 'text-danger';
            
            gainLossContainer.style.display = (purchasePrice > 0 && quantity > 0) ? 'block' : 'none';
        }
        
        calculatorEl.style.display = (purchasePrice > 0 && quantity > 0) ? 'block' : 'none';
    }
}

// Initialize ROI Calculator
function initializeROICalculator() {
    const investmentInput = document.getElementById('calc-investment');
    const returnInput = document.getElementById('calc-return');
    const yearsInput = document.getElementById('calc-years');
    
    if (investmentInput && returnInput && yearsInput) {
        const updateROICalculation = () => {
            const investment = parseFloat(investmentInput.value) || 0;
            const annualReturn = parseFloat(returnInput.value) || 0;
            const years = parseInt(yearsInput.value) || 0;
            
            const futureValue = investment * Math.pow(1 + annualReturn / 100, years);
            const profit = futureValue - investment;
            
            document.getElementById('calc-result').textContent = formatMoney(futureValue);
            document.getElementById('calc-profit').innerHTML = `<span class="${profit >= 0 ? 'positive' : 'negative'}">${profit >= 0 ? '+' : ''}${formatMoney(profit)}</span> ${__('total_profit')}`;
        };
        
        investmentInput.addEventListener('input', updateROICalculation);
        returnInput.addEventListener('input', updateROICalculation);
        yearsInput.addEventListener('input', updateROICalculation);
        
        // Initial calculation
        updateROICalculation();
    }
}

// Initialize tooltips
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

// Initialize table sorting
function initializeTableSorting() {
    const table = document.querySelector('.investments-table');
    if (!table) return;
    
    const headers = table.querySelectorAll('th.sortable');
    
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Toggle sort direction
            const isAscending = this.classList.contains('sort-asc');
            
            // Clear sort classes from all headers
            headers.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Add sort class to current header
            this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
            
            // Sort rows
            rows.sort((rowA, rowB) => {
                let valueA, valueB;
                
                // Get values for sorting based on the column type
                if (sortBy === 'name') {
                    valueA = rowA.querySelector('.investment-name')?.textContent.trim().toLowerCase() || '';
                    valueB = rowB.querySelector('.investment-name')?.textContent.trim().toLowerCase() || '';
                } else if (sortBy === 'type') {
                    valueA = rowA.querySelector('.risk-badge')?.textContent.trim().toLowerCase() || '';
                    valueB = rowB.querySelector('.risk-badge')?.textContent.trim().toLowerCase() || '';
                } else {
                    // For numeric values, look for data-value attribute first
                    const cellA = rowA.querySelector(`td:nth-child(${Array.from(headers).indexOf(this) + 1})`);
                    const cellB = rowB.querySelector(`td:nth-child(${Array.from(headers).indexOf(this) + 1})`);
                    
                    valueA = parseFloat(cellA?.getAttribute('data-value') || '0');
                    valueB = parseFloat(cellB?.getAttribute('data-value') || '0');
                }
                
                // Sort comparison
                if (valueA < valueB) return isAscending ? -1 : 1;
                if (valueA > valueB) return isAscending ? 1 : -1;
                return 0;
            });
            
            // Append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}

// Initialize view toggle
function initializeViewToggle() {
    const viewToggle = document.getElementById('viewToggle');
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (viewToggle && tableView && gridView) {
        viewToggle.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.getAttribute('data-view');
                
                // Update active button
                viewToggle.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show selected view
                if (view === 'table') {
                    tableView.style.display = 'block';
                    gridView.style.display = 'none';
                } else if (view === 'grid') {
                    tableView.style.display = 'none';
                    gridView.style.display = 'block';
                }
                
                // Save preference
                localStorage.setItem('investmentsView', view);
            });
        });
        
        // Check for saved preference
        const savedView = localStorage.getItem('investmentsView');
        if (savedView) {
            const viewBtn = viewToggle.querySelector(`[data-view="${savedView}"]`);
            if (viewBtn) viewBtn.click();
        }
    }
}

// Initialize filter controls
function initializeFilterControls() {
    const filterToggle = document.getElementById('filterToggle');
    const filterMenu = document.getElementById('filterMenu');
    const applyFilters = document.getElementById('applyFilters');
    const resetFilters = document.getElementById('resetFilters');
    const typeFilter = document.getElementById('typeFilter');
    const performanceFilter = document.getElementById('performanceFilter');
    
    if (filterToggle && filterMenu) {
        // Toggle filter menu
        filterToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            filterMenu.classList.toggle('active');
        });
        
        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (!filterMenu.contains(e.target) && e.target !== filterToggle) {
                filterMenu.classList.remove('active');
            }
        });
        
        // Apply filters
        if (applyFilters && typeFilter && performanceFilter) {
            applyFilters.addEventListener('click', function() {
                const selectedType = typeFilter.value;
                const selectedPerformance = performanceFilter.value;
                
                // Apply to table view
                const tableRows = document.querySelectorAll('.investments-table tbody tr');
                let visibleRows = 0;
                
                tableRows.forEach(row => {
                    let showRow = true;
                    
                    // Filter by type
                    if (selectedType && row.getAttribute('data-type') !== selectedType) {
                        showRow = false;
                    }
                    
                    // Filter by performance
                    if (selectedPerformance && row.getAttribute('data-performance') !== selectedPerformance) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                    if (showRow) visibleRows++;
                });
                
                // Apply to grid view
                const gridItems = document.querySelectorAll('.investment-card');
                let visibleCards = 0;
                
                gridItems.forEach(card => {
                    let showCard = true;
                    
                    // Filter by type
                    if (selectedType && card.getAttribute('data-type') !== selectedType) {
                        showCard = false;
                    }
                    
                    // Filter by performance
                    if (selectedPerformance && card.getAttribute('data-performance') !== selectedPerformance) {
                        showCard = false;
                    }
                    
                    card.style.display = showCard ? '' : 'none';
                    if (showCard) visibleCards++;
                });
                
                // Show no results message if needed
                const tableView = document.getElementById('tableView');
                const gridView = document.getElementById('gridView');
                const noDataMessage = document.getElementById('tableNoData');
                
                if ((visibleRows === 0 && tableView.style.display !== 'none') || 
                    (visibleCards === 0 && gridView.style.display !== 'none')) {
                    if (noDataMessage) {
                        noDataMessage.style.display = 'block';
                        noDataMessage.querySelector('h4').textContent = __('no_matching_investments');
                        noDataMessage.querySelector('p').textContent = __('try_adjusting_search');
                        noDataMessage.querySelector('button').style.display = 'none';
                    }
                } else {
                    if (noDataMessage) noDataMessage.style.display = 'none';
                }
                
                // Close filter menu
                filterMenu.classList.remove('active');
            });
        }
        
        // Reset filters
        if (resetFilters) {
            resetFilters.addEventListener('click', function() {
                typeFilter.value = '';
                performanceFilter.value = '';
                
                // Show all rows
                document.querySelectorAll('.investments-table tbody tr').forEach(row => {
                    row.style.display = '';
                });
                
                // Show all cards
                document.querySelectorAll('.investment-card').forEach(card => {
                    card.style.display = '';
                });
                
                // Hide no results message
                const noDataMessage = document.getElementById('tableNoData');
                if (noDataMessage) {
                    const tableRows = document.querySelectorAll('.investments-table tbody tr');
                    if (tableRows.length > 0) {
                        noDataMessage.style.display = 'none';
                    } else {
                        noDataMessage.style.display = 'block';
                        noDataMessage.querySelector('h4').textContent = __('no_investments_recorded');
                        noDataMessage.querySelector('p').textContent = __('start_tracking_investment');
                        noDataMessage.querySelector('button').style.display = '';
                    }
                }
                
                // Close filter menu
                filterMenu.classList.remove('active');
            });
        }
    }
}

// Initialize form validation
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

// Initialize animations
function initializeAnimations() {
    // Animate cards on load
    const cards = document.querySelectorAll('.summary-card, .analysis-card, .performers-card, .table-card, .tips-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}

// Initialize search
function initializeSearch() {
    const searchInput = document.getElementById('investmentSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Search in table view
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
            
            // Search in grid view
            const gridItems = document.querySelectorAll('.investment-card');
            let visibleCards = 0;
            
            gridItems.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = '';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show no results message if needed
            const tableView = document.getElementById('tableView');
            const gridView = document.getElementById('gridView');
            const noDataMessage = document.getElementById('tableNoData');
            
            if ((visibleRows === 0 && tableRows.length > 0 && tableView.style.display !== 'none') || 
                (visibleCards === 0 && gridItems.length > 0 && gridView.style.display !== 'none')) {
                if (noDataMessage) {
                    noDataMessage.style.display = 'block';
                    noDataMessage.querySelector('h4').textContent = __('no_matching_investments');
                    noDataMessage.querySelector('p').textContent = __('try_adjusting_search');
                    noDataMessage.querySelector('button').style.display = 'none';
                }
            } else {
                if (noDataMessage && (tableRows.length > 0 || gridItems.length > 0)) {
                    noDataMessage.style.display = 'none';
                }
            }
        });
    }
}

// Show toast notification
function showToast(type, title, message) {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    
    const id = 'toast_' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.id = id;
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        </div>
        <div class="toast-content">
            <h4 class="toast-title">${title}</h4>
            <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close" onclick="document.getElementById('${id}').remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Show toast with animation
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}