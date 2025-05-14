document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Stocks JS loaded');
    
    // Initialize all components
    initializeCharts();
    initializeEventListeners();
    initializeAnimations();
    initializeSearch();
    initializeRealTimeUpdates();
    initializeComparison();
    initializeTooltips();
    initializePortfolioSimulator();
    initializePriceAlerts();
    initializeWatchlistSorting();
    initializeWatchlistFilters();
});

// Global variables for alerts
let alertsList = {};

function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    if (typeof stockPriceData === 'undefined' || !stockPriceData) {
        console.log('No stock price data available');
        return;
    }

    // Get currency symbol from meta tag
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';

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
                                return 'Price: ' + currencySymbol + context.parsed.y.toFixed(2);
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
                                return currencySymbol + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
        // Initialize Bollinger Bands data
        const ma20 = calculateMovingAverage(stockPriceData.prices, 20);
        const bollinger = calculateBollingerBands(stockPriceData.prices);
        
        window.bollingerData = {
            upper: bollinger.upper,
            lower: bollinger.lower,
            middle: ma20
        };
        
        // Initialize Moving Averages data
        window.maData = {
            short: calculateMovingAverage(stockPriceData.prices, 20),
            long: calculateMovingAverage(stockPriceData.prices, 50)
        };
        
        // Setup chart annotations toggle
        document.getElementById('showMA').addEventListener('change', function() {
            toggleMovingAverages(this.checked);
        });
        
        document.getElementById('showBollinger').addEventListener('change', function() {
            toggleBollingerBands(this.checked);
        });
        
        // Initial state - show MA, hide Bollinger by default
        toggleMovingAverages(true);
        toggleBollingerBands(false);
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
    
    // Comparison Chart initialization
    const comparisonCanvas = document.getElementById('comparisonChart');
    if (comparisonCanvas) {
        const comparisonCtx = comparisonCanvas.getContext('2d');
        
        const ticker = document.getElementById('currentTickerSymbol').value;
        const currentPrice = parseFloat(document.getElementById('currentStockPrice').getAttribute('data-price'));
        
        window.comparisonChart = new Chart(comparisonCtx, {
            type: 'line',
            data: {
                labels: stockPriceData.dates,
                datasets: [{
                    label: ticker,
                    data: stockPriceData.prices.map(price => (price / stockPriceData.prices[0] - 1) * 100),
                    borderColor: '#6366f1',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.1,
                    pointRadius: 0,
                    pointHoverRadius: 4
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
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
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
                                return value.toFixed(2) + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Update comparison legend
        updateComparisonLegend();
    }
    
    // Setup chart type toggle
    const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
    chartTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            chartTypeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const chartType = this.getAttribute('data-chart-type');
            changeChartType(chartType);
        });
    });
}

function changeChartType(type) {
    if (!window.priceChart) return;
    
    const chartData = window.priceChart.data;
    let newType = 'line';
    
    switch(type) {
        case 'area':
            newType = 'line';
            chartData.datasets[0].fill = true;
            break;
        case 'candlestick':
            // Simulate candlestick by making bars
            newType = 'bar';
            chartData.datasets[0].fill = false;
            chartData.datasets[0].borderWidth = 1;
            chartData.datasets[0].backgroundColor = chartData.datasets[0].data.map((value, index) => {
                const prevValue = index > 0 ? chartData.datasets[0].data[index - 1] : value;
                return value >= prevValue ? 'rgba(16, 185, 129, 0.7)' : 'rgba(239, 68, 68, 0.7)';
            });
            break;
        case 'line':
        default:
            newType = 'line';
            chartData.datasets[0].fill = false;
            chartData.datasets[0].backgroundColor = 'transparent';
            chartData.datasets[0].borderWidth = 2;
            break;
    }
    
    window.priceChart.config.type = newType;
    window.priceChart.update();
}

function toggleMovingAverages(show) {
    if (!window.priceChart || !window.maData) return;
    
    const datasets = window.priceChart.data.datasets;
    
    // Remove existing MA lines
    window.priceChart.data.datasets = datasets.filter(dataset => 
        !dataset.label.includes('MA') && !dataset.label.includes('Moving Average'));
    
    if (show) {
        // Add short and long MA lines
        window.priceChart.data.datasets.push({
            label: '20-Day MA',
            data: window.maData.short,
            borderColor: '#3b82f6',
            backgroundColor: 'transparent',
            borderWidth: 1.5,
            pointRadius: 0,
            pointHoverRadius: 0,
            borderDash: [],
            tension: 0.1,
            fill: false
        });
        
        window.priceChart.data.datasets.push({
            label: '50-Day MA',
            data: window.maData.long,
            borderColor: '#f59e0b',
            backgroundColor: 'transparent',
            borderWidth: 1.5,
            pointRadius: 0,
            pointHoverRadius: 0,
            borderDash: [3, 3],
            tension: 0.1,
            fill: false
        });
    }
    
    window.priceChart.update();
}

