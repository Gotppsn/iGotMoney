<?php
// Set page title and current page for menu highlighting
$page_title = 'Stock Analysis - iGotMoney';
$current_page = 'stocks';

// Include header
require_once 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Stock Analysis</h1>
        <p class="page-subtitle">Track, analyze, and discover investment opportunities</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
            <i class="fas fa-plus me-1"></i> Add to Watchlist
        </button>
    </div>
</div>

<!-- Stock Analysis Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="stock-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Stock Analysis</h5>
                <div class="d-flex align-items-center">
                    <span class="small text-muted me-2">Alt+S to search</span>
                    <span class="badge bg-primary rounded-pill me-2 <?php echo isset($stock_analysis) && isset($stock_analysis['is_demo_data']) ? '' : 'd-none'; ?>">Demo Data</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Analysis Form -->
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="analyzeStockForm" class="analysis-form">
                    <input type="hidden" name="action" value="analyze_stock">
                    
                    <div class="position-relative">
                        <i class="fas fa-search form-icon"></i>
                        <input type="text" class="form-control" id="ticker_symbol" name="ticker_symbol" placeholder="Enter stock ticker (e.g., AAPL, MSFT, GOOG)" required>
                        <button class="btn btn-primary search-btn" type="submit">Analyze</button>
                    </div>
                    <div class="form-text text-center mt-2">Enter the ticker symbol of the stock you want to analyze</div>
                </form>
                
                <!-- Analysis Results -->
                <div id="analysisResult">
                    <?php if (isset($stock_analysis) && $stock_analysis['status'] === 'success'): ?>
                        <div class="analysis-result mt-4">
                            <input type="hidden" id="currentTickerSymbol" value="<?php echo htmlspecialchars($stock_analysis['ticker']); ?>">
                            
                            <!-- Stock Header -->
                            <div class="stock-header">
                                <div class="stock-logo">
                                    <?php echo substr(htmlspecialchars($stock_analysis['ticker']), 0, 1); ?>
                                </div>
                                <div class="stock-info">
                                    <h3><?php echo htmlspecialchars($stock_analysis['ticker']); ?></h3>
                                    <p><?php echo htmlspecialchars($stock_analysis['company_name']); ?></p>
                                </div>
                            </div>
                            
                            <!-- Price and Chart Row -->
                            <div class="row">
                                <div class="col-md-4">
                                    <!-- Current Price -->
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-2">Current Price</h6>
                                        <div class="stock-price" id="currentStockPrice" data-price="<?php echo $stock_analysis['current_price']; ?>">
                                            $<?php echo number_format($stock_analysis['current_price'], 2); ?>
                                        </div>
                                        
                                        <?php
                                        $change_class = $stock_analysis['price_change'] >= 0 ? 'positive' : 'negative';
                                        $change_icon = $stock_analysis['price_change'] >= 0 ? 'caret-up' : 'caret-down';
                                        ?>
                                        
                                        <div class="price-change <?php echo $change_class; ?>">
                                            <i class="fas fa-<?php echo $change_icon; ?>"></i>
                                            $<span id="priceChange"><?php echo number_format($stock_analysis['price_change'], 2); ?></span>
                                            (<span id="priceChangePercent"><?php echo number_format($stock_analysis['price_change_percent'], 2); ?>%</span>)
                                        </div>
                                        <div class="price-updated-time small text-muted mt-1">
                                            Auto-updates every minute
                                        </div>
                                    </div>
                                    
                                    <!-- Technical Indicators -->
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-3">Technical Indicators</h6>
                                        <ul class="indicator-list">
                                            <li class="indicator-item">
                                                <span class="indicator-label">Short MA (20-day)</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['short_ma'], 2); ?></span>
                                            </li>
                                            <li class="indicator-item">
                                                <span class="indicator-label">Long MA (50-day)</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['long_ma'], 2); ?></span>
                                            </li>
                                            <li class="indicator-item">
                                                <span class="indicator-label">RSI (14-day)</span>
                                                <span class="indicator-value"><?php echo number_format($stock_analysis['rsi'], 2); ?></span>
                                            </li>
                                            <?php if (isset($stock_analysis['bollinger_upper'])): ?>
                                            <li class="indicator-item">
                                                <span class="indicator-label">Bollinger Upper</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['bollinger_upper'], 2); ?></span>
                                            </li>
                                            <li class="indicator-item">
                                                <span class="indicator-label">Bollinger Lower</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['bollinger_lower'], 2); ?></span>
                                            </li>
                                            <?php else: ?>
                                            <li class="indicator-item">
                                                <span class="indicator-label">Support Level</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['support'], 2); ?></span>
                                            </li>
                                            <li class="indicator-item">
                                                <span class="indicator-label">Resistance Level</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['resistance'], 2); ?></span>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    
                                    <!-- Recommendation -->
                                    <?php
                                    $rec_class = '';
                                    $rec_icon = '';
                                    
                                    switch ($stock_analysis['recommendation']) {
                                        case 'buy':
                                            $rec_class = 'buy-card';
                                            $rec_icon = 'arrow-up';
                                            break;
                                        case 'sell':
                                            $rec_class = 'sell-card';
                                            $rec_icon = 'arrow-down';
                                            break;
                                        default:
                                            $rec_class = 'hold-card';
                                            $rec_icon = 'minus';
                                    }
                                    ?>
                                    
                                    <div class="recommendation-card <?php echo $rec_class; ?>">
                                        <h4>
                                            <i class="fas fa-<?php echo $rec_icon; ?>"></i>
                                            <?php echo ucfirst($stock_analysis['recommendation']); ?> Recommendation
                                        </h4>
                                        
                                        <?php if (isset($stock_analysis['recommendation_reasons']) && !empty($stock_analysis['recommendation_reasons'])): ?>
                                        <p class="mt-2 mb-2">
                                            <strong>Analysis:</strong>
                                        </p>
                                        <ul class="mb-2">
                                            <?php foreach ($stock_analysis['recommendation_reasons'] as $reason): ?>
                                                <li><?php echo htmlspecialchars($reason); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                        
                                        <hr>
                                        
                                        <?php if (!empty($stock_analysis['buy_points'])): ?>
                                            <p><strong>Potential Buy Points:</strong></p>
                                            <ul class="price-points">
                                                <?php foreach ($stock_analysis['buy_points'] as $point): ?>
                                                    <li><span class="price-point-value">$<?php echo number_format($point['price'], 2); ?></span> - <?php echo $point['reason']; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($stock_analysis['sell_points'])): ?>
                                            <p><strong>Potential Sell Points:</strong></p>
                                            <ul class="price-points">
                                                <?php foreach ($stock_analysis['sell_points'] as $point): ?>
                                                    <li><span class="price-point-value">$<?php echo number_format($point['price'], 2); ?></span> - <?php echo $point['reason']; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-sm btn-light add-to-watchlist-from-analysis" 
                                                    data-ticker="<?php echo htmlspecialchars($stock_analysis['ticker']); ?>" 
                                                    data-price="<?php echo $stock_analysis['current_price']; ?>"
                                                    data-company="<?php echo htmlspecialchars($stock_analysis['company_name']); ?>">
                                                <i class="fas fa-plus"></i> Add to Watchlist
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <!-- Stock Price Chart -->
                                    <div class="stock-chart-container">
                                        <div class="chart-header">
                                            <h6 class="chart-title">Price History</h6>
                                            <div class="chart-period">
                                                <button type="button" class="chart-period-btn active" data-period="1m">1M</button>
                                                <button type="button" class="chart-period-btn" data-period="3m">3M</button>
                                                <button type="button" class="chart-period-btn" data-period="1y">1Y</button>
                                            </div>
                                        </div>
                                        <canvas id="stockPriceChart" class="stock-chart"></canvas>
                                    </div>
                                    
                                    <!-- Volume Chart -->
                                    <?php if (isset($stockPriceData['volumes']) && !empty($stockPriceData['volumes'])): ?>
                                    <div class="stock-chart-container mt-3" style="height: 200px;">
                                        <div class="chart-header">
                                            <h6 class="chart-title">Trading Volume</h6>
                                        </div>
                                        <canvas id="stockVolumeChart" class="stock-chart"></canvas>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Watchlist Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="stock-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="fas fa-star me-2"></i>Stock Watchlist</h5>
                <div class="watchlist-search">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control search-input" placeholder="Search watchlist..." id="watchlistSearch" data-target="watchlistTable">
                </div>
            </div>
            <div class="card-body watchlist-container">
                <?php if (isset($watchlist) && $watchlist && $watchlist->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table watchlist-table" id="watchlistTable" width="100%" cellspacing="0">
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
                                    <tr data-notes="<?php echo htmlspecialchars($stock['notes'] ?? ''); ?>">
                                        <td data-symbol="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>">
                                            <span class="stock-symbol"><?php echo htmlspecialchars($stock['ticker_symbol']); ?></span>
                                        </td>
                                        <td>
                                            <span class="stock-company"><?php echo htmlspecialchars($stock['company_name']); ?></span>
                                        </td>
                                        <td data-price="<?php echo $stock['current_price']; ?>">
                                            $<?php echo number_format($stock['current_price'], 2); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($stock['target_buy_price'])): ?>
                                                <span class="target-price">$<?php echo number_format($stock['target_buy_price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($stock['target_sell_price'])): ?>
                                                <span class="target-price">$<?php echo number_format($stock['target_sell_price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-info analyze-from-watchlist" data-ticker="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>" data-bs-toggle="tooltip" title="Analyze Stock">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning edit-watchlist" data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger remove-from-watchlist" data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" data-bs-toggle="tooltip" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-watchlist">
                        <div class="empty-watchlist-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Your watchlist is empty</h4>
                        <p class="text-muted">Track stocks you're interested in by adding them to your watchlist.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                            <i class="fas fa-plus me-1"></i> Add Your First Stock
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Market Insights Section -->
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card insights-card technical">
            <div class="insights-header">
                <h5><i class="fas fa-chart-line me-2"></i>Technical Analysis</h5>
            </div>
            <div class="insights-body">
                <p>Technical analysis uses charts and historical data patterns to forecast future stock price movements.</p>
                <p><strong>Key Indicators:</strong></p>
                <ul class="insights-list">
                    <li>Moving Averages (MA) - Short vs Long term trends</li>
                    <li>Relative Strength Index (RSI) - Overbought/Oversold conditions</li>
                    <li>Bollinger Bands - Volatility and price levels</li>
                    <li>MACD - Momentum and trend direction</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card insights-card fundamental">
            <div class="insights-header">
                <h5><i class="fas fa-balance-scale me-2"></i>Fundamental Analysis</h5>
            </div>
            <div class="insights-body">
                <p>Fundamental analysis evaluates a company's intrinsic value through financial statements and economic factors.</p>
                <p><strong>Key Metrics:</strong></p>
                <ul class="insights-list">
                    <li>P/E Ratio - Price relative to earnings</li>
                    <li>Debt-to-Equity Ratio - Financial leverage</li>
                    <li>Revenue Growth - Sales trend over time</li>
                    <li>Profit Margins - Efficiency of operations</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card insights-card strategy">
            <div class="insights-header">
                <h5><i class="fas fa-chess me-2"></i>Investment Strategies</h5>
            </div>
            <div class="insights-body">
                <p>Different approaches to stock investing based on your goals and risk tolerance.</p>
                <p><strong>Common Strategies:</strong></p>
                <ul class="insights-list">
                    <li>Value Investing - Finding undervalued stocks</li>
                    <li>Growth Investing - Focus on expansion potential</li>
                    <li>Dividend Investing - Income generation</li>
                    <li>Dollar-Cost Averaging - Regular investments</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add to Watchlist Modal -->
<div class="modal fade" id="addToWatchlistModal" tabindex="-1" aria-labelledby="addToWatchlistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addToWatchlistModalLabel"><i class="fas fa-plus-circle me-2"></i>Add Stock to Watchlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/stocks" method="post" class="modal-form needs-validation" novalidate>
                <input type="hidden" name="action" value="add_to_watchlist">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ticker_symbol_watchlist" class="form-label">Ticker Symbol</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ticker_symbol_watchlist" name="ticker_symbol" required 
                                   pattern="[A-Za-z0-9.]{1,10}" maxlength="10">
                            <div class="invalid-feedback">
                                Please enter a valid ticker symbol (1-10 characters).
                            </div>
                        </div>
                        <div class="form-text">Enter the stock's ticker symbol (e.g., AAPL for Apple Inc.)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required maxlength="100">
                        <div class="invalid-feedback">
                            Please enter the company name.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_price_watchlist" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="current_price_watchlist" name="current_price" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">
                                Please enter a valid price (greater than 0).
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="target_buy_price" class="form-label">Target Buy Price (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="target_buy_price" name="target_buy_price" step="0.01" min="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="target_sell_price" class="form-label">Target Sell Price (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="target_sell_price" name="target_sell_price" step="0.01" min="0.01">
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add to Watchlist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove from Watchlist Modal -->
<div class="modal fade" id="removeFromWatchlistModal" tabindex="-1" aria-labelledby="removeFromWatchlistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeFromWatchlistModalLabel"><i class="fas fa-trash me-2"></i>Remove from Watchlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <p>Are you sure you want to remove this stock from your watchlist?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post">
                    <input type="hidden" name="action" value="remove_from_watchlist">
                    <input type="hidden" name="watchlist_id" id="remove_watchlist_id">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Watchlist Item Modal -->
<div class="modal fade" id="editWatchlistModal" tabindex="-1" aria-labelledby="editWatchlistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWatchlistModalLabel"><i class="fas fa-edit me-2"></i>Edit Watchlist Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/stocks" method="post" class="modal-form needs-validation" novalidate>
                <input type="hidden" name="action" value="update_watchlist_item">
                <input type="hidden" name="watchlist_id" id="edit_watchlist_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_ticker_symbol" class="form-label">Ticker Symbol</label>
                        <input type="text" class="form-control" id="edit_ticker_symbol" name="ticker_symbol" required 
                               pattern="[A-Za-z0-9.]{1,10}" maxlength="10" readonly>
                        <div class="invalid-feedback">
                            Please enter a valid ticker symbol (1-10 characters).
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="edit_company_name" name="company_name" required maxlength="100">
                        <div class="invalid-feedback">
                            Please enter the company name.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_current_price" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_current_price" name="current_price" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">
                                Please enter a valid price (greater than 0).
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_target_buy_price" class="form-label">Target Buy Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_target_buy_price" name="target_buy_price" step="0.01" min="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_target_sell_price" class="form-label">Target Sell Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_target_sell_price" name="target_sell_price" step="0.01" min="0.01">
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Add JavaScript variables for the chart
if (isset($stock_analysis) && $stock_analysis['status'] === 'success') {
    $page_scripts = "
    // Stock price data for chart
    const stockPriceData = " . json_encode($stockPriceData) . ";
    
    // Base path for API calls
    const BASE_PATH = '" . BASE_PATH . "';
    
    // Show success notification
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('Stock analysis completed successfully', 'success');
    });
    ";
} else if (isset($error)) {
    $page_scripts = "
    // Base path for API calls
    const BASE_PATH = '" . BASE_PATH . "';
    
    // Stock price data not available
    const stockPriceData = null;
    
    // Show error notification
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('" . addslashes($error) . "', 'danger');
    });
    ";
} else {
    $page_scripts = "
    // Base path for API calls
    const BASE_PATH = '" . BASE_PATH . "';
    
    // Stock price data not available
    const stockPriceData = null;
    ";
}

// Add custom CSS for the stock analysis page
$page_head_scripts = "
<style>
    .updating {
        position: relative;
    }
    
    .updating::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    
    .updating::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid #4361ee;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
        z-index: 3;
    }
    
    @keyframes spin {
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }
    
    .price-indicator {
        display: inline-block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
";

// Include footer
require_once 'includes/footer.php';
?>