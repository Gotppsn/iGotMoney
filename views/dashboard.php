<?php 
// Set page title and current page for menu highlighting
$page_title = __('dashboard_title') . ' - ' . __('app_name');
$current_page = 'dashboard';

// Add dashboard-specific CSS and JS
$additional_css = ['/assets/css/dashboard.css'];
$additional_js = ['/assets/js/dashboard.js'];

// Include formatter for currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';

// Get currency symbol for JS
$currency_symbol = getCurrencySymbol();
?>

<div class="dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="greeting">
                <h1><?php echo __('dashboard_title'); ?></h1>
                <p><?php echo __('welcome_back', ['username' => htmlspecialchars($_SESSION['username'])]); ?></p>
            </div>
            <div class="header-actions">
                <button type="button" id="refreshDashboard" class="btn-action">
                    <i class="fas fa-sync-alt"></i>
                    <span><?php echo __('refresh'); ?></span>
                </button>
                <button type="button" id="printDashboard" class="btn-action outlined">
                    <i class="fas fa-print"></i>
                    <span><?php echo __('print'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="summary-section">
        <div class="summary-grid">
            <!-- Income Card -->
            <div class="summary-card income" data-aos="fade-up" data-aos-delay="100">
                <div class="card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-content">
                    <div class="card-label"><?php echo __('monthly_income'); ?></div>
                    <div class="card-value" data-value="<?php echo $monthly_income; ?>">
                        <?php echo formatMoney($monthly_income); ?>
                    </div>
                    <?php 
                    $income_trend = 0;
                    if (isset($monthly_income) && isset($previous_monthly_income)) {
                        $income_trend = $monthly_income - $previous_monthly_income;
                    }
                    if (!isset($previous_monthly_income)) {
                        $income_trend = 150; // Sample value for demo
                    }
                    ?>
                    <div class="card-trend <?php echo $income_trend >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $income_trend >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <span><?php echo formatMoney(abs($income_trend)); ?> 
                        <?php echo $income_trend >= 0 ? __('increase') : __('decrease'); ?>
                        <?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Expenses Card -->
            <div class="summary-card expenses" data-aos="fade-up" data-aos-delay="150">
                <div class="card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="card-content">
                    <div class="card-label"><?php echo __('monthly_expenses'); ?></div>
                    <div class="card-value" data-value="<?php echo $monthly_expenses; ?>">
                        <?php echo formatMoney($monthly_expenses); ?>
                    </div>
                    <?php 
                    $expense_trend = 0;
                    if (isset($monthly_expenses) && isset($previous_monthly_expenses)) {
                        $expense_trend = $monthly_expenses - $previous_monthly_expenses;
                    }
                    if (!isset($previous_monthly_expenses)) {
                        $expense_trend = -50; // Sample value for demo
                    }
                    $expense_trend_positive = $expense_trend <= 0;
                    ?>
                    <div class="card-trend <?php echo $expense_trend_positive ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $expense_trend <= 0 ? 'arrow-down' : 'arrow-up'; ?>"></i>
                        <span><?php echo formatMoney(abs($expense_trend)); ?> 
                        <?php echo $expense_trend <= 0 ? __('decrease') : __('increase'); ?>
                        <?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Savings Card -->
            <div class="summary-card savings" data-aos="fade-up" data-aos-delay="200">
                <div class="card-icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-content">
                    <div class="card-label"><?php echo __('monthly_net'); ?></div>
                    <div class="card-value" data-value="<?php echo $monthly_net; ?>">
                        <?php echo formatMoney($monthly_net); ?>
                    </div>
                    <?php 
                    $savings_rate = 0;
                    if (isset($monthly_income) && $monthly_income > 0) {
                        $savings_rate = ($monthly_net / $monthly_income) * 100;
                    }
                    
                    $savings_class = 'negative';
                    if ($savings_rate >= 20) {
                        $savings_class = 'positive';
                    } else if ($savings_rate >= 10) {
                        $savings_class = 'warning';
                    }
                    ?>
                    <div class="card-trend <?php echo $savings_class; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span><?php echo number_format($savings_rate, 1); ?>% <?php echo __('savings_rate'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Projection Card -->
            <div class="summary-card projection" data-aos="fade-up" data-aos-delay="250">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <div class="card-label"><?php echo __('yearly_projection'); ?></div>
                    <div class="card-value" data-value="<?php echo $yearly_net; ?>">
                        <?php echo formatMoney($yearly_net); ?>
                    </div>
                    <div class="card-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo __('annual_forecast'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="dashboard-content">
        <!-- Expense Categories Chart -->
        <div class="dashboard-card expenses-chart" data-aos="fade-up">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    <h2><?php echo __('expenses_by_category'); ?></h2>
                </div>
                <div class="card-actions">
                    <select id="chartPeriodSelect" class="select-minimal">
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
                    <div class="chart-legend" id="chartLegend"></div>
                </div>
                
                <div class="categories-list">
                    <h3><?php echo __('top_expenses'); ?></h3>
                    
                    <?php 
                    if (isset($top_expenses) && $top_expenses->num_rows > 0):
                        while ($expense = $top_expenses->fetch_assoc()):
                            // Translate category name
                            $category_key = 'expense_category_' . $expense['category_name'];
                            $translated_name = __($category_key);
                            if ($translated_name === $category_key) {
                                $translated_name = $expense['category_name'];
                            }
                            
                            // Calculate percentage for bar
                            $percentage = min(100, ($expense['total'] / max(1, $monthly_expenses)) * 100);
                    ?>
                    <div class="category-item">
                        <div class="category-info">
                            <h4><?php echo htmlspecialchars($translated_name); ?></h4>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" data-percentage="<?php echo $percentage; ?>"></div>
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
        <div class="dashboard-card budget-status" data-aos="fade-up">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-tasks"></i>
                    <h2><?php echo __('budget_status'); ?></h2>
                </div>
                <div class="card-actions">
                    <a href="<?php echo BASE_PATH; ?>/budget" class="btn-link">
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
                            
                            // Set appropriate class based on percentage
                            $bar_class = 'safe';
                            if ($budget['percentage'] >= 90) {
                                $bar_class = 'danger';
                            } else if ($budget['percentage'] >= 75) {
                                $bar_class = 'warning';
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
                            <div class="progress-bar">
                                <div class="progress-bar-fill <?php echo $bar_class; ?>" 
                                     data-percentage="<?php echo min(100, $budget['percentage']); ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="overall-status">
                            <h3><?php echo __('income_vs_expenses'); ?></h3>
                            <?php 
                            $expense_percentage = 0;
                            if (isset($monthly_income) && $monthly_income > 0) {
                                $expense_percentage = min(100, ($monthly_expenses / $monthly_income) * 100);
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
                            <div class="progress-bar large">
                                <div class="progress-bar-fill <?php echo $bar_class; ?>" 
                                     data-percentage="<?php echo $expense_percentage; ?>">
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

    <!-- Bottom Section: Goals and Advice -->
    <div class="dashboard-bottom">
        <!-- Financial Goals -->
        <div class="dashboard-card goals" data-aos="fade-up">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-bullseye"></i>
                    <h2><?php echo __('financial_goals'); ?></h2>
                </div>
                <div class="card-actions">
                    <a href="<?php echo BASE_PATH; ?>/goals" class="btn-link">
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
                                    <div class="goal-title-wrapper">
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
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill <?php echo $bar_class; ?>" 
                                             data-percentage="<?php echo min(100, $progress); ?>">
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
                                </div>
                                
                                <div class="goal-details">
                                    <span class="goal-amount"><?php echo formatMoney($goal['current_amount']); ?> <?php echo __('of'); ?> 
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
                                    <span class="goal-monthly"><?php echo formatMoney($contribution); ?>/<?php echo __('month'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Financial Advice -->
        <div class="dashboard-card advice" data-aos="fade-up">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-lightbulb"></i>
                    <h2><?php echo __('financial_advice'); ?></h2>
                </div>
                <div class="card-actions">
                    <button type="button" id="generateAdvice" class="btn-link">
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
                            <button type="button" id="generateAdviceEmpty" class="btn-primary">
                                <i class="fas fa-magic"></i> <?php echo __('generate_advice'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <?php while ($advice = $financial_advice->fetch_assoc()): ?>
                            <?php 
                            // Determine the appropriate class based on importance level
                            $advice_class = 'info';
                            $advice_icon = 'info-circle';
                            
                            if ($advice['importance_level'] === 'high') {
                                $advice_class = 'danger';
                                $advice_icon = 'exclamation-circle';
                            } else if ($advice['importance_level'] === 'medium') {
                                $advice_class = 'warning';
                                $advice_icon = 'exclamation-triangle';
                            }
                            
                            // Format the date
                            $advice_date = date('M j', strtotime($advice['generated_at']));
                            ?>
                            <div class="advice-item <?php echo $advice_class; ?>">
                                <div class="advice-icon">
                                    <i class="fas fa-<?php echo $advice_icon; ?>"></i>
                                </div>
                                <div class="advice-content">
                                    <div class="advice-header">
                                        <h4 class="advice-title"><?php echo htmlspecialchars($advice['title']); ?></h4>
                                        <span class="advice-date"><?php echo $advice_date; ?></span>
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

<!-- Toast Notification Container -->
<div id="toast-container"></div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay hidden">
    <div class="spinner">
        <i class="fas fa-circle-notch fa-spin"></i>
        <div class="spinner-text">Loading...</div>
    </div>
</div>

<?php
// Prepare chart data with translations
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#4f46e5',
    '#a855f7', '#d946ef', '#f43f5e', '#fb7185', '#fbbf24',
    '#a3e635', '#4ade80', '#2dd4bf', '#22d3ee', '#38bdf8'
];

if (isset($top_expenses) && $top_expenses->num_rows > 0) {
    $top_expenses->data_seek(0);
    $index = 0;
    while ($category = $top_expenses->fetch_assoc()) {
        // Translate category name
        $category_key = 'expense_category_' . $category['category_name'];
        $translated_name = __($category_key);
        if ($translated_name === $category_key) {
            $translated_name = $category['category_name'];
        }
        
        $chart_labels[] = $translated_name;
        $chart_data[] = floatval($category['total']);
        $index++;
    }
}

// Create JS variables for chart data
echo '<script>';
echo 'const chartLabels = ' . json_encode($chart_labels) . ';';
echo 'const chartData = ' . json_encode($chart_data) . ';';
echo 'const chartColors = ' . json_encode(array_slice($chart_colors, 0, count($chart_data))) . ';';
echo 'const currencySymbol = "' . $currency_symbol . '";';
echo 'const basePath = "' . BASE_PATH . '";';
echo '</script>';

// Include footer
require_once 'includes/footer.php';
?>