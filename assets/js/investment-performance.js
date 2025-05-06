/**
 * iGotMoney - Investment Performance Analysis
 * Tracks and visualizes investment performance over time
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing investment performance analysis');
    
    // Get base path from meta tag
    const basePath = document.querySelector('meta[name="base-path"]') ? 
        document.querySelector('meta[name="base-path"]').getAttribute('content') : '';
    
    // Initialize performance chart
    initializePerformanceChart();
    
    // Initialize ROI calculator
    initializeROICalculator();
    
    // Initialize stock analysis
    initializeStockAnalysis();
});

/**
 * Initialize performance chart
 * Creates a chart showing investment performance over time
 */
function initializePerformanceChart() {
    const chartContainer = document.getElementById('performanceChartContainer');
    if (!chartContainer) return;
    
    // Create canvas if it doesn't exist
    if (!document.getElementById('performanceChart')) {
        const canvas = document.createElement('canvas');
        canvas.id = 'performanceChart';
        chartContainer.appendChild(canvas);
    }
    
    // Get performance data (this would come from the server in a real app)
    // For demonstration, we'll use mock data
    const performanceData = getMockPerformanceData();
    
    // Create chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    window.performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: performanceData.labels,
            datasets: [{
                label: 'Portfolio Value',
                data: performanceData.values,
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHitRadius: 10,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString();
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Add toggle for different time periods
    const timeframeControls = document.createElement('div');
    timeframeControls.className = 'btn-group mt-3';
    timeframeControls.innerHTML = `
        <button type="button" class="btn btn-outline-primary btn-sm timeframe-btn active" data-period="1m">1M</button>
        <button type="button" class="btn btn-outline-primary btn-sm timeframe-btn" data-period="3m">3M</button>
        <button type="button" class="btn btn-outline-primary btn-sm timeframe-btn" data-period="6m">6M</button>
        <button type="button" class="btn btn-outline-primary btn-sm timeframe-btn" data-period="1y">1Y</button>
        <button type="button" class="btn btn-outline-primary btn-sm timeframe-btn" data-period="all">All</button>
    `;
    chartContainer.appendChild(timeframeControls);
    
    // Add event listeners to timeframe buttons
    document.querySelectorAll('.timeframe-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.timeframe-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update chart data based on selected timeframe
            const period = this.getAttribute('data-period');
            const newData = getMockPerformanceData(period);
            
            window.performanceChart.data.labels = newData.labels;
            window.performanceChart.data.datasets[0].data = newData.values;
            window.performanceChart.update();
        });
    });
}

/**
 * Initialize ROI calculator
 * Calculates and displays ROI metrics for investments
 */
