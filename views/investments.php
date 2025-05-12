<?php
// Set page title and current page for menu highlighting
$page_title = __('investment_portfolio') . ' - ' . __('app_name');
$current_page = 'investments';

// Additional CSS and JS with cache busting
$additional_css = ['/assets/css/investments-modern.css?v=' . time()];
$additional_js = ['/assets/js/investments-modern.js?v=' . time()];

// Include header
require_once 'includes/header.php';

// Calculate percentages for portfolio metrics
$total_invested = $investment_summary['total_invested'] ?? 0;
$current_value = $investment_summary['current_value'] ?? 0;
$total_gain_loss = $investment_summary['total_gain_loss'] ?? 0;
$percent_gain_loss = $investment_summary['percent_gain_loss'] ?? 0;
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
            <button type="button" class="btn-add-investment" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                <i class="fas fa-plus-circle"></i>
                <span><?php echo __('add_investment'); ?></span>
            </button>
        </div>
    </div>

    <!-- Portfolio Summary Section -->
    <div class="portfolio-summary-section">
        <div class="summary-grid">
            <!-- Performance Summary Card -->
            <div class="summary-card performance-card">
                <div class="card-body">
                    <div class="performance-metrics">
                        <div class="performance-circle <?php echo $total_gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                            <h2 class="performance-value">
                                <i class="fas fa-<?php echo $total_gain_loss >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                <?php echo number_format(abs($percent_gain_loss), 2); ?>%
                            </h2>
                        </div>
                        <p class="performance-label"><?php echo __('total_return'); ?></p>
                        
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <p class="metric-label"><?php echo __('total_invested'); ?></p>
                                <h3 class="metric-value">$<?php echo number_format($total_invested, 2); ?></h3>
                            </div>
                            <div class="metric-item">
                                <p class="metric-label"><?php echo __('current_value'); ?></p>
                                <h3 class="metric-value">$<?php echo number_format($current_value, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- By Type Chart -->
            <div class="summary-card allocation-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_by_type'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="allocationChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- By Risk Chart -->
            <div class="summary-card allocation-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="card-title"><?php echo __('portfolio_by_risk'); ?></h3>
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
                                    <h4 class="performer-name"><?php echo htmlspecialchars($investment['name']); ?></h4>
                                    <p class="performer-type"><?php echo htmlspecialchars($investment['type']); ?></p>
                                </div>
                                <div class="performer-metrics">
                                    <div class="performer-return <?php echo $investment['percent_gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                        <?php echo number_format(abs($investment['percent_gain_loss']), 2); ?>%
                                    </div>
                                    <div class="performer-value">$<?php echo number_format($investment['current'], 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
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
                                    <h4 class="performer-name"><?php echo htmlspecialchars($investment['name']); ?></h4>
                                    <p class="performer-type"><?php echo htmlspecialchars($investment['type']); ?></p>
                                </div>
                                <div class="performer-metrics">
                                    <div class="performer-return <?php echo $investment['percent_gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                        <?php echo number_format(abs($investment['percent_gain_loss']), 2); ?>%
                                    </div>
                                    <div class="performer-value">$<?php echo number_format($investment['current'], 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
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
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="investmentSearch" placeholder="<?php echo __('search_investments'); ?>">
                    </div>
                </div>
            </div>
            <div class="table-body">
                <div class="table-responsive">
                    <table class="investments-table">
                        <thead>
                            <tr>
                                <th><?php echo __('investment'); ?></th>
                                <th><?php echo __('type_risk'); ?></th>
                                <th><?php echo __('purchase_date'); ?></th>
                                <th><?php echo __('purchase_price'); ?></th>
                                <th><?php echo __('quantity'); ?></th>
                                <th><?php echo __('current_price'); ?></th>
                                <th><?php echo __('current_value'); ?></th>
                                <th><?php echo __('gain_loss'); ?></th>
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
                                    <tr>
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
                                        <td><?php echo date('M j, Y', strtotime($investment['purchase_date'])); ?></td>
                                        <td class="amount-cell">$<?php echo number_format($investment['purchase_price'], 2); ?></td>
                                        <td><?php echo number_format($investment['quantity'], 6); ?></td>
                                        <td class="amount-cell">$<?php echo number_format($investment['current_price'], 2); ?></td>
                                        <td class="amount-cell">$<?php echo number_format($current_val, 2); ?></td>
                                        <td class="gain-loss-cell">
                                            <div class="gain-loss-amount <?php echo $gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                                <i class="fas fa-<?php echo $gain_loss >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                                $<?php echo number_format(abs($gain_loss), 2); ?>
                                            </div>
                                            <div class="gain-loss-percent"><?php echo number_format($percent_gl, 2); ?>%</div>
                                        </td>
                                        <td class="actions-cell">
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
                                    <?php while ($type = $investment_types->fetch_assoc()): ?>
                                        <option value="<?php echo $type['type_id']; ?>">
                                            <?php echo htmlspecialchars($type['name']); ?> (<?php echo ucfirst($type['risk_level']); ?> Risk)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="name"><?php echo __('investment_name'); ?></label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="ticker_symbol"><?php echo __('ticker_symbol'); ?></label>
                            <input type="text" id="ticker_symbol" name="ticker_symbol" class="form-control" placeholder="e.g., AAPL">
                        </div>
                        
                        <div class="form-field">
                            <label for="purchase_date"><?php echo __('purchase_date'); ?></label>
                            <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="purchase_price"><?php echo __('purchase_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="purchase_price" name="purchase_price" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="quantity"><?php echo __('quantity'); ?></label>
                            <input type="number" id="quantity" name="quantity" class="form-control" step="0.000001" min="0.000001" required>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="current_price"><?php echo __('current_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol">$</span>
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
                        <div class="calculator-grid">
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('initial_investment'); ?></p>
                                <h4 class="calculator-value" id="initial_investment">$0.00</h4>
                            </div>
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('current_value'); ?></p>
                                <h4 class="calculator-value" id="current_value">$0.00</h4>
                            </div>
                        </div>
                        <div id="gain_loss_container" class="calculator-gain-loss" style="display: none;">
                            <p class="calculator-label"><?php echo __('gain_loss'); ?></p>
                            <h4 class="calculator-value">
                                <span id="gain_loss">$0.00</span>
                                <span id="gain_loss_percent"></span>
                            </h4>
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
                                    <option value="<?php echo $type['type_id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?> (<?php echo ucfirst($type['risk_level']); ?> Risk)
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_name"><?php echo __('investment_name'); ?></label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_ticker_symbol"><?php echo __('ticker_symbol'); ?></label>
                            <input type="text" id="edit_ticker_symbol" name="ticker_symbol">
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_purchase_date"><?php echo __('purchase_date'); ?></label>
                            <input type="date" id="edit_purchase_date" name="purchase_date" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_purchase_price"><?php echo __('purchase_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_purchase_price" name="purchase_price" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_quantity"><?php echo __('quantity'); ?></label>
                            <input type="number" id="edit_quantity" name="quantity" step="0.000001" min="0.000001" required>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_current_price"><?php echo __('current_price'); ?></label>
                            <div class="currency-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_current_price" name="current_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_notes"><?php echo __('notes'); ?></label>
                            <textarea id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div id="edit_investment_calculator" class="investment-calculator" style="display: none;">
                        <div class="calculator-grid">
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('initial_investment'); ?></p>
                                <h4 class="calculator-value" id="edit_initial_investment">$0.00</h4>
                            </div>
                            <div class="calculator-item">
                                <p class="calculator-label"><?php echo __('current_value'); ?></p>
                                <h4 class="calculator-value" id="edit_current_value">$0.00</h4>
                            </div>
                        </div>
                        <div id="edit_gain_loss_container" class="calculator-gain-loss">
                            <p class="calculator-label"><?php echo __('gain_loss'); ?></p>
                            <h4 class="calculator-value">
                                <span id="edit_gain_loss">$0.00</span>
                                <span id="edit_gain_loss_percent"></span>
                            </h4>
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
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="investment_id" id="update_investment_id">
                
                <div class="modal-body">
                    <div class="form-field">
                        <label for="update_current_price"><?php echo __('current_price'); ?></label>
                        <div class="currency-input">
                            <span class="currency-symbol">$</span>
                            <input type="number" id="update_current_price" name="current_price" step="0.01" min="0" required>
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

require_once 'includes/footer.php';
?>