document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Stocks JS loaded');
    
    // Initialize all components
    initializeChart();
    initializeEventListeners();
    initializeAnimations();
    initializeSearch();
    initializeRealTimeUpdates();
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    if (typeof stockPriceData === 'undefined' || !stockPriceData) {
        console.log('No stock price data available');
        return;
    }

    // Price Chart
    const priceCanvas = document.getElementById('stockPriceChart');
    if (priceCanvas) {
        const priceCtx = priceCanvas.getContext('2d');
        
        // Create gradient
        const gradient = priceCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
        
        window.priceChart = new Chart(priceCtx, {
            type: 'line',
            data: {
                labels: stockPriceData.dates,
                datasets: [{
                    label: 'Price',
                    data: stockPriceData.prices,
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 600
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Price: $' + context.parsed.y.toFixed(2);
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
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Volume Chart
    const volumeCanvas = document.getElementById('stockVolumeChart');
    if (volumeCanvas && stockPriceData.volumes) {
        const volumeCtx = volumeCanvas.getContext('2d');
        
        window.volumeChart = new Chart(volumeCtx, {
            type: 'bar',
            data: {
                labels: stockPriceData.dates,
                datasets: [{
                    label: 'Volume',
                    data: stockPriceData.volumes,
                    backgroundColor: 'rgba(99, 102, 241, 0.3)',
                    borderColor: '#6366f1',
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
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value, 0);
                            }
                        }
                    }
                }
            }
        });
    }
}

function initializeEventListeners() {
    // Stock analysis form
    const analyzeForm = document.getElementById('analyzeStockForm');
    if (analyzeForm) {
        analyzeForm.addEventListener('submit', function(e) {
            const tickerInput = document.getElementById('ticker_symbol');
            
            if (!tickerInput.value.trim()) {
                e.preventDefault();
                showNotification('Please enter a valid stock ticker symbol', 'warning');
                return false;
            }
            
            const submitButton = this.querySelector('[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing...';
            
            // Add loading overlay
            const analysisCard = document.querySelector('.analysis-card');
            if (analysisCard) {
                analysisCard.style.position = 'relative';
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'loading-overlay';
                loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
                analysisCard.appendChild(loadingOverlay);
            }
        });
    }

    // Watchlist search
    const watchlistSearch = document.getElementById('watchlistSearch');
    if (watchlistSearch) {
        watchlistSearch.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#watchlistTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    }

    // Edit watchlist buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const watchlistId = button.getAttribute('data-watchlist-id');
            
            if (watchlistId) {
                const row = button.closest('tr');
                const ticker = row.querySelector('.stock-symbol').textContent;
                const company = row.querySelector('.stock-company').textContent;
                const price = row.querySelector('[data-price]').getAttribute('data-price');
                const targetBuy = row.cells[3].querySelector('.target-price')?.textContent.replace('$', '') || '';
                const targetSell = row.cells[4].querySelector('.target-price')?.textContent.replace('$', '') || '';
                const notes = row.getAttribute('data-notes') || '';
                
                // Populate edit form
                document.getElementById('edit_watchlist_id').value = watchlistId;
                document.getElementById('edit_ticker_symbol').value = ticker;
                document.getElementById('edit_company_name').value = company;
                document.getElementById('edit_current_price').value = price;
                document.getElementById('edit_target_buy_price').value = targetBuy;
                document.getElementById('edit_target_sell_price').value = targetSell;
                document.getElementById('edit_notes').value = notes;
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editWatchlistModal'));
                editModal.show();
            }
        }
    });

    // Delete watchlist buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const watchlistId = button.getAttribute('data-watchlist-id');
            
            if (watchlistId) {
                document.getElementById('remove_watchlist_id').value = watchlistId;
                const deleteModal = new bootstrap.Modal(document.getElementById('removeFromWatchlistModal'));
                deleteModal.show();
            }
        }
    });

    // Analyze from watchlist buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.analyze')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.analyze');
            const ticker = button.getAttribute('data-ticker');
            
            if (ticker) {
                document.getElementById('ticker_symbol').value = ticker;
                document.getElementById('analyzeStockForm').submit();
            }
        }
    });

    // Add to watchlist from analysis
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-add-from-analysis')) {
            e.preventDefault();
            const button = e.target.closest('.btn-add-from-analysis');
            
            const ticker = button.getAttribute('data-ticker');
            const price = button.getAttribute('data-price');
            const company = button.getAttribute('data-company');
            
            if (ticker) {
                document.getElementById('ticker_symbol_watchlist').value = ticker;
                document.getElementById('company_name').value = company || ticker + ' Inc.';
                document.getElementById('current_price_watchlist').value = price;
                
                // Try to extract target prices
                extractAndSetTargetPrices();
                
                // Show modal
                const addModal = new bootstrap.Modal(document.getElementById('addToWatchlistModal'));
                addModal.show();
            }
        }
    });

    // Chart period buttons
    const periodButtons = document.querySelectorAll('.chart-period-btn');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            updateChartPeriod(this.getAttribute('data-period'));
        });
    });

    // Form validation
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
    const cards = document.querySelectorAll('.analysis-card, .watchlist-card, .insight-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
}