function initializeROICalculator() {
    const roiContainer = document.getElementById('roiCalculatorContainer');
    if (!roiContainer) return;
    
    // Get investment data
    const investments = getInvestmentDataFromTable();
    
    // Calculate ROI metrics
    const roiMetrics = calculateROIMetrics(investments);
    
    // Create and append ROI cards
    const roiCards = document.createElement('div');
    roiCards.className = 'row';
    
    roiCards.innerHTML = `
        <div class="col-lg-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Average Annual ROI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${roiMetrics.averageAnnualROI}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Best Performing Investment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${roiMetrics.bestPerforming.name}</div>
                            <div class="small text-success">+${roiMetrics.bestPerforming.roi}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Portfolio CAGR</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${roiMetrics.cagr}%</div>
                            <div class="small">Compound Annual Growth Rate</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    roiContainer.appendChild(roiCards);
}

/**
 * Initialize stock analysis 
 * Analyzes stocks and provides buy/sell recommendations
 */
function initializeStockAnalysis() {
    const stockAnalysisContainer = document.getElementById('stockAnalysisContainer');
    if (!stockAnalysisContainer) return;
    
    // Get stock investments (with ticker symbols)
    const investments = getInvestmentDataFromTable();
    const stockInvestments = investments.filter(inv => inv.ticker && inv.ticker.trim() !== '');
    
    if (stockInvestments.length === 0) {
        stockAnalysisContainer.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No stock investments found with ticker symbols. Add stock investments with valid ticker symbols to get buy/sell recommendations.
            </div>
        `;
        return;
    }
    
    // Create table for stock analysis
    const stockTable = document.createElement('div');
    stockTable.className = 'card shadow mb-4';
    stockTable.innerHTML = `
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Stock Buy/Sell Analysis</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="stockAnalysisDropdown" 
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                     aria-labelledby="stockAnalysisDropdown">
                    <a class="dropdown-item" href="#" id="refreshStockAnalysis">
                        <i class="fas fa-sync fa-sm fa-fw me-2 text-gray-400"></i>
                        Refresh Analysis
                    </a>
                    <a class="dropdown-item" href="#" id="analyzeTechnicals">
                        <i class="fas fa-chart-line fa-sm fa-fw me-2 text-gray-400"></i>
                        Technical Analysis
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" id="analyzeSettings">
                        <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                        Analysis Settings
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="stockAnalysisTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Stock</th>
                            <th>Ticker</th>
                            <th>Current Price</th>
                            <th>Fair Value</th>
                            <th>Buy Target</th>
                            <th>Sell Target</th>
                            <th>Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${stockInvestments.map(stock => {
                            // Calculate analysis values (in a real app, this would come from an API)
                            const analysis = calculateStockAnalysis(stock);
                            return `
                                <tr>
                                    <td>${stock.name}</td>
                                    <td>${stock.ticker}</td>
                                    <td>$${parseFloat(stock.currentPrice).toFixed(2)}</td>
                                    <td>$${analysis.fairValue.toFixed(2)}</td>
                                    <td>$${analysis.buyTarget.toFixed(2)}</td>
                                    <td>$${analysis.sellTarget.toFixed(2)}</td>
                                    <td>${getRecommendationBadge(analysis.recommendation)}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Analysis is based on historical data and market trends. Updated: ${new Date().toLocaleDateString()}
                </small>
            </div>
        </div>
    `;
    
    stockAnalysisContainer.appendChild(stockTable);
    
    // Add event listener to refresh button
    document.getElementById('refreshStockAnalysis').addEventListener('click', function(e) {
        e.preventDefault();
        refreshStockAnalysis();
    });
    
    // Add event listener to technical analysis button
    document.getElementById('analyzeTechnicals').addEventListener('click', function(e) {
        e.preventDefault();
        showTechnicalAnalysisModal();
    });
    
    // Add event listener to settings button
    document.getElementById('analyzeSettings').addEventListener('click', function(e) {
        e.preventDefault();
        showAnalysisSettingsModal();
    });
}

/**
 * Refresh stock analysis data
 */
function refreshStockAnalysis() {
    // In a real app, this would fetch fresh data from an API
    // For demo, we'll just simulate a refresh
    const tbody = document.querySelector('#stockAnalysisTable tbody');
    if (!tbody) return;
    
    // Show loading indicator
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Refreshing stock analysis...</p>
            </td>
        </tr>
    `;
    
    // Simulate network delay
    setTimeout(() => {
        // Get investment data
        const investments = getInvestmentDataFromTable();
        const stockInvestments = investments.filter(inv => inv.ticker && inv.ticker.trim() !== '');
        
        // Update table
        tbody.innerHTML = stockInvestments.map(stock => {
            // Calculate analysis values with slight variations to simulate updated data
            const analysis = calculateStockAnalysis(stock, true);
            return `
                <tr>
                    <td>${stock.name}</td>
                    <td>${stock.ticker}</td>
                    <td>$${parseFloat(stock.currentPrice).toFixed(2)}</td>
                    <td>$${analysis.fairValue.toFixed(2)}</td>
                    <td>$${analysis.buyTarget.toFixed(2)}</td>
                    <td>$${analysis.sellTarget.toFixed(2)}</td>
                    <td>${getRecommendationBadge(analysis.recommendation)}</td>
                </tr>
            `;
        }).join('');
        
        // Show success message
        showNotification('Stock analysis refreshed successfully', 'success');
    }, 1500);
}

/**
 * Show technical analysis modal
 */