function toggleBollingerBands(show) {
    if (!window.priceChart || !window.bollingerData) return;
    
    const datasets = window.priceChart.data.datasets;
    
    // Remove existing Bollinger bands
    window.priceChart.data.datasets = datasets.filter(dataset => 
        !dataset.label.includes('Bollinger'));
    
    if (show) {
        // Add Bollinger bands
        window.priceChart.data.datasets.push({
            label: 'Bollinger Upper',
            data: window.bollingerData.upper,
            borderColor: 'rgba(99, 102, 241, 0.8)',
            backgroundColor: 'transparent',
            borderWidth: 1,
            pointRadius: 0,
            pointHoverRadius: 0,
            borderDash: [2, 2],
            tension: 0.1,
            fill: false
        });
        
        window.priceChart.data.datasets.push({
            label: 'Bollinger Lower',
            data: window.bollingerData.lower,
            borderColor: 'rgba(99, 102, 241, 0.8)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 1,
            pointRadius: 0,
            pointHoverRadius: 0,
            borderDash: [2, 2],
            tension: 0.1,
            fill: '-1'
        });
    }
    
    window.priceChart.update();
}

function initializeEventListeners() {
    // Stock analysis form
    const analyzeForm = document.getElementById('analyzeStockForm');
    if (analyzeForm) {
        analyzeForm.addEventListener('submit', function(e) {
            const tickerInput = document.getElementById('ticker_symbol');
            
            if (!tickerInput.value.trim()) {
                e.preventDefault();
                showToast('Please enter a valid stock ticker symbol', 'warning');
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

    // Shortcut buttons for tickers
    const shortcutButtons = document.querySelectorAll('.shortcut-btn');
    shortcutButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticker = this.getAttribute('data-ticker');
            document.getElementById('ticker_symbol').value = ticker;
            document.getElementById('analyzeStockForm').submit();
        });
    });

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
                const price = row.cells[2].querySelector('.current-price').textContent.replace(/[^0-9.]/g, '');
                // Get target prices without currency symbols
                const targetBuyEl = row.cells[3].querySelector('.target-price');
                const targetSellEl = row.cells[4].querySelector('.target-price');
                const targetBuy = targetBuyEl ? targetBuyEl.textContent.replace(/[^0-9.]/g, '') : '';
                const targetSell = targetSellEl ? targetSellEl.textContent.replace(/[^0-9.]/g, '') : '';
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

    // Alert watchlist buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.alert')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.alert');
            const ticker = button.getAttribute('data-ticker');
            const price = button.getAttribute('data-price');
            
            if (ticker && price) {
                document.getElementById('alertStockSymbol').textContent = ticker;
                document.getElementById('alertCurrentPrice').textContent = formatMoney(price);
                document.getElementById('modalAlertPrice').value = price;
                
                const alertModal = new bootstrap.Modal(document.getElementById('setAlertModal'));
                alertModal.show();
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

    // Quick add to watchlist button
    document.getElementById('quickAddToWatchlist')?.addEventListener('click', function() {
        const ticker = document.getElementById('currentTickerSymbol').value;
        const price = document.getElementById('currentStockPrice').getAttribute('data-price');
        const company = document.querySelector('.stock-info p').textContent;
        
        if (ticker) {
            document.getElementById('ticker_symbol_watchlist').value = ticker;
            document.getElementById('company_name').value = company;
            document.getElementById('current_price_watchlist').value = price;
            
            // Try to extract buy/sell targets if available
            const buyPoints = document.querySelectorAll('.price-point');
            if (buyPoints.length > 0) {
                const buyPoint = buyPoints[0].querySelector('.price-point-value').textContent.replace(/[^0-9.]/g, '');
                const sellPoint = buyPoints[buyPoints.length - 1].querySelector('.price-point-value').textContent.replace(/[^0-9.]/g, '');
                
                document.getElementById('target_buy_price').value = buyPoint;
                document.getElementById('target_sell_price').value = sellPoint;
            }
            
            // Show modal
            const addModal = new bootstrap.Modal(document.getElementById('addToWatchlistModal'));
            addModal.show();
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
    
    // Set alert price presets
    const alertPresets = document.querySelectorAll('.alert-preset');
    alertPresets.forEach(btn => {
        btn.addEventListener('click', function() {
            const percentChange = parseFloat(this.getAttribute('data-change'));
            const currentPrice = parseFloat(document.getElementById('currentStockPrice').getAttribute('data-price'));
            const newPrice = currentPrice * (1 + percentChange / 100);
            document.getElementById('alertPrice').value = newPrice.toFixed(2);
        });
    });
    
    // Set Alert button
    document.getElementById('setAlert')?.addEventListener('click', function() {
        const price = parseFloat(document.getElementById('alertPrice').value);
        if (!price || price <= 0) {
            showToast('Please enter a valid price', 'warning');
            return;
        }
        
        const ticker = document.getElementById('currentTickerSymbol').value;
        addPriceAlert(ticker, price);
    });
    
    // Set alert from price points
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-add-alert')) {
            const button = e.target.closest('.btn-add-alert');
            const price = parseFloat(button.getAttribute('data-price'));
            const ticker = document.getElementById('currentTickerSymbol').value;
            
            if (price && ticker) {
                addPriceAlert(ticker, price);
                showToast('Price alert set for ' + formatMoney(price), 'success');
            }
        }
    });
    
    // Alert modal presets
    const presetButtons = document.querySelectorAll('.preset-button');
    presetButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const percent = parseFloat(this.getAttribute('data-percent'));
            const priceText = document.getElementById('alertCurrentPrice').textContent;
            const currentPrice = parseFloat(priceText.replace(/[^0-9.]/g, ''));
            
            if (!isNaN(currentPrice)) {
                const targetPrice = currentPrice * (1 + percent / 100);
                document.getElementById('modalAlertPrice').value = targetPrice.toFixed(2);
            }
        });
    });
    
    // Save alert from modal
    document.getElementById('saveAlertButton')?.addEventListener('click', function() {
        const price = parseFloat(document.getElementById('modalAlertPrice').value);
        const ticker = document.getElementById('alertStockSymbol').textContent;
        const note = document.getElementById('alertNote').value;
        
        if (!price || price <= 0) {
            showToast('Please enter a valid price', 'warning');
            return;
        }
        
        addPriceAlert(ticker, price, note);
        bootstrap.Modal.getInstance(document.getElementById('setAlertModal')).hide();
    });
    
    // Refresh price button
    document.getElementById('refreshPrice')?.addEventListener('click', function() {
        const ticker = document.getElementById('currentTickerSymbol').value;
        this.classList.add('fa-spin');
        updateCurrentPrice(ticker, true);
    });
    
    // Refresh watchlist button
    document.getElementById('refreshWatchlist')?.addEventListener('click', function() {
        refreshWatchlistPrices();
    });
    
    // Fetch price button (add)
    document.getElementById('fetchCurrentPrice')?.addEventListener('click', function() {
        const ticker = document.getElementById('ticker_symbol_watchlist').value;
        if (!ticker) {
            showToast('Please enter a ticker symbol first', 'warning');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
        
        fetchStockPrice(ticker)
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('current_price_watchlist').value = data.price;
                    showToast('Price fetched successfully', 'success');
                } else {
                    showToast('Failed to fetch price', 'error');
                }
            })
            .catch(() => {
                showToast('Error fetching price', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Fetch Current Price';
            });
    });
    
    // Fetch price button (edit)
    document.getElementById('editFetchCurrentPrice')?.addEventListener('click', function() {
        const ticker = document.getElementById('edit_ticker_symbol').value;
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
        
        fetchStockPrice(ticker)
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('edit_current_price').value = data.price;
                    showToast('Price fetched successfully', 'success');
                } else {
                    showToast('Failed to fetch price', 'error');
                }
            })
            .catch(() => {
                showToast('Error fetching price', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Fetch Current Price';
            });
    });
}

