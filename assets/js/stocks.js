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
    
    // Initialize edit watchlist functionality
    initializeEditWatchlist();
    
    // Initialize real-time price updates
    initializePriceUpdates();
    
    // Initialize stock chart if data exists
    initializeStockChart();
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
                    fetchStockInfo(this.value.toUpperCase(), companyNameInput, currentPriceInput);
                }
            });
        }
    });
}

/**
 * Fetch stock information for a ticker symbol
 * @param {string} ticker - Stock ticker symbol
 * @param {HTMLElement} companyNameInput - Input to populate with company name
 * @param {HTMLElement} currentPriceInput - Input to populate with current price
 */
function fetchStockInfo(ticker, companyNameInput, currentPriceInput) {
    // Add loading indicators
    companyNameInput.value = 'Loading...';
    currentPriceInput.value = '';
    
    // In a real app, this would fetch data from an API
    // For demo, we'll generate mock data
    
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]')?.content || '';
    
    // Simulate network delay
    setTimeout(() => {
        fetch(`${basePath}/stocks?action=get_stock_price&ticker=${ticker}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.status === 'success') {
                    companyNameInput.value = `${ticker} Corporation`;
                    currentPriceInput.value = data.price.toFixed(2);
                } else {
                    companyNameInput.value = '';
                    currentPriceInput.value = '';
                    showNotification('Could not get stock information. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching stock data:', error);
                // Fallback to generate company name and price
                companyNameInput.value = `${ticker} Inc.`;
                currentPriceInput.value = (Math.random() * 100 + 50).toFixed(2);
            });
    }, 500);
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
        const buyTargetText = buyTargetCell.textContent.trim();
        const sellTargetText = sellTargetCell.textContent.trim();
        
        const buyTarget = buyTargetText !== 'N/A' ? 
            parseFloat(buyTargetText.replace('$', '').replace(',', '')) : null;
        const sellTarget = sellTargetText !== 'N/A' ? 
            parseFloat(sellTargetText.replace('$', '').replace(',', '')) : null;
        
        // Add indicator to price cell
        let indicator = '';
        
        if (buyTarget && currentPrice <= buyTarget) {
            indicator = '<span class="badge bg-success ms-2" data-bs-toggle="tooltip" title="Below buy target - consider buying">BUY</span>';
        } else if (sellTarget && currentPrice >= sellTarget) {
            indicator = '<span class="badge bg-danger ms-2" data-bs-toggle="tooltip" title="Above sell target - consider selling">SELL</span>';
        }
        
        if (indicator) {
            // Remove any existing indicators
            const existingIndicator = priceCell.querySelector('.badge');
            if (existingIndicator) {
                priceCell.removeChild(existingIndicator);
            }
            
            // Add new indicator
            priceCell.innerHTML += indicator;
            
            // Initialize tooltip
            const tooltipEl = priceCell.querySelector('[data-bs-toggle="tooltip"]');
            if (tooltipEl) {
                new bootstrap.Tooltip(tooltipEl);
            }
        }
    });
}

/**
 * Initialize edit watchlist functionality
 */
function initializeEditWatchlist() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    watchlistTable.querySelectorAll('.edit-watchlist').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const watchlistId = this.getAttribute('data-watchlist-id');
            const tickerSymbol = row.querySelector('td:nth-child(1)').textContent.trim();
            const companyName = row.querySelector('td:nth-child(2)').textContent.trim();
            const currentPrice = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('$', '').replace(',', ''));
            
            const buyTargetCell = row.querySelector('td:nth-child(4)');
            const sellTargetCell = row.querySelector('td:nth-child(5)');
            
            const buyTargetText = buyTargetCell.textContent.trim();
            const sellTargetText = sellTargetCell.textContent.trim();
            
            const buyTarget = buyTargetText !== 'N/A' ? 
                parseFloat(buyTargetText.replace('$', '').replace(',', '')) : '';
            const sellTarget = sellTargetText !== 'N/A' ? 
                parseFloat(sellTargetText.replace('$', '').replace(',', '')) : '';
                
            // Create edit modal if it doesn't exist
            let editModal = document.getElementById('editWatchlistModal');
            if (!editModal) {
                editModal = document.createElement('div');
                editModal.className = 'modal fade';
                editModal.id = 'editWatchlistModal';
                editModal.tabIndex = '-1';
                editModal.setAttribute('aria-labelledby', 'editWatchlistModalLabel');
                editModal.setAttribute('aria-hidden', 'true');
                
                editModal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editWatchlistModalLabel">Edit Watchlist Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="/stocks" method="post">
                                <input type="hidden" name="action" value="update_watchlist_item">
                                <input type="hidden" name="watchlist_id" id="edit_watchlist_id">
                                
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="edit_ticker_symbol" class="form-label">Ticker Symbol</label>
                                        <input type="text" class="form-control" id="edit_ticker_symbol" name="ticker_symbol" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="edit_company_name" name="company_name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_current_price" class="form-label">Current Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="edit_current_price" name="current_price" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="edit_target_buy_price" class="form-label">Target Buy Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="edit_target_buy_price" name="target_buy_price" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_target_sell_price" class="form-label">Target Sell Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="edit_target_sell_price" name="target_sell_price" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(editModal);
            }
            
            // Fill the form with stock data
            document.getElementById('edit_watchlist_id').value = watchlistId;
            document.getElementById('edit_ticker_symbol').value = tickerSymbol;
            document.getElementById('edit_company_name').value = companyName;
            document.getElementById('edit_current_price').value = currentPrice.toFixed(2);
            document.getElementById('edit_target_buy_price').value = buyTarget;
            document.getElementById('edit_target_sell_price').value = sellTarget;
            
            // Try to get notes (if they exist)
            const notes = row.getAttribute('data-notes') || '';
            document.getElementById('edit_notes').value = notes;
            
            // Show the modal
            const modal = new bootstrap.Modal(editModal);
            modal.show();
        });
    });
}

/**
 * Initialize real-time price updates
 * Simulates real-time stock price updates for watchlist items
 */
function initializePriceUpdates() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    // Set update interval (15 seconds)
    const updateInterval = 15000;
    
    setInterval(() => {
        watchlistTable.querySelectorAll('tbody tr').forEach(row => {
            const symbolCell = row.querySelector('td:first-child');
            const priceCell = row.querySelector('td:nth-child(3)');
            
            if (!symbolCell || !priceCell) return;
            
            const symbol = symbolCell.textContent.trim();
            const currentPrice = parseFloat(priceCell.textContent.replace('$', '').replace(',', ''));
            
            // Simulate price change (randomly up or down by 0.1% to 0.5%)
            const changePercent = (Math.random() * 0.4 + 0.1) * (Math.random() < 0.5 ? -1 : 1);
            const newPrice = currentPrice * (1 + changePercent / 100);
            
            // Update price cell with animation
            const priceText = priceCell.childNodes[0];
            if (priceText && priceText.nodeType === Node.TEXT_NODE) {
                const oldHtml = priceCell.innerHTML;
                
                // Update price
                priceText.nodeValue = '$' + newPrice.toFixed(2);
                
                // Add change indicator
                const changeClass = changePercent >= 0 ? 'text-success' : 'text-danger';
                const changeIcon = changePercent >= 0 ? 'up' : 'down';
                
                // Remove any existing change indicator
                const existingIndicator = priceCell.querySelector('.price-change');
                if (existingIndicator) {
                    priceCell.removeChild(existingIndicator);
                }
                
                // Add new change indicator
                const changeIndicator = document.createElement('small');
                changeIndicator.className = `${changeClass} ms-1 price-change`;
                changeIndicator.innerHTML = `<i class="fas fa-arrow-${changeIcon}"></i> ${Math.abs(changePercent).toFixed(2)}%`;
                
                priceCell.appendChild(changeIndicator);
                
                // Animate the change
                priceCell.classList.add('price-updated');
                setTimeout(() => {
                    priceCell.classList.remove('price-updated');
                }, 2000);
                
                // Update status indicators based on new price
                updateStatusIndicators(row, newPrice);
            }
        });
    }, updateInterval);
    
    // Add CSS for price update animation if not already present
    if (!document.getElementById('price-update-css')) {
        const style = document.createElement('style');
        style.id = 'price-update-css';
        style.textContent = `
            .price-updated {
                animation: price-flash 2s;
            }
            @keyframes price-flash {
                0% { background-color: rgba(0,0,0,0); }
                20% { background-color: rgba(255,255,0,0.3); }
                100% { background-color: rgba(0,0,0,0); }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Update status indicators based on new price
 * @param {HTMLElement} row - Table row element
 * @param {number} newPrice - New stock price
 */
function updateStatusIndicators(row, newPrice) {
    const buyTargetCell = row.querySelector('td:nth-child(4)');
    const sellTargetCell = row.querySelector('td:nth-child(5)');
    const priceCell = row.querySelector('td:nth-child(3)');
    
    if (!buyTargetCell || !sellTargetCell || !priceCell) return;
    
    const buyTargetText = buyTargetCell.textContent.trim();
    const sellTargetText = sellTargetCell.textContent.trim();
    
    const buyTarget = buyTargetText !== 'N/A' ? 
        parseFloat(buyTargetText.replace('$', '').replace(',', '')) : null;
    const sellTarget = sellTargetText !== 'N/A' ? 
        parseFloat(sellTargetText.replace('$', '').replace(',', '')) : null;
    
    // Remove existing indicator
    const existingIndicator = priceCell.querySelector('.badge');
    if (existingIndicator) {
        priceCell.removeChild(existingIndicator);
    }
    
    // Add new indicator if needed
    let indicator = '';
    
    if (buyTarget && newPrice <= buyTarget) {
        indicator = '<span class="badge bg-success ms-2" data-bs-toggle="tooltip" title="Below buy target - consider buying">BUY</span>';
    } else if (sellTarget && newPrice >= sellTarget) {
        indicator = '<span class="badge bg-danger ms-2" data-bs-toggle="tooltip" title="Above sell target - consider selling">SELL</span>';
    }
    
    if (indicator) {
        // Add new indicator
        priceCell.innerHTML += indicator;
        
        // Initialize tooltip
        const tooltipEl = priceCell.querySelector('[data-bs-toggle="tooltip"]');
        if (tooltipEl) {
            new bootstrap.Tooltip(tooltipEl);
        }
    }
}

/**
 * Initialize stock chart
 * Sets up stock price chart if data exists
 */
function initializeStockChart() {
    const chartElement = document.getElementById('stockPriceChart');
    if (!chartElement) return;
    
    // Check if stockPriceData exists and has valid data
    if (typeof stockPriceData === 'undefined' || !stockPriceData || 
        !stockPriceData.dates || !stockPriceData.prices || 
        stockPriceData.dates.length === 0) {
        // Display message if no data
        chartElement.style.display = 'none';
        const noDataMessage = document.createElement('div');
        noDataMessage.className = 'alert alert-info mt-3';
        noDataMessage.innerHTML = 'No chart data available for this stock.';
        chartElement.parentNode.appendChild(noDataMessage);
        return;
    }
    
    const ctx = chartElement.getContext('2d');
        
        // Add moving averages to chart data
        const chartData = {
            labels: stockPriceData.dates,
            datasets: [
                {
                    label: 'Stock Price',
                    data: stockPriceData.prices,
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    pointRadius: 3,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    fill: true
                }
            ]
        };
        
        // Add short-term MA if data exists
        if (typeof shortMaValue !== 'undefined') {
            const shortMaData = new Array(stockPriceData.dates.length).fill(shortMaValue);
            chartData.datasets.push({
                label: '20-Day MA',
                data: shortMaData,
                borderColor: 'rgba(28, 200, 138, 1)',
                borderWidth: 2,
                pointRadius: 0,
                fill: false
            });
        }
        
        // Add long-term MA if data exists
        if (typeof longMaValue !== 'undefined') {
            const longMaData = new Array(stockPriceData.dates.length).fill(longMaValue);
            chartData.datasets.push({
                label: '50-Day MA',
                data: longMaData,
                borderColor: 'rgba(246, 194, 62, 1)',
                borderWidth: 2,
                pointRadius: 0,
                fill: false
            });
        }
        
        // Add support level if data exists
        if (typeof supportValue !== 'undefined') {
            const supportData = new Array(stockPriceData.dates.length).fill(supportValue);
            chartData.datasets.push({
                label: 'Support',
                data: supportData,
                borderColor: 'rgba(54, 185, 204, 1)',
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 0,
                fill: false
            });
        }
        
        // Add resistance level if data exists
        if (typeof resistanceValue !== 'undefined') {
            const resistanceData = new Array(stockPriceData.dates.length).fill(resistanceValue);
            chartData.datasets.push({
                label: 'Resistance',
                data: resistanceData,
                borderColor: 'rgba(231, 74, 59, 1)',
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 0,
                fill: false
            });
        }
        
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        beginAtZero: false,
                        maxTicksLimit: 5,
                        padding: 10,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        },
                        grid: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += '$' + context.parsed.y.toFixed(2);
                                return label;
                            }
                        }
                    }
            }
        }
    });
}

/**
 * Show notification
 * @param {string} message - Message to display
 * @param {string} type - Notification type (success, danger, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.role = 'alert';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add notification to container
    container.appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}