function initializeSearch() {
    const tickerInput = document.getElementById('ticker_symbol');
    if (tickerInput) {
        // Auto uppercase
        tickerInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Alt+S shortcut
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                tickerInput.focus();
            }
        });
    }
}

function initializeRealTimeUpdates() {
    const currentTickerSymbol = document.getElementById('currentTickerSymbol');
    
    if (currentTickerSymbol && currentTickerSymbol.value) {
        const ticker = currentTickerSymbol.value;
        
        // Update price every 60 seconds
        setInterval(() => {
            updateCurrentPrice(ticker);
        }, 60000);
    }
}

function updateCurrentPrice(ticker) {
    const priceElement = document.getElementById('currentStockPrice');
    const priceChangeElement = document.getElementById('priceChange');
    const priceChangePercentElement = document.getElementById('priceChangePercent');
    
    if (!priceElement) return;
    
    // Add updating class
    priceElement.parentElement.classList.add('updating');
    
    fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .then(data => {
            priceElement.parentElement.classList.remove('updating');
            
            if (data.status === 'success') {
                const oldPrice = parseFloat(priceElement.getAttribute('data-price'));
                const newPrice = parseFloat(data.price);
                
                if (newPrice !== oldPrice) {
                    // Update price
                    priceElement.textContent = '$' + newPrice.toFixed(2);
                    priceElement.setAttribute('data-price', newPrice);
                    
                    // Flash animation
                    priceElement.classList.add('price-updated');
                    setTimeout(() => {
                        priceElement.classList.remove('price-updated');
                    }, 2000);
                    
                    // Update price change
                    const priceChange = newPrice - oldPrice;
                    const priceChangePercent = (priceChange / oldPrice) * 100;
                    
                    if (priceChangeElement && priceChangePercentElement) {
                        priceChangeElement.textContent = priceChange.toFixed(2);
                        priceChangePercentElement.textContent = priceChangePercent.toFixed(2) + '%';
                        
                        const changeContainer = priceChangeElement.closest('.price-change');
                        if (changeContainer) {
                            changeContainer.classList.remove('positive', 'negative');
                            changeContainer.classList.add(priceChange >= 0 ? 'positive' : 'negative');
                            
                            const icon = changeContainer.querySelector('i');
                            if (icon) {
                                icon.className = priceChange >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
                            }
                        }
                    }
                }
            }
        })
        .catch(error => {
            priceElement.parentElement.classList.remove('updating');
            console.error('Error updating price:', error);
        });
}

function updateChartPeriod(period) {
    if (!window.priceChart || !stockPriceData) return;
    
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
    
    const dataLength = stockPriceData.dates.length;
    pointsToShow = Math.min(dataLength, pointsToShow);
    
    if (pointsToShow < dataLength) {
        window.priceChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
        window.priceChart.data.datasets[0].data = stockPriceData.prices.slice(-pointsToShow);
        window.priceChart.update();
        
        if (window.volumeChart && stockPriceData.volumes) {
            window.volumeChart.data.labels = stockPriceData.dates.slice(-pointsToShow);
            window.volumeChart.data.datasets[0].data = stockPriceData.volumes.slice(-pointsToShow);
            window.volumeChart.update();
        }
    }
}

function extractAndSetTargetPrices() {
    // Try to extract technical indicators from the page
    const indicators = document.querySelectorAll('.indicator-item');
    let bollingerUpper = 0;
    let bollingerLower = 0;
    
    indicators.forEach(item => {
        const label = item.querySelector('.indicator-label')?.textContent;
        const value = item.querySelector('.indicator-value')?.textContent;
        
        if (label && value) {
            if (label.includes('Bollinger Upper')) {
                bollingerUpper = parseFloat(value.replace('$', ''));
            } else if (label.includes('Bollinger Lower')) {
                bollingerLower = parseFloat(value.replace('$', ''));
            }
        }
    });
    
    // Set target prices if we found the values
    if (bollingerLower > 0) {
        document.getElementById('target_buy_price').value = bollingerLower.toFixed(2);
    }
    
    if (bollingerUpper > 0) {
        document.getElementById('target_sell_price').value = bollingerUpper.toFixed(2);
    }
}

function formatNumber(number, decimals = 2) {
    if (number === null || isNaN(number)) return '0';
    
    if (number >= 1000000) {
        return (number / 1000000).toFixed(1) + 'M';
    } else if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'K';
    }
    
    return number.toFixed(decimals);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#10b981';
            break;
        case 'warning':
            notification.style.backgroundColor = '#f59e0b';
            break;
        case 'error':
            notification.style.backgroundColor = '#ef4444';
            break;
        default:
            notification.style.backgroundColor = '#3b82f6';
    }
    
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