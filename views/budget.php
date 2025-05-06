<?php
// Set page title and current page for menu highlighting
$page_title = 'Budget Management - iGotMoney';
$current_page = 'budget';

// Additional JS
$additional_js = ['/assets/js/budget.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Budget Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                <i class="fas fa-magic"></i> Auto-Generate Budget
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
            <i class="fas fa-plus"></i> Add Budget
        </button>
    </div>
</div>

<!-- Budget Overview -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Budget Summary</h6>
            </div>
            <div class="card-body">
                <?php
                $total_budget = 0;
                $total_spent = 0;
                $total_remaining = 0;
                
                if (!empty($budget_status)) {
                    foreach ($budget_status as $budget) {
                        $total_budget += $budget['budget_amount'];
                        $total_spent += $budget['spent'];
                    }
                    $total_remaining = $total_budget - $total_spent;
                }
                
                // Calculate budget health as percentage
                $budget_health = $total_budget > 0 ? (($total_budget - $total_spent) / $total_budget) * 100 : 0;
                $health_class = 'text-success';
                $health_icon = 'check-circle';
                
                if ($budget_health < 30) {
                    $health_class = 'text-danger';
                    $health_icon = 'exclamation-circle';
                } elseif ($budget_health < 50) {
                    $health_class = 'text-warning';
                    $health_icon = 'exclamation-triangle';
                }
                ?>
                
                <div class="text-center mb-4">
                    <h4 class="<?php echo $health_class; ?>">
                        <i class="fas fa-<?php echo $health_icon; ?> me-2"></i>
                        <?php echo number_format($budget_health, 0); ?>% Budget Health
                    </h4>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 text-center">
                        <h5>Total Budget</h5>
                        <h3 class="text-primary">$<?php echo number_format($total_budget, 2); ?></h3>
                    </div>
                    <div class="col-md-4 text-center">
                        <h5>Spent</h5>
                        <h3 class="text-danger">$<?php echo number_format($total_spent, 2); ?></h3>
                    </div>
                    <div class="col-md-4 text-center">
                        <h5>Remaining</h5>
                        <h3 class="text-success">$<?php echo number_format($total_remaining, 2); ?></h3>
                    </div>
                </div>
                
                <div class="progress mb-2">
                    <?php
                    $spent_percentage = $total_budget > 0 ? min(100, ($total_spent / $total_budget) * 100) : 0;
                    $progress_class = 'bg-success';
                    
                    if ($spent_percentage >= 90) {
                        $progress_class = 'bg-danger';
                    } elseif ($spent_percentage >= 70) {
                        $progress_class = 'bg-warning';
                    }
                    ?>
                    <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                        style="width: <?php echo $spent_percentage; ?>%" 
                        aria-valuenow="<?php echo $spent_percentage; ?>" 
                        aria-valuemin="0" aria-valuemax="100">
                        <?php echo number_format($spent_percentage, 0); ?>%
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="mb-0">
                        <?php if ($total_budget === 0): ?>
                            <span class="text-muted">No budgets defined yet.</span>
                        <?php else: ?>
                            <span class="<?php echo $health_class; ?>">
                                <?php
                                if ($budget_health >= 70) {
                                    echo 'Your budget is doing great!';
                                } elseif ($budget_health >= 50) {
                                    echo 'Your budget is on track.';
                                } elseif ($budget_health >= 30) {
                                    echo 'Your budget needs attention.';
                                } else {
                                    echo 'Warning: Your budget is nearly depleted!';
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Budget vs. Actual</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="budgetVsActualChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Budget Categories Status -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Budget Categories Status</h6>
    </div>
    <div class="card-body">
        <?php if (empty($budget_status)): ?>
            <div class="text-center py-4">
                <p>No budgets defined yet. Start by creating a budget for each expense category.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus"></i> Add Your First Budget
                </button>
                <span class="mx-2">or</span>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                    <i class="fas fa-magic"></i> Auto-Generate Budget
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Remaining</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budget_status as $budget): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($budget['category_name']); ?></td>
                                <td>$<?php echo number_format($budget['budget_amount'], 2); ?></td>
                                <td>$<?php echo number_format($budget['spent'], 2); ?></td>
                                <td>$<?php echo number_format($budget['available'], 2); ?></td>
                                <td>
                                    <div class="progress">
                                        <?php
                                        $progress_class = 'bg-success';
                                        if ($budget['percentage'] >= 90) {
                                            $progress_class = 'bg-danger';
                                        } elseif ($budget['percentage'] >= 70) {
                                            $progress_class = 'bg-warning';
                                        }
                                        ?>
                                        <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                            style="width: <?php echo min(100, $budget['percentage']); ?>%" 
                                            aria-valuenow="<?php echo $budget['percentage']; ?>" 
                                            aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($budget['percentage'], 0); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if ($budget['percentage'] >= 90) {
                                        echo '<span class="badge bg-danger">Critical</span>';
                                    } elseif ($budget['percentage'] >= 70) {
                                        echo '<span class="badge bg-warning">Warning</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Good</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info edit-budget" data-budget-id="<?php echo $budget['budget_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-budget" data-budget-id="<?php echo $budget['budget_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Budget Recommendations -->
<?php if (isset($monthly_income) && $monthly_income > 0 && isset($budget_plan) && !empty($budget_plan)): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Budget Recommendations</h6>
        <button class="btn btn-sm btn-success" id="adoptAllRecommendations">
            <i class="fas fa-check"></i> Adopt All Recommendations
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Based on your income of $<?php echo number_format($monthly_income, 2); ?> per month, here are some recommended budget allocations.
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Recommended Budget</th>
                        <th>Percentage of Income</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budget_plan as $recommendation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recommendation['category_name']); ?></td>
                            <td>$<?php echo number_format($recommendation['allocated_amount'], 2); ?></td>
                            <td><?php echo number_format($recommendation['percentage'], 1); ?>%</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success adopt-recommendation" 
                                        data-category-id="<?php echo $recommendation['category_id']; ?>"
                                        data-amount="<?php echo $recommendation['allocated_amount']; ?>">
                                    <i class="fas fa-check"></i> Adopt
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php elseif (isset($monthly_income) && $monthly_income <= 0): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Budget Recommendations</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            You need to add income sources before we can generate budget recommendations.
            <a href="<?php echo BASE_PATH; ?>/income" class="alert-link">Go to Income Management</a> to add your income sources.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBudgetModalLabel">Add Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Check if categories exist
                            if (isset($categories) && $categories->num_rows > 0) {
                                // Reset the categories result pointer
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
                        <label for="amount" class="form-label">Budget Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">Please select a start date.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                        <div class="invalid-feedback">Please select an end date.</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div class="modal fade" id="editBudgetModal" tabindex="-1" aria-labelledby="editBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBudgetModalLabel">Edit Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="edit_budget_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <?php 
                            // Check if categories exist
                            if (isset($categories) && $categories->num_rows > 0) {
                                // Reset the categories result pointer
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
                        <label for="edit_amount" class="form-label">Budget Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        <div class="invalid-feedback">Please select a start date.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        <div class="invalid-feedback">Please select an end date.</div>
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

<!-- Delete Budget Modal -->
<div class="modal fade" id="deleteBudgetModal" tabindex="-1" aria-labelledby="deleteBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBudgetModalLabel">Delete Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this budget? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/budget" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="budget_id" id="delete_budget_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Generate Budget Modal -->
<div class="modal fade" id="generateBudgetModal" tabindex="-1" aria-labelledby="generateBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateBudgetModalLabel">Auto-Generate Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post">
                <input type="hidden" name="action" value="generate_plan">
                <input type="hidden" name="replace_existing" value="1">
                
                <div class="modal-body">
                    <?php if (isset($monthly_income) && $monthly_income > 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            The system will generate a recommended budget based on your income and spending patterns.
                        </div>
                        
                        <p>Your current monthly income: <strong>$<?php echo number_format($monthly_income, 2); ?></strong></p>
                        
                        <p>This will create budget allocations for all expense categories using:</p>
                        <ul>
                            <li>Your income level</li>
                            <li>Your previous spending patterns</li>
                            <li>Recommended financial ratios</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will replace any existing budgets for the current month.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You need to add income sources before generating a budget plan.
                            <a href="<?php echo BASE_PATH; ?>/income" class="alert-link">Go to Income Management</a> to add your income sources.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" <?php echo (isset($monthly_income) && $monthly_income > 0) ? '' : 'disabled'; ?>>Generate Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Chart data
$categories = [];
$budget_amounts = [];
$spent_amounts = [];

if (!empty($budget_status)) {
    foreach ($budget_status as $budget) {
        $categories[] = $budget['category_name'];
        $budget_amounts[] = $budget['budget_amount'];
        $spent_amounts[] = $budget['spent'];
    }
}

// Include Chart.js library directly
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>';

// JavaScript for budget page
$page_scripts = "
// Initialize the chart for budget vs actual when document is ready and Chart.js is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make sure Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Please include it in your page.');
        // Add fallback message in chart container
        var chartContainer = document.querySelector('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class=\"alert alert-warning\">Chart cannot be displayed: Chart.js library not available</div>';
        }
        return;
    }
    
    var ctx = document.getElementById('budgetVsActualChart');
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }
    
    try {
        var budgetVsActualChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($categories) . ",
                datasets: [
                    {
                        label: 'Budget',
                        data: " . json_encode($budget_amounts) . ",
                        backgroundColor: 'rgba(78, 115, 223, 0.8)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Spent',
                        data: " . json_encode($spent_amounts) . ",
                        backgroundColor: 'rgba(231, 74, 59, 0.8)',
                        borderColor: 'rgba(231, 74, 59, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.raw || 0;
                                return label + ': $' + value.toLocaleString();
                            }
                        }
                    }
                },
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }
        });
        console.log('Chart initialized successfully');
    } catch (e) {
        console.error('Error initializing chart:', e);
        // Display error in chart container
        var chartContainer = document.querySelector('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class=\"alert alert-danger\">Error initializing chart: ' + e.message + '</div>';
        }
    }
});";

// Include footer
require_once 'includes/footer.php';
?>