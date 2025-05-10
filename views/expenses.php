<?php
// Set page title and current page for menu highlighting
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

// Additional CSS and JS - make sure paths are correct
$additional_css = ['/assets/css/expenses.css'];
$additional_js = ['/assets/js/expenses.js', '/assets/js/direct-form-handler.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="page-title">Expense Management</h1>
        <p class="page-description text-muted mb-0">Track and manage your expenses effectively</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="fas fa-plus me-2"></i> Add Expense
    </button>
</div>

<!-- Success and error messages -->
<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card summary-card expenses h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-wrapper expenses-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <p class="card-title mb-0">Monthly Expenses</p>
                    </div>
                </div>
                <h3 class="card-value">$<?php echo number_format($monthly_expenses, 2); ?></h3>
                <?php 
                // Determine if expenses are up/down (placeholder calculation)
                // In a real implementation, you would compare with previous month
                $previous_month = isset($prev_monthly_expenses) ? $prev_monthly_expenses : $monthly_expenses * 1.05;
                $is_decreased = $monthly_expenses <= $previous_month;
                $percent_change = abs(($monthly_expenses - $previous_month) / max(1, $previous_month) * 100);
                ?>
                
                <p class="card-trend <?php echo $is_decreased ? 'trend-positive' : 'trend-negative'; ?>">
                    <i class="fas fa-<?php echo $is_decreased ? 'arrow-down' : 'arrow-up'; ?> me-1"></i>
                    <?php echo number_format($percent_change, 1); ?>% 
                    <?php echo $is_decreased ? 'decrease' : 'increase'; ?> from last month
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card summary-card annual h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-wrapper annual-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <p class="card-title mb-0">Annual Expenses</p>
                    </div>
                </div>
                <h3 class="card-value">$<?php echo number_format($yearly_expenses, 2); ?></h3>
                <p class="card-trend text-muted">
                    <i class="fas fa-chart-line me-1"></i>
                    Projected total for <?php echo date('Y'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Expense Categories Chart & Top Expenses -->
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card chart-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    Expenses by Category
                </h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar-alt me-1"></i> This Month
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartPeriodDropdown">
                        <li><a class="dropdown-item chart-period active" href="#" data-period="current-month">This Month</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="last-month">Last Month</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="quarter">This Quarter</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="last-3-months">Last 3 Months</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="current-year">This Year</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="all">All Time</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <div class="chart-container position-relative">
                        <canvas id="expenseCategoryChart"></canvas>
                    </div>
                </div>
                <div id="chartNoData" class="text-center py-4 empty-state" style="display: <?php echo (isset($top_expenses) && $top_expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>No expense data</h4>
                    <p>No expense data available for the selected period.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Top Expenses
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($top_expenses) && $top_expenses->num_rows > 0): ?>
                    <?php $top_expenses->data_seek(0); ?>
                    <?php while ($category = $top_expenses->fetch_assoc()): ?>
                        <div class="expense-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="expense-category"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                <span class="expense-amount">$<?php echo number_format($category['total'], 2); ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <?php 
                                $percentage = ($category['total'] / max(0.01, $monthly_expenses)) * 100;
                                $color_class = 'bg-info';
                                
                                if ($percentage > 30) {
                                    $color_class = 'bg-danger';
                                } else if ($percentage > 20) {
                                    $color_class = 'bg-warning';
                                } else if ($percentage > 10) {
                                    $color_class = 'bg-primary';
                                }
                                ?>
                                <div class="progress-bar <?php echo $color_class; ?>" role="progressbar" 
                                    style="width: 0%" 
                                    aria-valuenow="<?php echo $percentage; ?>" 
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4 empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h4>No expenses found</h4>
                        <p>Add your first expense to see top spending categories</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card table-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
            Your Expenses
        </h5>
        <div class="d-flex align-items-center">
            <div class="position-relative table-search me-2">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Search expenses..." id="expenseSearch" data-table-search="expenseTable">
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dateFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar-alt me-1"></i> Filter
                </button>
                <div class="dropdown-menu date-filter-dropdown dropdown-menu-end" aria-labelledby="dateFilterDropdown">
                    <h6 class="dropdown-header">Date Range</h6>
                    <div class="mb-2">
                        <select class="form-select form-select-sm" id="dateRangeSelect">
                            <option value="all">All Time</option>
                            <option value="current-month" selected>Current Month</option>
                            <option value="last-month">Last Month</option>
                            <option value="last-3-months">Last 3 Months</option>
                            <option value="last-6-months">Last 6 Months</option>
                            <option value="current-year">Current Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateRange" style="display: none;">
                        <div class="mb-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="startDate">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="endDate">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-sm btn-primary" id="applyDateFilter">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table expenses-table align-middle" id="expenseTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Frequency</th>
                        <th class="text-center">Recurring</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($expenses) && $expenses->num_rows > 0): ?>
                        <?php while ($expense = $expenses->fetch_assoc()): ?>
                            <tr>
                                <td class="description-cell"><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                                <td class="amount-cell">$<?php echo number_format($expense['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                <td>
                                    <?php 
                                    $frequency = ucfirst(str_replace('-', ' ', $expense['frequency']));
                                    echo $frequency; 
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($expense['is_recurring']): ?>
                                        <span class="badge bg-info">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-expense" data-expense-id="<?php echo $expense['expense_id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-expense" data-expense-id="<?php echo $expense['expense_id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="tableNoData" class="empty-state" style="display: <?php echo (isset($expenses) && $expenses->num_rows > 0) ? 'none' : 'block'; ?>">
            <div class="empty-state-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <h4>No expenses recorded yet</h4>
            <p>Start tracking your expenses to get insights into your spending habits</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus me-1"></i> Add Your First Expense
            </button>
        </div>
    </div>
</div>

<!-- Expense Analytics -->
<div class="card analytics-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-chart-line text-primary me-2"></i>
            Expense Analytics
        </h5>
        <button class="btn btn-sm btn-outline-primary" id="calculateAnalytics">
            <i class="fas fa-calculator me-1"></i> Calculate
        </button>
    </div>
    <div class="card-body">
        <div class="row" id="analyticsContent" style="display: none;">
            <!-- Analytics content will be loaded here -->
        </div>
        <div id="analyticsPlaceholder" class="text-center py-4">
            <div class="empty-state-icon">
                <i class="fas fa-analytics"></i>
            </div>
            <h4>Expense Analytics</h4>
            <p>Click the Calculate button to generate detailed expense analytics</p>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Expense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="addExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Reset the categories result pointer
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
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="description" name="description" required>
                        <div class="invalid-feedback">Please provide a description.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="is_recurring" name="is_recurring">
                        <label class="form-check-label" for="is_recurring">Recurring Expense</label>
                    </div>
                    
                    <div id="recurring_options" style="display: none;">
                        <div class="mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Expense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="editExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="expense_id" id="edit_expense_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Reset the categories result pointer
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
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_description" name="description" required>
                        <div class="invalid-feedback">Please provide a description.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="edit_is_recurring" name="is_recurring">
                        <label class="form-check-label" for="edit_is_recurring">Recurring Expense</label>
                    </div>
                    
                    <div id="edit_recurring_options" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_frequency" class="form-label">Frequency</label>
                            <select class="form-select" id="edit_frequency" name="frequency">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Expense Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExpenseModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Delete Expense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="empty-state-icon mb-3" style="background-color: var(--danger-light); color: var(--danger-color);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">This action cannot be undone. This will permanently delete the expense.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="deleteExpenseForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="expense_id" id="delete_expense_id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Chart data - Make sure these variables are properly set
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
];

// Make sure we fetch the data and reset the pointer
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

// Add page-specific script
$page_scripts = "";

// Include footer
require_once 'includes/footer.php';
?>