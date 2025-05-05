/**
 * iGotMoney - Stocks JavaScript
 * Handles functionality for the stock analysis page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize stock symbol lookup
    initializeSymbolLookup();
    
    // Initialize price alerts
    initializePriceAlerts();
    
    // Initialize watchlist status indicators
    initializeWatchlistStatus();
});

/**
 * Initialize stock symbol lookup
 * Provides autocomplete for stock symbols
 */
function initializeSymbolLookup() {
    const symbolInputs = document.querySelectorAll('#ticker_symbol, #ticker_symbol_watchlist');
    
    symbolInputs.forEach(input => {
        // Add lookup button next to input
        const inputGroup = input.parentNode;
        if (inputGroup.classList.contains('input-group')) {
            const lookupButton = document.createElement('button');
            lookupButton.className = 'btn btn-outline-secondary';
            lookupButton.type = 'button';
            lookupButton.innerHTML = '<i class="fas fa-search"></i>';
            lookupButton.setAttribute('data-bs-toggle', 'tooltip');
            lookupButton.setAttribute('title', 'Lookup Symbol');
            
            // Insert before the submit button if it exists
            const submitButton = inputGroup.querySelector('button[type="submit"]');
            if (submitButton) {
                inputGroup.insertBefore(lookupButton, submitButton);
            } else {
                inputGroup.appendChild(lookupButton);
            }
            
            // Initialize tooltip
            new bootstrap.Tooltip(lookupButton);
            
            // Add click event
            lookupButton.addEventListener('click', function() {
                showSymbolLookupModal(input);
            });
        }
        
        // Add company name auto-fill for watchlist
        if (input.id === 'ticker_symbol_watchlist') {
            input.addEventListener('blur', function() {
                const companyNameInput = document.getElementById('company_name');
                const currentPriceInput = document.getElementById('current_price_watchlist');
                
                if (companyNameInput && this.value && !companyNameInput.value) {
                    // This would normally fetch company data from an API
                    // For demo, we'll just add "Inc." to the ticker
                    companyNameInput.value = this.value.toUpperCase() + ' Inc.';
                    
                    // Set a mock current price
                    if (currentPriceInput && !currentPriceInput.value) {
                        currentPriceInput.value = (Math.random() * 100 + 50).toFixed(2);
                    }
                }
            });
        }
    });
}

/**
 * Show symbol lookup modal
 * @param {HTMLElement} targetInput - Input to populate with selected symbol
 */