function showTechnicalAnalysisModal() {
    // Check if modal already exists
    let modal = document.getElementById('technicalAnalysisModal');
    
    if (!modal) {
        // Create modal element
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'technicalAnalysisModal';
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', 'technicalAnalysisModalLabel');
        modal.setAttribute('aria-hidden', 'true');
        
        // Get stock tickers
        const investments = getInvestmentDataFromTable();
        const stockInvestments = investments.filter(inv => inv.ticker && inv.ticker.trim() !== '');
        const stockOptions = stockInvestments.map(stock => 
            `<option value="${stock.ticker}">${stock.name} (${stock.ticker})</option>`
        ).join('');
        
        // Set modal content
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="technicalAnalysisModalLabel">Technical Analysis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="techAnalysisStock" class="form-label">Select Stock</label>
                                <select class="form-select" id="techAnalysisStock">
                                    ${stockOptions}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="techAnalysisPeriod" class="form-label">Time Period</label>
                                <select class="form-select" id="techAnalysisPeriod">
                                    <option value="1m">1 Month</option>
                                    <option value="3m" selected>3 Months</option>
                                    <option value="6m">6 Months</option>
                                    <option value="1y">1 Year</option>
                                    <option value="2y">2 Years</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="mb-0">Price Chart</h6>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary active" data-chart-type="line">Line</button>
                                    <button type="button" class="btn btn-outline-primary" data-chart-type="candlestick">Candlestick</button>
                                </div>
                            </div>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="technicalAnalysisChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="m-0 font-weight-bold text-primary">Technical Indicators</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <table class="table table-sm">
                                            <tbody id="technicalIndicators">
                                                <tr>
                                                    <td>Moving Average (50)</td>
                                                    <td id="ma50">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Moving Average (200)</td>
                                                    <td id="ma200">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>RSI (14)</td>
                                                    <td id="rsi14">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>MACD</td>
                                                    <td id="macd">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Bollinger Bands</td>
                                                    <td id="bollinger">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="m-0 font-weight-bold text-primary">Support & Resistance</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Strong Resistance</td>
                                                    <td id="strongResistance">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Resistance</td>
                                                    <td id="resistance">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Current Price</td>
                                                    <td id="currentPriceTech">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Support</td>
                                                    <td id="support">Loading...</td>
                                                </tr>
                                                <tr>
                                                    <td>Strong Support</td>
                                                    <td id="strongSupport">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-primary">Analysis Summary</h6>
                            </div>
                            <div class="card-body py-2">
                                <div id="analysisSummary">
                                    <p>Select a stock and time period to view technical analysis.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="generateTechnicalReport">Generate Report</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.appendChild(modal);
        
        // Initialize technical analysis chart
        let technicalChart; // Define chart variable in outer scope
        
        // Add event listeners for select changes
        document.getElementById('techAnalysisStock').addEventListener('change', updateTechnicalAnalysis);
        document.getElementById('techAnalysisPeriod').addEventListener('change', updateTechnicalAnalysis);
        
        // Add event listeners for chart type buttons
        document.querySelectorAll('[data-chart-type]').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('[data-chart-type]').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update chart type
                updateTechnicalAnalysis();
            });
        });
        
        // Generate report button handler
        document.getElementById('generateTechnicalReport').addEventListener('click', function() {
            const ticker = document.getElementById('techAnalysisStock').value;
            const period = document.getElementById('techAnalysisPeriod').value;
            
            // Show notification
            showNotification(`Generating technical analysis report for ${ticker}...`, 'info');
            
            // In a real app, this would generate and download a PDF report
            setTimeout(() => {
                showNotification(`Technical analysis report for ${ticker} has been generated`, 'success');
            }, 1500);
        });
        
        function updateTechnicalAnalysis() {
            const ticker = document.getElementById('techAnalysisStock').value;
            const period = document.getElementById('techAnalysisPeriod').value;
            const chartType = document.querySelector('[data-chart-type].active').getAttribute('data-chart-type');
            
            if (!ticker || !period) return;
            
            // Get stock data
            const stockData = getMockStockData(ticker, period);
            
            // Update chart
            if (technicalChart) {
                technicalChart.destroy();
            }
            
            const ctx = document.getElementById('technicalAnalysisChart').getContext('2d');
            
            if (chartType === 'line') {
                technicalChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: stockData.dates,
                        datasets: [{
                            label: ticker,
                            data: stockData.prices,
                            borderColor: 'rgba(78, 115, 223, 1)',
                            backgroundColor: 'rgba(78, 115, 223, 0.1)',
                            pointRadius: 0,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxTicksLimit: 10
                                }
                            },
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // For candlestick, we'd use a different chart library in a real app
                // For this demo, we'll use a modified line chart
                technicalChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: stockData.dates,
                        datasets: [{
                            label: ticker + ' Price',
                            data: stockData.prices,
                            backgroundColor: stockData.prices.map((price, i) => {
                                return i > 0 && price > stockData.prices[i-1] 
                                    ? 'rgba(40, 167, 69, 0.7)' 
                                    : 'rgba(220, 53, 69, 0.7)';
                            }),
                            borderColor: stockData.prices.map((price, i) => {
                                return i > 0 && price > stockData.prices[i-1] 
                                    ? 'rgba(40, 167, 69, 1)' 
                                    : 'rgba(220, 53, 69, 1)';
                            }),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxTicksLimit: 10
                                }
                            },
                            y: {
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
            
            // Update technical indicators
            updateTechnicalIndicators(ticker, stockData);
        }
        
        function updateTechnicalIndicators(ticker, stockData) {
            // Calculate technical indicators
            const lastPrice = stockData.prices[stockData.prices.length - 1];
            const ma50 = calculateMovingAverage(stockData.prices, 50);
            const ma200 = calculateMovingAverage(stockData.prices, 200);
            const rsi = calculateRSI(stockData.prices);
            const macd = {
                value: (ma50 - calculateMovingAverage(stockData.prices, 26)).toFixed(2),
                signal: (calculateMovingAverage(stockData.prices, 12) - calculateMovingAverage(stockData.prices, 26)).toFixed(2)
            };
            const bollinger = calculateBollingerBands(stockData.prices);
            
            // Calculate support and resistance levels
            const priceRange = Math.max(...stockData.prices) - Math.min(...stockData.prices);
            const strongResistance = (Math.max(...stockData.prices) - priceRange * 0.1).toFixed(2);
            const resistance = (lastPrice + priceRange * 0.05).toFixed(2);
            const support = (lastPrice - priceRange * 0.05).toFixed(2);
            const strongSupport = (Math.min(...stockData.prices) + priceRange * 0.1).toFixed(2);
            
            // Update UI
            document.getElementById('ma50').innerHTML = `$${ma50.toFixed(2)} <span class="${lastPrice > ma50 ? 'text-success' : 'text-danger'}">${lastPrice > ma50 ? 'Above' : 'Below'}</span>`;
            document.getElementById('ma200').innerHTML = `$${ma200.toFixed(2)} <span class="${lastPrice > ma200 ? 'text-success' : 'text-danger'}">${lastPrice > ma200 ? 'Above' : 'Below'}</span>`;
            document.getElementById('rsi14').innerHTML = `${rsi.toFixed(2)} <span class="${getRSIClass(rsi)}">${getRSIStatus(rsi)}</span>`;
            document.getElementById('macd').innerHTML = `${macd.value} <span class="${parseFloat(macd.value) > parseFloat(macd.signal) ? 'text-success' : 'text-danger'}">${parseFloat(macd.value) > parseFloat(macd.signal) ? 'Bullish' : 'Bearish'}</span>`;
            document.getElementById('bollinger').innerHTML = `Upper: $${bollinger.upper.toFixed(2)}, Lower: $${bollinger.lower.toFixed(2)}`;
            
            document.getElementById('strongResistance').textContent = `$${strongResistance}`;
            document.getElementById('resistance').textContent = `$${resistance}`;
            document.getElementById('currentPriceTech').textContent = `$${lastPrice.toFixed(2)}`;
            document.getElementById('support').textContent = `$${support}`;
            document.getElementById('strongSupport').textContent = `$${strongSupport}`;
            
            // Generate analysis summary
            const summary = generateTechnicalSummary(ticker, lastPrice, ma50, ma200, rsi, macd, bollinger);
            document.getElementById('analysisSummary').innerHTML = summary;
        }
    }
    
    // Initialize the chart when the modal is shown
    modal.addEventListener('shown.bs.modal', function() {
        if (document.getElementById('techAnalysisStock').options.length > 0) {
            document.getElementById('techAnalysisStock').dispatchEvent(new Event('change'));
        }
    });
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Show analysis settings modal
 */
function showAnalysisSettingsModal() {
    // Check if modal already exists
    let modal = document.getElementById('analysisSettingsModal');
    
    if (!modal) {
        // Create modal element
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'analysisSettingsModal';
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', 'analysisSettingsModalLabel');
        modal.setAttribute('aria-hidden', 'true');
        
        // Set modal content
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="analysisSettingsModalLabel">Analysis Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="analysisSettingsForm">
                            <div class="mb-3">
                                <label class="form-label">Analysis Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="analysisMethod" id="methodTechnical" value="technical" checked>
                                    <label class="form-check-label" for="methodTechnical">
                                        Technical Analysis
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="analysisMethod" id="methodFundamental" value="fundamental">
                                    <label class="form-check-label" for="methodFundamental">
                                        Fundamental Analysis
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="analysisMethod" id="methodHybrid" value="hybrid">
                                    <label class="form-check-label" for="methodHybrid">
                                        Hybrid (Technical + Fundamental)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="valuationModel" class="form-label">Valuation Model</label>
                                <select class="form-select" id="valuationModel">
                                    <option value="dcf">Discounted Cash Flow (DCF)</option>
                                    <option value="pe" selected>Price to Earnings (P/E)</option>
                                    <option value="pbv">Price to Book Value (P/BV)</option>
                                    <option value="multi">Multiple Valuation Metrics</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="riskTolerance" class="form-label">Risk Tolerance</label>
                                <select class="form-select" id="riskTolerance">
                                    <option value="low">Low (Conservative)</option>
                                    <option value="medium" selected>Medium (Balanced)</option>
                                    <option value="high">High (Aggressive)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="marginOfSafety" class="form-label">Margin of Safety</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="marginOfSafety" value="15" min="5" max="50">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Percentage below estimated fair value to set buy target</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profitTarget" class="form-label">Profit Target</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="profitTarget" value="20" min="5" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Percentage above estimated fair value to set sell target</div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="autoUpdatePrices" checked>
                                <label class="form-check-label" for="autoUpdatePrices">
                                    Automatically update prices daily
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveAnalysisSettings">Save Settings</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.appendChild(modal);
        
        // Add event listener to save button
        document.getElementById('saveAnalysisSettings').addEventListener('click', function() {
            // In a real app, this would save settings to server/local storage
            showNotification('Analysis settings have been updated', 'success');
            
            // Close modal
            const bsModal = bootstrap.Modal.getInstance(modal);
            bsModal.hide();
            
            // Update stock analysis with new settings
            refreshStockAnalysis();
        });
    }
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Generate technical analysis summary
 * @param {string} ticker - Stock ticker symbol
 * @param {number} lastPrice - Most recent price
 * @param {number} ma50 - 50-day moving average
 * @param {number} ma200 - 200-day moving average
 * @param {number} rsi - Relative Strength Index
 * @param {object} macd - MACD values
 * @param {object} bollinger - Bollinger bands values
 * @returns {string} - HTML summary
 */
function generateTechnicalSummary(ticker, lastPrice, ma50, ma200, rsi, macd, bollinger) {
    // Determine trend
    const trend = lastPrice > ma50 && ma50 > ma200 
        ? 'uptrend' 
        : (lastPrice < ma50 && ma50 < ma200 
            ? 'downtrend' 
            : 'sideways');
    
    // Determine strength
    let strength;
    if (rsi > 70) strength = 'overbought';
    else if (rsi < 30) strength = 'oversold';
    else if (rsi > 50) strength = 'strong';
    else strength = 'weak';
    
    // Determine bollinger band position
    const bollingerPosition = lastPrice > bollinger.upper 
        ? 'above upper band (potentially overbought)' 
        : (lastPrice < bollinger.lower 
            ? 'below lower band (potentially oversold)' 
            : 'within bands (normal volatility)');
    
    // Generate summary text
    const summary = `
        <p>${ticker} is currently in a <strong>${trend}</strong> with <strong>${strength}</strong> momentum.</p>
        <p>The stock is trading ${lastPrice > ma50 ? 'above' : 'below'} its 50-day moving average
           and ${lastPrice > ma200 ? 'above' : 'below'} its 200-day moving average.</p>
        <p>RSI(14) indicates the stock is <strong>${getRSIStatus(rsi)}</strong> at ${rsi.toFixed(2)}.</p>
        <p>MACD is showing a <strong>${parseFloat(macd.value) > parseFloat(macd.signal) ? 'bullish' : 'bearish'}</strong> signal.</p>
        <p>The stock is trading ${bollingerPosition}.</p>
        <div class="mt-3">
            <strong>Technical Outlook:</strong> 
            <span class="${getTechnicalOutlookClass(trend, strength, rsi, macd)}">
                ${getTechnicalOutlook(trend, strength, rsi, macd)}
            </span>
        </div>
    `;
    
    return summary;
}

/**
 * Get technical outlook class
 * @param {string} trend - Price trend
 * @param {string} strength - Trend strength
 * @param {number} rsi - RSI value
 * @param {object} macd - MACD values
 * @returns {string} - CSS class
 */
function getTechnicalOutlookClass(trend, strength, rsi, macd) {
    if (trend === 'uptrend' && strength !== 'overbought' && parseFloat(macd.value) > parseFloat(macd.signal)) {
        return 'text-success';
    } else if (trend === 'downtrend' && strength !== 'oversold' && parseFloat(macd.value) < parseFloat(macd.signal)) {
        return 'text-danger';
    } else if (trend === 'uptrend' && strength === 'overbought') {
        return 'text-warning';
    } else if (trend === 'downtrend' && strength === 'oversold') {
        return 'text-warning';
    } else {
        return 'text-info';
    }
}

/**
 * Get technical outlook text
 * @param {string} trend - Price trend
 * @param {string} strength - Trend strength
 * @param {number} rsi - RSI value
 * @param {object} macd - MACD values
 * @returns {string} - Outlook text
 */
function getTechnicalOutlook(trend, strength, rsi, macd) {
    if (trend === 'uptrend' && strength !== 'overbought' && parseFloat(macd.value) > parseFloat(macd.signal)) {
        return 'Bullish - Consider buying on dips';
    } else if (trend === 'downtrend' && strength !== 'oversold' && parseFloat(macd.value) < parseFloat(macd.signal)) {
        return 'Bearish - Consider selling or reducing position';
    } else if (trend === 'uptrend' && strength === 'overbought') {
        return 'Cautiously Bullish - Potential pullback ahead';
    } else if (trend === 'downtrend' && strength === 'oversold') {
        return 'Cautiously Bearish - Potential bounce ahead';
    } else if (trend === 'sideways') {
        return 'Neutral - Wait for clearer signals';
    } else {
        return 'Mixed signals - Monitor closely';
    }
}

/**
 * Get RSI status text
 * @param {number} rsi - RSI value
 * @returns {string} - Status text
 */
function getRSIStatus(rsi) {
    if (rsi > 70) return 'Overbought';
    if (rsi < 30) return 'Oversold';
    if (rsi > 50) return 'Bullish';
    return 'Bearish';
}

/**
 * Get RSI class
 * @param {number} rsi - RSI value
 * @returns {string} - CSS class
 */
function getRSIClass(rsi) {
    if (rsi > 70) return 'text-danger';
    if (rsi < 30) return 'text-success';
    if (rsi > 50) return 'text-success';
    return 'text-danger';
}

/**
 * Calculate moving average
 * @param {Array} prices - Array of prices
 * @param {number} period - Period for moving average
 * @returns {number} - Moving average value
 */
function calculateMovingAverage(prices, period) {
    if (prices.length < period) {
        return prices.reduce((sum, price) => sum + price, 0) / prices.length;
    }
    
    const recentPrices = prices.slice(-period);
    return recentPrices.reduce((sum, price) => sum + price, 0) / period;
}

/**
 * Calculate RSI (Relative Strength Index)
 * @param {Array} prices - Array of prices
 * @param {number} period - Period for RSI (default: 14)
 * @returns {number} - RSI value
 */
function calculateRSI(prices, period = 14) {
    if (prices.length < period + 1) {
        return 50; // Not enough data
    }
    
    // Calculate price changes
    const changes = [];
    for (let i = 1; i < prices.length; i++) {
        changes.push(prices[i] - prices[i-1]);
    }
    
    // Get recent changes for RSI period
    const recentChanges = changes.slice(-period);
    
    // Calculate gains and losses
    let gains = 0;
    let losses = 0;
    
    recentChanges.forEach(change => {
        if (change > 0) {
            gains += change;
        } else {
            losses -= change;
        }
    });
    
    // Calculate average gain and loss
    const avgGain = gains / period;
    const avgLoss = losses / period;
    
    // Calculate RS and RSI
    if (avgLoss === 0) return 100;
    const rs = avgGain / avgLoss;
    return 100 - (100 / (1 + rs));
}

/**
 * Calculate Bollinger Bands
 * @param {Array} prices - Array of prices
 * @param {number} period - Period for moving average (default: 20)
 * @param {number} multiplier - Standard deviation multiplier (default: 2)
 * @returns {object} - Bollinger bands values
 */
function calculateBollingerBands(prices, period = 20, multiplier = 2) {
    if (prices.length < period) {
        return {
            middle: prices[prices.length - 1],
            upper: prices[prices.length - 1] * 1.1,
            lower: prices[prices.length - 1] * 0.9
        };
    }
    
    // Calculate SMA (middle band)
    const recentPrices = prices.slice(-period);
    const sma = recentPrices.reduce((sum, price) => sum + price, 0) / period;
    
    // Calculate standard deviation
    const squaredDiffs = recentPrices.map(price => Math.pow(price - sma, 2));
    const variance = squaredDiffs.reduce((sum, diff) => sum + diff, 0) / period;
    const stdDev = Math.sqrt(variance);
    
    // Calculate bands
    return {
        middle: sma,
        upper: sma + (multiplier * stdDev),
        lower: sma - (multiplier * stdDev)
    };
}

/**
 * Get recommendation badge HTML
 * @param {string} recommendation - Recommendation type
 * @returns {string} - HTML for badge
 */
function getRecommendationBadge(recommendation) {
    let badgeClass, icon;
    
    switch (recommendation) {
        case 'Strong Buy':
            badgeClass = 'bg-success';
            icon = 'thumbs-up';
            break;
        case 'Buy':
            badgeClass = 'bg-primary';
            icon = 'arrow-up';
            break;
        case 'Hold':
            badgeClass = 'bg-info';
            icon = 'minus';
            break;
        case 'Sell':
            badgeClass = 'bg-warning';
            icon = 'arrow-down';
            break;
        case 'Strong Sell':
            badgeClass = 'bg-danger';
            icon = 'thumbs-down';
            break;
        default:
            badgeClass = 'bg-secondary';
            icon = 'question';
    }
    
    return `<span class="badge ${badgeClass}"><i class="fas fa-${icon} me-1"></i> ${recommendation}</span>`;
}

/**
 * Calculate ROI metrics for investments
 * @param {Array} investments - Array of investment objects
 * @returns {object} - ROI metrics
 */
function calculateROIMetrics(investments) {
    // If no investments, return default values
    if (!investments || investments.length === 0) {
        return {
            averageAnnualROI: '0.00',
            bestPerforming: {
                name: 'N/A',
                roi: '0.00'
            },
            cagr: '0.00'
        };
    }
    
    // Calculate ROI for each investment
    const investmentsWithROI = investments.map(inv => {
        // Calculate days held
        const purchaseDate = new Date(inv.purchaseDate);
        const today = new Date();
        const daysHeld = Math.max(1, Math.round((today - purchaseDate) / (1000 * 60 * 60 * 24)));
        
        // Calculate ROI
        const initialInvestment = inv.purchasePrice * inv.quantity;
        const currentValue = inv.currentPrice * inv.quantity;
        const roi = ((currentValue - initialInvestment) / initialInvestment) * 100;
        
        // Calculate annualized ROI
        const yearsHeld = daysHeld / 365;
        const annualizedROI = Math.pow((1 + roi/100), 1/yearsHeld) - 1;
        
        return {
            ...inv,
            roi,
            annualizedROI: annualizedROI * 100
        };
    });
    
    // Calculate average annual ROI
    const totalAnnualROI = investmentsWithROI.reduce((sum, inv) => sum + inv.annualizedROI, 0);
    const averageAnnualROI = totalAnnualROI / investmentsWithROI.length;
    
    // Find best performing investment
    const bestPerforming = investmentsWithROI.reduce((best, inv) => {
        return inv.roi > best.roi ? inv : best;
    }, { roi: -Infinity });
    
    // Calculate CAGR (Compound Annual Growth Rate) for entire portfolio
    const initialPortfolioValue = investments.reduce((sum, inv) => sum + (inv.purchasePrice * inv.quantity), 0);
    const currentPortfolioValue = investments.reduce((sum, inv) => sum + (inv.currentPrice * inv.quantity), 0);
    
    // Calculate average hold time in years
    const avgPurchaseDate = new Date(
        investments.reduce((sum, inv) => sum + new Date(inv.purchaseDate).getTime(), 0) / investments.length
    );
    const today = new Date();
    const avgYearsHeld = Math.max(0.1, (today - avgPurchaseDate) / (1000 * 60 * 60 * 24 * 365));
    
    // Calculate CAGR
    const cagr = Math.pow((currentPortfolioValue / initialPortfolioValue), 1/avgYearsHeld) - 1;
    
    return {
        averageAnnualROI: averageAnnualROI.toFixed(2),
        bestPerforming: {
            name: bestPerforming.name || 'N/A',
            roi: bestPerforming.roi.toFixed(2)
        },
        cagr: (cagr * 100).toFixed(2)
    };
}

/**
 * Calculate stock analysis values
 * @param {object} stock - Stock investment data
 * @param {boolean} isRefresh - Whether this is a refresh (for demo data variation)
 * @returns {object} - Analysis results
 */
function calculateStockAnalysis(stock, isRefresh = false) {
    // In a real app, this would use fundamental and technical analysis
    // For demo purposes, we'll use simple calculations based on current price
    
    // Add some variety for refresh simulation
    const randomFactor = isRefresh ? (1 + ((Math.random() - 0.5) * 0.05)) : 1;
    
    // Get current price
    const currentPrice = parseFloat(stock.currentPrice);
    
    // Calculate fair value (in a real app, this would use DCF or other methods)
    // For demo, we'll use a simple calculation
    let fairValue = currentPrice * (1 + (Math.random() * 0.4 - 0.2)) * randomFactor;
    
    // Calculate buy and sell targets
    const marginOfSafety = 0.15; // 15% below fair value
    const profitTarget = 0.20;   // 20% above fair value
    
    const buyTarget = fairValue * (1 - marginOfSafety);
    const sellTarget = fairValue * (1 + profitTarget);
    
    // Determine recommendation
    let recommendation;
    if (currentPrice < buyTarget * 0.9) {
        recommendation = 'Strong Buy';
    } else if (currentPrice < buyTarget) {
        recommendation = 'Buy';
    } else if (currentPrice > sellTarget * 1.1) {
        recommendation = 'Strong Sell';
    } else if (currentPrice > sellTarget) {
        recommendation = 'Sell';
    } else {
        recommendation = 'Hold';
    }
    
    return {
        fairValue,
        buyTarget,
        sellTarget,
        recommendation
    };
}

/**
 * Get mock performance data
 * @param {string} period - Time period (1m, 3m, 6m, 1y, all)
 * @returns {object} - Performance data
 */
function getMockPerformanceData(period = '1y') {
    // In a real app, this would be actual portfolio value over time
    // For demo, we'll generate mockup data
    
    let days;
    switch (period) {
        case '1m': days = 30; break;
        case '3m': days = 90; break;
        case '6m': days = 180; break;
        case '1y': days = 365; break;
        case 'all': days = 730; break; // 2 years
        default: days = 365;
    }
    
    const labels = [];
    const values = [];
    
    // Generate dates and values
    const today = new Date();
    let currentValue = 10000; // Starting value
    
    for (let i = days; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        
        // Format date as MMM DD
        const month = date.toLocaleString('default', { month: 'short' });
        const day = date.getDate();
        labels.push(`${month} ${day}`);
        
        // Add some randomness to simulate market fluctuations
        // More volatility for longer periods
        const volatility = period === '1m' ? 0.003 : (period === '3m' ? 0.006 : 0.01);
        const change = 1 + ((Math.random() - 0.45) * volatility); // Slightly bullish trend
        
        currentValue *= change;
        values.push(Math.round(currentValue * 100) / 100);
    }
    
    return {
        labels,
        values
    };
}

/**
 * Get mock stock data
 * @param {string} ticker - Stock ticker symbol
 * @param {string} period - Time period (1m, 3m, 6m, 1y, 2y)
 * @returns {object} - Stock data
 */
function getMockStockData(ticker, period) {
    // In a real app, this would fetch data from an API
    // For demo, we'll generate mockup data
    
    let days;
    switch (period) {
        case '1m': days = 30; break;
        case '3m': days = 90; break;
        case '6m': days = 180; break;
        case '1y': days = 365; break;
        case '2y': days = 730; break;
        default: days = 90;
    }
    
    const dates = [];
    const prices = [];
    
    // Generate dates and prices
    const today = new Date();
    
    // Get a base price (use a hash of the ticker for consistency)
    let basePrice = 0;
    for (let i = 0; i < ticker.length; i++) {
        basePrice += ticker.charCodeAt(i);
    }
    basePrice = (basePrice % 300) + 20; // Between $20 and $320
    
    // Set trend direction (slightly positive bias)
    const trend = Math.random() > 0.3 ? 1 : -1;
    const trendStrength = Math.random() * 0.001; // Daily trend factor
    
    let currentPrice = basePrice;
    
    for (let i = days; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        
        // Format date as MMM DD
        const month = date.toLocaleString('default', { month: 'short' });
        const day = date.getDate();
        dates.push(`${month} ${day}`);
        
        // Add randomness and trend
        const dailyVolatility = Math.random() * 0.02 - 0.01; // -1% to +1%
        const dailyTrend = trend * trendStrength * i; // Trend gets stronger over time
        const change = 1 + dailyVolatility + dailyTrend;
        
        currentPrice *= change;
        prices.push(Math.round(currentPrice * 100) / 100);
    }
    
    return {
        dates,
        prices
    };
}

/**
 * Get investment data from table
 * @returns {Array} - Array of investment objects
 */
function getInvestmentDataFromTable() {
    // In a real app, this might be provided by the server or API
    // For demo purposes, we'll extract data from the table on the page
    const investments = [];
    const table = document.getElementById('investmentTable');
    
    if (!table) {
        // If table doesn't exist, return mock data
        return [
            {
                name: 'Apple Inc.',
                ticker: 'AAPL',
                purchaseDate: '2020-01-15',
                purchasePrice: 82.12,
                currentPrice: 175.42,
                quantity: 10
            },
            {
                name: 'Microsoft Corporation',
                ticker: 'MSFT',
                purchaseDate: '2019-06-10',
                purchasePrice: 132.45,
                currentPrice: 330.11,
                quantity: 5
            },
            {
                name: 'Amazon.com Inc.',
                ticker: 'AMZN',
                purchaseDate: '2021-03-22',
                purchasePrice: 3052.03,
                currentPrice: 129.33,
                quantity: 2
            }
        ];
    }
    
    // Extract data from table rows
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(1)').textContent.trim();
        const tickerCell = row.querySelector('td:nth-child(2)');
        const ticker = tickerCell ? tickerCell.textContent.trim() : '';
        const purchaseDateCell = row.querySelector('td:nth-child(3)');
        const purchaseDate = purchaseDateCell ? purchaseDateCell.textContent.trim() : '';
        const purchasePriceCell = row.querySelector('td:nth-child(4)');
        const purchasePrice = purchasePriceCell ? 
            parseFloat(purchasePriceCell.textContent.replace('$', '').replace(/,/g, '')) : 0;
        const quantityCell = row.querySelector('td:nth-child(5)');
        const quantity = quantityCell ? 
            parseFloat(quantityCell.textContent.replace(/,/g, '')) : 0;
        const currentPriceCell = row.querySelector('td:nth-child(6)');
        const currentPrice = currentPriceCell ? 
            parseFloat(currentPriceCell.textContent.replace('$', '').replace(/,/g, '')) : 0;
        
        investments.push({
            name,
            ticker,
            purchaseDate,
            purchasePrice,
            quantity,
            currentPrice
        });
    });
    
    return investments;
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 * @param {number} duration - Display duration in ms
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Check if notification container exists
    let container = document.getElementById('notification-container');
    
    if (!container) {
        // Create container
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.width = '300px';
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.marginBottom = '10px';
    notification.style.boxShadow = '0 0.25rem 0.75rem rgba(0, 0, 0, 0.1)';
    
    // Add content
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Try to use Bootstrap's alert dismiss
    try {
        const bsAlert = new bootstrap.Alert(notification);
        
        // Auto close after duration
        setTimeout(() => {
            bsAlert.close();
        }, duration);
        
        // Remove from DOM after closing animation
        notification.addEventListener('closed.bs.alert', () => {
            notification.remove();
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        });
    } catch (e) {
        // Fallback if bootstrap is not available
        setTimeout(() => {
            notification.remove();
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        }, duration);
    }
}