function initializeAnimations() {
    // Animate cards on load
    const cards = document.querySelectorAll('.analysis-card, .watchlist-card, .insight-card, .market-overview-card');
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

function updateCurrentPrice(ticker, showToast = false) {
    const priceElement = document.getElementById('currentStockPrice');
    const priceChangeElement = document.getElementById('priceChangePercent');
    const refreshBtn = document.getElementById('refreshPrice');
    
    if (!priceElement) return;
    
    // Get currency symbol from meta tag
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
    
    // Add updating class
    priceElement.parentElement.classList.add('updating');
    
    fetchStockPrice(ticker)
        .then(data => {
            priceElement.parentElement.classList.remove('updating');
            if (refreshBtn) {
                refreshBtn.classList.remove('fa-spin');
            }
            
            if (data.status === 'success') {
                const oldPrice = parseFloat(priceElement.getAttribute('data-price'));
                const newPrice = parseFloat(data.price);
                
                if (newPrice !== oldPrice) {
                    // Update price
                    priceElement.textContent = currencySymbol + newPrice.toFixed(2);
                    priceElement.setAttribute('data-price', newPrice);
                    
                    // Flash animation
                    priceElement.classList.add('price-updated');
                    setTimeout(() => {
                        priceElement.classList.remove('price-updated');
                    }, 2000);
                    
                    // Update price change
                    const priceChange = newPrice - oldPrice;
                    const priceChangePercent = (priceChange / oldPrice) * 100;
                    
                    if (priceChangeElement) {
                        priceChangeElement.textContent = priceChangePercent.toFixed(2) + '%';
                        
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
                    
                    // Update last updated time
                    const lastUpdated = document.getElementById('lastUpdated');
                    if (lastUpdated) {
                        lastUpdated.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
                    }
                    
                    // Show toast if requested
                    if (showToast) {
                        showToast(ticker + ' price updated to ' + currencySymbol + newPrice.toFixed(2), 'success');
                    }
                    
                    // Update portfolio simulator if available
                    updatePortfolioSimulation();
                    
                    // Check for price alerts
                    checkPriceAlerts(ticker, newPrice);
                }
            }
        })
        .catch(error => {
            priceElement.parentElement.classList.remove('updating');
            if (refreshBtn) {
                refreshBtn.classList.remove('fa-spin');
            }
            console.error('Error updating price:', error);
        });
}

function fetchStockPrice(ticker) {
    return fetch(`${BASE_PATH}/stocks?action=get_stock_price&ticker=${ticker}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching stock price:', error);
            return { status: 'error', message: 'Failed to fetch price' };
        });
}

function updateChartPeriod(period) {
    if (!window.priceChart || !stockPriceData) return;
    
    let pointsToShow;
    switch(period) {
        case '3m':
            pointsToShow = 90;
            break;
        case '6m':
            pointsToShow = 180;
            break;
        case '1y':
            pointsToShow = 365;
            break;
        case 'all':
            pointsToShow = stockPriceData.dates.length;
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
        
        // Update Bollinger Bands data if they're being shown
        if (document.getElementById('showBollinger')?.checked) {
            toggleBollingerBands(true);
        }
        
        // Update Moving Averages data if they're being shown
        if (document.getElementById('showMA')?.checked) {
            toggleMovingAverages(true);
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
            // Use regex to extract just the numeric value from the indicator value
            const numericValue = parseFloat(value.replace(/[^0-9.]/g, ''));
            
            if (!isNaN(numericValue)) {
                if (label.includes('Bollinger Upper')) {
                    bollingerUpper = numericValue;
                } else if (label.includes('Bollinger Lower')) {
                    bollingerLower = numericValue;
                }
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

function formatMoney(number) {
    if (number === null || isNaN(number)) return '$0.00';
    
    // Get currency symbol from meta tag
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
    
    return currencySymbol + parseFloat(number).toFixed(2);
}

function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    let icon = '';
    let title = '';
    
    switch(type) {
        case 'success':
            icon = 'fas fa-check-circle';
            title = 'Success';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            title = 'Warning';
            break;
        case 'error':
            icon = 'fas fa-times-circle';
            title = 'Error';
            break;
        default:
            icon = 'fas fa-info-circle';
            title = 'Information';
    }
    
    toast.innerHTML = `
        <div class="toast-header">
            <i class="toast-icon ${icon}"></i>
            <strong class="toast-title">${title}</strong>
            <button type="button" class="toast-close" data-bs-dismiss="toast" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });
    
    bsToast.show();
    
    // Remove toast from DOM after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

function initializeComparison() {
    const addCompareButton = document.getElementById('addCompareStock');
    const compareInput = document.getElementById('compareTickerInput');
    
    if (addCompareButton && compareInput) {
        addCompareButton.addEventListener('click', function() {
            const ticker = compareInput.value.trim().toUpperCase();
            if (!ticker) {
                showToast('Please enter a ticker symbol', 'warning');
                return;
            }
            
            // Check if already added
            const exists = document.querySelector(`.compare-chip[data-ticker="${ticker}"]`);
            if (exists) {
                showToast('Stock already added to comparison', 'warning');
                return;
            }
            
            // Get primary ticker
            const primaryTicker = document.getElementById('currentTickerSymbol').value;
            if (ticker === primaryTicker) {
                showToast('Cannot compare with the same stock', 'warning');
                return;
            }
            
            // Show loading state
            addCompareButton.disabled = true;
            addCompareButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Fetch historical data
            fetchComparisonData(ticker)
                .then(data => {
                    if (data.status === 'success') {
                        addStockToComparison(ticker, data);
                        compareInput.value = '';
                        showToast(`Added ${ticker} to comparison`, 'success');
                    } else {
                        showToast('Failed to fetch data for ' + ticker, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error adding comparison:', error);
                    showToast('Error adding comparison stock', 'error');
                })
                .finally(() => {
                    addCompareButton.disabled = false;
                    addCompareButton.innerHTML = 'Compare';
                });
        });
    }
}

function fetchComparisonData(ticker) {
    // In a real implementation, we would fetch historical data from the server
    // For this demo, we'll generate synthetic data
    return new Promise((resolve) => {
        setTimeout(() => {
            // Generate synthetic price history based on the ticker
            const dates = stockPriceData.dates;
            const prices = [];
            
            // Create a deterministic pattern based on ticker
            const tickerSum = ticker.split('').reduce((sum, char) => sum + char.charCodeAt(0), 0);
            const trend = (tickerSum % 10) - 5; // -5 to +4
            const volatility = (tickerSum % 5) + 5; // 5 to 9
            
            let basePrice = 100;
            for (let i = 0; i < dates.length; i++) {
                // Add trend and random noise
                const randomChange = (Math.random() - 0.5) * volatility;
                const trendChange = trend / 100;
                basePrice = basePrice * (1 + trendChange + randomChange / 100);
                prices.push(basePrice);
            }
            
            resolve({
                status: 'success',
                ticker: ticker,
                dates: dates,
                prices: prices
            });
        }, 1000);
    });
}

function addStockToComparison(ticker, data) {
    // Add to chips list
    const compareList = document.getElementById('compareStocksList');
    const colors = ['#3b82f6', '#f59e0b', '#ef4444', '#10b981', '#8b5cf6', '#ec4899'];
    const colorIndex = compareList.querySelectorAll('.compare-chip').length % colors.length;
    
    const chip = document.createElement('div');
    chip.className = 'compare-chip';
    chip.setAttribute('data-ticker', ticker);
    chip.innerHTML = `
        ${ticker}
        <button type="button" class="remove-compare" data-ticker="${ticker}">
            <i class="fas fa-times"></i>
        </button>
    `;
    compareList.appendChild(chip);
    
    // Add to chart
    if (window.comparisonChart) {
        // Calculate percentage change from first value
        const normalizedPrices = data.prices.map((price, i) => 
            (price / data.prices[0] - 1) * 100
        );
        
        // Add dataset
        window.comparisonChart.data.datasets.push({
            label: ticker,
            data: normalizedPrices,
            borderColor: colors[colorIndex],
            backgroundColor: 'transparent',
            borderWidth: 2,
            tension: 0.1,
            pointRadius: 0,
            pointHoverRadius: 4
        });
        
        window.comparisonChart.update();
        
        // Update legend
        updateComparisonLegend();
    }
    
    // Add remove event listener
    document.querySelector(`.remove-compare[data-ticker="${ticker}"]`).addEventListener('click', function() {
        removeStockFromComparison(ticker);
    });
}

function removeStockFromComparison(ticker) {
    // Remove from chips list
    const chip = document.querySelector(`.compare-chip[data-ticker="${ticker}"]`);
    if (chip) {
        chip.remove();
    }
    
    // Remove from chart
    if (window.comparisonChart) {
        const datasets = window.comparisonChart.data.datasets;
        const index = datasets.findIndex(d => d.label === ticker);
        
        if (index !== -1) {
            datasets.splice(index, 1);
            window.comparisonChart.update();
            
            // Update legend
            updateComparisonLegend();
        }
    }
}

function updateComparisonLegend() {
    const legendEl = document.getElementById('comparisonLegend');
    if (!legendEl || !window.comparisonChart) return;
    
    legendEl.innerHTML = '';
    
    window.comparisonChart.data.datasets.forEach(dataset => {
        const item = document.createElement('div');
        item.className = 'legend-item';
        
        const colorBox = document.createElement('span');
        colorBox.className = 'legend-color';
        colorBox.style.backgroundColor = dataset.borderColor;
        
        const label = document.createElement('span');
        label.textContent = dataset.label;
        
        item.appendChild(colorBox);
        item.appendChild(label);
        legendEl.appendChild(item);
    });
}

function initializeTooltips() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializePortfolioSimulator() {
    const investmentAmountInput = document.getElementById('investmentAmount');
    const targetPriceInput = document.getElementById('targetPrice');
    const sharesCountElement = document.getElementById('sharesCount');
    const potentialProfitElement = document.getElementById('potentialProfit');
    const potentialReturnElement = document.getElementById('potentialReturn');
    
    if (!investmentAmountInput || !targetPriceInput || !sharesCountElement) return;
    
    function updateSimulation() {
        const currentPrice = parseFloat(document.getElementById('currentStockPrice').getAttribute('data-price'));
        const investmentAmount = parseFloat(investmentAmountInput.value) || 0;
        const targetPrice = parseFloat(targetPriceInput.value) || currentPrice;
        
        if (currentPrice <= 0 || investmentAmount <= 0) return;
        
        // Calculate shares
        const sharesCount = Math.floor(investmentAmount / currentPrice);
        sharesCountElement.value = sharesCount;
        
        // Calculate potential profit
        const initialInvestment = sharesCount * currentPrice;
        const futureValue = sharesCount * targetPrice;
        const profit = futureValue - initialInvestment;
        
        potentialProfitElement.value = formatMoney(profit);
        
        // Calculate return percentage
        const returnPercent = initialInvestment > 0 ? (profit / initialInvestment) * 100 : 0;
        potentialReturnElement.textContent = returnPercent.toFixed(2) + '%';
        
        // Style based on profit/loss
        potentialReturnElement.classList.remove('positive', 'negative');
        if (returnPercent > 0) {
            potentialReturnElement.classList.add('positive');
        } else if (returnPercent < 0) {
            potentialReturnElement.classList.add('negative');
        }
    }
    
    // Set a default target price slightly higher than current price
    const currentPrice = parseFloat(document.getElementById('currentStockPrice').getAttribute('data-price'));
    if (currentPrice > 0) {
        targetPriceInput.value = (currentPrice * 1.1).toFixed(2);
    }
    
    // Initialize simulation
    updateSimulation();
    
    // Add event listeners
    investmentAmountInput.addEventListener('input', updateSimulation);
    targetPriceInput.addEventListener('input', updateSimulation);
    
    // Function to update simulation when price changes
    window.updatePortfolioSimulation = updateSimulation;
}

function initializePriceAlerts() {
    // Load any saved alerts from localStorage
    loadAlerts();
    
    // Display alerts in the UI
    renderAlerts();
}

function addPriceAlert(ticker, price, note = '') {
    if (!ticker || !price) return false;
    
    // Create alert object
    const alertId = Date.now().toString();
    const alert = {
        id: alertId,
        ticker: ticker,
        price: price,
        note: note,
        created: new Date().toISOString()
    };
    
    // Add to alerts list
    if (!alertsList[ticker]) {
        alertsList[ticker] = [];
    }
    
    alertsList[ticker].push(alert);
    
    // Save to localStorage
    saveAlerts();
    
    // Update UI
    renderAlerts();
    
    // Show success message
    showToast(`Price alert set for ${ticker} at ${formatMoney(price)}`, 'success');
    
    return true;
}

function removePriceAlert(alertId) {
    let removed = false;
    
    // Find and remove the alert
    for (const ticker in alertsList) {
        const index = alertsList[ticker].findIndex(alert => alert.id === alertId);
        if (index !== -1) {
            alertsList[ticker].splice(index, 1);
            
            // Remove empty arrays
            if (alertsList[ticker].length === 0) {
                delete alertsList[ticker];
            }
            
            removed = true;
            break;
        }
    }
    
    if (removed) {
        // Save to localStorage
        saveAlerts();
        
        // Update UI
        renderAlerts();
        
        // Show success message
        showToast('Price alert removed', 'success');
    }
    
    return removed;
}

function renderAlerts() {
    const alertsContainer = document.getElementById('activeAlerts');
    if (!alertsContainer) return;
    
    // Get current ticker
    const currentTicker = document.getElementById('currentTickerSymbol')?.value;
    if (!currentTicker || !alertsList[currentTicker] || alertsList[currentTicker].length === 0) {
        alertsContainer.innerHTML = '<div class="no-alerts-message">No active alerts</div>';
        return;
    }
    
    // Sort alerts by price
    const alerts = [...alertsList[currentTicker]].sort((a, b) => a.price - b.price);
    
    let html = '';
    alerts.forEach(alert => {
        html += `
            <div class="alert-item">
                <span class="alert-price">${formatMoney(alert.price)}</span>
                ${alert.note ? `<span class="alert-note">${alert.note}</span>` : ''}
                <button type="button" class="alert-remove" data-alert-id="${alert.id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    alertsContainer.innerHTML = html;
    
    // Add event listeners to remove buttons
    alertsContainer.querySelectorAll('.alert-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const alertId = this.getAttribute('data-alert-id');
            removePriceAlert(alertId);
        });
    });
}

function saveAlerts() {
    try {
        localStorage.setItem('stockAlerts', JSON.stringify(alertsList));
    } catch (e) {
        console.error('Failed to save alerts to localStorage:', e);
    }
}

function loadAlerts() {
    try {
        const saved = localStorage.getItem('stockAlerts');
        if (saved) {
            alertsList = JSON.parse(saved);
        }
    } catch (e) {
        console.error('Failed to load alerts from localStorage:', e);
        alertsList = {};
    }
}

function checkPriceAlerts(ticker, currentPrice) {
    if (!alertsList[ticker] || alertsList[ticker].length === 0) return;
    
    const triggeredAlerts = [];
    
    // Check each alert
    alertsList[ticker].forEach(alert => {
        // If current price crossed the alert price
        if ((alert.price >= currentPrice && alert.price <= currentPrice * 1.01) || 
            (alert.price <= currentPrice && alert.price >= currentPrice * 0.99)) {
            
            triggeredAlerts.push(alert);
        }
    });
    
    // Notify about triggered alerts
    if (triggeredAlerts.length > 0) {
        triggeredAlerts.forEach(alert => {
            showToast(`Price Alert: ${ticker} price has reached ${formatMoney(alert.price)}`, 'warning');
            
            // Remove triggered alert
            removePriceAlert(alert.id);
        });
    }
}

function initializeWatchlistSorting() {
    const sortableHeaders = document.querySelectorAll('.watchlist-table th.sortable');
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortField = this.getAttribute('data-sort');
            const isAscending = !this.classList.contains('sort-asc');
            
            // Remove sort classes from all headers
            sortableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Add sort class to current header
            this.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
            
            // Sort the table
            sortWatchlistTable(sortField, isAscending);
        });
    });
}

function sortWatchlistTable(field, ascending) {
    const table = document.getElementById('watchlistTable');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Sort rows
    rows.sort((rowA, rowB) => {
        let valueA, valueB;
        
        switch(field) {
            case 'symbol':
                valueA = rowA.querySelector('.stock-symbol').textContent;
                valueB = rowB.querySelector('.stock-symbol').textContent;
                return ascending ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
                
            case 'company':
                valueA = rowA.querySelector('.stock-company').textContent;
                valueB = rowB.querySelector('.stock-company').textContent;
                return ascending ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
                
            case 'price':
                valueA = parseFloat(rowA.cells[2].querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                valueB = parseFloat(rowB.cells[2].querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                return ascending ? valueA - valueB : valueB - valueA;
                
            case 'target-buy':
                const buyA = rowA.cells[3].querySelector('.target-price');
                const buyB = rowB.cells[3].querySelector('.target-price');
                valueA = buyA ? parseFloat(buyA.textContent.replace(/[^0-9.]/g, '')) : 0;
                valueB = buyB ? parseFloat(buyB.textContent.replace(/[^0-9.]/g, '')) : 0;
                return ascending ? valueA - valueB : valueB - valueA;
                
            case 'target-sell':
                const sellA = rowA.cells[4].querySelector('.target-price');
                const sellB = rowB.cells[4].querySelector('.target-price');
                valueA = sellA ? parseFloat(sellA.textContent.replace(/[^0-9.]/g, '')) : 0;
                valueB = sellB ? parseFloat(sellB.textContent.replace(/[^0-9.]/g, '')) : 0;
                return ascending ? valueA - valueB : valueB - valueA;
                
            default:
                return 0;
        }
    });
    
    // Reappend rows in sorted order
    rows.forEach(row => tbody.appendChild(row));
}

function initializeWatchlistFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            filterWatchlist(filter);
        });
    });
}

function filterWatchlist(filter) {
    const rows = document.querySelectorAll('#watchlistTable tbody tr');
    
    rows.forEach(row => {
        const changeIndicator = row.querySelector('.price-change-indicator');
        const changeText = changeIndicator ? changeIndicator.textContent : '0.00%';
        const changeValue = parseFloat(changeText);
        
        switch(filter) {
            case 'gainers':
                row.style.display = changeValue > 0 ? '' : 'none';
                break;
            case 'losers':
                row.style.display = changeValue < 0 ? '' : 'none';
                break;
            default:
                row.style.display = '';
        }
    });
}

function refreshWatchlistPrices() {
    const watchlistTable = document.getElementById('watchlistTable');
    if (!watchlistTable) return;
    
    const rows = watchlistTable.querySelectorAll('tbody tr');
    if (rows.length === 0) return;
    
    // Show loading state
    const refreshButton = document.getElementById('refreshWatchlist');
    if (refreshButton) {
        refreshButton.disabled = true;
        refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    }
    
    // Get currency symbol
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
    
    // Collect all tickers
    const tickers = Array.from(rows).map(row => row.querySelector('.stock-symbol').textContent);
    
    // Process in batches to avoid too many simultaneous requests
    const batchSize = 5;
    const batches = [];
    for (let i = 0; i < tickers.length; i += batchSize) {
        batches.push(tickers.slice(i, i + batchSize));
    }
    
    let completedBatches = 0;
    
    // Process each batch
    batches.forEach(batch => {
        setTimeout(() => {
            Promise.all(batch.map(ticker => fetchStockPrice(ticker)))
                .then(results => {
                    results.forEach((data, index) => {
                        if (data.status === 'success') {
                            const ticker = batch[index];
                            updateWatchlistRow(ticker, data.price);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error refreshing prices:', error);
                })
                .finally(() => {
                    completedBatches++;
                    
                    // If all batches are done, reset button
                    if (completedBatches === batches.length && refreshButton) {
                        refreshButton.disabled = false;
                        refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Prices';
                    }
                });
        }, 1000); // Add delay between batches
    });
}

function updateWatchlistRow(ticker, newPrice) {
    const row = document.querySelector(`#watchlistTable tbody tr td:first-child .stock-symbol:contains("${ticker}")`).closest('tr');
    if (!row) return;
    
    const priceCell = row.cells[2];
    const priceElement = priceCell.querySelector('.current-price');
    const changeElement = priceCell.querySelector('.price-change-indicator');
    
    if (priceElement) {
        const oldPrice = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, ''));
        
        // Get currency symbol
        const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.content || '$';
        
        // Update price
        priceElement.textContent = currencySymbol + newPrice.toFixed(2);
        
        // Calculate and update price change
        if (changeElement && oldPrice > 0) {
            const change = ((newPrice - oldPrice) / oldPrice) * 100;
            changeElement.textContent = change.toFixed(2) + '%';
            
            changeElement.classList.remove('positive', 'negative', 'neutral');
            if (change > 0) {
                changeElement.classList.add('positive');
            } else if (change < 0) {
                changeElement.classList.add('negative');
            } else {
                changeElement.classList.add('neutral');
            }
            
            // Store change percentage for filtering
            row.setAttribute('data-change', change);
        }
        
        // Highlight the row briefly
        row.classList.add('highlight');
        setTimeout(() => {
            row.classList.remove('highlight');
        }, 2000);
    }
}

// Helper functions for jQuery-like behavior
Element.prototype.matches = Element.prototype.matches || Element.prototype.msMatchesSelector;

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;
        if (!document.documentElement.contains(el)) return null;
        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

// Add :contains selector functionality
document.querySelectorAll = document.querySelectorAll || function(selector) {
    if (selector.includes(':contains')) {
        const parts = selector.split(':contains');
        const baseSelector = parts[0];
        const text = parts[1].replace(/[\(\)"']/g, '');
        
        const elements = Array.from(document.querySelectorAll(baseSelector));
        return elements.filter(el => el.textContent.includes(text));
    }
    
    return document.querySelectorAll(selector);
};

// Technical indicator calculation functions
function calculateMovingAverage(prices, period) {
    if (!prices || prices.length === 0) return [];
    
    const ma = [];
    
    // Fill with nulls for initial periods where MA isn't defined
    for (let i = 0; i < period - 1; i++) {
        ma.push(null);
    }
    
    // Calculate MA for each point
    for (let i = period - 1; i < prices.length; i++) {
        let sum = 0;
        for (let j = 0; j < period; j++) {
            sum += prices[i - j];
        }
        ma.push(sum / period);
    }
    
    return ma;
}

function calculateBollingerBands(prices, period = 20, stdDev = 2) {
    if (!prices || prices.length < period) return { upper: [], lower: [] };
    
    const ma = calculateMovingAverage(prices, period);
    const upper = [];
    const lower = [];
    
    // Fill initial points with nulls
    for (let i = 0; i < period - 1; i++) {
        upper.push(null);
        lower.push(null);
    }
    
    // Calculate bands for each point
    for (let i = period - 1; i < prices.length; i++) {
        let sum = 0;
        for (let j = 0; j < period; j++) {
            sum += Math.pow(prices[i - j] - ma[i], 2);
        }
        const standardDeviation = Math.sqrt(sum / period);
        
        upper.push(ma[i] + (standardDeviation * stdDev));
        lower.push(ma[i] - (standardDeviation * stdDev));
    }
    
    return { upper, lower };
}