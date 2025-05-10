<?php
// Set page title and current page for menu highlighting
$page_title = 'Stock Analysis - iGotMoney';
$current_page = 'stocks';

// Additional CSS and JS
$additional_css = ['/assets/css/stocks-modern.css'];
$additional_js = ['/assets/js/stocks-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="stocks-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Stock Analysis</h1>
                <p class="page-subtitle">Track, analyze, and discover investment opportunities</p>
            </div>
            <button type="button" class="btn-add-watchlist" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                <i class="fas fa-plus-circle"></i>
                <span>Add to Watchlist</span>
            </button>
        </div>
    </div>

    <!-- Stock Analysis Section -->
    <div class="analysis-section">
        <div class="analysis-card">
            <div class="analysis-header">
                <div class="analysis-title">
                    <i class="fas fa-chart-line"></i>
                    <h3>Stock Analysis</h3>
                </div>
                <?php if (isset($stock_analysis) && isset($stock_analysis['is_demo_data'])): ?>
                <span class="badge bg-primary">Demo Data</span>
                <?php endif; ?>
            </div>
            <div class="analysis-body">
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="analyzeStockForm" class="search-form">
                    <input type="hidden" name="action" value="analyze_stock">
                    
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="ticker_symbol" name="ticker_symbol" 
                               placeholder="Enter stock ticker (e.g., AAPL, MSFT, GOOG)" required>
                        <button class="search-btn" type="submit">Analyze</button>
                    </div>
                    <div class="form-text">Enter the ticker symbol of the stock you want to analyze</div>
                </form>
                
                <!-- Analysis Results -->
                <div id="analysisResult">
                    <?php if (isset($stock_analysis) && $stock_analysis['status'] === 'success'): ?>
                        <div class="analysis-result">
                            <input type="hidden" id="currentTickerSymbol" value="<?php echo htmlspecialchars($stock_analysis['ticker']); ?>">
                            
                            <div class="result-grid">
                                <!-- Left Sidebar -->
                                <div class="result-sidebar">
                                    <!-- Stock Info Card -->
                                    <div class="stock-info-card">
                                        <div class="stock-header">
                                            <div class="stock-logo">
                                                <?php echo substr(htmlspecialchars($stock_analysis['ticker']), 0, 1); ?>
                                            </div>
                                            <div class="stock-info">
                                                <h3><?php echo htmlspecialchars($stock_analysis['ticker']); ?></h3>
                                                <p><?php echo htmlspecialchars($stock_analysis['company_name']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="price-section">
                                            <div class="stock-price" id="currentStockPrice" data-price="<?php echo $stock_analysis['current_price']; ?>">
                                                $<?php echo number_format($stock_analysis['current_price'], 2); ?>
                                            </div>
                                            
                                            <?php
                                            $change_class = $stock_analysis['price_change'] >= 0 ? 'positive' : 'negative';
                                            $change_icon = $stock_analysis['price_change'] >= 0 ? 'arrow-up' : 'arrow-down';
                                            ?>
                                            
                                            <div class="price-change <?php echo $change_class; ?>">
                                                <i class="fas fa-<?php echo $change_icon; ?>"></i>
                                                $<span id="priceChange"><?php echo number_format($stock_analysis['price_change'], 2); ?></span>
                                                (<span id="priceChangePercent"><?php echo number_format($stock_analysis['price_change_percent'], 2); ?>%</span>)
                                            </div>
                                            <div class="price-update-time">Auto-updates every minute</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Technical Indicators Card -->
                                    <div class="indicators-card">
                                        <h4>Technical Indicators</h4>
                                        <div class="indicator-list">
                                            <div class="indicator-item">
                                                <span class="indicator-label">Short MA (20-day)</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['short_ma'], 2); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label">Long MA (50-day)</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['long_ma'], 2); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label">RSI (14-day)</span>
                                                <span class="indicator-value"><?php echo number_format($stock_analysis['rsi'], 2); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label">Bollinger Upper</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['bollinger_upper'], 2); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label">Bollinger Lower</span>
                                                <span class="indicator-value">$<?php echo number_format($stock_analysis['bollinger_lower'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recommendation Card -->
                                    <?php
                                    $rec_class = $stock_analysis['recommendation'];
                                    $rec_icon = '';
                                    
                                    switch ($stock_analysis['recommendation']) {
                                        case 'buy':
                                            $rec_icon = 'arrow-up';
                                            break;
                                        case 'sell':
                                            $rec_icon = 'arrow-down';
                                            break;
                                        default:
                                            $rec_icon = 'minus';
                                    }
                                    ?>
                                    
                                    <div class="recommendation-card <?php echo $rec_class; ?>">
                                        <div class="recommendation-header <?php echo $rec_class; ?>">
                                            <i class="fas fa-<?php echo $rec_icon; ?>"></i>
                                            <h4><?php echo ucfirst($stock_analysis['recommendation']); ?> Recommendation</h4>
                                        </div>
                                        
                                        <div class="recommendation-content">
                                            <?php if (!empty($stock_analysis['recommendation_reasons'])): ?>
                                            <ul>
                                                <?php foreach ($stock_analysis['recommendation_reasons'] as $reason): ?>
                                                    <li><?php echo htmlspecialchars($reason); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="recommendation-points">
                                            <?php if (!empty($stock_analysis['buy_points'])): ?>
                                                <h5>Buy Points:</h5>
                                                <ul class="price-points">
                                                    <?php foreach ($stock_analysis['buy_points'] as $point): ?>
                                                        <li class="price-point">
                                                            <span class="price-point-value">$<?php echo number_format($point['price'], 2); ?></span>
                                                            <span class="price-point-reason"><?php echo $point['reason']; ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($stock_analysis['sell_points'])): ?>
                                                <h5>Sell Points:</h5>
                                                <ul class="price-points">
                                                    <?php foreach ($stock_analysis['sell_points'] as $point): ?>
                                                        <li class="price-point">
                                                            <span class="price-point-value">$<?php echo number_format($point['price'], 2); ?></span>
                                                            <span class="price-point-reason"><?php echo $point['reason']; ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button type="button" class="btn-add-from-analysis" 
                                                data-ticker="<?php echo htmlspecialchars($stock_analysis['ticker']); ?>" 
                                                data-price="<?php echo $stock_analysis['current_price']; ?>"
                                                data-company="<?php echo htmlspecialchars($stock_analysis['company_name']); ?>">
                                            <i class="fas fa-plus"></i> Add to Watchlist
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Charts Section -->
                                <div class="charts-section">
                                    <!-- Price Chart -->
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h4 class="chart-title">Price History</h4>
                                            <div class="chart-period">
                                                <button type="button" class="chart-period-btn active" data-period="1m">1M</button>
                                                <button type="button" class="chart-period-btn" data-period="3m">3M</button>
                                                <button type="button" class="chart-period-btn" data-period="1y">1Y</button>
                                            </div>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="stockPriceChart"></canvas>
                                        </div>
                                    </div>
                                    
                                    <!-- Volume Chart -->
                                    <?php if (!empty($stockPriceData['volumes'])): ?>
                                    <div class="chart-card volume-chart">
                                        <div class="chart-header">
                                            <h4 class="chart-title">Trading Volume</h4>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="stockVolumeChart"></canvas>
                                        </div>
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

    <!-- Watchlist Section -->
    <div class="watchlist-section">
        <div class="watchlist-card">
            <div class="watchlist-header">
                <div class="watchlist-title">
                    <i class="fas fa-star"></i>
                    <h3>Stock Watchlist</h3>
                </div>
                <div class="watchlist-controls">
                    <div class="watchlist-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search watchlist..." id="watchlistSearch">
                    </div>
                </div>
            </div>
            <div class="watchlist-body">
                <?php if (isset($watchlist) && $watchlist->num_rows > 0): ?>
                    <table class="watchlist-table" id="watchlistTable">
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
                                    <td>
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
                                            <button type="button" class="btn-action analyze" 
                                                    data-ticker="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>" 
                                                    title="Analyze">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                            <button type="button" class="btn-action edit" 
                                                    data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn-action delete" 
                                                    data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" 
                                                    title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-watchlist">
                        <div class="empty-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Your watchlist is empty</h4>
                        <p>Track stocks you're interested in by adding them to your watchlist.</p>
                        <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                            <i class="fas fa-plus"></i> Add Your First Stock
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Market Insights Section -->
    <div class="insights-section">
        <div class="insights-grid">
            <div class="insight-card technical">
                <div class="insight-header">
                    <h4 class="insight-title">
                        <i class="fas fa-chart-line"></i>
                        Technical Analysis
                    </h4>
                </div>
                <div class="insight-body">
                    <p>Technical analysis uses charts and historical data patterns to forecast future stock price movements.</p>
                    <p><strong>Key Indicators:</strong></p>
                    <ul class="insight-list">
                        <li>Moving Averages (MA) - Short vs Long term trends</li>
                        <li>Relative Strength Index (RSI) - Overbought/Oversold conditions</li>
                        <li>Bollinger Bands - Volatility and price levels</li>
                        <li>MACD - Momentum and trend direction</li>
                    </ul>
                </div>
            </div>
            
            <div class="insight-card fundamental">
                <div class="insight-header">
                    <h4 class="insight-title">
                        <i class="fas fa-balance-scale"></i>
                        Fundamental Analysis
                    </h4>
                </div>
                <div class="insight-body">
                    <p>Fundamental analysis evaluates a company's intrinsic value through financial statements and economic factors.</p>
                    <p><strong>Key Metrics:</strong></p>
                    <ul class="insight-list">
                        <li>P/E Ratio - Price relative to earnings</li>
                        <li>Debt-to-Equity Ratio - Financial leverage</li>
                        <li>Revenue Growth - Sales trend over time</li>
                        <li>Profit Margins - Efficiency of operations</li>
                    </ul>
                </div>
            </div>
            
            <div class="insight-card strategy">
                <div class="insight-header">
                    <h4 class="insight-title">
                        <i class="fas fa-chess"></i>
                        Investment Strategies
                    </h4>
                </div>
                <div class="insight-body">
                    <p>Different approaches to stock investing based on your goals and risk tolerance.</p>
                    <p><strong>Common Strategies:</strong></p>
                    <ul class="insight-list">
                        <li>Value Investing - Finding undervalued stocks</li>
                        <li>Growth Investing - Focus on expansion potential</li>
                        <li>Dividend Investing - Income generation</li>
                        <li>Dollar-Cost Averaging - Regular investments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add to Watchlist Modal -->
<div class="modal fade modern-modal" id="addToWatchlistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title">Add Stock to Watchlist</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="addWatchlistForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_to_watchlist">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ticker_symbol_watchlist">Ticker Symbol</label>
                            <input type="text" class="form-control" id="ticker_symbol_watchlist" name="ticker_symbol" 
                                   required pattern="[A-Za-z0-9.]{1,10}" maxlength="10">
                            <div class="invalid-feedback">Please enter a valid ticker symbol.</div>
                        </div>
                        
                        <div class="form-field">
                            <label for="company_name">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   required maxlength="100">
                            <div class="invalid-feedback">Please enter the company name.</div>
                        </div>
                        
                        <div class="form-field">
                            <label for="current_price_watchlist">Current Price</label>
                            <input type="number" class="form-control" id="current_price_watchlist" name="current_price" 
                                   step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid price.</div>
                        </div>
                        
                        <div class="form-field">
                            <label for="target_buy_price">Target Buy Price</label>
                            <input type="number" class="form-control" id="target_buy_price" name="target_buy_price" 
                                   step="0.01" min="0.01">
                        </div>
                        
                        <div class="form-field">
                            <label for="target_sell_price">Target Sell Price</label>
                            <input type="number" class="form-control" id="target_sell_price" name="target_sell_price" 
                                   step="0.01" min="0.01">
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="notes_watchlist">Notes</label>
                            <textarea class="form-control" id="notes_watchlist" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i> Add to Watchlist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Watchlist Item Modal -->
<div class="modal fade modern-modal" id="editWatchlistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title">Edit Watchlist Item</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="editWatchlistForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="update_watchlist_item">
                <input type="hidden" name="watchlist_id" id="edit_watchlist_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="edit_ticker_symbol">Ticker Symbol</label>
                            <input type="text" class="form-control" id="edit_ticker_symbol" name="ticker_symbol" 
                                   required pattern="[A-Za-z0-9.]{1,10}" maxlength="10" readonly>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_company_name">Company Name</label>
                            <input type="text" class="form-control" id="edit_company_name" name="company_name" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_current_price">Current Price</label>
                            <input type="number" class="form-control" id="edit_current_price" name="current_price" 
                                   step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_target_buy_price">Target Buy Price</label>
                            <input type="number" class="form-control" id="edit_target_buy_price" name="target_buy_price" 
                                   step="0.01" min="0.01">
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_target_sell_price">Target Sell Price</label>
                            <input type="number" class="form-control" id="edit_target_sell_price" name="target_sell_price" 
                                   step="0.01" min="0.01">
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_notes">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade modern-modal" id="removeFromWatchlistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title">Remove from Watchlist</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to remove this stock from your watchlist?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="deleteWatchlistForm">
                    <input type="hidden" name="action" value="remove_from_watchlist">
                    <input type="hidden" name="watchlist_id" id="remove_watchlist_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Chart data
if (isset($stock_analysis) && $stock_analysis['status'] === 'success') {
    $stockPriceData = [
        'dates' => $stock_analysis['historical_dates'] ?? [],
        'prices' => $stock_analysis['historical_prices'] ?? [],
        'volumes' => $stock_analysis['historical_volumes'] ?? []
    ];
    
    echo '<script>const stockPriceData = ' . json_encode($stockPriceData) . ';</script>';
    echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';
} else {
    echo '<script>const stockPriceData = null;</script>';
    echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';
}

require_once 'includes/footer.php';
?>