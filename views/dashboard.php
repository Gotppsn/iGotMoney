<?php 
// Set page title and current page for menu highlighting
$page_title = __('dashboard_title') . ' - ' . __('app_name');
$current_page = 'dashboard';

// Add dashboard-specific CSS
$additional_css = ['/assets/css/dashboard-modern.css'];
$additional_js = ['/assets/js/dashboard-modern.js'];

// Include formatter for currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';
?>

<!-- Main Dashboard Container -->
<div class="dashboard-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('dashboard_title'); ?></h1>
                <p class="page-subtitle"><?php echo __('welcome_back', ['username' => htmlspecialchars($_SESSION['username'])]); ?></p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-action btn-refresh" id="refreshDashboard">
                    <i class="fas fa-sync-alt"></i>
                    <span><?php echo __('refresh'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="summary-cards-section">
        <div class="summary-grid">
            <div class="summary-card income">
                <div class="card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-label"><?php echo __('monthly_income'); ?></h3>
                    <p class="card-value"><?php echo formatMoney($monthly_income); ?></p>
                    <?php 
                    $income_trend = 0;
                    if (isset($monthly_income) && isset($previous_monthly_income)) {
                        $income_trend = $monthly_income - $previous_monthly_income;
                    }
                    if (!isset($previous_monthly_income)) {
                        $income_trend = 150;
                    }
                    ?>
                    <div class="card-trend <?php echo $income_trend >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $income_trend >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <span><?php echo abs($income_trend) > 0 ? formatMoney(abs($income_trend)) : '0.00'; ?> 
                        <?php echo $income_trend >= 0 ? __('increase') : __('decrease'); ?> <?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="summary-card expenses">
                <div class="card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-label"><?php echo __('monthly_expenses'); ?></h3>
                    <p class="card-value"><?php echo formatMoney($monthly_expenses); ?></p>
                    <?php 
                    $expense_trend = 0;
                    if (isset($monthly_expenses) && isset($previous_monthly_expenses)) {
                        $expense_trend = $monthly_expenses - $previous_monthly_expenses;
                    }
                    if (!isset($previous_monthly_expenses)) {
                        $expense_trend = -50;
                    }
                    $expense_trend_positive = $expense_trend <= 0;
                    ?>
                    <div class="card-trend <?php echo $expense_trend_positive ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $expense_trend <= 0 ? 'arrow-down' : 'arrow-up'; ?>"></i>
                        <span><?php echo abs($expense_trend) > 0 ? formatMoney(abs($expense_trend)) : '0.00'; ?> 
                        <?php echo $expense_trend <= 0 ? __('decrease') : __('increase'); ?> <?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="summary-card savings">
                <div class="card-icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-label"><?php echo __('monthly_net'); ?></h3>
                    <p class="card-value"><?php echo formatMoney($monthly_net); ?></p>
                    <?php 
                    $savings_rate = 0;
                    if (isset($monthly_income) && $monthly_income > 0) {
                        $savings_rate = ($monthly_net / $monthly_income) * 100;
                    }
                    ?>
                    <div class="card-trend <?php echo $savings_rate >= 20 ? 'positive' : ($savings_rate >= 10 ? 'warning' : 'negative'); ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span><?php echo number_format($savings_rate, 1); ?>% <?php echo __('savings_rate'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="summary-card projection">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-label"><?php echo __('yearly_projection'); ?></h3>
                    <p class="card-value"><?php echo formatMoney($yearly_net); ?></p>
                    <div class="card-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo __('annual_forecast'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Expense Categories Chart -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    <h3><?php echo __('expenses_by_category'); ?></h3>
                </div>
                <div class="card-actions">
                    <select id="chartPeriodSelect" class="btn-card-action">
                        <option value="current-month"><?php echo __('this_month_option'); ?></option>
                        <option value="last-month"><?php echo __('last_month_option'); ?></option>
                        <option value="last-3-months"><?php echo __('last_3_months_option'); ?></option>
                        <option value="current-year"><?php echo __('this_year_option'); ?></option>
                        <option value="all-time"><?php echo __('all_time_option'); ?></option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
                
                <div class="categories-list">
                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin: 0 0 1rem 0;"><?php echo __('top_expenses'); ?></h4>
                    
                    <?php 
                    if (isset($top_expenses) && $top_expenses->num_rows > 0):
                        while ($expense = $top_expenses->fetch_assoc()):
                            // Translate category name
                            $category_key = 'expense_category_' . $expense['category_name'];
                            $translated_name = __($category_key);
                            if ($translated_name === $category_key) {
                                $translated_name = $expense['category_name'];
                            }
                    ?>
                    <div class="category-item">
                        <div class="category-info">
                            <h4><?php echo htmlspecialchars($translated_name); ?></h4>
                            <div class="category-bar">
                                <div class="category-bar-fill" 
                                    style="width: <?php echo min(100, ($expense['total'] / max(1, $monthly_expenses)) * 100); ?>%" 
                                    data-percentage="<?php echo ($expense['total'] / max(1, $monthly_expenses)) * 100; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="category-amount">
                            <span class="amount"><?php echo formatMoney($expense['total']); ?></span>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4><?php echo __('no_expense_data'); ?></h4>
                        <p><?php echo __('start_tracking'); ?></p>
                        <a href="<?php echo BASE_PATH; ?>/expenses/add" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            <?php echo __('add_first_expense'); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Budget Status -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-tasks"></i>
                    <h3><?php echo __('budget_status'); ?></h3>
                </div>
                <div class="card-actions">
                    <a href="<?php echo BASE_PATH; ?>/budget" class="btn-card-action">
                        <?php echo __('manage_budgets'); ?>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="budget-list">
                    <?php if (empty($budget_status)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <h4><?php echo __('no_budget_data'); ?></h4>
                            <p><?php echo __('set_up_budget'); ?></p>
                            <a href="<?php echo BASE_PATH; ?>/budget" class="btn-primary"><?php echo __('create_budget'); ?></a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($budget_status as $budget): 
                            // Translate category name
                            $category_key = 'expense_category_' . $budget['category_name'];
                            $translated_category = __($category_key);
                            if ($translated_category === $category_key) {
                                $translated_category = $budget['category_name'];
                            }
                        ?>
                            <div class="budget-item">
                                <div class="budget-info">
                                    <span class="budget-category"><?php echo htmlspecialchars($translated_category); ?></span>
                                    <div class="budget-values">
                                        <div class="budget-percentage">
                                            <?php echo number_format($budget['percentage'], 0); ?>%
                                        </div>
                                        <div class="budget-amounts">
                                            <?php echo formatMoney($budget['spent']); ?> / <?php echo formatMoney($budget['budget_amount']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="budget-bar">
                                    <?php 
                                    $bar_class = 'safe';
                                    if ($budget['percentage'] >= 90) {
                                        $bar_class = 'danger';
                                    } else if ($budget['percentage'] >= 75) {
                                        $bar_class = 'warning';
                                    }
                                    ?>
                                    <div class="budget-bar-fill <?php echo $bar_class; ?>" 
                                        style="width: <?php echo min(100, $budget['percentage']); ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="overall-status">
                            <h4><?php echo __('income_vs_expenses'); ?></h4>
                            <?php 
                            $expense_percentage = 0;
                            if (isset($monthly_income) && $monthly_income > 0) {
                                $expense_percentage = ($monthly_expenses / $monthly_income) * 100;
                            }
                            $overall_class = 'positive';
                            $overall_message = __('budget_great');
                            $bar_class = 'safe';
                            
                            if ($expense_percentage >= 90) {
                                $overall_class = 'negative';
                                $overall_message = __('budget_danger');
                                $bar_class = 'danger';
                            } else if ($expense_percentage >= 75) {
                                $overall_class = 'warning';
                                $overall_message = __('budget_warning');
                                $bar_class = 'warning';
                            }
                            ?>
                            <div class="status-bar">
                                <div class="status-bar-fill <?php echo $bar_class; ?>" 
                                    style="width: <?php echo min(100, $expense_percentage); ?>%">
                                </div>
                            </div>
                            <div class="status-info">
                                <div class="status-indicator <?php echo $overall_class; ?>">
                                    <i class="fas fa-circle"></i>
                                    <span><?php echo number_format($expense_percentage, 0); ?>% <?php echo __('of_income_spent'); ?></span>
                                </div>
                                <span class="status-target"><?php echo __('target'); ?>: <85%</span>
                            </div>
                            <p class="status-message"><?php echo $overall_message; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals and Advice Section -->
    <div class="goals-advice-grid">
        <!-- Financial Goals -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-bullseye"></i>
                    <h3><?php echo __('financial_goals'); ?></h3>
                </div>
                <div class="card-actions">
                    <a href="<?php echo BASE_PATH; ?>/goals" class="btn-card-action">
                        <?php echo __('view_all_goals'); ?>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="goals-list">
                    <?php if (!isset($goals) || $goals->num_rows === 0): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <h4><?php echo __('no_financial_goals'); ?></h4>
                            <p><?php echo __('start_planning'); ?></p>
                            <a href="<?php echo BASE_PATH; ?>/goals" class="btn-primary"><?php echo __('set_goals'); ?></a>
                        </div>
                    <?php else: ?>
                        <?php while ($goal = $goals->fetch_assoc()): ?>
                            <?php 
                            $progress = 0;
                            if ($goal['target_amount'] > 0) {
                                $progress = ($goal['current_amount'] / $goal['target_amount']) * 100; 
                            }
                            
                            $status_class = 'progress';
                            $status_icon = 'clock';
                            $bar_class = 'info';
                            
                            if ($progress >= 100) {
                                $status_class = 'completed';
                                $status_icon = 'check-circle';
                                $bar_class = 'success';
                            }
                            
                            $target_date = new DateTime($goal['target_date']);
                            $today = new DateTime();
                            $days_remaining = $today->diff($target_date)->days;
                            $is_overdue = $today > $target_date;
                            ?>
                            
                            <div class="goal-item">
                                <div class="goal-header">
                                    <div>
                                        <h4 class="goal-title"><?php echo htmlspecialchars($goal['name']); ?></h4>
                                        <span class="goal-status <?php echo $status_class; ?>">
                                            <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                            <?php echo $progress >= 100 ? __('completed') : number_format($progress, 0) . '%'; ?>
                                        </span>
                                    </div>
                                    <div class="goal-deadline <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo $is_overdue ? __('overdue') : $days_remaining . ' ' . __('days_left'); ?>
                                    </div>
                                </div>
                                
                                <div class="goal-progress">
                                    <div class="goal-bar">
                                        <div class="goal-bar-fill <?php echo $bar_class; ?>" 
                                            style="width: <?php echo min(100, $progress); ?>%">
                                        </div>
                                    </div>
                                    <?php if ($progress >= 25 || $progress >= 50 || $progress >= 75): ?>
                                    <div class="milestone-markers">
                                        <?php if ($progress >= 25): ?>
                                        <div class="milestone-marker <?php echo $progress >= 25 ? 'reached' : ''; ?>" 
                                             style="left: 25%;"></div>
                                        <?php endif; ?>
                                        <?php if ($progress >= 50): ?>
                                        <div class="milestone-marker <?php echo $progress >= 50 ? 'reached' : ''; ?>" 
                                             style="left: 50%;"></div>
                                        <?php endif; ?>
                                        <?php if ($progress >= 75): ?>
                                        <div class="milestone-marker <?php echo $progress >= 75 ? 'reached' : ''; ?>" 
                                             style="left: 75%;"></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="goal-details">
                                    <span><?php echo formatMoney($goal['current_amount']); ?> <?php echo __('of'); ?> 
                                    <?php echo formatMoney($goal['target_amount']); ?></span>
                                    
                                    <?php 
                                    $remaining = max(0, $goal['target_amount'] - $goal['current_amount']);
                                    $contribution = 0;
                                    if ($days_remaining > 0 && !$is_overdue) {
                                        $months_remaining = max(1, ceil($days_remaining / 30));
                                        $contribution = $remaining / $months_remaining;
                                    }
                                    ?>
                                    
                                    <?php if ($progress < 100 && !$is_overdue && $contribution > 0): ?>
                                    <span><?php echo formatMoney($contribution); ?><?php echo __('per_month_to_reach_goal'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Financial Advice -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-lightbulb"></i>
                    <h3><?php echo __('financial_advice'); ?></h3>
                </div>
                <div class="card-actions">
                    <button type="button" class="btn-card-action" id="generateAdvice">
                        <i class="fas fa-sync"></i> <?php echo __('get_new_advice'); ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="advice-list">
                    <?php if (!isset($financial_advice) || $financial_advice->num_rows === 0): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-comment-dollar"></i>
                            </div>
                            <h4><?php echo __('no_financial_advice'); ?></h4>
                            <p><?php echo __('advice_description'); ?></p>
                            <button type="button" class="btn-primary" id="generateAdviceEmpty">
                                <i class="fas fa-magic"></i> <?php echo __('generate_advice'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <?php while ($advice = $financial_advice->fetch_assoc()): ?>
                            <?php 
                            $advice_class = 'info';
                            $advice_icon = 'info-circle';
                            
                            if ($advice['importance_level'] === 'high') {
                                $advice_class = 'danger';
                                $advice_icon = 'exclamation-circle';
                            } else if ($advice['importance_level'] === 'medium') {
                                $advice_class = 'warning';
                                $advice_icon = 'exclamation-triangle';
                            }
                            ?>
                            <div class="advice-item <?php echo $advice_class; ?>">
                                <div class="advice-icon">
                                    <i class="fas fa-<?php echo $advice_icon; ?>"></i>
                                </div>
                                <div class="advice-content">
                                    <div class="advice-header">
                                        <h4 class="advice-title"><?php echo htmlspecialchars($advice['title']); ?></h4>
                                        <span class="advice-date"><?php echo date('M j', strtotime($advice['generated_at'])); ?></span>
                                    </div>
                                    <p class="advice-text">
                                        <?php echo htmlspecialchars($advice['content']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data with translations
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
];

if (isset($top_expenses) && $top_expenses->num_rows > 0) {
    $top_expenses->data_seek(0);
    while ($category = $top_expenses->fetch_assoc()) {
        // Translate category name
        $category_key = 'expense_category_' . $category['category_name'];
        $translated_name = __($category_key);
        if ($translated_name === $category_key) {
            $translated_name = $category['category_name'];
        }
        
        $chart_labels[] = $translated_name;
        $chart_data[] = floatval($category['total']);
    }
}

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';
echo '<meta name="currency-symbol" content="' . getCurrencySymbol() . '">';

// Include footer
require_once 'includes/footer.php';
?>