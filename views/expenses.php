<?php
// Set page title and current page for menu highlighting
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

// Additional CSS and JS
$additional_css = ['/assets/css/expenses-modern.css'];
$additional_js = ['/assets/js/expenses-modern.js', '/assets/js/direct-form-handler.js'];

// Include header
require_once 'includes/header.php';
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
                    <div class="filter-dropdown">
                        <button class="btn-filter" id="filterDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <div class="dropdown-menu filter-menu">
                            <div class="filter-section">
                                <h5>Date Range</h5>
                                <select id="dateRangeSelect" class="filter-select">
                                    <option value="all">All Time</option>
                                    <option value="current-month" selected>Current Month</option>
                                    <option value="last-month">Last Month</option>
                                    <option value="last-3-months">Last 3 Months</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div id="customDateRange" class="filter-section" style="display: none;">
                                <input type="date" id="startDate" class="filter-date">
                                <input type="date" id="endDate" class="filter-date">
                            </div>
                            <div class="filter-actions">
                                <button class="btn-apply-filter" id="applyDateFilter">Apply Filter</button>
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

<!-- Add Expense Modal (Redesigned) -->
<div class="modal fade modern-modal" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="addExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="category_id">Category</label>
                            <select class="modern-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php 
                                if (isset($categories) && $categories->num_rows > 0) {
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="description">Description</label>
                            <input type="text" id="description" name="description" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="expense_date">Date</label>
                            <input type="date" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="frequency">Frequency</label>
                            <select id="frequency" name="frequency">
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
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
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal (Similar structure) -->
<div class="modal fade modern-modal" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title">Edit Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="editExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="expense_id" id="edit_expense_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="edit_category_id">Category</label>
                            <select class="modern-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php 
                                if (isset($categories) && $categories->num_rows > 0) {
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="edit_description">Description</label>
                            <input type="text" id="edit_description" name="description" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_expense_date">Date</label>
                            <input type="date" id="edit_expense_date" name="expense_date" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_frequency">Frequency</label>
                            <select id="edit_frequency" name="frequency">
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
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
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Expense Modal -->
<div class="modal fade modern-modal" id="deleteExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title">Delete Expense</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete this expense?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="deleteExpenseForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="expense_id" id="delete_expense_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
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