function showSymbolLookupModal(targetInput) {
    // Check if modal already exists
    let lookupModal = document.getElementById('symbolLookupModal');
    
    if (!lookupModal) {
        // Create modal
        lookupModal = document.createElement('div');
        lookupModal.className = 'modal fade';
        lookupModal.id = 'symbolLookupModal';
        lookupModal.tabIndex = '-1';
        lookupModal.setAttribute('aria-labelledby', 'symbolLookupModalLabel');
        lookupModal.setAttribute('aria-hidden', 'true');
        
        lookupModal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="symbolLookupModalLabel">Stock Symbol Lookup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="companySearchInput" class="form-label">Search for a company</label>
                            <input type="text" class="form-control" id="companySearchInput" placeholder="Enter company name...">
                        </div>
                        <div id="lookupResults" class="mt-3">
                            <div class="alert alert-info">
                                Enter a company name to search for its stock symbol.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(lookupModal);
        
        // Add search functionality
        const searchInput = lookupModal.querySelector('#companySearchInput');
        const resultsDiv = lookupModal.querySelector('#lookupResults');
        
        searchInput.addEventListener('input', function() {
            if (this.value.length < 2) {
                resultsDiv.innerHTML = `
                    <div class="alert alert-info">
                        Enter a company name to search for its stock symbol.
                    </div>
                `;
                return;
            }
            
            // Show loading indicator
            resultsDiv.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Searching...</p>
                </div>
            `;
            
            // In a real app, this would make an API call to a stock symbol lookup service
            // For the demo, we'll use mock data
            setTimeout(() => {
                const mockResults = getMockCompanyResults(this.value);
                displayLookupResults(mockResults, resultsDiv, targetInput);
            }, 500);
        });
    }
    
    // Show modal
    const modal = new bootstrap.Modal(lookupModal);
    modal.show();
    
    // Focus on search input
    setTimeout(() => {
        lookupModal.querySelector('#companySearchInput').focus();
    }, 500);
}

/**
 * Get mock company results
 * @param {string} query - Search query
 * @returns {Array} - Array of company objects
 */
function getMockCompanyResults(query) {
    // Mock data for demonstration
    const companies = [
        { symbol: 'AAPL', name: 'Apple Inc.', exchange: 'NASDAQ', price: 175.42 },
        { symbol: 'MSFT', name: 'Microsoft Corporation', exchange: 'NASDAQ', price: 330.11 },
        { symbol: 'GOOGL', name: 'Alphabet Inc.', exchange: 'NASDAQ', price: 127.64 },
        { symbol: 'AMZN', name: 'Amazon.com Inc.', exchange: 'NASDAQ', price: 129.33 },
        { symbol: 'META', name: 'Meta Platforms Inc.', exchange: 'NASDAQ', price: 312.49 },
        { symbol: 'TSLA', name: 'Tesla Inc.', exchange: 'NASDAQ', price: 237.01 },
        { symbol: 'NVDA', name: 'NVIDIA Corporation', exchange: 'NASDAQ', price: 437.53 },
        { symbol: 'JPM', name: 'JPMorgan Chase & Co.', exchange: 'NYSE', price: 147.10 },
        { symbol: 'BAC', name: 'Bank of America Corporation', exchange: 'NYSE', price: 29.42 },
        { symbol: 'WMT', name: 'Walmart Inc.', exchange: 'NYSE', price: 160.25 }
    ];
    
    // Filter companies by name containing the query (case insensitive)
    return companies.filter(company => 
        company.name.toLowerCase().includes(query.toLowerCase()) || 
        company.symbol.toLowerCase().includes(query.toLowerCase())
    );
}

/**
 * Display lookup results
 * @param {Array} results - Array of company objects
 * @param {HTMLElement} container - Container to display results
 * @param {HTMLElement} targetInput - Input to populate with selected symbol
 */
function displayLookupResults(results, container, targetInput) {
    if (results.length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                No companies found matching your search.
            </div>
        `;
        return;
    }
    
    let resultsHTML = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Company Name</th>
                        <th>Exchange</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    results.forEach(company => {
        resultsHTML += `
            <tr>
                <td><strong>${company.symbol}</strong></td>
                <td>${company.name}</td>
                <td>${company.exchange}</td>
                <td>$${company.price.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary select-symbol" 
                        data-symbol="${company.symbol}" 
                        data-company="${company.name}" 
                        data-price="${company.price}">
                        Select
                    </button>
                </td>
            </tr>
        `;
    });
    
    resultsHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = resultsHTML;
    
    // Add click events to select buttons
    container.querySelectorAll('.select-symbol').forEach(button => {
        button.addEventListener('click', function() {
            const symbol = this.getAttribute('data-symbol');
            const company = this.getAttribute('data-company');
            const price = this.getAttribute('data-price');
            
            // Populate target input with selected symbol
            targetInput.value = symbol;
            
            // If target is watchlist input, also populate company name and price
            if (targetInput.id === 'ticker_symbol_watchlist') {
                document.getElementById('company_name').value = company;
                document.getElementById('current_price_watchlist').value = price;
            }
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('symbolLookupModal')).hide();
        });
    });
}

/**
 * Initialize price alerts
 * Enables setting alerts for price movements
 */
function initializePriceAlerts() {
    // Create alert button for watchlist items
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    const rows = watchlistTable.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const actionsCell = row.querySelector('td:last-child');
        const symbolCell = row.querySelector('td:first-child');
        const priceCell = row.querySelector('td:nth-child(3)');
        
        if (!actionsCell || !symbolCell || !priceCell) return;
        
        const symbol = symbolCell.textContent.trim();
        const price = parseFloat(priceCell.textContent.replace('$', '').replace(',', ''));
        
        // Add alert button if not already present
        if (!actionsCell.querySelector('.set-price-alert')) {
            const alertButton = document.createElement('button');
            alertButton.className = 'btn btn-sm btn-outline-primary set-price-alert ms-1';
            alertButton.innerHTML = '<i class="fas fa-bell"></i>';
            alertButton.setAttribute('data-bs-toggle', 'tooltip');
            alertButton.setAttribute('title', 'Set Price Alert');
            alertButton.setAttribute('data-symbol', symbol);
            alertButton.setAttribute('data-current-price', price);
            
            actionsCell.appendChild(alertButton);
            
            // Initialize tooltip
            new bootstrap.Tooltip(alertButton);
            
            // Add click event
            alertButton.addEventListener('click', function() {
                showPriceAlertModal(this.getAttribute('data-symbol'), this.getAttribute('data-current-price'));
            });
        }
    });
}

/**
 * Show price alert modal
 * @param {string} symbol - Stock symbol
 * @param {number} currentPrice - Current stock price
 */
