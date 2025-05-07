/**
 * iGotMoney - Modern Stock Analysis JavaScript
 * Handles stock data visualization, real-time updates, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize stock page
    initializeStockPage();
    
    // Setup form submissions and actions
    setupFormHandlers();
    
    // Initialize any data visualizations
    initializeCharts();
    
    // Setup real-time price updates
    setupRealTimePriceUpdates();
});

/**
 * Initialize the stock page components
 */
function initializeStockPage() {
    // Add animations to cards
    animateCards();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize search functionality
    initializeSearch();
}

/**
 * Add animations to cards with staggered timing
 */
function animateCards() {
    const cards = document.querySelectorAll('.stock-card');
    
    cards.forEach((card, index) => {
        card.style.setProperty('--index', index);
        card.classList.add('fade-in');
    });
}

/**
 * Initialize tooltips for interactive elements
 */
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize search functionality for tables
 */
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        const targetTable = document.getElementById(input.getAttribute('data-target'));
        
        if (targetTable) {
            input.addEventListener('keyup', function() {
                const searchText = this.value.toLowerCase();
                
                targetTable.querySelectorAll('tbody tr').forEach(row => {
                    let found = false;
                    row.querySelectorAll('td').forEach(cell => {
                        if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                            found = true;
                        }
                    });
                    
                    row.style.display = found ? '' : 'none';
                });
            });
        }
    });
}

/**
 * Setup event handlers for forms and button actions
 */
function setupFormHandlers() {
    // Stock analysis form submit
    const analyzeForm = document.getElementById('analyzeStockForm');
    if (analyzeForm) {
        analyzeForm.addEventListener('submit', function(e) {
            const tickerInput = document.getElementById('ticker_symbol');
            const resultContainer = document.getElementById('analysisResult');
            
            if (resultContainer) {
                // Show loading state
                resultContainer.innerHTML = `
                    <div class="loading-overlay">
                        <div class="loading-spinner"></div>
                    </div>
                `;
            }
        });
    }
    
    // Analyze from watchlist buttons
    const analyzeButtons = document.querySelectorAll('.analyze-from-watchlist');
    analyzeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticker = this.getAttribute('data-ticker');
            const tickerInput = document.getElementById('ticker_symbol');
            
            if (tickerInput) {
                tickerInput.value = ticker;
                const form = document.getElementById('analyzeStockForm');
                if (form) form.submit();
            }
        });
    });
    
    // Add to watchlist from analysis buttons
    const addToWatchlistButtons = document.querySelectorAll('.add-to-watchlist-from-analysis');
    addToWatchlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticker = this.getAttribute('data-ticker');
            const price = this.getAttribute('data-price');
            const company = this.getAttribute('data-company');
            
            // Fill modal form
            const tickerInput = document.getElementById('ticker_symbol_watchlist');
            const priceInput = document.getElementById('current_price_watchlist');
            const companyInput = document.getElementById('company_name');
            
            if (tickerInput) tickerInput.value = ticker;
            if (priceInput) priceInput.value = price;
            if (companyInput) companyInput.value = company || ticker;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addToWatchlistModal'));
            modal.show();
        });
    });
    
    // Remove from watchlist buttons
    const removeButtons = document.querySelectorAll('.remove-from-watchlist');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const watchlistId = this.getAttribute('data-watchlist-id');
            const idInput = document.getElementById('remove_watchlist_id');
            
            if (idInput) {
                idInput.value = watchlistId;
                const modal = new bootstrap.Modal(document.getElementById('removeFromWatchlistModal'));
                modal.show();
            }
        });
    });
    
    // Edit watchlist buttons
    const editButtons = document.querySelectorAll('.edit-watchlist');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const watchlistId = this.getAttribute('data-watchlist-id');
            const row = this.closest('tr');
            
            if (row) {
                // Get data from the row
                const symbol = row.querySelector('td:nth-child(1)').textContent.trim();
                const company = row.querySelector('td:nth-child(2)').textContent.trim();
                const price = row.querySelector('td:nth-child(3)').textContent.trim().replace('$', '');
                
                // Get target prices
                let buyPrice = row.querySelector('td:nth-child(4)').textContent.trim();
                buyPrice = buyPrice !== '--' ? buyPrice.replace('$', '') : '';
                
                let sellPrice = row.querySelector('td:nth-child(5)').textContent.trim();
                sellPrice = sellPrice !== '--' ? sellPrice.replace('$', '') : '';
                
                // Get notes if any
                const notes = row.getAttribute('data-notes') || '';
                
                // Fill the form
                document.getElementById('edit_watchlist_id').value = watchlistId;
                document.getElementById('edit_ticker_symbol').value = symbol;
                document.getElementById('edit_company_name').value = company;
                document.getElementById('edit_current_price').value = price;
                document.getElementById('edit_target_buy_price').value = buyPrice;
                document.getElementById('edit_target_sell_price').value = sellPrice;
                document.getElementById('edit_notes').value = notes;
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editWatchlistModal'));
                modal.show();
            }
        });
    });
    
    // Chart period buttons
    const periodButtons = document.querySelectorAll('.chart-period-btn');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.getAttribute('data-period');
            updateChart(period);
        });
    });
}

