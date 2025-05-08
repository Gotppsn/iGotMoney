/**
 * iGotMoney - Stock Analysis JavaScript
 * Handles stock analysis, chart rendering, and watchlist functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeStockAnalysis();
    initializeWatchlist();
    initializeCharts();
    initializeModals();
    
    // Add fade-in animation to cards
    document.querySelectorAll('.stock-card').forEach(function(card, index) {
        card.style.setProperty('--index', index);
        setTimeout(function() {
            card.classList.add('fade-in');
        }, 100 * index);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

/**
 * Initialize stock analysis functionality
 */
function initializeStockAnalysis() {
    const analyzeForm = document.getElementById('analyzeStockForm');
    const analyzeButtons = document.querySelectorAll('.analyze-from-watchlist');
    const addToWatchlistFromAnalysis = document.querySelectorAll('.add-to-watchlist-from-analysis');
    const currentTickerSymbol = document.getElementById('currentTickerSymbol');
    
    // Handle stock analysis form submission
    if (analyzeForm) {
        analyzeForm.addEventListener('submit', function(e) {
            const tickerInput = document.getElementById('ticker_symbol');
            
            if (!tickerInput.value.trim()) {
                e.preventDefault();
                showNotification('Please enter a valid stock ticker symbol', 'warning');
                return false;
            }
            
            // Show loading state
            const analyzeButton = this.querySelector('button[type="submit"]');
            analyzeButton.disabled = true;
            analyzeButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing...';
            
            // Create loading overlay for results section
            const resultContainer = document.getElementById('analysisResult');
            if (resultContainer) {
                resultContainer.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="ms-3 h5 mb-0">Analyzing ${tickerInput.value.toUpperCase()}...</div>
                    </div>
                `;
            }
            
            // Let the form submit normally
        });
    }
    
    // Handle analyze from watchlist
    if (analyzeButtons.length > 0) {
        analyzeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const ticker = this.getAttribute('data-ticker');
                if (ticker) {
                    // Show quick loading feedback
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    button.disabled = true;
                    
                    // Set the ticker in the form and submit
                    const tickerInput = document.getElementById('ticker_symbol');
                    if (tickerInput) {
                        tickerInput.value = ticker;
                        document.getElementById('analyzeStockForm').submit();
                    }
                }
            });
        });
    }
    
    // Handle "Add to Watchlist" button from analysis results
    if (addToWatchlistFromAnalysis.length > 0) {
        addToWatchlistFromAnalysis.forEach(function(button) {
            button.addEventListener('click', function() {
                const ticker = this.getAttribute('data-ticker');
                const price = this.getAttribute('data-price');
                const company = this.getAttribute('data-company');
                
                if (ticker && price) {
                    const modal = document.getElementById('addToWatchlistModal');
                    const modalInstance = new bootstrap.Modal(modal);
                    
                    // Pre-fill the form
                    document.getElementById('ticker_symbol_watchlist').value = ticker;
                    document.getElementById('company_name').value = company;
                    document.getElementById('current_price_watchlist').value = price;
                    
                    // Show the modal
                    modalInstance.show();
                }
            });
        });
    }
    
    // Auto-refresh current price if viewing a stock
    if (currentTickerSymbol) {
        const ticker = currentTickerSymbol.value;
        if (ticker) {
            // Set up periodic price update every 60 seconds
            setInterval(function() {
                updateCurrentPrice(ticker);
            }, 60000); // 60 seconds
        }
    }
    
    // Add ticker symbol input enhancement
    const tickerInput = document.getElementById('ticker_symbol');
    if (tickerInput) {
        // Auto uppercase tickers
        tickerInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Fetch company info when user tabs/clicks out
        tickerInput.addEventListener('blur', function() {
            const ticker = this.value.trim();
            if (ticker && ticker.length >= 1 && ticker.length <= 5) {
                fetchTickerInfo(ticker);
            }
        });
    }
    
    // Add ticker search keyboard shortcut
    document.addEventListener('keydown', function(e) {
        // Alt+S for quick search focus
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            const tickerInput = document.getElementById('ticker_symbol');
            if (tickerInput) {
                tickerInput.focus();
            }
        }
    });
}

/**
 * Extract target buy and sell prices based on technical indicators
 */
function extractAndSetTargetPrices() {
    // Get current price - try multiple selectors to ensure we get it
    let currentPrice = 0;
    
    // First, try the main price display
    const priceElement = document.getElementById('currentStockPrice');
    if (priceElement) {
        // Get from text content first
        const priceText = priceElement.textContent.trim();
        const priceMatch = priceText.match(/\$?(\d+(\.\d+)?)/);
        if (priceMatch) {
            currentPrice = parseFloat(priceMatch[1]);
        } 
        // If that fails, try data attribute
        else if (priceElement.getAttribute('data-price')) {
            currentPrice = parseFloat(priceElement.getAttribute('data-price'));
        }
    }
    
    // If we couldn't get the price, try other elements
    if (!currentPrice) {
        const priceDisplays = document.querySelectorAll('.stock-price, h2, h3, [class*="price"]');
        for (const el of priceDisplays) {
            const text = el.textContent.trim();
            if (text.includes('$') && /\$\d+\.\d+/.test(text)) {
                const match = text.match(/\$(\d+\.\d+)/);
                if (match) {
                    currentPrice = parseFloat(match[1]);
                    break;
                }
            }
        }
    }
    
    console.log("Found current price:", currentPrice);
    
    // Get technical indicators (Bollinger Bands)
    let bollingerLower = 0;
    let bollingerUpper = 0;
    
    // Find Bollinger values from the page
    document.querySelectorAll('.indicator-item, tr, td, li').forEach(el => {
        const text = el.textContent.trim();
        
        if (text.includes('Bollinger Lower') || text.includes('Lower Bollinger')) {
            const match = text.match(/\$(\d+\.\d+)/);
            if (match) {
                bollingerLower = parseFloat(match[1]);
            } else {
                // Try to find in next element or sibling
                const valueEl = el.nextElementSibling || el.querySelector('.indicator-value');
                if (valueEl) {
                    const valueMatch = valueEl.textContent.match(/\$(\d+\.\d+)/);
                    if (valueMatch) {
                        bollingerLower = parseFloat(valueMatch[1]);
                    }
                }
            }
        }
        
        if (text.includes('Bollinger Upper') || text.includes('Upper Bollinger')) {
            const match = text.match(/\$(\d+\.\d+)/);
            if (match) {
                bollingerUpper = parseFloat(match[1]);
            } else {
                // Try to find in next element or sibling
                const valueEl = el.nextElementSibling || el.querySelector('.indicator-value');
                if (valueEl) {
                    const valueMatch = valueEl.textContent.match(/\$(\d+\.\d+)/);
                    if (valueMatch) {
                        bollingerUpper = parseFloat(valueMatch[1]);
                    }
                }
            }
        }
    });
    
    console.log("Found Bollinger Upper:", bollingerUpper);
    console.log("Found Bollinger Lower:", bollingerLower);
    
    // Calculate reasonable buy and sell targets
    let buyPrice = 0;
    let sellPrice = 0;
    
    // If we have Bollinger Bands, use them
    if (bollingerLower > 0 && bollingerUpper > 0) {
        // Use Bollinger Bands for targets
        buyPrice = bollingerLower;
        sellPrice = bollingerUpper;
    } 
    // If we have current price, use percentage-based targets
    else if (currentPrice > 0) {
        buyPrice = Math.round(currentPrice * 0.95 * 100) / 100; // 5% below current
        sellPrice = Math.round(currentPrice * 1.1 * 100) / 100;  // 10% above current
    }
    
    // Set the prices in the form fields
    const buyPriceInput = document.getElementById('target_buy_price');
    const sellPriceInput = document.getElementById('target_sell_price');
    
    if (buyPriceInput && buyPrice > 0) {
        buyPriceInput.value = buyPrice.toFixed(2);
    }
    
    if (sellPriceInput && sellPrice > 0) {
        sellPriceInput.value = sellPrice.toFixed(2);
    }
    
    console.log("Set buy price:", buyPrice);
    console.log("Set sell price:", sellPrice);
}

/**
 * Fetch basic ticker information
 * @param {string} ticker Stock ticker symbol
 */
function fetchTickerInfo(ticker) {
    // Check if we're already in analysis mode for this ticker
    const currentTickerSymbol = document.getElementById('currentTickerSymbol');
    if (currentTickerSymbol && currentTickerSymbol.value === ticker) {
        return; // Already analyzing this ticker
    }
    
    // Quick check for valid ticker format
    if (!ticker.match(/^[A-Z0-9.]{1,10}$/)) {
        return;
    }
    
    // Show subtle loading indicator
    const formIcon = document.querySelector('.form-icon');
    if (formIcon) {
        formIcon.classList.remove('fa-search');
        formIcon.classList.add('fa-spinner', 'fa-spin');
    }
    
    // Fetch basic info about the ticker
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            // Reset icon
            if (formIcon) {
                formIcon.classList.remove('fa-spinner', 'fa-spin');
                formIcon.classList.add('fa-search');
            }
            
            if (data.status === 'success' && data.price > 0) {
                // Show a quick tooltip with the current price
                showNotification(`${ticker}: $${data.price.toFixed(2)}`, 'info', 2000);
            }
        })
        .catch(error => {
            // Reset icon
            if (formIcon) {
                formIcon.classList.remove('fa-spinner', 'fa-spin');
                formIcon.classList.add('fa-search');
            }
        });
}

/**
 * Update current price via AJAX
 * @param {string} ticker Stock ticker symbol
 */
function updateCurrentPrice(ticker) {
    const priceElement = document.getElementById('currentStockPrice');
    const priceChangeElement = document.getElementById('priceChange');
    const priceChangePercentElement = document.getElementById('priceChangePercent');
    
    if (!priceElement) return;
    
    // Show subtle loading indicator
    priceElement.classList.add('updating');
    
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            // Remove loading indicator
            priceElement.classList.remove('updating');
            
            if (data.status === 'success') {
                const oldPrice = parseFloat(priceElement.getAttribute('data-price'));
                const newPrice = parseFloat(data.price);
                
                if (newPrice !== oldPrice) {
                    // Update price display with animation
                    priceElement.classList.add('price-updated');
                    setTimeout(function() {
                        priceElement.classList.remove('price-updated');
                    }, 2000);
                    
                    // Update the price
                    priceElement.textContent = '$' + newPrice.toFixed(2);
                    priceElement.setAttribute('data-price', newPrice);
                    
                    // Calculate and update price change
                    const priceChange = newPrice - oldPrice;
                    const priceChangePercent = (priceChange / oldPrice) * 100;
                    
                    if (priceChangeElement && priceChangePercentElement) {
                        priceChangeElement.textContent = priceChange.toFixed(2);
                        priceChangePercentElement.textContent = priceChangePercent.toFixed(2);
                        
                        // Update change class
                        const changeContainer = priceChangeElement.closest('.price-change');
                        if (changeContainer) {
                            if (priceChange >= 0) {
                                changeContainer.classList.remove('negative');
                                changeContainer.classList.add('positive');
                                changeContainer.querySelector('i').className = 'fas fa-caret-up';
                            } else {
                                changeContainer.classList.remove('positive');
                                changeContainer.classList.add('negative');
                                changeContainer.querySelector('i').className = 'fas fa-caret-down';
                            }
                        }
                    }
                    
                    // Update title to show real-time price
                    document.title = `${ticker}: $${newPrice.toFixed(2)} | Stocks - iGotMoney`;
                }
            }
        })
        .catch(error => {
            priceElement.classList.remove('updating');
            console.error('Error updating price:', error);
        });
}

/**
 * Initialize watchlist functionality
 */
function initializeWatchlist() {
    const watchlistSearch = document.getElementById('watchlistSearch');
    const removeButtons = document.querySelectorAll('.remove-from-watchlist');
    const editButtons = document.querySelectorAll('.edit-watchlist');
    
    // Handle watchlist search
    if (watchlistSearch) {
        watchlistSearch.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableId = this.getAttribute('data-target');
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(function(row) {
                    const symbol = row.querySelector('td[data-symbol]')?.getAttribute('data-symbol')?.toLowerCase() || '';
                    const text = row.textContent.toLowerCase();
                    const notes = row.getAttribute('data-notes')?.toLowerCase() || '';
                    
                    if (symbol.includes(searchValue) || text.includes(searchValue) || notes.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show message if no results
                const noResultsMessage = table.nextElementSibling;
                if (noResultsMessage && noResultsMessage.classList.contains('no-results-message')) {
                    let hasVisibleRows = false;
                    
                    rows.forEach(row => {
                        if (row.style.display !== 'none') {
                            hasVisibleRows = true;
                        }
                    });
                    
                    noResultsMessage.style.display = hasVisibleRows ? 'none' : 'block';
                } else if (rows.length > 0) {
                    // Create no results message if it doesn't exist
                    let hasVisibleRows = false;
                    
                    rows.forEach(row => {
                        if (row.style.display !== 'none') {
                            hasVisibleRows = true;
                        }
                    });
                    
                    if (!hasVisibleRows) {
                        const message = document.createElement('div');
                        message.className = 'no-results-message text-center py-3';
                        message.innerHTML = `
                            <p class="text-muted mb-0">No stocks found matching "${this.value}"</p>
                        `;
                        
                        table.parentNode.insertBefore(message, table.nextSibling);
                    }
                }
            }
        });
        
        // Clear search button
        const searchContainer = watchlistSearch.parentElement;
        if (searchContainer) {
            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'clear-search';
            clearButton.innerHTML = '&times;';
            clearButton.style.position = 'absolute';
            clearButton.style.right = '10px';
            clearButton.style.top = '50%';
            clearButton.style.transform = 'translateY(-50%)';
            clearButton.style.background = 'none';
            clearButton.style.border = 'none';
            clearButton.style.color = '#a0aec0';
            clearButton.style.fontSize = '18px';
            clearButton.style.cursor = 'pointer';
            clearButton.style.display = 'none';
            
            clearButton.addEventListener('click', function() {
                watchlistSearch.value = '';
                watchlistSearch.dispatchEvent(new Event('keyup'));
                this.style.display = 'none';
            });
            
            searchContainer.appendChild(clearButton);
            
            watchlistSearch.addEventListener('input', function() {
                clearButton.style.display = this.value ? 'block' : 'none';
            });
        }
    }
    
    // Handle remove from watchlist
    if (removeButtons.length > 0) {
        removeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const watchlistId = this.getAttribute('data-watchlist-id');
                const tickerSymbol = this.closest('tr').querySelector('[data-symbol]').getAttribute('data-symbol');
                const modal = document.getElementById('removeFromWatchlistModal');
                const modalInstance = new bootstrap.Modal(modal);
                
                // Set the ticker in the confirmation message
                const confirmMessage = modal.querySelector('.modal-body p');
                if (confirmMessage) {
                    confirmMessage.textContent = `Are you sure you want to remove ${tickerSymbol} from your watchlist?`;
                }
                
                document.getElementById('remove_watchlist_id').value = watchlistId;
                modalInstance.show();
            });
        });
    }
    
    // Handle edit watchlist
    if (editButtons.length > 0) {
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const watchlistId = this.getAttribute('data-watchlist-id');
                const row = this.closest('tr');
                const ticker = row.querySelector('[data-symbol]').getAttribute('data-symbol');
                const company = row.querySelector('.stock-company').textContent;
                const price = row.querySelector('[data-price]').getAttribute('data-price');
                
                // Get target prices
                const targetBuyCell = row.querySelectorAll('td')[3];
                const targetSellCell = row.querySelectorAll('td')[4];
                
                let targetBuy = '';
                let targetSell = '';
                
                if (targetBuyCell) {
                    const targetBuyElement = targetBuyCell.querySelector('.target-price');
                    if (targetBuyElement) {
                        targetBuy = targetBuyElement.textContent.replace('$', '');
                    }
                }
                
                if (targetSellCell) {
                    const targetSellElement = targetSellCell.querySelector('.target-price');
                    if (targetSellElement) {
                        targetSell = targetSellElement.textContent.replace('$', '');
                    }
                }
                
                const notes = row.getAttribute('data-notes') || '';
                
                const modal = document.getElementById('editWatchlistModal');
                const modalInstance = new bootstrap.Modal(modal);
                
                // Update modal title to include ticker
                const modalTitle = modal.querySelector('.modal-title');
                if (modalTitle) {
                    modalTitle.innerHTML = `<i class="fas fa-edit me-2"></i>Edit ${ticker}`;
                }
                
                // Fill the form
                document.getElementById('edit_watchlist_id').value = watchlistId;
                document.getElementById('edit_ticker_symbol').value = ticker;
                document.getElementById('edit_company_name').value = company;
                document.getElementById('edit_current_price').value = price;
                
                if (targetBuy && targetBuy !== '--') {
                    document.getElementById('edit_target_buy_price').value = targetBuy;
                } else {
                    document.getElementById('edit_target_buy_price').value = '';
                }
                
                if (targetSell && targetSell !== '--') {
                    document.getElementById('edit_target_sell_price').value = targetSell;
                } else {
                    document.getElementById('edit_target_sell_price').value = '';
                }
                
                document.getElementById('edit_notes').value = notes;
                
                modalInstance.show();
            });
        });
    }
    
    // Add quick refresh button for watchlist
    const watchlistHeader = document.querySelector('.watchlist-container .card-header');
    if (watchlistHeader) {
        const refreshButton = document.createElement('button');
        refreshButton.type = 'button';
        refreshButton.className = 'btn btn-sm btn-outline-primary ms-2';
        refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Prices';
        refreshButton.style.display = 'none'; // Hide initially
        
        refreshButton.addEventListener('click', function() {
            refreshWatchlistPrices();
            
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            
            // Re-enable after 5 seconds to prevent spam
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Prices';
            }, 5000);
        });
        
        // Show refresh button only if we have watchlist items
        const watchlistTable = document.getElementById('watchlistTable');
        if (watchlistTable && watchlistTable.querySelector('tbody tr')) {
            refreshButton.style.display = 'inline-block';
            watchlistHeader.appendChild(refreshButton);
        }
    }
}

/**
 * Refresh prices for all visible watchlist items
 */
function refreshWatchlistPrices() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    const rows = watchlistTable.querySelectorAll('tbody tr:not([style*="display: none"])');
    if (rows.length === 0) return;
    
    // Collect all ticker symbols
    const symbols = [];
    rows.forEach(row => {
        const symbol = row.querySelector('td[data-symbol]')?.getAttribute('data-symbol');
        if (symbol) symbols.push(symbol);
    });
    
    if (symbols.length === 0) return;
    
    // Show loading state on price cells
    rows.forEach(row => {
        const priceCell = row.querySelector('td[data-price]');
        if (priceCell) {
            priceCell.classList.add('updating');
        }
    });
    
    // Request updated prices
    fetch(`${BASE_PATH}/stocks?action=batch_stock_quotes&symbols=${symbols.join(',')}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'partial') {
                const quotes = data.quotes || {};
                
                rows.forEach(row => {
                    const symbol = row.querySelector('td[data-symbol]')?.getAttribute('data-symbol');
                    const priceCell = row.querySelector('td[data-price]');
                    
                    if (symbol && priceCell && quotes[symbol]) {
                        const oldPrice = parseFloat(priceCell.getAttribute('data-price'));
                        const newPrice = parseFloat(quotes[symbol].price);
                        
                        // Update price display
                        priceCell.textContent = '$' + newPrice.toFixed(2);
                        priceCell.setAttribute('data-price', newPrice);
                        priceCell.classList.remove('updating');
                        
                        // Add visual indication of price change
                        if (newPrice !== oldPrice) {
                            priceCell.classList.add('price-updated');
                            
                            // Add price change indicator
                            const indicator = document.createElement('span');
                            indicator.className = 'price-indicator ms-2';
                            
                            if (newPrice > oldPrice) {
                                indicator.className += ' text-success';
                                indicator.innerHTML = '<i class="fas fa-caret-up"></i>';
                            } else if (newPrice < oldPrice) {
                                indicator.className += ' text-danger';
                                indicator.innerHTML = '<i class="fas fa-caret-down"></i>';
                            }
                            
                            priceCell.appendChild(indicator);
                            
                            // Remove indicator after 5 seconds
                            setTimeout(() => {
                                if (indicator.parentNode) {
                                    indicator.parentNode.removeChild(indicator);
                                }
                                priceCell.classList.remove('price-updated');
                            }, 5000);
                        }
                    } else if (priceCell) {
                        priceCell.classList.remove('updating');
                    }
                });
                
                showNotification('Prices updated successfully', 'success');
            } else {
                // Remove loading state
                rows.forEach(row => {
                    const priceCell = row.querySelector('td[data-price]');
                    if (priceCell) {
                        priceCell.classList.remove('updating');
                    }
                });
                
                showNotification('Could not update prices. Try again later.', 'warning');
            }
        })
        .catch(error => {
            // Remove loading state
            rows.forEach(row => {
                const priceCell = row.querySelector('td[data-price]');
                if (priceCell) {
                    priceCell.classList.remove('updating');
                }
            });
            
            console.error('Error updating watchlist prices:', error);
            showNotification('Error updating prices', 'danger');
        });
}

/**
 * Initialize charts if stock price data is available
 */
function initializeCharts() {
    // If stock price data is not available, return
    if (typeof stockPriceData === 'undefined' || !stockPriceData) return;
    
    // Get chart context
    const priceChartCtx = document.getElementById('stockPriceChart')?.getContext('2d');
    const volumeChartCtx = document.getElementById('stockVolumeChart')?.getContext('2d');
    
    // If chart context is not available, return
    if (!priceChartCtx) return;
    
    // Create gradient for price chart
    const gradient = priceChartCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(67, 97, 238, 0.3)');
    gradient.addColorStop(1, 'rgba(67, 97, 238, 0.0)');
    
    // Get current price and indicators for reference lines
    const currentPriceElement = document.getElementById('currentStockPrice');
    const shortMAElement = document.querySelector('.indicator-item:nth-child(1) .indicator-value');
    const longMAElement = document.querySelector('.indicator-item:nth-child(2) .indicator-value');
    
    let currentPrice = 0;
    let shortMA = 0;
    let longMA = 0;
    
    if (currentPriceElement) {
        currentPrice = parseFloat(currentPriceElement.getAttribute('data-price'));
    }
    
    if (shortMAElement) {
        shortMA = parseFloat(shortMAElement.textContent.replace('$', ''));
    }
    
    if (longMAElement) {
        longMA = parseFloat(longMAElement.textContent.replace('$', ''));
    }
    
    // Prepare datasets for the price chart
    const dataSets = [
        {
            label: 'Price',
            data: stockPriceData.prices,
            borderColor: '#4361ee',
            backgroundColor: gradient,
            borderWidth: 2,
            fill: true,
            tension: 0.2,
            pointRadius: 2,
            pointHoverRadius: 6,
            pointBackgroundColor: '#4361ee',
            pointHoverBackgroundColor: '#4361ee',
            pointBorderColor: '#fff',
            pointHoverBorderColor: '#fff',
            pointBorderWidth: 1,
            pointHoverBorderWidth: 2
        }
    ];
    
    // Add MA lines if we have the data
    if (shortMA > 0) {
        dataSets.push({
            label: '20-day MA',
            data: Array(stockPriceData.prices.length).fill(shortMA),
            borderColor: 'rgba(52, 152, 219, 0.7)',
            borderWidth: 1.5,
            borderDash: [5, 5],
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 0
        });
    }
    
    if (longMA > 0) {
        dataSets.push({
            label: '50-day MA',
            data: Array(stockPriceData.prices.length).fill(longMA),
            borderColor: 'rgba(243, 156, 18, 0.7)',
            borderWidth: 1.5,
            borderDash: [2, 3],
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 0
        });
    }
    
    // Create price chart
    const priceChart = new Chart(priceChartCtx, {
        type: 'line',
        data: {
            labels: stockPriceData.dates,
            datasets: dataSets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 6,
                    caretSize: 6,
                    callbacks: {
                        label: function(context) {
                            // Use different prefix based on dataset
                            let prefix = context.dataset.label + ': ';
                            return prefix + '$' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        },
                        font: {
                            size: 11
                        },
                        stepSize: calculateYAxisStepSize(stockPriceData.prices)
                    }
                }
            }
        }
    });
    
    // Create volume chart if volumeChartCtx exists and stockPriceData.volumes exists
    if (volumeChartCtx && stockPriceData.volumes && stockPriceData.volumes.length > 0) {
        // Create gradient for volume bars
        const volumeGradient = volumeChartCtx.createLinearGradient(0, 0, 0, 200);
        volumeGradient.addColorStop(0, 'rgba(52, 152, 219, 0.7)');
        volumeGradient.addColorStop(1, 'rgba(52, 152, 219, 0.2)');
        
        const volumeChart = new Chart(volumeChartCtx, {
            type: 'bar',
            data: {
                labels: stockPriceData.dates,
                datasets: [{
                    label: 'Volume',
                    data: stockPriceData.volumes,
                    backgroundColor: volumeGradient,
                    borderColor: 'rgba(52, 152, 219, 0.8)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        callbacks: {
                            label: function(context) {
                                return 'Volume: ' + formatNumber(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value, 0);
                            },
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Add chart period functionality
    const periodButtons = document.querySelectorAll('.chart-period-btn');
    if (periodButtons.length > 0) {
        periodButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const period = this.getAttribute('data-period');
                
                // Remove active class from all buttons
                periodButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update chart timeframe
                updateChartPeriod(period, priceChart, window.volumeChart);
            });
        });
    }
    
    // Store chart references globally to access later
    window.priceChart = priceChart;
}

/**
 * Calculate appropriate step size for Y axis based on price range
 * @param {Array} prices Array of price data points
 * @return {number} Step size for Y axis
 */
function calculateYAxisStepSize(prices) {
    if (!prices || prices.length === 0) return 5;
    
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const range = max - min;
    
    // If range is small, use small step size
    if (range < 1) return 0.1;
    if (range < 5) return 0.5;
    if (range < 10) return 1;
    if (range < 20) return 2;
    if (range < 50) return 5;
    if (range < 100) return 10;
    
    // For larger ranges, use larger steps
    return Math.ceil(range / 10);
}

/**
 * Update chart based on selected time period
 * @param {string} period Time period (1m, 3m, 1y)
 * @param {object} priceChart Chart.js price chart instance
 * @param {object} volumeChart Chart.js volume chart instance (optional)
 */
function updateChartPeriod(period, priceChart, volumeChart) {
    if (!priceChart || !stockPriceData) return;
    
    // Show loading overlay
    const chartContainer = document.querySelector('.stock-chart-container');
    if (chartContainer) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        chartContainer.appendChild(overlay);
    }
    
    // Determine how many data points to show
    let pointsToShow;
    switch(period) {
        case '3m':
            pointsToShow = 90;
            break;
        case '1y':
            pointsToShow = 365;
            break;
        case '1m':
        default:
            pointsToShow = 30;
    }
    
    // Ensure we don't exceed available data
    const dataLength = stockPriceData.dates.length;
    pointsToShow = Math.min(dataLength, pointsToShow);
    
    // Update chart data
    if (pointsToShow < dataLength) {
        // We have more data than needed, slice to show only the required period
        priceChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
        
        priceChart.data.datasets.forEach((dataset, index) => {
            // Only slice the price data, not the reference lines
            if (index === 0) {
                dataset.data = stockPriceData.prices.slice(-pointsToShow);
            } else {
                // Adjust reference lines to match new data length
                dataset.data = Array(pointsToShow).fill(dataset.data[0]);
            }
        });
        
        // Update volume chart if available
        if (volumeChart && stockPriceData.volumes) {
            volumeChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
            volumeChart.data.datasets[0].data = stockPriceData.volumes.slice(-pointsToShow);
            volumeChart.update();
        }
    }
    
    // Update the price chart
    priceChart.update();
    
    // Remove loading overlay after a short delay
    setTimeout(() => {
        if (chartContainer) {
            const overlay = chartContainer.querySelector('.loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    }, 500);
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    // Add to Watchlist Modal - Pre-populate fields when clicking "Add to Watchlist" from analysis
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-watchlist-from-analysis') || 
            e.target.closest('.add-to-watchlist-from-analysis') ||
            (e.target.textContent.includes('Add to Watchlist') && e.target.tagName === 'BUTTON')) {
                
            const button = e.target.classList.contains('add-to-watchlist-from-analysis') ? 
                        e.target : e.target.closest('.add-to-watchlist-from-analysis') || e.target;
                
            // Using dataset attributes if available, otherwise try to find from page context
            const ticker = button.getAttribute('data-ticker') || 
                          document.getElementById('currentTickerSymbol')?.value ||
                          document.querySelector('.stock-info h3')?.textContent;
                          
            const price = button.getAttribute('data-price') || 
                         document.getElementById('currentStockPrice')?.getAttribute('data-price') ||
                         parseFloat(document.getElementById('currentStockPrice')?.textContent.replace('$', ''));
                         
            const company = button.getAttribute('data-company') || 
                           document.querySelector('.stock-info p')?.textContent ||
                           document.querySelector('.company-name')?.textContent;
            
            if (ticker) {
                const modal = document.getElementById('addToWatchlistModal');
                if (modal) {
                    // Pre-fill the form
                    const tickerInput = document.getElementById('ticker_symbol_watchlist');
                    const companyInput = document.getElementById('company_name');
                    const priceInput = document.getElementById('current_price_watchlist');
                    
                    if (tickerInput) tickerInput.value = ticker;
                    if (companyInput && company) companyInput.value = company;
                    if (priceInput && price) priceInput.value = typeof price === 'number' ? price.toFixed(2) : price;
                    
                    // Show the modal
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                    
                    // Extract and set target prices after modal is shown
                    setTimeout(extractAndSetTargetPrices, 100);
                }
            }
        }
    });
    
    // Auto-fetch stock info when entering ticker in watchlist modal
    const watchlistTickerInput = document.getElementById('ticker_symbol_watchlist');
    if (watchlistTickerInput) {
        // Auto uppercase tickers
        watchlistTickerInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Fetch current price when typing stops
        let typingTimer;
        watchlistTickerInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            
            const ticker = this.value.trim();
            if (ticker.length >= 1 && ticker.length <= 5) {
                // Show loading indicator
                const companyInput = document.getElementById('company_name');
                const priceInput = document.getElementById('current_price_watchlist');
                
                if (companyInput && priceInput) {
                    companyInput.placeholder = 'Loading...';
                    priceInput.placeholder = 'Loading...';
                }
                
                typingTimer = setTimeout(function() {
                    fetchStockInfoForWatchlist(ticker);
                }, 800);
            }
        });
    }
    
    // Also bind to the modal shown event to make sure we have access to the DOM
    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target.id === 'addToWatchlistModal') {
            // Try to extract and set prices again when modal is fully shown
            extractAndSetTargetPrices();
        }
    });
    
    // Enhance form validation for all modals
    document.querySelectorAll('.modal form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find first invalid input and focus it
                const invalidInput = this.querySelector(':invalid');
                if (invalidInput) {
                    invalidInput.focus();
                    
                    // Show validation message
                    const formGroup = invalidInput.closest('.mb-3');
                    if (formGroup) {
                        const invalidFeedback = formGroup.querySelector('.invalid-feedback');
                        if (!invalidFeedback) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = invalidInput.validationMessage;
                            invalidInput.parentNode.appendChild(feedback);
                        }
                    }
                }
            }
            
            this.classList.add('was-validated');
        });
    });
}

