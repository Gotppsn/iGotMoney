<?php
// Set page title and current page for menu highlighting
$page_title = 'Stock Analysis - iGotMoney';
$current_page = 'stocks';

// Additional JS
$additional_js = ['/assets/js/stocks.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Stock Analysis</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
            <i class="fas fa-plus"></i> Add to Watchlist
        </button>
    </div>
</div>

<!-- Stock Analysis and Watchlist -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Analyze Stock</h6>
            </div>
            <div class="card-body">
                <form action="/stocks" method="post" id="analyzeStockForm">
                    <input type="hidden" name="action" value="analyze_stock">
                    
                    <div class="mb-3">
                        <label for="ticker_symbol" class="form-label">Stock Ticker Symbol</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ticker_symbol" name="ticker_symbol" placeholder="e.g., AAPL, MSFT" required>
                            <button class="btn btn-primary" type="submit">Analyze</button>
                        </div>
                        <div class="form-text">Enter the ticker symbol of the stock you want to analyze.</div>
                    </div>
                </form>
                
                <?php if (isset($stock_analysis) && $stock_analysis['status'] === 'success'): ?>
                    <div class="alert alert-success">
                        <h5>Analysis Results for <?php echo htmlspecialchars($stock_analysis['ticker']); ?></h5>
                        <p><strong>Current Price:</strong> $<?php echo number_format($stock_analysis['current_price'], 2); ?></p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Technical Indicators</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        Short MA (20-day)
                                        <span>$<?php echo number_format($stock_analysis['short_ma'], 2); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        Long MA (50-day)
                                        <span>$<?php echo number_format($stock_analysis['long_ma'], 2); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        RSI (14-day)
                                        <span><?php echo number_format($stock_analysis['rsi'], 2); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        Support Level
                                        <span>$<?php echo number_format($stock_analysis['support'], 2); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        Resistance Level
                                        <span>$<?php echo number_format($stock_analysis['resistance'], 2); ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Recommendation</h6>
                                <?php
                                $recommendation_class = '';
                                $recommendation_icon = '';
                                
                                switch ($stock_analysis['recommendation']) {
                                    case 'buy':
                                        $recommendation_class = 'success';
                                        $recommendation_icon = 'arrow-up';
                                        break;
                                    case 'sell':
                                        $recommendation_class = 'danger';
                                        $recommendation_icon = 'arrow-down';
                                        break;
                                    default:
                                        $recommendation_class = 'info';
                                        $recommendation_icon = 'minus';
                                }
                                ?>
                                <div class="alert alert-<?php echo $recommendation_class; ?>">
                                    <h4 class="alert-heading">
                                        <i class="fas fa-<?php echo $recommendation_icon; ?> me-2"></i>
                                        <?php echo ucfirst($stock_analysis['recommendation']); ?>
                                    </h4>
                                    <hr>
                                    <?php if (!empty($stock_analysis['buy_points'])): ?>
                                        <p><strong>Potential Buy Points:</strong></p>
                                        <ul>
                                            <?php foreach ($stock_analysis['buy_points'] as $point): ?>
                                                <li>$<?php echo number_format($point['price'], 2); ?> - <?php echo $point['reason']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($stock_analysis['sell_points'])): ?>
                                        <p><strong>Potential Sell Points:</strong></p>
                                        <ul>
                                            <?php foreach ($stock_analysis['sell_points'] as $point): ?>
                                                <li>$<?php echo number_format($point['price'], 2); ?> - <?php echo $point['reason']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm add-to-watchlist-from-analysis" 
                                        data-ticker="<?php echo htmlspecialchars($stock_analysis['ticker']); ?>" 
                                        data-price="<?php echo $stock_analysis['current_price']; ?>">
                                    <i class="fas fa-plus"></i> Add to Watchlist
                                </button>
                            </div>
                        </div>
                        
                        <div class="stock-chart-container mt-4">
                            <canvas id="stockPriceChart" class="stock-chart"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Stock Watchlist</h6>
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Search watchlist..." id="watchlistSearch" data-table-search="watchlistTable">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($watchlist) && $watchlist->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered data-table" id="watchlistTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Symbol</th>
                                    <th>Company</th>
                                    <th>Current Price</th>
                                    <th>Target Buy</th>
                                    <th>Target Sell</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($stock = $watchlist->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stock['ticker_symbol']); ?></td>
                                        <td><?php echo htmlspecialchars($stock['company_name']); ?></td>
                                        <td>$<?php echo number_format($stock['current_price'], 2); ?></td>
                                        <td>
                                            <?php if (!empty($stock['target_buy_price'])): ?>
                                                $<?php echo number_format($stock['target_buy_price'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($stock['target_sell_price'])): ?>
                                                $<?php echo number_format($stock['target_sell_price'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info analyze-from-watchlist" data-ticker="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning edit-watchlist" data-watchlist-id="<?php echo $stock['watchlist_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger remove-from-watchlist" data-watchlist-id="<?php echo $stock['watchlist_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No stocks in your watchlist yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                            <i class="fas fa-plus"></i> Add Your First Stock
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stock Market Insights -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Stock Market Insights</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">Technical Analysis</h5>
                        <p class="card-text">Technical analysis uses charts and indicators to predict future stock price movements based on historical data patterns.</p>
                        <p><strong>Key Indicators:</strong></p>
                        <ul>
                            <li>Moving Averages (MA) - Short vs Long term trends</li>
                            <li>Relative Strength Index (RSI) - Overbought/Oversold conditions</li>
                            <li>Support & Resistance - Price level boundaries</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">Fundamental Analysis</h5>
                        <p class="card-text">Fundamental analysis evaluates a company's intrinsic value by examining financial statements, industry position, and economic factors.</p>
                        <p><strong>Key Metrics:</strong></p>
                        <ul>
                            <li>P/E Ratio - Price relative to earnings</li>
                            <li>Debt-to-Equity Ratio - Financial leverage</li>
                            <li>Revenue Growth - Sales trend over time</li>
                            <li>Profit Margins - Efficiency of operations</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">Investment Strategies</h5>
                        <p class="card-text">Different approaches to stock investing based on your goals and risk tolerance.</p>
                        <p><strong>Common Strategies:</strong></p>
                        <ul>
                            <li>Value Investing - Finding undervalued stocks</li>
                            <li>Growth Investing - Focus on expansion potential</li>
                            <li>Dividend Investing - Income generation</li>
                            <li>Dollar-Cost Averaging - Regular investments over time</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add to Watchlist Modal -->
<div class="modal fade" id="addToWatchlistModal" tabindex="-1" aria-labelledby="addToWatchlistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addToWatchlistModalLabel">Add Stock to Watchlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/stocks" method="post">
                <input type="hidden" name="action" value="add_to_watchlist">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ticker_symbol_watchlist" class="form-label">Ticker Symbol</label>
                        <input type="text" class="form-control" id="ticker_symbol_watchlist" name="ticker_symbol" required>
                        <div class="form-text">Enter the stock's ticker symbol (e.g., AAPL for Apple Inc.)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_price_watchlist" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="current_price_watchlist" name="current_price" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="target_buy_price" class="form-label">Target Buy Price (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="target_buy_price" name="target_buy_price" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="target_sell_price" class="form-label">Target Sell Price (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="target_sell_price" name="target_sell_price" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes_watchlist" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes_watchlist" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Watchlist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove from Watchlist Modal -->
<div class="modal fade" id="removeFromWatchlistModal" tabindex="-1" aria-labelledby="removeFromWatchlistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeFromWatchlistModalLabel">Remove from Watchlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this stock from your watchlist?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/stocks" method="post">
                    <input type="hidden" name="action" value="remove_from_watchlist">
                    <input type="hidden" name="watchlist_id" id="remove_watchlist_id">
                    <button type="submit" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for stock analysis page
$page_scripts = "
// Handle add to watchlist from analysis button
document.querySelectorAll('.add-to-watchlist-from-analysis').forEach(button => {
    button.addEventListener('click', function() {
        const ticker = this.getAttribute('data-ticker');
        const price = this.getAttribute('data-price');
        
        // Populate the add to watchlist form
        document.getElementById('ticker_symbol_watchlist').value = ticker;
        document.getElementById('current_price_watchlist').value = price;
        document.getElementById('company_name').value = ticker + ' Inc.'; // Placeholder company name
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('addToWatchlistModal'));
        modal.show();
    });
});

// Handle analyze from watchlist button
document.querySelectorAll('.analyze-from-watchlist').forEach(button => {
    button.addEventListener('click', function() {
        const ticker = this.getAttribute('data-ticker');
        
        // Populate the analyze form and submit
        document.getElementById('ticker_symbol').value = ticker;
        document.getElementById('analyzeStockForm').submit();
    });
});

// Handle remove from watchlist button
document.querySelectorAll('.remove-from-watchlist').forEach(button => {
    button.addEventListener('click', function() {
        const watchlistId = this.getAttribute('data-watchlist-id');
        document.getElementById('remove_watchlist_id').value = watchlistId;
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('removeFromWatchlistModal'));
        modal.show();
    });
});

// Initialize stock price chart if data is available
if (typeof stockPriceData !== 'undefined' && document.getElementById('stockPriceChart')) {
    const ctx = document.getElementById('stockPriceChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: stockPriceData.dates,
            datasets: [{
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
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Price: $' + context.raw.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}

// Prepare stock price data if analysis is available
" . (isset($stock_analysis) && $stock_analysis['status'] === 'success' ? "
// Mock data for demonstration
const stockPriceData = {
    dates: [" . implode(',', array_map(function($i) {
        $date = date('M j', strtotime("-$i days"));
        return "'$date'";
    }, range(30, 0))) . "],
    prices: [" . implode(',', array_map(function($i) use ($stock_analysis) {
        $variation = rand(-500, 500) / 100;
        return $stock_analysis['current_price'] + $variation;
    }, range(30, 0))) . "]
};
" : "") . "
";

// Include footer
require_once 'includes/footer.php';
?>