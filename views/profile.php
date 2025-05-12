<?php
// Set page title and current page for menu highlighting
$page_title = __('financial_profile') . ' - ' . __('app_name');
$current_page = 'profile';

// Additional CSS and JS
$additional_css = ['/assets/css/profile-modern.css'];
$additional_js = ['/assets/js/profile-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="profile-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('financial_profile'); ?></h1>
                <p class="page-subtitle"><?php echo __('your_comprehensive_financial_overview'); ?></p>
            </div>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span><?php echo __('print_report'); ?></span>
            </button>
        </div>
    </div>

    <!-- Financial Stats Section -->
    <div class="financial-stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-info">
                        <div class="stat-label"><?php echo __('monthly_income'); ?></div>
                        <div class="stat-value">$<?php echo number_format($monthly_income, 2); ?></div>
                    </div>
                    <div class="stat-icon income">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <?php if (isset($prev_monthly_income) && $prev_monthly_income > 0): ?>
                    <?php 
                    $income_change = (($monthly_income - $prev_monthly_income) / $prev_monthly_income) * 100;
                    ?>
                    <div class="stat-footer">
                        <div class="stat-trend <?php echo $income_change >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-<?php echo $income_change >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <span><?php echo number_format(abs($income_change), 1); ?>% <?php echo __('from_last_month'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-info">
                        <div class="stat-label"><?php echo __('monthly_expenses'); ?></div>
                        <div class="stat-value">$<?php echo number_format($monthly_expenses, 2); ?></div>
                    </div>
                    <div class="stat-icon expenses">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <?php if (isset($prev_monthly_expenses) && $prev_monthly_expenses > 0): ?>
                    <?php 
                    $expense_change = (($monthly_expenses - $prev_monthly_expenses) / $prev_monthly_expenses) * 100;
                    ?>
                    <div class="stat-footer">
                        <div class="stat-trend <?php echo $expense_change <= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-<?php echo $expense_change <= 0 ? 'arrow-down' : 'arrow-up'; ?>"></i>
                            <span><?php echo number_format(abs($expense_change), 1); ?>% <?php echo __('from_last_month'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-info">
                        <div class="stat-label"><?php echo __('monthly_net'); ?></div>
                        <div class="stat-value <?php echo $monthly_net >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $monthly_net >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($monthly_net), 2); ?>
                        </div>
                    </div>
                    <div class="stat-icon net">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <div class="stat-trend">
                        <span><?php echo $monthly_net >= 0 ? __('surplus') : __('deficit'); ?> <?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-info">
                        <div class="stat-label"><?php echo __('savings_rate'); ?></div>
                        <div class="stat-value <?php echo getScoreColorClass($saving_rate); ?>">
                            <?php echo number_format($saving_rate, 1); ?>%
                        </div>
                    </div>
                    <div class="stat-icon saving">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                </div>
                <div class="stat-footer">
                    <div class="stat-trend">
                        <span><?php echo __('of_monthly_income_saved'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Overview Section -->
    <div class="profile-overview-section">
        <div class="profile-grid">
            <!-- Profile Information Card -->
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-user-circle"></i>
                        <h3><?php echo __('profile_overview'); ?></h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="profile-content">
                        <!-- Personal Information -->
                        <div class="profile-section">
                            <h4 class="section-title"><?php echo __('personal_information'); ?></h4>
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <span class="info-label"><?php echo __('name'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-at"></i>
                                <span class="info-label"><?php echo __('username'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user->username); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <span class="info-label"><?php echo __('email'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user->email); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="info-label"><?php echo __('member_since'); ?>:</span>
                                <span class="info-value"><?php echo date('F j, Y', strtotime($user->created_at)); ?></span>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="profile-section">
                            <h4 class="section-title"><?php echo __('financial_summary'); ?></h4>
                            <div class="info-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span class="info-label"><?php echo __('annual_income'); ?>:</span>
                                <span class="info-value">$<?php echo number_format($yearly_income, 2); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="info-label"><?php echo __('annual_expenses'); ?>:</span>
                                <span class="info-value">$<?php echo number_format($yearly_expenses, 2); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chart-line"></i>
                                <span class="info-label"><?php echo __('annual_net'); ?>:</span>
                                <span class="info-value <?php echo $yearly_net >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $yearly_net >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($yearly_net), 2); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-percentage"></i>
                                <span class="info-label"><?php echo __('annual_saving_rate'); ?>:</span>
                                <span class="info-value <?php echo ($yearly_income > 0 ? (($yearly_net / $yearly_income) * 100) : 0) >= 10 ? 'positive' : ''; ?>">
                                    <?php echo number_format($yearly_income > 0 ? (($yearly_net / $yearly_income) * 100) : 0, 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Health Score Card -->
            <div class="profile-card health-score-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-heartbeat"></i>
                        <h3><?php echo __('financial_health_score'); ?></h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="score-display">
                        <div class="score-circle">
                            <div class="score-value <?php echo strtolower(str_replace(' ', '-', $financial_health['status'])); ?>">
                                <?php echo $financial_health['score']; ?>
                            </div>
                            <div class="score-max">/100</div>
                        </div>
                        <div class="score-status"><?php echo __($financial_health['status']); ?></div>
                    </div>

                    <div class="health-breakdown">
                        <?php foreach ($financial_health['breakdown'] as $key => $item): ?>
                            <div class="breakdown-item">
                                <div class="breakdown-header">
                                    <span class="breakdown-label"><?php echo __($item['label']); ?></span>
                                    <span class="breakdown-score"><?php echo $item['score']; ?>/<?php echo $item['max']; ?></span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill <?php echo getProgressBarClass($item['score'], $item['max']); ?>" 
                                         style="width: 0%"
                                         data-percentage="<?php echo ($item['score'] / $item['max']) * 100; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="advice-box <?php echo strtolower(str_replace(' ', '-', $financial_health['status'])); ?>">
                        <i class="fas fa-lightbulb"></i>
                        <div class="advice-text">
                            <?php echo getFinancialHealthAdvice($financial_health['score']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="profile-overview-section">
        <div class="profile-grid">
            <!-- Income vs Expenses Chart -->
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3><?php echo __('income_vs_expenses'); ?></h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="incomeExpensesChart"></canvas>
                    </div>
                    <div class="chart-footer text-center mt-3">
                        <p class="mb-0">
                            <?php
                            if ($monthly_net > 0) {
                                echo __('saving_percent', ['percent' => number_format($saving_rate, 1)]);
                            } else {
                                echo __('spending_more');
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Top Expense Categories -->
            <?php if ($top_expenses && $top_expenses->num_rows > 0): ?>
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-list-ol"></i>
                        <h3><?php echo __('top_expense_categories'); ?></h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="categories-list">
                        <?php 
                        $top_expenses->data_seek(0);
                        $rank = 1;
                        while ($category = $top_expenses->fetch_assoc()): 
                            $percentage = ($category['total'] / max(0.01, $monthly_expenses)) * 100;
                        ?>
                            <div class="category-item">
                                <div class="category-rank"><?php echo $rank++; ?></div>
                                <div class="category-info">
                                    <h4 class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></h4>
                                    <div class="category-bar">
                                        <div class="category-bar-fill" 
                                             style="width: <?php echo $percentage; ?>%"
                                             data-percentage="<?php echo $percentage; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="category-amount">
                                    <span class="amount">$<?php echo number_format($category['total'], 2); ?></span>
                                    <span class="percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Investments & Budget Section -->
    <div class="bottom-section">
        <!-- Investment Overview -->
        <div class="profile-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-line"></i>
                    <h3><?php echo __('investment_overview'); ?></h3>
                </div>
            </div>
            <div class="card-body">
                <?php if ($total_invested > 0): ?>
                    <div class="investment-stats">
                        <div class="investment-metric">
                            <div class="metric-label"><?php echo __('total_invested'); ?></div>
                            <div class="metric-value">$<?php echo number_format($total_invested, 2); ?></div>
                        </div>
                        <div class="investment-metric">
                            <div class="metric-label"><?php echo __('current_value'); ?></div>
                            <div class="metric-value">$<?php echo number_format($current_investment_value, 2); ?></div>
                        </div>
                        <div class="investment-metric">
                            <div class="metric-label"><?php echo __('total_gain_loss'); ?></div>
                            <div class="metric-value <?php echo $investment_gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $investment_gain_loss >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($investment_gain_loss), 2); ?>
                            </div>
                            <div class="metric-subtext"><?php echo number_format(abs($investment_gain_loss_percent), 2); ?>%</div>
                        </div>
                        <div class="investment-metric">
                            <div class="metric-label"><?php echo __('portfolio_diversity'); ?></div>
                            <div class="metric-value"><?php echo isset($investment_summary['by_type']) ? count($investment_summary['by_type']) : 0; ?></div>
                            <div class="metric-subtext"><?php echo __('asset_types'); ?></div>
                        </div>
                    </div>

                    <?php if (isset($investment_summary['by_type']) && count($investment_summary['by_type']) > 0): ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="investmentPieChart"></canvas>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h4><?php echo __('no_investments_yet'); ?></h4>
                        <p><?php echo __('start_tracking_investments'); ?></p>
                        <a href="<?php echo BASE_PATH; ?>/investments" class="btn-add-data">
                            <i class="fas fa-plus"></i>
                            <?php echo __('add_investment'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Budget Status -->
        <div class="profile-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-bar"></i>
                    <h3><?php echo __('budget_status'); ?></h3>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($budget_status)): ?>
                    <div class="budget-overview">
                        <div class="budget-header">
                            <span class="budget-title"><?php echo __('overall_budget_utilization'); ?></span>
                            <?php 
                            $total_budget = 0;
                            $total_spent = 0;
                            foreach ($budget_status as $budget_item) {
                                $total_budget += $budget_item['budget_amount'];
                                $total_spent += $budget_item['spent'];
                            }
                            $overall_percentage = ($total_budget > 0) ? ($total_spent / $total_budget) * 100 : 0;
                            ?>
                            <span class="budget-percentage"><?php echo number_format($overall_percentage, 1); ?>%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill <?php echo getBudgetProgressClass($overall_percentage); ?>" 
                                 style="width: <?php echo min(100, $overall_percentage); ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="budget-categories">
                        <h4 class="section-title"><?php echo __('budget_categories'); ?></h4>
                        <?php foreach ($budget_status as $budget_item): ?>
                            <div class="budget-category">
                                <div class="category-header">
                                    <span class="category-name"><?php echo htmlspecialchars($budget_item['category_name']); ?></span>
                                    <span class="category-percentage"><?php echo number_format($budget_item['percentage'], 0); ?>%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill <?php echo getBudgetProgressClass($budget_item['percentage']); ?>" 
                                         style="width: <?php echo min(100, $budget_item['percentage']); ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <h4><?php echo __('no_budget_set'); ?></h4>
                        <p><?php echo __('create_budget_track'); ?></p>
                        <a href="<?php echo BASE_PATH; ?>/budget" class="btn-add-data">
                            <i class="fas fa-plus"></i>
                            <?php echo __('create_budget'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions for the profile page
function getScoreColorClass($score) {
    if ($score >= 20) return 'excellent';
    if ($score >= 15) return 'good';
    if ($score >= 10) return 'average';
    if ($score >= 5) return 'below-average';
    return 'poor';
}

function getProgressBarClass($score, $max) {
    $percentage = ($score / $max) * 100;
    if ($percentage >= 80) return 'good';
    if ($percentage >= 50) return 'average';
    return 'poor';
}

function getBudgetProgressClass($percentage) {
    if ($percentage >= 90) return 'progress-bar-bg-danger';
    if ($percentage >= 75) return 'progress-bar-bg-warning';
    return 'progress-bar-bg-success';
}

// Updated to use translated messages based on score
function getFinancialHealthAdvice($score) {
    if ($score >= 85) {
        return __('financial_health_advice_excellent');
    } elseif ($score >= 70) {
        return __('financial_health_advice_good');
    } elseif ($score >= 50) {
        return __('financial_health_advice_average');
    } elseif ($score >= 35) {
        return __('financial_health_advice_below_average');
    } else {
        return __('financial_health_advice_poor');
    }
}

// Add meta tags for chart data
echo '<meta name="monthly-income" content="' . $monthly_income . '">';
echo '<meta name="monthly-expenses" content="' . $monthly_expenses . '">';

if (isset($investment_summary['by_type']) && count($investment_summary['by_type']) > 0) {
    $investment_labels = array_keys($investment_summary['by_type']);
    $investment_data = array_map(function($item) { return $item['current']; }, array_values($investment_summary['by_type']));
    echo '<meta name="investment-labels" content="' . htmlspecialchars(json_encode($investment_labels)) . '">';
    echo '<meta name="investment-data" content="' . htmlspecialchars(json_encode($investment_data)) . '">';
}

// Include footer
require_once 'includes/footer.php';
?>