/**
 * Initialize stock price chart
 */
function initializeCharts() {
    const chartCanvas = document.getElementById('stockPriceChart');
    
    if (chartCanvas && typeof stockPriceData !== 'undefined' && stockPriceData) {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please make sure it is properly included.');
            
            // Create a message to display on the chart canvas
            const ctx = chartCanvas.getContext('2d');
            ctx.font = '14px Arial';
            ctx.fillStyle = '#e74c3c';
            ctx.textAlign = 'center';
            ctx.fillText('Chart.js is not loaded. Please check your dependencies.', chartCanvas.width / 2, chartCanvas.height / 2);
            return;
        }
        
        // Check if data is valid
        if (!stockPriceData.dates || !stockPriceData.prices || 
            stockPriceData.dates.length === 0 || stockPriceData.prices.length === 0) {
            console.error('Invalid stock price data:', stockPriceData);
            
            // Create a message to display on the chart canvas
            const ctx = chartCanvas.getContext('2d');
            ctx.font = '14px Arial';
            ctx.fillStyle = '#e74c3c';
            ctx.textAlign = 'center';
            ctx.fillText('No stock data available for chart visualization.', chartCanvas.width / 2, chartCanvas.height / 2);
            return;
        }
        
        try {
            const ctx = chartCanvas.getContext('2d');
            
            // Set chart gradient
            const gradientFill = ctx.createLinearGradient(0, 0, 0, chartCanvas.height);
            gradientFill.addColorStop(0, 'rgba(67, 97, 238, 0.3)');
            gradientFill.addColorStop(1, 'rgba(67, 97, 238, 0.0)');
            
            window.stockChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: stockPriceData.dates,
                    datasets: [
                        {
                            label: 'Price',
                            data: stockPriceData.prices,
                            borderColor: 'rgb(67, 97, 238)',
                            backgroundColor: gradientFill,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: 'rgb(67, 97, 238)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxTicksLimit: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            grid: {
                                borderDash: [2],
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toFixed(2);
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            cornerRadius: 4,
                            displayColors: false
                        }
                    }
                }
            });
            
            // Add volume chart if volume data is available
            if (stockPriceData.volumes && stockPriceData.volumes.length > 0) {
                const volumeCanvas = document.getElementById('stockVolumeChart');
                if (volumeCanvas) {
                    const volumeCtx = volumeCanvas.getContext('2d');
                    
                    window.volumeChart = new Chart(volumeCtx, {
                        type: 'bar',
                        data: {
                            labels: stockPriceData.dates,
                            datasets: [
                                {
                                    label: 'Volume',
                                    data: stockPriceData.volumes,
                                    backgroundColor: 'rgba(67, 97, 238, 0.3)',
                                    borderColor: 'rgba(67, 97, 238, 0.5)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    display: false
                                },
                                y: {
                                    grid: {
                                        borderDash: [2],
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        callback: function(value) {
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(1) + 'K';
                                            }
                                            return value;
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(2) + ' Million shares';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(2) + ' Thousand shares';
                                            }
                                            return value + ' shares';
                                        }
                                    },
                                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                    padding: 10,
                                    cornerRadius: 4,
                                    displayColors: false
                                }
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error initializing charts:', error);
            showNotification('Error initializing chart. Please try again.', 'danger');
        }
    }
}

/**
 * Update chart based on the selected time period
 * @param {string} period - Time period to display (1d, 1w, 1m, 3m, 1y)
 */
function updateChart(period) {
    if (!window.stockChart || !stockPriceData) return;
    
    // Show loading state
    const chartContainer = document.querySelector('.stock-chart-container');
    if (chartContainer) {
        chartContainer.classList.add('loading');
        
        // Create loading overlay if it doesn't exist
        if (!chartContainer.querySelector('.loading-overlay')) {
            const loading = document.createElement('div');
            loading.className = 'loading-overlay';
            loading.innerHTML = '<div class="loading-spinner"></div>';
            chartContainer.appendChild(loading);
        } else {
            chartContainer.querySelector('.loading-overlay').style.display = 'flex';
        }
    }
    
    // Simulate API delay (would be replaced with actual API call in production)
    setTimeout(() => {
        let dataPoints;
        
        switch(period) {
            case '1w':
                dataPoints = 7;
                break;
            case '1m':
                dataPoints = 30;
                break;
            case '3m':
                dataPoints = 90;
                break;
            case '1y':
                dataPoints = 365;
                break;
            default: // 1d
                dataPoints = 1;
        }
        
        // Update chart with data slice based on period
        // In a real implementation, this would be an API call for the correct timeframe
        const dataLength = stockPriceData.dates.length;
        const pointsToShow = Math.min(dataLength, dataPoints);
        
        window.stockChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
        window.stockChart.data.datasets[0].data = stockPriceData.prices.slice(-pointsToShow);
        window.stockChart.update();
        
        // Update volume chart if it exists
        if (window.volumeChart) {
            window.volumeChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
            window.volumeChart.data.datasets[0].data = stockPriceData.volumes.slice(-pointsToShow);
            window.volumeChart.update();
        }
        
        // Hide loading state
        if (chartContainer) {
            chartContainer.classList.remove('loading');
            const loadingOverlay = chartContainer.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        }
    }, 800);
}

/**
 * Setup real-time price updates for analyzed stock and watchlist
 */
function setupRealTimePriceUpdates() {
    // Set up real-time updates for the analyzed stock (if present)
    const currentTickerElement = document.getElementById('currentTickerSymbol');
    if (currentTickerElement) {
        const ticker = currentTickerElement.value;
        if (ticker) {
            // Update price every 60 seconds (adjust based on API limits)
            setInterval(() => {
                updateStockPrice(ticker);
            }, 60000);
        }
    }
    
    // Set up real-time updates for watchlist items
    const watchlistTable = document.getElementById('watchlistTable');
    if (watchlistTable) {
        const watchlistItems = watchlistTable.querySelectorAll('tbody tr');
        
        if (watchlistItems.length > 0) {
            // Update all watchlist prices every 5 minutes (adjust based on API limits)
            setInterval(() => {
                updateWatchlistPrices();
            }, 300000);
        }
    }
}

/**
 * Update the stock price for the currently analyzed stock
 * @param {string} ticker - Stock ticker symbol
 */
function updateStockPrice(ticker) {
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const priceElement = document.getElementById('currentStockPrice');
                if (priceElement) {
                    const oldPrice = parseFloat(priceElement.getAttribute('data-price'));
                    const newPrice = parseFloat(data.price);
                    
                    // Update price display
                    priceElement.textContent = '$' + newPrice.toFixed(2);
                    priceElement.setAttribute('data-price', newPrice);
                    
                    // Add visual indication of price change
                    priceElement.classList.add('price-updated');
                    setTimeout(() => {
                        priceElement.classList.remove('price-updated');
                    }, 2000);
                    
                    // Update price change elements if they exist
                    const changeElement = document.getElementById('priceChange');
                    const changePercentElement = document.getElementById('priceChangePercent');
                    
                    if (changeElement && changePercentElement) {
                        const priceDiff = newPrice - oldPrice;
                        const percentChange = (priceDiff / oldPrice) * 100;
                        
                        changeElement.textContent = priceDiff.toFixed(2);
                        changePercentElement.textContent = percentChange.toFixed(2) + '%';
                        
                        // Update classes based on price direction
                        const container = changeElement.parentElement;
                        if (container) {
                            container.classList.remove('positive', 'negative');
                            container.classList.add(priceDiff >= 0 ? 'positive' : 'negative');
                            
                            // Update icon
                            const icon = container.querySelector('i');
                            if (icon) {
                                icon.className = priceDiff >= 0 ? 'fas fa-caret-up' : 'fas fa-caret-down';
                            }
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error updating stock price:', error);
        });
}

/**
 * Update prices for all stocks in the watchlist
 */
function updateWatchlistPrices() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    const rows = watchlistTable.querySelectorAll('tbody tr');
    if (rows.length === 0) return;
    
    // Collect all ticker symbols
    const symbols = [];
    rows.forEach(row => {
        const symbolCell = row.querySelector('td:first-child');
        if (symbolCell) {
            const symbol = symbolCell.getAttribute('data-symbol') || symbolCell.textContent.trim();
            symbols.push(symbol);
        }
    });
    
    // Make batch request to update all prices
    if (symbols.length > 0) {
        fetch(`${BASE_PATH}/stocks?action=get_stock_data&batch=1&symbols=${symbols.join(',')}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.quotes) {
                    rows.forEach(row => {
                        const symbolCell = row.querySelector('td:first-child');
                        const priceCell = row.querySelector('td:nth-child(3)');
                        
                        if (symbolCell && priceCell) {
                            const symbol = symbolCell.getAttribute('data-symbol') || symbolCell.textContent.trim();
                            
                            if (data.quotes[symbol]) {
                                const oldPrice = parseFloat(priceCell.getAttribute('data-price') || '0');
                                const newPrice = parseFloat(data.quotes[symbol].price);
                                
                                // Update price display
                                priceCell.textContent = '$' + newPrice.toFixed(2);
                                priceCell.setAttribute('data-price', newPrice);
                                
                                // Add visual indication
                                priceCell.classList.add('price-updated');
                                setTimeout(() => {
                                    priceCell.classList.remove('price-updated');
                                }, 2000);
                                
                                // Add price change indicator
                                if (!priceCell.querySelector('.price-indicator')) {
                                    const indicator = document.createElement('span');
                                    indicator.className = 'price-indicator ms-2';
                                    priceCell.appendChild(indicator);
                                }
                                
                                const indicator = priceCell.querySelector('.price-indicator');
                                if (indicator) {
                                    indicator.className = 'price-indicator ms-2';
                                    
                                    if (newPrice > oldPrice) {
                                        indicator.className += ' text-success';
                                        indicator.innerHTML = '<i class="fas fa-caret-up"></i>';
                                    } else if (newPrice < oldPrice) {
                                        indicator.className += ' text-danger';
                                        indicator.innerHTML = '<i class="fas fa-caret-down"></i>';
                                    }
                                    
                                    // Remove indicator after a few seconds
                                    setTimeout(() => {
                                        indicator.innerHTML = '';
                                    }, 5000);
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error updating watchlist prices:', error);
            });
    }
}

/**
 * Fetch stock info (name, price) based on ticker symbol
 * @param {string} ticker - Stock ticker symbol
 * @param {HTMLElement} companyNameField - Company name input field
 * @param {HTMLElement} priceField - Price input field
 */
function fetchStockInfo(ticker, companyNameField, priceField) {
    if (!ticker || !companyNameField || !priceField) return;
    
    // Show loading state
    companyNameField.disabled = true;
    priceField.disabled = true;
    companyNameField.value = 'Loading...';
    
    fetch(`${BASE_PATH}/stocks?action=get_stock_data&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                companyNameField.value = data.company_name || ticker;
                priceField.value = data.current_price;
            } else {
                companyNameField.value = ticker;
            }
        })
        .catch(error => {
            console.error('Error fetching stock info:', error);
            companyNameField.value = ticker;
        })
        .finally(() => {
            companyNameField.disabled = false;
            priceField.disabled = false;
        });
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Check if notification container exists
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
    notification.className = `alert alert-${type} notification`;
    notification.style.minWidth = '300px';
    notification.style.boxShadow = '0 3px 10px rgba(0,0,0,0.15)';
    notification.style.borderRadius = '0.5rem';
    notification.style.marginBottom = '10px';
    notification.style.transform = 'translateX(120%)';
    notification.style.transition = 'transform 0.3s ease';
    
    // Set notification content
    let icon;
    switch(type) {
        case 'success':
            icon = 'check-circle';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            break;
        case 'danger':
            icon = 'exclamation-circle';
            break;
        default: // info
            icon = 'info-circle';
    }
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${icon} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" aria-label="Close"></button>
        </div>
    `;
    
    // Add close button handler
    notification.querySelector('.btn-close').addEventListener('click', function() {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Add to container
    container.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto-hide after duration
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}