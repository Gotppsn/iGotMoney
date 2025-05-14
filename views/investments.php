<?php
// Set page title and current page for menu highlighting
$page_title = __('investment_portfolio') . ' - ' . __('app_name');
$current_page = 'investments';

// Additional CSS and JS with cache busting
$additional_css = ['/assets/css/investments-modern.css?v=' . time()];
$additional_js = ['/assets/js/investments-modern.js?v=' . time()];

// Include currency helper for dynamic currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';

// Calculate percentages for portfolio metrics
$total_invested = $investment_summary['total_invested'] ?? 0;
$current_value = $investment_summary['current_value'] ?? 0;
$total_gain_loss = $investment_summary['total_gain_loss'] ?? 0;
$percent_gain_loss = $investment_summary['percent_gain_loss'] ?? 0;

// Calculate portfolio health score (0-100)
$portfolio_health = 50; // Default neutral health
if ($total_invested > 0) {
    // Based on: diversity, performance, and active investments count
    $risk_diversity = !empty($investment_summary['by_risk']) ? min(100, count($investment_summary['by_risk']) * 20) : 0;
    $type_diversity = !empty($investment_summary['by_type']) ? min(100, count($investment_summary['by_type']) * 15) : 0;
    $performance_score = $percent_gain_loss >= 0 ? min(100, $percent_gain_loss * 5 + 50) : max(0, 50 - abs($percent_gain_loss) * 2);
    
    // Count active investments
    $investments_count = 0;
    if (isset($investments) && $investments && $investments->num_rows > 0) {
        $investments_count = $investments->num_rows;
    }
    $count_score = min(100, $investments_count * 10);
    
    $portfolio_health = ($risk_diversity * 0.3) + ($type_diversity * 0.3) + ($performance_score * 0.3) + ($count_score * 0.1);
    $portfolio_health = min(100, max(0, $portfolio_health));
}

// Determine health status
$health_status = 'neutral';
if ($portfolio_health >= 75) {
    $health_status = 'excellent';
} elseif ($portfolio_health >= 60) {
    $health_status = 'good';
} elseif ($portfolio_health >= 40) {
    $health_status = 'neutral';
} elseif ($portfolio_health >= 25) {
    $health_status = 'warning';
} else {
    $health_status = 'danger';
}

// Get portfolio diversity percentage
$diversity_score = 0;
if (!empty($investment_summary['by_type'])) {
    $type_count = count($investment_summary['by_type']);
    $diversity_score = min(100, $type_count * 20); // 5 or more types = 100%
}

// Get data for latest investment trends 
$trend_period = __('this_month');
$trend_direction = 'up';
$trend_percentage = 0;
if ($total_invested > 0 && $percent_gain_loss != 0) {
    $trend_direction = $percent_gain_loss >= 0 ? 'up' : 'down';
    $trend_percentage = abs($percent_gain_loss);
}
?>

