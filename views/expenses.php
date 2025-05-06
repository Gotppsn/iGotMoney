<?php
// Set page title and current page for menu highlighting
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

// Additional JS
$additional_js = ['/assets/js/chart.js', '/assets/js/expenses.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Expense Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fas fa-plus"></i> Add Expense
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-danger shadow h-100 py-2 dashboard-card expenses">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Monthly Expenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_expenses, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-warning shadow h-100 py-2 dashboard-card expenses">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Annual Expenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($yearly_expenses, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense Categories Chart -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Expenses by Category</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar-alt me-1"></i> This Month
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="chartPeriodDropdown">
                        <li><a class="dropdown-item chart-period" href="#" data-period="current-month">This Month</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="last-month">Last Month</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="quarter">This Quarter</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="last-3-months">Last 3 Months</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="current-year">This Year</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="all">All Time</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
                <div id="chartNoData" class="text-center py-4" style="display: <?php echo (isset($top_expenses) && $top_expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                    <p>No expense data available for the selected period.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top Expenses</h6>
            </div>
            <div class="card-body">
                <div id="topExpensesContent">
                    <?php if (isset($top_expenses) && $top_expenses->num_rows > 0): ?>
                        <?php while ($category = $top_expenses->fetch_assoc()): ?>
                            <h4 class="small font-weight-bold">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                                <span class="float-end">$<?php echo number_format($category['total'], 2); ?></span>
                            </h4>
                            <div class="progress mb-4">
                                <?php 
                                $percentage = ($category['total'] / $monthly_expenses) * 100;
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
                                    style="width: <?php echo min(100, $percentage); ?>%" 
                                    aria-valuenow="<?php echo $percentage; ?>" 
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No expense data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Your Expenses</h6>
        <div class="d-flex">
            <div class="input-group input-group-sm me-2" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search expenses..." id="expenseSearch" data-table-search="expenseTable">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dateFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar me-1"></i> Filter
                </button>
                <div class="dropdown-menu p-3" style="width: 300px;">
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="expenseTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Frequency</th>
                        <th>Recurring</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($expenses) && $expenses->num_rows > 0): ?>
                        <?php while ($expense = $expenses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                                <td>$<?php echo number_format($expense['amount'], 2); ?></td>
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
                                    <button type="button" class="btn btn-sm btn-info edit-expense" data-expense-id="<?php echo $expense['expense_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-expense" data-expense-id="<?php echo $expense['expense_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="tableNoData" class="text-center py-4" style="display: <?php echo (isset($expenses) && $expenses->num_rows > 0) ? 'none' : 'block'; ?>">
            <p>No expenses recorded yet.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus"></i> Add Your First Expense
            </button>
        </div>
    </div>
</div>

<!-- Expense Analytics -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Expense Analytics</h6>
        <button class="btn btn-sm btn-outline-primary" id="calculateAnalytics">
            <i class="fas fa-calculator me-1"></i> Calculate
        </button>
    </div>
    <div class="card-body">
        <div class="row" id="analyticsContent" style="display: none;">
            <!-- Analytics content will be loaded here -->
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="addExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
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
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                        <div class="invalid-feedback">Please provide a description.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    
                    <div class="mb-3 form-check">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="editExpenseForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="expense_id" id="edit_expense_id">
                
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Expense Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExpenseModalLabel">Delete Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Deleting this expense will also remove it from your financial calculations and reports.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/expenses" method="post" id="deleteExpenseForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="expense_id" id="delete_expense_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
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
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
];

if (isset($top_expenses) && $top_expenses->num_rows > 0) {
    $top_expenses->data_seek(0);
    while ($category = $top_expenses->fetch_assoc()) {
        $chart_labels[] = $category['category_name'];
        $chart_data[] = $category['total'];
    }
}

// Add meta tag for base path
echo '<meta name="base-path" content="' . BASE_PATH . '">';

// Add hidden meta tags for passing chart data
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';

// Add page-specific script for Chart.js initialization
$page_scripts = "
// Add Chart.js global defaults
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = '\"Roboto\", \"Helvetica Neue\", Arial, sans-serif';
    Chart.defaults.color = '#5a5c69';
    Chart.defaults.responsive = true;
}
";

// Include footer
require_once 'includes/footer.php';
?>