<?php
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

$additional_css = ['/assets/css/expenses-modern.css'];
$additional_js = ['/assets/js/expenses-modern.js', '/assets/js/direct-form-handler.js'];

require_once 'includes/header.php';

$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
?>

<div class="expenses-page">
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

    <div class="charts-section">
        <div class="charts-grid">
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
                            <form id="filterForm" action="<?php echo BASE_PATH; ?>/expenses" method="GET">
                                <div class="filter-section">
                                    <h5>Month</h5>
                                    <select id="monthSelect" name="month" class="filter-select">
                                        <option value="0" <?php echo (!isset($_GET['month']) || $_GET['month'] == 0) ? 'selected' : ''; ?>>All Months</option>
                                        <?php 
                                        $months = [
                                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                        ];
                                        
                                        foreach ($months as $month_num => $month_name): 
                                            $is_selected = ($month_num == $current_month) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $month_num; ?>" <?php echo $is_selected; ?>><?php echo $month_name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-section">
                                    <h5>Year</h5>
                                    <select id="yearSelect" name="year" class="filter-select">
                                        <option value="0" <?php echo (!isset($_GET['year']) || $_GET['year'] == 0) ? 'selected' : ''; ?>>All Years</option>
                                        <?php 
                                        $current_year_actual = date('Y');
                                        for ($year = $current_year_actual; $year >= $current_year_actual - 5; $year--): 
                                            $is_selected = ($year == $current_year) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $year; ?>" <?php echo $is_selected; ?>><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="filter-section">
                                    <h5>Category</h5>
                                    <select id="categorySelect" name="category" class="filter-select">
                                        <option value="0">All Categories</option>
                                        <?php 
                                        $categories->data_seek(0);
                                        while($category = $categories->fetch_assoc()): 
                                            $is_selected = ($category['category_id'] == $filter_category) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo $is_selected; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                        <?php $categories->data_seek(0); ?>
                                    </select>
                                </div>
                                <div class="filter-actions">
                                    <button type="submit" class="btn-apply-filter" id="applyFilter">Apply Filter</button>
                                    <button type="button" class="btn-reset-filter" id="resetFilter">Reset</button>
                                </div>
                            </form>
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

<div class="modal fade modern-modal" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="description">Description</label>
                            <input type="text" id="description" name="description" class="form-control" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="modern-select" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0);
                                while($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                                <?php $categories->data_seek(0); ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="expense_date">Date</label>
                            <input type="date" id="expense_date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="frequency">Frequency</label>
                            <select id="frequency" name="frequency" class="modern-select" disabled>
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="is_recurring" name="is_recurring">
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Recurring Expense</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modern-modal" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_expense_id" name="expense_id">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="edit_description">Description</label>
                            <input type="text" id="edit_description" name="description" class="form-control" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_category_id">Category</label>
                            <select id="edit_category_id" name="category_id" class="modern-select" required>
                                <option value="">Select Category</option>
                                <?php $categories->data_seek(0); ?>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                                <?php $categories->data_seek(0); ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_amount" name="amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_expense_date">Date</label>
                            <input type="date" id="edit_expense_date" name="expense_date" class="form-control" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_frequency">Frequency</label>
                            <select id="edit_frequency" name="frequency" class="modern-select">
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="edit_is_recurring" name="is_recurring">
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Recurring Expense</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modern-modal" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title" id="deleteExpenseModalLabel">Delete Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="deleteExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_expense_id" name="expense_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
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

echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';

require_once 'includes/footer.php';
?>