<!-- Main Content Wrapper -->
<div class="investments-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('investment_portfolio'); ?></h1>
                <p class="page-subtitle"><?php echo __('track_manage_investments'); ?></p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn-add-investment" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                    <i class="fas fa-plus-circle"></i>
                    <span><?php echo __('add_investment'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Portfolio Summary Section -->
    <div class="portfolio-summary-section">
        <div class="summary-grid">
            <!-- Performance Summary Card -->
            <div class="summary-card performance-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_performance'); ?></h3>
                    <div class="card-trend <?php echo $trend_direction; ?>">
                        <i class="fas fa-arrow-<?php echo $trend_direction; ?>"></i>
                        <span><?php echo number_format($trend_percentage, 1); ?>% <?php echo $trend_period; ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="performance-metrics">
                        <div class="performance-circle <?php echo $total_gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                            <h2 class="performance-value">
                                <i class="fas fa-<?php echo $total_gain_loss >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                <?php echo number_format(abs($percent_gain_loss), 1); ?>%
                            </h2>
                        </div>
                        <p class="performance-label"><?php echo __('total_return'); ?></p>
                        
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <p class="metric-label"><?php echo __('total_invested'); ?></p>
                                <h3 class="metric-value"><?php echo formatMoney($total_invested); ?></h3>
                            </div>
                            <div class="metric-item">
                                <p class="metric-label"><?php echo __('current_value'); ?></p>
                                <h3 class="metric-value"><?php echo formatMoney($current_value); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Portfolio Health Card -->
            <div class="summary-card health-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_health'); ?></h3>
                    <div class="health-badge <?php echo $health_status; ?>">
                        <?php 
                        $health_label = 'Neutral';
                        if ($health_status == 'excellent') $health_label = __('excellent');
                        else if ($health_status == 'good') $health_label = __('good');
                        else if ($health_status == 'warning') $health_label = __('needs_attention');
                        else if ($health_status == 'danger') $health_label = __('critical');
                        echo $health_label; 
                        ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="health-metrics">
                        <div class="health-indicator">
                            <div class="health-gauge <?php echo $health_status; ?>">
                                <div class="health-gauge-value" style="width: <?php echo $portfolio_health; ?>%;"></div>
                            </div>
                            <div class="health-value"><?php echo round($portfolio_health); ?>/100</div>
                        </div>
                        
                        <div class="health-factors">
                            <div class="health-factor">
                                <div class="factor-icon"><i class="fas fa-percentage"></i></div>
                                <div class="factor-details">
                                    <h4 class="factor-name"><?php echo __('performance'); ?></h4>
                                    <div class="factor-value <?php echo $total_gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo number_format(abs($percent_gain_loss), 1); ?>%
                                        <i class="fas fa-arrow-<?php echo $total_gain_loss >= 0 ? 'up' : 'down'; ?>"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="health-factor">
                                <div class="factor-icon"><i class="fas fa-project-diagram"></i></div>
                                <div class="factor-details">
                                    <h4 class="factor-name"><?php echo __('diversity'); ?></h4>
                                    <div class="factor-value <?php echo $diversity_score >= 60 ? 'positive' : ($diversity_score >= 40 ? 'neutral' : 'negative'); ?>">
                                        <?php echo $diversity_score; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick ROI Calculator Card -->
            <div class="summary-card calculator-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('roi_calculator'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="calculator-form">
                        <div class="form-group">
                            <label for="calc-investment"><?php echo __('investment_amount'); ?></label>
                            <div class="input-with-icon">
                                <i class="fas fa-coins"></i>
                                <input type="number" id="calc-investment" class="form-control" value="1000" min="1" step="100">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="calc-return"><?php echo __('annual_return'); ?> (%)</label>
                            <div class="input-with-icon">
                                <i class="fas fa-chart-line"></i>
                                <input type="number" id="calc-return" class="form-control" value="7" min="0" max="100" step="0.5">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="calc-years"><?php echo __('time_period'); ?> (<?php echo __('years'); ?>)</label>
                            <div class="input-with-icon">
                                <i class="fas fa-clock"></i>
                                <input type="number" id="calc-years" class="form-control" value="5" min="1" max="50" step="1">
                            </div>
                        </div>
                    </div>
                    <div class="calculator-result">
                        <div class="result-label"><?php echo __('future_value'); ?></div>
                        <div class="result-value" id="calc-result"><?php echo formatMoney(1000 * pow(1 + 7/100, 5)); ?></div>
                        <div class="result-details" id="calc-profit">
                            <span class="positive">+<?php echo formatMoney(1000 * pow(1 + 7/100, 5) - 1000); ?></span> <?php echo __('total_profit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Portfolio Analysis Section -->
    <div class="portfolio-analysis-section">
        <div class="analysis-grid">
            <!-- By Type Chart -->
            <div class="analysis-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_by_type'); ?></h3>
                    <div class="card-actions">
                        <button type="button" class="btn-help" data-bs-toggle="tooltip" title="<?php echo __('type_distribution_help'); ?>">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="allocationChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- By Risk Chart -->
            <div class="analysis-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_by_risk'); ?></h3>
                    <div class="card-actions">
                        <button type="button" class="btn-help" data-bs-toggle="tooltip" title="<?php echo __('risk_distribution_help'); ?>">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="riskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performers Section -->
    <div class="performers-section">
        <div class="performers-grid">
            <!-- Top Performers -->
            <div class="performers-card top-performers">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('top_performers'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($investment_summary['top_performers'])): ?>
                        <?php foreach ($investment_summary['top_performers'] as $index => $investment): ?>
                            <div class="performer-item">
                                <div class="performer-rank"><?php echo $index + 1; ?></div>
                                <div class="performer-info">
                                    <h4 class="performer-name">
                                        <?php echo htmlspecialchars($investment['name']); ?>
                                        <?php if (!empty($investment['ticker'])): ?>
                                            <span class="performer-ticker"><?php echo htmlspecialchars($investment['ticker']); ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="performer-type"><?php echo htmlspecialchars($investment['type']); ?></p>
                                </div>
                                <div class="performer-metrics">
                                    <div class="performer-return <?php echo $investment['percent_gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                        <?php echo number_format(abs($investment['percent_gain_loss']), 2); ?>%
                                    </div>
                                    <div class="performer-value"><?php echo formatMoney($investment['current']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <p><?php echo __('no_investment_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Worst Performers -->
            <div class="performers-card worst-performers">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('worst_performers'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($investment_summary['worst_performers'])): ?>
                        <?php foreach ($investment_summary['worst_performers'] as $index => $investment): ?>
                            <div class="performer-item">
                                <div class="performer-rank"><?php echo count($investment_summary['worst_performers']) - $index; ?></div>
                                <div class="performer-info">
                                    <h4 class="performer-name">
                                        <?php echo htmlspecialchars($investment['name']); ?>
                                        <?php if (!empty($investment['ticker'])): ?>
                                            <span class="performer-ticker"><?php echo htmlspecialchars($investment['ticker']); ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="performer-type"><?php echo htmlspecialchars($investment['type']); ?></p>
                                </div>
                                <div class="performer-metrics">
                                    <div class="performer-return <?php echo $investment['percent_gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                        <?php echo number_format(abs($investment['percent_gain_loss']), 2); ?>%
                                    </div>
                                    <div class="performer-value"><?php echo formatMoney($investment['current']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <p><?php echo __('no_investment_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Investments Table Section -->
    <div class="investments-table-section">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    <h3><?php echo __('all_investments'); ?></h3>
                </div>
                <div class="table-controls">
                    <div class="view-toggle" id="viewToggle">
                        <button class="view-btn active" data-view="table"><i class="fas fa-table"></i></button>
                        <button class="view-btn" data-view="grid"><i class="fas fa-th-large"></i></button>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="investmentSearch" placeholder="<?php echo __('search_investments'); ?>">
                    </div>
                    <div class="filter-dropdown">
                        <button class="btn-filter" id="filterToggle">
                            <i class="fas fa-filter"></i>
                            <span><?php echo __('filter'); ?></span>
                        </button>
                        <div class="filter-menu" id="filterMenu">
                            <div class="filter-group">
                                <label><?php echo __('investment_type'); ?></label>
                                <select id="typeFilter" class="form-select">
                                    <option value=""><?php echo __('all_types'); ?></option>
                                    <?php 
                                    if ($investment_types && $investment_types->num_rows > 0) {
                                        $investment_types->data_seek(0);
                                        while ($type = $investment_types->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($type['name']); ?>">
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label><?php echo __('performance'); ?></label>
                                <select id="performanceFilter" class="form-select">
                                    <option value=""><?php echo __('all_performance'); ?></option>
                                    <option value="profit"><?php echo __('profitable'); ?></option>
                                    <option value="loss"><?php echo __('loss_making'); ?></option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button id="applyFilters" class="btn-apply"><?php echo __('apply'); ?></button>
                                <button id="resetFilters" class="btn-reset"><?php echo __('reset'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-body">
                <!-- Table View -->
                <div class="table-view" id="tableView">
                    <div class="table-responsive">
                        <table class="investments-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="name"><?php echo __('investment'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="type"><?php echo __('type_risk'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="date"><?php echo __('purchase_date'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="purchase"><?php echo __('purchase_price'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="quantity"><?php echo __('quantity'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="current"><?php echo __('current_price'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="value"><?php echo __('current_value'); ?> <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="gain"><?php echo __('gain_loss'); ?> <i class="fas fa-sort"></i></th>
                                    <th><?php echo __('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($investments) && $investments && $investments->num_rows > 0): ?>
                                    <?php while ($investment = $investments->fetch_assoc()): ?>
                                        <?php
                                        $purchase_value = $investment['purchase_price'] * $investment['quantity'];
                                        $current_val = $investment['current_price'] * $investment['quantity'];
                                        $gain_loss = $current_val - $purchase_value;
                                        $percent_gl = $purchase_value > 0 ? ($gain_loss / $purchase_value) * 100 : 0;
                                        ?>
                                        <tr data-investment-id="<?php echo $investment['investment_id']; ?>"
                                            data-type="<?php echo htmlspecialchars($investment['type_name']); ?>"
                                            data-performance="<?php echo $gain_loss >= 0 ? 'profit' : 'loss'; ?>">
                                            <td>
                                                <div class="investment-name-cell">
                                                    <div class="investment-icon">
                                                        <i class="fas fa-<?php echo !empty($investment['ticker_symbol']) ? 'chart-line' : 'landmark'; ?>"></i>
                                                    </div>
                                                    <div class="investment-details">
                                                        <h4 class="investment-name"><?php echo htmlspecialchars($investment['name']); ?></h4>
                                                        <?php if (!empty($investment['ticker_symbol'])): ?>
                                                            <p class="investment-ticker"><?php echo htmlspecialchars($investment['ticker_symbol']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="risk-badge <?php echo strtolower(str_replace(' ', '-', $investment['risk_level'])); ?>">
                                                    <?php echo htmlspecialchars($investment['type_name']); ?>
                                                </span>
                                            </td>
                                            <td data-value="<?php echo strtotime($investment['purchase_date']); ?>">
                                                <?php echo date('M j, Y', strtotime($investment['purchase_date'])); ?>
                                            </td>
                                            <td class="amount-cell" data-value="<?php echo $investment['purchase_price']; ?>">
                                                <?php echo formatMoney($investment['purchase_price']); ?>
                                            </td>
                                            <td data-value="<?php echo $investment['quantity']; ?>">
                                                <?php echo number_format($investment['quantity'], 6); ?>
                                            </td>
                                            <td class="amount-cell" data-value="<?php echo $investment['current_price']; ?>">
                                                <?php echo formatMoney($investment['current_price']); ?>
                                            </td>
                                            <td class="amount-cell" data-value="<?php echo $current_val; ?>">
                                                <?php echo formatMoney($current_val); ?>
                                            </td>
                                            <td class="gain-loss-cell" data-value="<?php echo $gain_loss; ?>">
                                                <div class="gain-loss-amount <?php echo $gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                                    <i class="fas fa-<?php echo $gain_loss >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                                    <?php echo formatMoney(abs($gain_loss)); ?>
                                                </div>
                                                <div class="gain-loss-percent"><?php echo number_format($percent_gl, 2); ?>%</div>
                                            </td>
                                            <td class="actions-cell">
                                                <button class="btn-action quick-view" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('quick_view'); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-action update" data-investment-id="<?php echo $investment['investment_id']; ?>" data-current-price="<?php echo $investment['current_price']; ?>" title="<?php echo __('update_price'); ?>">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                <button class="btn-action edit" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('edit'); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-action delete" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('delete'); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Grid View -->
                <div class="grid-view" id="gridView" style="display: none;">
                    <div class="investments-grid">
                        <?php if (isset($investments) && $investments && $investments->num_rows > 0): ?>
                            <?php $investments->data_seek(0); // Reset result set pointer ?>
                            <?php while ($investment = $investments->fetch_assoc()): ?>
                                <?php
                                $purchase_value = $investment['purchase_price'] * $investment['quantity'];
                                $current_val = $investment['current_price'] * $investment['quantity'];
                                $gain_loss = $current_val - $purchase_value;
                                $percent_gl = $purchase_value > 0 ? ($gain_loss / $purchase_value) * 100 : 0;
                                ?>
                                <div class="investment-card" 
                                     data-investment-id="<?php echo $investment['investment_id']; ?>"
                                     data-type="<?php echo htmlspecialchars($investment['type_name']); ?>"
                                     data-performance="<?php echo $gain_loss >= 0 ? 'profit' : 'loss'; ?>">
                                    <div class="card-tag <?php echo $gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                        <i class="fas fa-<?php echo $gain_loss >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                        <?php echo number_format($percent_gl, 1); ?>%
                                    </div>
                                    <div class="card-header">
                                        <div class="investment-icon">
                                            <i class="fas fa-<?php echo !empty($investment['ticker_symbol']) ? 'chart-line' : 'landmark'; ?>"></i>
                                        </div>
                                        <h4 class="investment-name"><?php echo htmlspecialchars($investment['name']); ?></h4>
                                        <?php if (!empty($investment['ticker_symbol'])): ?>
                                            <p class="investment-ticker"><?php echo htmlspecialchars($investment['ticker_symbol']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="investment-type">
                                            <span class="risk-badge <?php echo strtolower(str_replace(' ', '-', $investment['risk_level'])); ?>">
                                                <?php echo htmlspecialchars($investment['type_name']); ?>
                                            </span>
                                        </div>
                                        <div class="investment-details">
                                            <div class="detail-item">
                                                <span class="detail-label"><?php echo __('current_value'); ?></span>
                                                <span class="detail-value"><?php echo formatMoney($current_val); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label"><?php echo __('purchase_date'); ?></span>
                                                <span class="detail-value"><?php echo date('M j, Y', strtotime($investment['purchase_date'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn-action quick-view" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('quick_view'); ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action update" data-investment-id="<?php echo $investment['investment_id']; ?>" data-current-price="<?php echo $investment['current_price']; ?>" title="<?php echo __('update_price'); ?>">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="btn-action edit" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('edit'); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action delete" data-investment-id="<?php echo $investment['investment_id']; ?>" title="<?php echo __('delete'); ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- No data message -->
                <div id="tableNoData" class="empty-state" style="display: <?php echo (isset($investments) && $investments->num_rows > 0) ? 'none' : 'block'; ?>">
                    <div class="empty-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4><?php echo __('no_investments_recorded'); ?></h4>
                    <p><?php echo __('start_tracking_investment'); ?></p>
                    <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_your_first_investment'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Tips Section -->
    <div class="tips-section">
        <div class="tips-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="card-title"><?php echo __('investment_tips'); ?></h3>
            </div>
            <div class="card-body">
                <div class="tips-grid">
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4 class="tip-title"><?php echo __('diversify_portfolio'); ?></h4>
                        <p class="tip-description"><?php echo __('spread_investments'); ?></p>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="tip-title"><?php echo __('long_term_perspective'); ?></h4>
                        <p class="tip-description"><?php echo __('focus_long_term'); ?></p>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4 class="tip-title"><?php echo __('regular_review'); ?></h4>
                        <p class="tip-description"><?php echo __('review_rebalance'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div class="toast-container">
    <!-- Toasts will be dynamically inserted here -->
</div>

<!-- Add Investment Modal -->
<div class="modal fade modern-modal" id="addInvestmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title"><?php echo __('add_new_investment'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="type_id"><?php echo __('investment_type'); ?></label>
                            <select id="type_id" name="type_id" class="form-select" required>
                                <option value=""><?php echo __('select_type'); ?></option>
                                <?php if ($investment_types && $investment_types->num_rows > 0): ?>
                                    <?php $investment_types->data_seek(0); // Reset result set pointer ?>
                                    <?php while ($type = $investment_types->fetch_assoc()): ?>
                                        <option value="<?php echo $type['type_id']; ?>" data-risk="<?php echo ucfirst($type['risk_level']); ?>">
                                            <?php echo htmlspecialchars($type['name']); ?> (<?php echo ucfirst($type['risk_level']); ?> Risk)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback"><?php echo __('please_select_investment_type'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="name"><?php echo __('investment_name'); ?></label>
                            <input type="text" id="name" name="name" class="form-control" required>
                            <div class="invalid-feedback"><?php echo __('please_enter_investment_name'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="ticker_symbol"><?php echo __('ticker_symbol'); ?></label>
                            <input type="text" id="ticker_symbol" name="ticker_symbol" class="form-control" placeholder="e.g., AAPL">
                        </div>
                        
                        <div class="form-field">
                            <label for="purchase_date"><?php echo __('purchase_date'); ?></label>
                            <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback"><?php echo __('please_select_purchase_date'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="purchase_price"><?php echo __('purchase_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="purchase_price" name="purchase_price" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="invalid-feedback"><?php echo __('please_enter_valid_purchase_price'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="quantity"><?php echo __('quantity'); ?></label>
                            <input type="number" id="quantity" name="quantity" class="form-control" step="0.000001" min="0.000001" required>
                            <div class="invalid-feedback"><?php echo __('please_enter_valid_quantity'); ?></div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="current_price"><?php echo __('current_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="current_price" name="current_price" class="form-control" step="0.01" min="0">
                            </div>
                            <small class="form-text text-muted"><?php echo __('leave_blank_purchase_price'); ?></small>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="notes"><?php echo __('notes'); ?></label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div id="investment_calculator" class="investment-calculator" style="display: none;">
                        <h4 class="calculator-title"><?php echo __('investment_overview'); ?></h4>
                        <div class="calculator-grid">
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('initial_investment'); ?></p>
                                <h4 class="calculator-value" id="initial_investment"><?php echo formatMoney(0); ?></h4>
                            </div>
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('current_value'); ?></p>
                                <h4 class="calculator-value" id="current_value"><?php echo formatMoney(0); ?></h4>
                            </div>
                        </div>
                        <div id="gain_loss_container" class="calculator-gain-loss" style="display: none;">
                            <p class="calculator-label"><?php echo __('gain_loss'); ?></p>
                            <h4 class="calculator-value">
                                <span id="gain_loss"><?php echo formatMoney(0); ?></span>
                                <span id="gain_loss_percent"></span>
                            </h4>
                        </div>
                        <div class="risk-indicator">
                            <p class="calculator-label"><?php echo __('risk_level'); ?></p>
                            <div class="risk-level" id="risk_level"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_investment'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Investment Modal -->
<div class="modal fade modern-modal" id="editInvestmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title"><?php echo __('edit_investment'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="investment_id" id="edit_investment_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="edit_type_id"><?php echo __('investment_type'); ?></label>
                            <select id="edit_type_id" name="type_id" class="form-select" required>
                                <option value=""><?php echo __('select_type'); ?></option>
                                <?php 
                                if ($investment_types && $investment_types->num_rows > 0) {
                                    $investment_types->data_seek(0);
                                    while ($type = $investment_types->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $type['type_id']; ?>" data-risk="<?php echo ucfirst($type['risk_level']); ?>">
                                        <?php echo htmlspecialchars($type['name']); ?> (<?php echo ucfirst($type['risk_level']); ?> Risk)
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback"><?php echo __('please_select_investment_type'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_name"><?php echo __('investment_name'); ?></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                            <div class="invalid-feedback"><?php echo __('please_enter_investment_name'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_ticker_symbol"><?php echo __('ticker_symbol'); ?></label>
                            <input type="text" id="edit_ticker_symbol" name="ticker_symbol" class="form-control" placeholder="e.g., AAPL">
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_purchase_date"><?php echo __('purchase_date'); ?></label>
                            <input type="date" id="edit_purchase_date" name="purchase_date" class="form-control" required>
                            <div class="invalid-feedback"><?php echo __('please_select_purchase_date'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_purchase_price"><?php echo __('purchase_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="edit_purchase_price" name="purchase_price" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="invalid-feedback"><?php echo __('please_enter_valid_purchase_price'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_quantity"><?php echo __('quantity'); ?></label>
                            <input type="number" id="edit_quantity" name="quantity" class="form-control" step="0.000001" min="0.000001" required>
                            <div class="invalid-feedback"><?php echo __('please_enter_valid_quantity'); ?></div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_current_price"><?php echo __('current_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="edit_current_price" name="current_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback"><?php echo __('please_enter_valid_current_price'); ?></div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_notes"><?php echo __('notes'); ?></label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div id="edit_investment_calculator" class="investment-calculator" style="display: none;">
                        <h4 class="calculator-title"><?php echo __('investment_overview'); ?></h4>
                        <div class="calculator-grid">
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('initial_investment'); ?></p>
                                <h4 class="calculator-value" id="edit_initial_investment"><?php echo formatMoney(0); ?></h4>
                            </div>
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('current_value'); ?></p>
                                <h4 class="calculator-value" id="edit_current_value"><?php echo formatMoney(0); ?></h4>
                            </div>
                        </div>
                        <div id="edit_gain_loss_container" class="calculator-gain-loss">
                            <p class="calculator-label"><?php echo __('gain_loss'); ?></p>
                            <h4 class="calculator-value">
                                <span id="edit_gain_loss"><?php echo formatMoney(0); ?></span>
                                <span id="edit_gain_loss_percent"></span>
                            </h4>
                        </div>
                        <div class="risk-indicator">
                            <p class="calculator-label"><?php echo __('risk_level'); ?></p>
                            <div class="risk-level" id="edit_risk_level"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        <?php echo __('save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Investment Modal -->
<div class="modal fade modern-modal" id="deleteInvestmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title"><?php echo __('delete_investment'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('confirm_delete_investment'); ?></p>
                <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
                <div class="delete-investment-info">
                    <h4 id="delete-investment-name"></h4>
                    <p id="delete-investment-value"></p>
                </div>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="investment_id" id="delete_investment_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo __('delete'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Price Modal -->
<div class="modal fade modern-modal" id="updatePriceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h5 class="modal-title"><?php echo __('update_price'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="investment_id" id="update_investment_id">
                
                <div class="modal-body">
                    <div class="update-investment-info">
                        <h4 id="update-investment-name"></h4>
                        <p id="update-investment-current"></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="update_current_price"><?php echo __('current_price'); ?></label>
                        <div class="currency-input">
                            <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                            <input type="number" id="update_current_price" name="current_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="invalid-feedback"><?php echo __('please_enter_valid_price'); ?></div>
                    </div>
                    
                    <div class="price-change-indicator" id="price-change-indicator">
                        <div class="price-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span id="price-change-percentage">0%</span>
                        </div>
                        <div class="estimated-value">
                            <span class="label"><?php echo __('new_value'); ?>:</span>
                            <span class="value" id="new-value"><?php echo formatMoney(0); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-sync-alt"></i>
                        <?php echo __('update_price'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade modern-modal" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon view">
                    <i class="fas fa-eye"></i>
                </div>
                <h5 class="modal-title"><?php echo __('investment_details'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="quick-view-header">
                    <div class="investment-icon">
                        <i class="fas fa-landmark" id="qv-icon"></i>
                    </div>
                    <div class="investment-title">
                        <h3 id="qv-name"></h3>
                        <p id="qv-ticker"></p>
                    </div>
                </div>
                
                <div class="quick-view-performance">
                    <div class="performance-indicator">
                        <div class="indicator-value" id="qv-performance-value"></div>
                        <div class="indicator-label"><?php echo __('total_return'); ?></div>
                    </div>
                </div>
                
                <div class="quick-view-details">
                    <div class="detail-section">
                        <h4><?php echo __('basic_info'); ?></h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('investment_type'); ?></div>
                                <div class="detail-value" id="qv-type"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('risk_level'); ?></div>
                                <div class="detail-value" id="qv-risk"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('purchase_date'); ?></div>
                                <div class="detail-value" id="qv-date"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('holding_period'); ?></div>
                                <div class="detail-value" id="qv-period"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><?php echo __('value_information'); ?></h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('purchase_price'); ?></div>
                                <div class="detail-value" id="qv-purchase-price"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('current_price'); ?></div>
                                <div class="detail-value" id="qv-current-price"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('quantity'); ?></div>
                                <div class="detail-value" id="qv-quantity"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><?php echo __('price_change'); ?></div>
                                <div class="detail-value" id="qv-price-change"></div>
                            </div>
                            <div class="detail-item wide">
                                <div class="detail-label"><?php echo __('initial_investment'); ?></div>
                                <div class="detail-value" id="qv-initial"></div>
                            </div>
                            <div class="detail-item wide">
                                <div class="detail-label"><?php echo __('current_value'); ?></div>
                                <div class="detail-value" id="qv-current"></div>
                            </div>
                            <div class="detail-item wide">
                                <div class="detail-label"><?php echo __('gain_loss'); ?></div>
                                <div class="detail-value" id="qv-gain-loss"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section" id="qv-notes-section">
                        <h4><?php echo __('notes'); ?></h4>
                        <div class="detail-notes" id="qv-notes"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action update" id="qv-update-price">
                    <i class="fas fa-sync-alt"></i>
                    <?php echo __('update_price'); ?>
                </button>
                <button type="button" class="btn-action edit" id="qv-edit">
                    <i class="fas fa-edit"></i>
                    <?php echo __('edit'); ?>
                </button>
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                    <?php echo __('close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data
$allocationLabels = [];
$allocationData = [];
$allocationColors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
];

$riskLabels = [];
$riskData = [];
$riskColors = [
    'very low' => '#10b981',
    'low' => '#3b82f6',
    'moderate' => '#f59e0b',
    'high' => '#ef4444',
    'very high' => '#ef4444'
];

// Prepare allocation chart data
if (!empty($investment_summary['by_type'])) {
    foreach ($investment_summary['by_type'] as $type => $data) {
        $allocationLabels[] = $type;
        $allocationData[] = $data['current'];
    }
}

// Prepare risk chart data
if (!empty($investment_summary['by_risk'])) {
    foreach ($investment_summary['by_risk'] as $risk => $data) {
        $riskLabels[] = ucfirst($risk) . ' Risk';
        $riskData[] = $data['current'];
    }
}

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="allocation-labels" content="' . htmlspecialchars(json_encode($allocationLabels)) . '">';
echo '<meta name="allocation-data" content="' . htmlspecialchars(json_encode($allocationData)) . '">';
echo '<meta name="allocation-colors" content="' . htmlspecialchars(json_encode(array_slice($allocationColors, 0, count($allocationData)))) . '">';
echo '<meta name="risk-labels" content="' . htmlspecialchars(json_encode($riskLabels)) . '">';
echo '<meta name="risk-data" content="' . htmlspecialchars(json_encode($riskData)) . '">';

// Map risk colors
$riskColorsMapping = [];
foreach ($riskLabels as $label) {
    $risk = strtolower(str_replace(' Risk', '', $label));
    $riskColorsMapping[] = $riskColors[$risk] ?? '#6366f1';
}
echo '<meta name="risk-colors" content="' . htmlspecialchars(json_encode($riskColorsMapping)) . '">';

// Add currency symbol meta tag for JavaScript
echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';

// Add translations for JavaScript
$jsTranslations = [
    'no_matching_investments' => __('no_matching_investments'),
    'try_adjusting_search' => __('try_adjusting_your_search_term'),
    'update_success' => __('update_success'),
    'delete_success' => __('delete_success'),
    'add_success' => __('add_success'),
    'edit_success' => __('edit_success'),
    'days' => __('days'),
    'months' => __('months'),
    'years' => __('years'),
    'type_distribution_help' => __('type_distribution_help'),
    'risk_distribution_help' => __('risk_distribution_help'),
    'all_types' => __('all_types'),
    'all_performance' => __('all_performance'),
    'profitable' => __('profitable'),
    'loss_making' => __('loss_making'),
];
echo '<meta name="js-translations" content="' . htmlspecialchars(json_encode($jsTranslations)) . '">';

require_once 'includes/footer.php';
?>