function showPriceAlertModal(symbol, currentPrice) {
    // Check if modal already exists
    let alertModal = document.getElementById('priceAlertModal');
    
    if (!alertModal) {
        // Create modal
        alertModal = document.createElement('div');
        alertModal.className = 'modal fade';
        alertModal.id = 'priceAlertModal';
        alertModal.tabIndex = '-1';
        alertModal.setAttribute('aria-labelledby', 'priceAlertModalLabel');
        alertModal.setAttribute('aria-hidden', 'true');
        
        alertModal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="priceAlertModalLabel">Set Price Alert</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Stock: <span id="alertSymbol" class="fw-bold"></span></label>
                            <div>Current Price: $<span id="alertCurrentPrice"></span></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alertType" class="form-label">Alert Type</label>
                            <select class="form-select" id="alertType">
                                <option value="above">Price Above</option>
                                <option value="below">Price Below</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alertPrice" class="form-label">Alert Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="alertPrice" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alertMethod" class="form-label">Alert Method</label>
                            <select class="form-select" id="alertMethod">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="app">App Notification</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle me-1"></i> In this demo, alerts are for demonstration only and won't be sent.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveAlertButton">Set Alert</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(alertModal);
        
        // Add save functionality
        const saveButton = alertModal.querySelector('#saveAlertButton');
        
        saveButton.addEventListener('click', function() {
            const alertType = document.getElementById('alertType').value;
            const alertPrice = document.getElementById('alertPrice').value;
            const alertMethod = document.getElementById('alertMethod').value;
            
            if (!alertPrice) {
                alert('Please enter an alert price.');
                return;
            }
            
            // In a real app, this would save the alert to the backend
            // For the demo, we'll just show a success message
            
            // Close the modal
            bootstrap.Modal.getInstance(alertModal).hide();
            
            // Show success notification
            showNotification(
                `Alert set: ${symbol} ${alertType === 'above' ? 'rises above' : 'falls below'} $${alertPrice}`,
                'success',
                5000
            );
        });
    }
    
    // Update modal content with current values
    document.getElementById('alertSymbol').textContent = symbol;
    document.getElementById('alertCurrentPrice').textContent = parseFloat(currentPrice).toFixed(2);
    
    // Set default alert price based on current price and alert type
    const alertType = document.getElementById('alertType');
    const alertPrice = document.getElementById('alertPrice');
    
    alertType.addEventListener('change', function() {
        if (this.value === 'above') {
            alertPrice.value = (parseFloat(currentPrice) * 1.05).toFixed(2); // 5% above current price
        } else {
            alertPrice.value = (parseFloat(currentPrice) * 0.95).toFixed(2); // 5% below current price
        }
    });
    
    // Set initial values
    alertType.value = 'above';
    alertPrice.value = (parseFloat(currentPrice) * 1.05).toFixed(2);
    
    // Show modal
    const modal = new bootstrap.Modal(alertModal);
    modal.show();
}

/**
 * Initialize watchlist status indicators
 * Visual indicators for stocks in watchlist
 */
function initializeWatchlistStatus() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    const rows = watchlistTable.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const symbolCell = row.querySelector('td:first-child');
        const priceCell = row.querySelector('td:nth-child(3)');
        const buyTargetCell = row.querySelector('td:nth-child(4)');
        const sellTargetCell = row.querySelector('td:nth-child(5)');
        
        if (!symbolCell || !priceCell || !buyTargetCell || !sellTargetCell) return;
        
        const currentPrice = parseFloat(priceCell.textContent.replace('$', '').replace(',', ''));
        const buyTarget = buyTargetCell.textContent.trim() !== 'N/A' ? 
            parseFloat(buyTargetCell.textContent.replace('$', '').replace(',', '')) : null;
        const sellTarget = sellTargetCell.textContent.trim() !== 'N/A' ? 
            parseFloat(sellTargetCell.textContent.replace('$', '').replace(',', '')) : null;
        
        // Add indicator to price cell
        let indicator = '';
        
        if (buyTarget && currentPrice <= buyTarget) {
            indicator = '<span class="badge bg-success ms-2" data-bs-toggle="tooltip" title="Below buy target - consider buying">BUY</span>';
        } else if (sellTarget && currentPrice >= sellTarget) {
            indicator = '<span class="badge bg-danger ms-2" data-bs-toggle="tooltip" title="Above sell target - consider selling">SELL</span>';
        }
        
        if (indicator) {
            priceCell.innerHTML += indicator;
            
            // Initialize tooltip
            const tooltipEl = priceCell.querySelector('[data-bs-toggle="tooltip"]');
            if (tooltipEl) {
                new bootstrap.Tooltip(tooltipEl);
            }
        }
    });
}