/**
 * Fetch stock information for watchlist
 * @param {string} ticker Stock ticker symbol
 */
function fetchStockInfoForWatchlist(ticker) {
    const companyInput = document.getElementById('company_name');
    const priceInput = document.getElementById('current_price_watchlist');
    
    if (!companyInput || !priceInput) return;
    
    // Check if ticker is valid
    if (!ticker.match(/^[A-Z0-9.]{1,10}$/)) {
        companyInput.placeholder = 'Company Name';
        priceInput.placeholder = 'Current Price';
        return;
    }
    
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            // Reset placeholders
            companyInput.placeholder = 'Company Name';
            priceInput.placeholder = 'Current Price';
            
            if (data.status === 'success') {
                priceInput.value = data.price.toFixed(2);
                
                // If company name wasn't provided, use ticker as default
                if (!companyInput.value) {
                    companyInput.value = ticker + ' Inc.';
                }
            }
        })
        .catch(error => {
            companyInput.placeholder = 'Company Name';
            priceInput.placeholder = 'Current Price';
            console.error('Error fetching stock info:', error);
        });
}

/**
 * Show notification toast
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Remove any existing notifications with the same message
    const existingNotifications = document.querySelectorAll('.stock-notification');
    existingNotifications.forEach(notification => {
        if (notification.textContent.includes(message)) {
            notification.remove();
        }
    });
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `stock-notification ${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '8px';
    notification.style.backgroundColor = getColorByType(type);
    notification.style.color = 'white';
    notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notification.style.zIndex = '9999';
    notification.style.transform = 'translateX(120%)';
    notification.style.transition = 'transform 0.3s ease';
    
    // Add icon based on type
    let icon;
    switch (type) {
        case 'success':
            icon = 'fa-check-circle';
            break;
        case 'warning':
            icon = 'fa-exclamation-triangle';
            break;
        case 'danger':
            icon = 'fa-exclamation-circle';
            break;
        case 'info':
        default:
            icon = 'fa-info-circle';
    }
    
    notification.innerHTML = `<i class="fas ${icon} me-2"></i> ${message}`;
    
    // Add close button
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '&times;';
    closeButton.style.marginLeft = '10px';
    closeButton.style.background = 'none';
    closeButton.style.border = 'none';
    closeButton.style.color = 'white';
    closeButton.style.fontSize = '16px';
    closeButton.style.fontWeight = 'bold';
    closeButton.style.cursor = 'pointer';
    
    closeButton.addEventListener('click', function() {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
    
    notification.appendChild(closeButton);
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto-hide after duration
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

/**
 * Get color for notification based on type
 * @param {string} type - Notification type
 * @returns {string} Color for notification
 */
function getColorByType(type) {
    switch (type) {
        case 'success':
            return 'rgba(46, 204, 113, 0.9)';
        case 'warning':
            return 'rgba(243, 156, 18, 0.9)';
        case 'danger':
            return 'rgba(231, 76, 60, 0.9)';
        case 'info':
        default:
            return 'rgba(52, 152, 219, 0.9)';
    }
}

/**
 * Format number with commas and optional decimal places
 * @param {number} number - Number to format
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted number
 */
function formatNumber(number, decimals = 2) {
    if (number === null || isNaN(number)) return '0';
    
    if (number >= 1000000) {
        return (number / 1000000).toFixed(1) + 'M';
    } else if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'K';
    }
    
    const parts = number.toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    return parts.join('.');
}

/**
 * Format currency
 * @param {number} number - Number to format
 * @returns {string} Formatted currency
 */
function formatCurrency(number) {
    return '$' + formatNumber(number);
}