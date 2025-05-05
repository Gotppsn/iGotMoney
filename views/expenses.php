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

<!-- Expense Categories Chart -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Expenses by Category</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="expenseCategoryChart"></canvas>
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

<!-- Expenses Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Your Expenses</h6>
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
                            <th>Frequency</th>
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
                                    <?php 
                                    $frequency = ucfirst(str_replace('-', ' ', $expense['frequency']));
                                    echo $frequency; 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($expense['is_recurring']): ?>
                                        <span class="badge bg-info">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
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
                <p>No expenses recorded yet.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus"></i> Add Your First Expense
                </button>
            </div>
        <?php endif; ?>
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
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php 
                            // Reset the categories result pointer
                            if ($categories->num_rows > 0) {
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
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
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
                                <option value="one-time">One Time</option>
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
            <form action="<?php echo BASE_PATH; ?>/expenses" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="expense_id" id="edit_expense_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <?php 
                            // Reset the categories result pointer
                            if ($categories->num_rows > 0) {
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
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="edit_description" name="description" required>
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
                                <option value="one-time">One Time</option>
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
                <form action="<?php echo BASE_PATH; ?>/expenses" method="post">
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

// JavaScript for expense page
$page_scripts = "
// Initialize the pie chart for expense categories
var ctx = document.getElementById('expenseCategoryChart').getContext('2d');
var expenseCategoryChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: " . json_encode($chart_labels) . ",
        datasets: [{
            data: " . json_encode($chart_data) . ",
            backgroundColor: " . json_encode(array_slice($chart_colors, 0, count($chart_data))) . ",
            hoverBackgroundColor: " . json_encode(array_map(function($color) {
                return adjustColor($color, -20);
            }, array_slice($chart_colors, 0, count($chart_data)))) . ",
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
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
        cutout: '60%',
    }
});

// Toggle recurring options when checkbox is clicked
document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurring_options').style.display = this.checked ? 'block' : 'none';
    
    // Set default frequency for one-time expenses
    if (!this.checked) {
        document.getElementById('frequency').value = 'one-time';
    } else {
        document.getElementById('frequency').value = 'monthly';
    }
});

// Toggle edit recurring options when checkbox is clicked
document.getElementById('edit_is_recurring').addEventListener('change', function() {
    document.getElementById('edit_recurring_options').style.display = this.checked ? 'block' : 'none';
    
    // Set default frequency for one-time expenses
    if (!this.checked) {
        document.getElementById('edit_frequency').value = 'one-time';
    }
});

// Handle edit expense button
document.querySelectorAll('.edit-expense').forEach(button => {
    button.addEventListener('click', function() {
        const expenseId = this.getAttribute('data-expense-id');
        
        // Show loading spinner
        const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
        modal.show();
        
        // Fetch expense data
        fetch('/expenses?action=get_expense&expense_id=' + expenseId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_expense_id').value = data.expense.expense_id;
                    document.getElementById('edit_category_id').value = data.expense.category_id;
                    document.getElementById('edit_description').value = data.expense.description;
                    document.getElementById('edit_amount').value = data.expense.amount;
                    document.getElementById('edit_expense_date').value = data.expense.expense_date;
                    document.getElementById('edit_is_recurring').checked = data.expense.is_recurring == 1;
                    document.getElementById('edit_frequency').value = data.expense.frequency;
                    
                    // Show/hide recurring options
                    document.getElementById('edit_recurring_options').style.display = 
                        data.expense.is_recurring == 1 ? 'block' : 'none';
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

// Search functionality for expense table
document.getElementById('expenseSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('expenseTable');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
";

// Function to adjust color brightness
function adjustColor($hex, $steps) {
    // Extract RGB values
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

// Include footer
require_once 'includes/footer.php';
?>