<?php
// Set page title and current page for menu highlighting
$page_title = 'Expense Management - iGotMoney';
$current_page = 'expenses';

// Additional JS
$additional_js = ['/assets/js/expenses.js'];

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

<!-- Expenses Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Expenses</h6>
        <div class="input-group input-group-sm" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search expenses..." id="expenseSearch" data-table-search="expenseTable">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($expenses) && $expenses->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered data-table" id="expenseTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Recurring</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($expense = $expenses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                                <td>$<?php echo number_format($expense['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                <td>
                                    <?php if ($expense['is_recurring']): ?>
                                        <span class="badge bg-info">
                                            <?php 
                                            $frequency = ucfirst(str_replace('-', ' ', $expense['frequency']));
                                            echo $frequency; 
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">One-time</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info edit-expense" data-expense-id="<?php echo $expense['expense_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-expense" data-expense-id="<?php echo $expense['expense_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p>No expenses found.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus"></i> Add Your First Expense
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Expense by Category Chart -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Expenses by Category</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">View Options:</div>
                        <a class="dropdown-item" href="#" data-period="month">This Month</a>
                        <a class="dropdown-item" href="#" data-period="quarter">This Quarter</a>
                        <a class="dropdown-item" href="#" data-period="year">This Year</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top Expense Categories</h6>
            </div>
            <div class="card-body">
                <?php 
                // Display top expense categories
                if (isset($top_expenses) && $top_expenses->num_rows > 0):
                    $top_expenses->data_seek(0); // Reset pointer
                    while ($expense = $top_expenses->fetch_assoc()):
                ?>
                <h4 class="small font-weight-bold">
                    <?php echo htmlspecialchars($expense['category_name']); ?>
                    <span class="float-end">$<?php echo number_format($expense['total'], 2); ?></span>
                </h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-<?php echo getProgressColor($expense['total'], $monthly_expenses); ?>" role="progressbar" 
                        style="width: <?php echo min(100, ($expense['total'] / $monthly_expenses) * 100); ?>%" 
                        aria-valuenow="<?php echo ($expense['total'] / $monthly_expenses) * 100; ?>" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <p>No expense data available.</p>
                <?php endif; ?>
            </div>
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
            <form action="/expenses" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php 
                            if (isset($categories) && $categories->num_rows > 0):
                                while ($category = $categories->fetch_assoc()):
                            ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
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
            <form action="/expenses" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="expense_id" id="edit_expense_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="edit_description" name="description" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <?php 
                            if (isset($categories) && $categories->num_rows > 0):
                                $categories->data_seek(0); // Reset pointer
                                while ($category = $categories->fetch_assoc()):
                            ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_expense_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                    </div>
                    
                    <div class="mb-3 form-check">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/expenses" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="expense_id" id="delete_expense_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to get progress bar color
function getProgressColor($amount, $total) {
    $percentage = ($amount / $total) * 100;
    if ($percentage < 25) {
        return 'info';
    } elseif ($percentage < 50) {
        return 'success';
    } elseif ($percentage < 75) {
        return 'warning';
    } else {
        return 'danger';
    }
}

// JavaScript for expenses page
$page_scripts = "
// Initialize Chart
var ctx = document.getElementById('expenseCategoryChart').getContext('2d');
var expenseCategoryChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: [" . (isset($top_expenses) && $top_expenses->num_rows > 0 ? 
                    $top_expenses->data_seek(0) && 
                    implode(',', array_map(function($row) { 
                        return \"'\" . addslashes($row['category_name']) . \"'\"; 
                    }, iterator_to_array($top_expenses)))
                    : '') . "],
        datasets: [{
            data: [" . (isset($top_expenses) && $top_expenses->num_rows > 0 ? 
                    $top_expenses->data_seek(0) && 
                    implode(',', array_map(function($row) { 
                        return $row['total']; 
                    }, iterator_to_array($top_expenses)))
                    : '') . "],
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
            ],
            hoverBackgroundColor: [
                '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617',
                '#5a32a3', '#db6a02', '#169b7f', '#3a3b45', '#60616f'
            ],
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }],
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        var value = context.raw || 0;
                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                        var percentage = Math.round((value / total) * 100);
                        return label + ': $' + value.toFixed(2) + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '70%',
    },
});

// Toggle recurring options
document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurring_options').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('edit_is_recurring').addEventListener('change', function() {
    document.getElementById('edit_recurring_options').style.display = this.checked ? 'block' : 'none';
});

// Handle edit expense button
document.querySelectorAll('.edit-expense').forEach(button => {
    button.addEventListener('click', function() {
        const expenseId = this.getAttribute('data-expense-id');
        
        // Show loading spinner
        showSpinner(document.querySelector('#editExpenseModal .modal-body'));
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
        modal.show();
        
        // Fetch expense data
        fetch('/expenses?action=get_expense&expense_id=' + expenseId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_expense_id').value = data.expense.expense_id;
                    document.getElementById('edit_description').value = data.expense.description;
                    document.getElementById('edit_category_id').value = data.expense.category_id;
                    document.getElementById('edit_amount').value = data.expense.amount;
                    document.getElementById('edit_expense_date').value = data.expense.expense_date;
                    document.getElementById('edit_is_recurring').checked = data.expense.is_recurring == 1;
                    
                    // Show/hide recurring options
                    document.getElementById('edit_recurring_options').style.display = data.expense.is_recurring == 1 ? 'block' : 'none';
                    
                    if (data.expense.is_recurring == 1) {
                        document.getElementById('edit_frequency').value = data.expense.frequency;
                    }
                    
                    // Remove spinner
                    hideSpinner(document.querySelector('#editExpenseModal .modal-body'));
                } else {
                    // Show error
                    alert('Failed to load expense data: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error fetching expense data:', error);
                alert('An error occurred while loading expense data.');
                modal.hide();
            });
    });
});

// Handle delete expense button
document.querySelectorAll('.delete-expense').forEach(button => {
    button.addEventListener('click', function() {
        const expenseId = this.getAttribute('data-expense-id');
        document.getElementById('delete_expense_id').value = expenseId;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
        modal.show();
    });
});

// Handle period selection for chart
document.querySelectorAll('.dropdown-item[data-period]').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const period = this.getAttribute('data-period');
        
        // In a real app, this would fetch data for the selected period
        // For now, we'll just show a message
        alert('Chart would be updated to show ' + period + ' data');
    });
});
";

// Include footer
require_once 'includes/footer.php';
?>