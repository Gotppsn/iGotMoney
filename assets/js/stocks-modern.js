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
    const analyzeButton = document.querySelector('.analyze-from-watchlist');
    const addToWatchlistFromAnalysis = document.querySelector('.add-to-watchlist-from-analysis');
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
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing...';
            
            // Let the form submit normally since we're using PHP for the analysis
        });
    }
    
    // Handle analyze from watchlist
    if (analyzeButton) {
        const analyzeButtons = document.querySelectorAll('.analyze-from-watchlist');
        analyzeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const ticker = this.getAttribute('data-ticker');
                if (ticker) {
                    document.getElementById('ticker_symbol').value = ticker;
                    document.getElementById('analyzeStockForm').submit();
                }
            });
        });
    }
    
    // Handle "Add to Watchlist" button from analysis results
    if (addToWatchlistFromAnalysis) {
        document.querySelectorAll('.add-to-watchlist-from-analysis').forEach(function(button) {
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
    
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
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
                }
            }
        })
        .catch(error => {
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
                    const symbol = row.querySelector('[data-symbol]')?.getAttribute('data-symbol')?.toLowerCase() || '';
                    const text = row.textContent.toLowerCase();
                    const notes = row.getAttribute('data-notes')?.toLowerCase() || '';
                    
                    if (symbol.includes(searchValue) || text.includes(searchValue) || notes.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
    
    // Handle remove from watchlist
    if (removeButtons.length > 0) {
        removeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const watchlistId = this.getAttribute('data-watchlist-id');
                const modal = document.getElementById('removeFromWatchlistModal');
                const modalInstance = new bootstrap.Modal(modal);
                
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
                const targetBuy = row.querySelectorAll('td')[3].querySelector('.target-price')?.textContent.replace('$', '');
                const targetSell = row.querySelectorAll('td')[4].querySelector('.target-price')?.textContent.replace('$', '');
                const notes = row.getAttribute('data-notes');
                
                const modal = document.getElementById('editWatchlistModal');
                const modalInstance = new bootstrap.Modal(modal);
                
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
}

/**
 * Initialize charts if stock price data is available
 */
function initializeCharts() {
    // If stock price data is not available, return
    if (!stockPriceData) return;
    
    // Get chart context
    const priceChartCtx = document.getElementById('stockPriceChart')?.getContext('2d');
    const volumeChartCtx = document.getElementById('stockVolumeChart')?.getContext('2d');
    
    // If chart context is not available, return
    if (!priceChartCtx) return;
    
    // Create price chart
    const priceChart = new Chart(priceChartCtx, {
        type: 'line',
        data: {
            labels: stockPriceData.dates,
            datasets: [{
                label: 'Price',
                data: stockPriceData.prices,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
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
            }]
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
                    display: false
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
                            return `Price: $${context.parsed.y.toFixed(2)}`;
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
                        }
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
                        }
                    }
                }
            }
        }
    });
    
    // Create volume chart if volumeChartCtx exists and stockPriceData.volumes exists
    if (volumeChartCtx && stockPriceData.volumes) {
        const volumeChart = new Chart(volumeChartCtx, {
            type: 'bar',
            data: {
                labels: stockPriceData.dates,
                datasets: [{
                    label: 'Volume',
                    data: stockPriceData.volumes,
                    backgroundColor: 'rgba(52, 152, 219, 0.5)',
                    borderColor: 'rgba(52, 152, 219, 1)',
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
                                return `Volume: ${formatNumber(context.parsed.y)}`;
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
                
                // Update chart period (this is a placeholder - in a real app, this would fetch new data)
                // For demonstration, we're just showing a notification
                showNotification(`Changed to ${period} view`, 'info');
                
                // In a real app, you would fetch new data and update the chart
                // fetchPriceData(period).then(data => updateChart(data));
            });
        });
    }
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    // Add to Watchlist Modal - Pre-populate fields when clicking "Add to Watchlist" from analysis
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-watchlist-from-analysis') || 
            e.target.closest('.add-to-watchlist-from-analysis')) {
                
            const button = e.target.classList.contains('add-to-watchlist-from-analysis') ? 
                        e.target : e.target.closest('.add-to-watchlist-from-analysis');
                
            const ticker = button.getAttribute('data-ticker');
            const price = button.getAttribute('data-price');
            const company = button.getAttribute('data-company');
            
            if (ticker && price) {
                document.getElementById('ticker_symbol_watchlist').value = ticker;
                document.getElementById('company_name').value = company;
                document.getElementById('current_price_watchlist').value = price;
            }
        }
    });
}

/**
 * Show notification toast
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.stock-notification');
    existingNotifications.forEach(notification => {
        notification.remove();
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