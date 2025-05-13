<?php
// Set page title and current page for menu highlighting
$page_title = __('stocks_page_title') . ' - ' . __('app_name');
$current_page = 'stocks';

// Additional CSS and JS
$additional_css = ['/assets/css/stocks-modern.css'];
$additional_js = ['/assets/js/stocks-modern.js'];

// Include currency helper for currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="stocks-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('stocks_page_title'); ?></h1>
                <p class="page-subtitle"><?php echo __('stocks_subtitle'); ?></p>
            </div>
            <button type="button" class="btn-add-watchlist" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                <i class="fas fa-plus-circle"></i>
                <span><?php echo __('add_to_watchlist'); ?></span>
            </button>
        </div>
    </div>

    <!-- Stock Analysis Section -->
    <div class="analysis-section">
        <div class="analysis-card">
            <div class="analysis-header">
                <div class="analysis-title">
                    <i class="fas fa-chart-line"></i>
                    <h3><?php echo __('stock_analysis'); ?></h3>
                </div>
                <?php if (isset($stock_analysis) && isset($stock_analysis['is_demo_data'])): ?>
                <span class="badge bg-primary"><?php echo __('demo_data'); ?></span>
                <?php endif; ?>
            </div>
            <div class="analysis-body">
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="analyzeStockForm" class="search-form">
                    <input type="hidden" name="action" value="analyze_stock">
                    
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="ticker_symbol" name="ticker_symbol" 
                               placeholder="<?php echo __('enter_stock_ticker'); ?>" required>
                        <button class="search-btn" type="submit"><?php echo __('analyze'); ?></button>
                    </div>
                    <div class="form-text"><?php echo __('ticker_symbol_description'); ?></div>
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
                                                <?php echo formatMoney($stock_analysis['current_price']); ?>
                                            </div>
                                            
                                            <?php
                                            $change_class = $stock_analysis['price_change'] >= 0 ? 'positive' : 'negative';
                                            $change_icon = $stock_analysis['price_change'] >= 0 ? 'arrow-up' : 'arrow-down';
                                            ?>
                                            
                                            <div class="price-change <?php echo $change_class; ?>">
                                                <i class="fas fa-<?php echo $change_icon; ?>"></i>
                                                <?php echo formatMoney($stock_analysis['price_change']); ?>
                                                (<span id="priceChangePercent"><?php echo number_format($stock_analysis['price_change_percent'], 2); ?>%</span>)
                                            </div>
                                            <div class="price-update-time"><?php echo __('auto_updates_every_minute'); ?></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Technical Indicators Card -->
                                    <div class="indicators-card">
                                        <h4><?php echo __('technical_indicators'); ?></h4>
                                        <div class="indicator-list">
                                            <div class="indicator-item">
                                                <span class="indicator-label"><?php echo __('short_ma'); ?></span>
                                                <span class="indicator-value"><?php echo formatMoney($stock_analysis['short_ma']); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label"><?php echo __('long_ma'); ?></span>
                                                <span class="indicator-value"><?php echo formatMoney($stock_analysis['long_ma']); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label"><?php echo __('rsi'); ?></span>
                                                <span class="indicator-value"><?php echo number_format($stock_analysis['rsi'], 2); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label"><?php echo __('bollinger_upper'); ?></span>
                                                <span class="indicator-value"><?php echo formatMoney($stock_analysis['bollinger_upper']); ?></span>
                                            </div>
                                            <div class="indicator-item">
                                                <span class="indicator-label"><?php echo __('bollinger_lower'); ?></span>
                                                <span class="indicator-value"><?php echo formatMoney($stock_analysis['bollinger_lower']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recommendation Card -->
                                    <?php
                                    $rec_class = $stock_analysis['recommendation'];
                                    $rec_icon = '';
                                    $rec_text = '';
                                    
                                    switch ($stock_analysis['recommendation']) {
                                        case 'buy':
                                            $rec_icon = 'arrow-up';
                                            $rec_text = __('buy_recommendation');
                                            break;
                                        case 'sell':
                                            $rec_icon = 'arrow-down';
                                            $rec_text = __('sell_recommendation');
                                            break;
                                        default:
                                            $rec_icon = 'minus';
                                            $rec_text = __('hold_recommendation');
                                    }
                                    ?>
                                    
                                    <div class="recommendation-card <?php echo $rec_class; ?>">
                                        <div class="recommendation-header <?php echo $rec_class; ?>">
                                            <i class="fas fa-<?php echo $rec_icon; ?>"></i>
                                            <h4><?php echo $rec_text; ?></h4>
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
                                                <h5><?php echo __('buy_points'); ?></h5>
                                                <ul class="price-points">
                                                    <?php foreach ($stock_analysis['buy_points'] as $point): ?>
                                                        <li class="price-point">
                                                            <span class="price-point-value"><?php echo formatMoney($point['price']); ?></span>
                                                            <span class="price-point-reason"><?php echo $point['reason']; ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($stock_analysis['sell_points'])): ?>
                                                <h5><?php echo __('sell_points'); ?></h5>
                                                <ul class="price-points">
                                                    <?php foreach ($stock_analysis['sell_points'] as $point): ?>
                                                        <li class="price-point">
                                                            <span class="price-point-value"><?php echo formatMoney($point['price']); ?></span>
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
                                            <i class="fas fa-plus"></i> <?php echo __('add_to_watchlist'); ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Charts Section -->
                                <div class="charts-section">
                                    <!-- Price Chart -->
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h4 class="chart-title"><?php echo __('price_history'); ?></h4>
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
                                            <h4 class="chart-title"><?php echo __('trading_volume'); ?></h4>
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
                    <h3><?php echo __('stock_watchlist'); ?></h3>
                </div>
                <div class="watchlist-controls">
                    <div class="watchlist-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="<?php echo __('search_watchlist'); ?>" id="watchlistSearch">
                    </div>
                </div>
            </div>
            <div class="watchlist-body">
                <?php if (isset($watchlist) && $watchlist->num_rows > 0): ?>
                    <table class="watchlist-table" id="watchlistTable">
                        <thead>
                            <tr>
                                <th><?php echo __('symbol'); ?></th>
                                <th><?php echo __('company'); ?></th>
                                <th><?php echo __('current_price_label'); ?></th>
                                <th><?php echo __('target_buy'); ?></th>
                                <th><?php echo __('target_sell'); ?></th>
                                <th><?php echo __('actions'); ?></th>
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
                                        <?php echo formatMoney($stock['current_price']); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($stock['target_buy_price'])): ?>
                                            <span class="target-price"><?php echo formatMoney($stock['target_buy_price']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($stock['target_sell_price'])): ?>
                                            <span class="target-price"><?php echo formatMoney($stock['target_sell_price']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-action analyze" 
                                                    data-ticker="<?php echo htmlspecialchars($stock['ticker_symbol']); ?>" 
                                                    title="<?php echo __('analyze'); ?>">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                            <button type="button" class="btn-action edit" 
                                                    data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" 
                                                    title="<?php echo __('edit'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn-action delete" 
                                                    data-watchlist-id="<?php echo $stock['watchlist_id']; ?>" 
                                                    title="<?php echo __('delete'); ?>">
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
                        <h4><?php echo __('watchlist_empty'); ?></h4>
                        <p><?php echo __('track_stocks_message'); ?></p>
                        <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addToWatchlistModal">
                            <i class="fas fa-plus"></i> <?php echo __('add_your_first_stock'); ?>
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
                        <?php echo __('technical_analysis'); ?>
                    </h4>
                </div>
                <div class="insight-body">
                    <p><?php echo __('technical_analysis_description'); ?></p>
                    <p><strong><?php echo __('key_indicators'); ?></strong></p>
                    <ul class="insight-list">
                        <li><?php echo __('moving_averages'); ?></li>
                        <li><?php echo __('rsi_description'); ?></li>
                        <li><?php echo __('bollinger_bands'); ?></li>
                        <li><?php echo __('macd'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="insight-card fundamental">
                <div class="insight-header">
                    <h4 class="insight-title">
                        <i class="fas fa-balance-scale"></i>
                        <?php echo __('fundamental_analysis'); ?>
                    </h4>
                </div>
                <div class="insight-body">
                    <p><?php echo __('fundamental_analysis_description'); ?></p>
                    <p><strong><?php echo __('key_metrics'); ?></strong></p>
                    <ul class="insight-list">
                        <li><?php echo __('pe_ratio'); ?></li>
                        <li><?php echo __('debt_equity_ratio'); ?></li>
                        <li><?php echo __('revenue_growth'); ?></li>
                        <li><?php echo __('profit_margins'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="insight-card strategy">
                <div class="insight-header">
                    <h4 class="insight-title">
                        <i class="fas fa-chess"></i>
                        <?php echo __('investment_strategies'); ?>
                    </h4>
                </div>
                <div class="insight-body">
                    <p><?php echo __('investment_strategies_description'); ?></p>
                    <p><strong><?php echo __('common_strategies'); ?></strong></p>
                    <ul class="insight-list">
                        <li><?php echo __('value_investing'); ?></li>
                        <li><?php echo __('growth_investing'); ?></li>
                        <li><?php echo __('dividend_investing'); ?></li>
                        <li><?php echo __('dollar_cost_averaging'); ?></li>
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
                <h5 class="modal-title"><?php echo __('add_stock_to_watchlist'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="addWatchlistForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_to_watchlist">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ticker_symbol_watchlist"><?php echo __('ticker_symbol_label'); ?></label>
                            <input type="text" class="form-control" id="ticker_symbol_watchlist" name="ticker_symbol" 
                                   required pattern="[A-Za-z0-9.]{1,10}" maxlength="10">
                            <div class="invalid-feedback"><?php echo __('ticker_symbol_invalid'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="company_name"><?php echo __('company_name'); ?></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   required maxlength="100">
                            <div class="invalid-feedback"><?php echo __('company_name_required'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="current_price_watchlist"><?php echo __('current_price_label'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="current_price_watchlist" name="current_price" 
                                       step="0.01" min="0.01" required>
                            </div>
                            <div class="invalid-feedback"><?php echo __('current_price_invalid'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="target_buy_price"><?php echo __('target_buy_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="target_buy_price" name="target_buy_price" 
                                       step="0.01" min="0.01">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="target_sell_price"><?php echo __('target_sell_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="target_sell_price" name="target_sell_price" 
                                       step="0.01" min="0.01">
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="notes_watchlist"><?php echo __('notes_label'); ?></label>
                            <textarea class="form-control" id="notes_watchlist" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i> <?php echo __('add_to_watchlist'); ?>
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
                <h5 class="modal-title"><?php echo __('edit_watchlist_item'); ?></h5>
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
                            <label for="edit_ticker_symbol"><?php echo __('ticker_symbol_label'); ?></label>
                            <input type="text" class="form-control" id="edit_ticker_symbol" name="ticker_symbol" 
                                   required pattern="[A-Za-z0-9.]{1,10}" maxlength="10" readonly>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_company_name"><?php echo __('company_name'); ?></label>
                            <input type="text" class="form-control" id="edit_company_name" name="company_name" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_current_price"><?php echo __('current_price_label'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="edit_current_price" name="current_price" 
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_target_buy_price"><?php echo __('target_buy_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="edit_target_buy_price" name="target_buy_price" 
                                       step="0.01" min="0.01">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_target_sell_price"><?php echo __('target_sell_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="edit_target_sell_price" name="target_sell_price" 
                                       step="0.01" min="0.01">
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_notes"><?php echo __('notes_label'); ?></label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> <?php echo __('save_changes'); ?>
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
                <h5 class="modal-title"><?php echo __('remove_from_watchlist'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('confirm_remove_watchlist'); ?></p>
                <p class="text-muted"><?php echo __('this_action_cannot_be_undone'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/stocks" method="post" id="deleteWatchlistForm">
                    <input type="hidden" name="action" value="remove_from_watchlist">
                    <input type="hidden" name="watchlist_id" id="remove_watchlist_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i> <?php echo __('remove'); ?>
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
    
    // Add currency symbol meta tag for JavaScript
    echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';
} else {
    echo '<script>const stockPriceData = null;</script>';
    echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';
    
    // Add currency symbol meta tag for JavaScript
    echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';
}

require_once 'includes/footer.php';
?>