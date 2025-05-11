<?php
// Set page title and current page for menu highlighting
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

// Additional CSS and JS
$additional_css = ['/assets/css/expenses-modern.css'];
$additional_js = ['/assets/js/expenses-modern.js', '/assets/js/direct-form-handler.js'];

// Include header
require_once 'includes/header.php';

// Get current month and year for selection
$current_month = isset($_GET['month']) ? $_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
?>

<!-- Main Content Wrapper -->
<div class="expenses-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Expense Management</h1>
                <p class="page-subtitle">Track and manage your expenses effectively</p>
            </div>
            <button type="button" class="btn-add-expense" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus-circle"></i>
                <span>Add Expense</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats-section">
        <div class="stats-grid">
            <div class="stat-card monthly">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Monthly Expenses</h3>
                    <p class="stat-value">$<?php echo number_format($monthly_expenses, 2); ?></p>
                    <?php 
                    $previous_month = isset($prev_monthly_expenses) ? $prev_monthly_expenses : $monthly_expenses * 1.05;
                    $is_decreased = $monthly_expenses <= $previous_month;
                    $percent_change = abs(($monthly_expenses - $previous_month) / max(1, $previous_month) * 100);
                    ?>
                    <div class="stat-trend <?php echo $is_decreased ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $is_decreased ? 'arrow-down' : 'arrow-up'; ?>"></i>
                        <span><?php echo number_format($percent_change, 1); ?>% from last month</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card annual">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Annual Expenses</h3>
                    <p class="stat-value">$<?php echo number_format($yearly_expenses, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Projected for <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card average">
                <div class="stat-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Daily Average</h3>
                    <p class="stat-value">$<?php echo number_format($monthly_expenses / 30, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-clock"></i>
                        <span>Based on current month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-grid">
            <!-- Category Distribution Chart -->
            <div class="chart-card category-chart">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Expense Categories</h3>
                    </div>
                    <div class="chart-controls">
                        <select id="chartPeriodSelect" class="chart-period-select">
                            <option value="current-month">This Month</option>
                            <option value="last-month">Last Month</option>
                            <option value="last-3-months">Last 3 Months</option>
                            <option value="current-year">This Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div id="chartNoData" class="no-data-message" style="display: <?php echo (isset($top_expenses) && $top_expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <p>No expense data available for the selected period</p>
                    </div>
                </div>
            </div>
            
            <!-- Top Categories List -->
            <div class="chart-card categories-list">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-list-ol"></i>
                        <h3>Top Categories</h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="categories-list-content">
                        <?php if (isset($top_expenses) && $top_expenses->num_rows > 0): ?>
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
                        <?php else: ?>
                            <div class="empty-categories">
                                <i class="fas fa-folder-open"></i>
                                <p>No expenses recorded yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Table Section -->
    <div class="expenses-table-section">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-receipt"></i>
                    <h3>Recent Expenses</h3>
                </div>
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="expenseSearch" placeholder="Search expenses..." data-table-search="expenseTable">
                    </div>
                    <div class="dropdown filter-dropdown">
                        <button class="btn-filter dropdown-toggle" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <div class="dropdown-menu filter-menu" aria-labelledby="filterDropdown">
                            <div class="filter-section">
                                <h5>Month</h5>
                                <select id="monthSelect" class="filter-select">
                                    <option value="all" <?php echo (!isset($_GET['month']) && !isset($_GET['year'])) ? 'selected' : ''; ?>>All Time</option>
                                    <option value="0" data-year="<?php echo date('Y'); ?>" <?php echo ($current_month == date('n') && $current_year == date('Y')) ? 'selected' : ''; ?>>Current Month</option>
                                    <?php 
                                    $months = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    
                                    // Show past 12 months
                                    for ($i = 0; $i < 12; $i++) {
                                        $timestamp = strtotime("-$i months");
                                        $month_num = date('n', $timestamp);
                                        $year = date('Y', $timestamp);
                                        $is_selected = ($month_num == $current_month && $year == $current_year) ? 'selected' : '';
                                        echo "<option value='$month_num' data-year='$year' $is_selected>{$months[$month_num]} $year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-section">
                                <h5>Year</h5>
                                <select id="yearSelect" class="filter-select">
                                    <option value="all" <?php echo (!isset($_GET['year'])) ? 'selected' : ''; ?>>All Years</option>
                                    <?php 
                                    $current_year_actual = date('Y');
                                    for ($year = $current_year_actual; $year >= $current_year_actual - 5; $year--) {
                                        $is_selected = ($year == $current_year && isset($_GET['year'])) ? 'selected' : '';
                                        echo "<option value='$year' $is_selected>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button class="btn-apply-filter" id="applyMonthFilter">Apply Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-body">
                <div class="table-responsive">
                    <table class="expenses-table" id="expenseTable">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($expenses) && $expenses->num_rows > 0): ?>
                                <?php while ($expense = $expenses->fetch_assoc()): ?>
                                    <tr>
                                        <td class="description-cell">
                                            <span class="description-text"><?php echo htmlspecialchars($expense['description']); ?></span>
                                        </td>
                                        <td class="category-cell">
                                            <span class="category-badge"><?php echo htmlspecialchars($expense['category_name']); ?></span>
                                        </td>
                                        <td class="amount-cell">$<?php echo number_format($expense['amount'], 2); ?></td>
                                        <td class="date-cell"><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                        <td class="type-cell">
                                            <?php if ($expense['is_recurring']): ?>
                                                <span class="type-badge recurring">
                                                    <i class="fas fa-sync-alt"></i>
                                                    <?php echo ucfirst(str_replace('-', ' ', $expense['frequency'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="type-badge one-time">
                                                    <i class="fas fa-check"></i>
                                                    One-time
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn-action edit" data-expense-id="<?php echo $expense['expense_id']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action delete" data-expense-id="<?php echo $expense['expense_id']; ?>" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="tableNoData" class="table-empty" style="display: <?php echo (isset($expenses) && $expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                    <div class="empty-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h4>No expenses recorded yet</h4>
                    <p>Start tracking your expenses to get insights into your spending habits</p>
                    <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fas fa-plus"></i>
                        Add Your First Expense
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals remain the same -->
<!-- Add Expense Modal -->
<div class="modal fade modern-modal" id="addExpenseModal" tabindex="-1">
    <!-- ... modal content remains the same ... -->
</div>

<!-- Edit Expense Modal -->
<div class="modal fade modern-modal" id="editExpenseModal" tabindex="-1">
    <!-- ... modal content remains the same ... -->
</div>

<!-- Delete Expense Modal -->
<div class="modal fade modern-modal" id="deleteExpenseModal" tabindex="-1">
    <!-- ... modal content remains the same ... -->
</div>

<?php
// Chart data
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
];

if (isset($top_expenses) && $top_expenses->num_rows > 0) {
    $top_expenses->data_seek(0);
    while ($category = $top_expenses->fetch_assoc()) {
        $chart_labels[] = $category['category_name'];
        $chart_data[] = floatval($category['total']);
    }
}

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';

require_once 'includes/footer.php';
?>