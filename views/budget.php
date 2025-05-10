<?php
// Set page title and current page for menu highlighting
$page_title = 'Budget Management - iGotMoney';
$current_page = 'budget';

// Additional CSS for modern design
$additional_css = ['/assets/css/budget-modern.css'];

// Include header
require_once 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="h2 fw-bold">Budget Management</h1>
        <p class="text-muted mb-0">Track, allocate, and optimize your monthly budget with smart investments</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-outline-primary me-2" id="generateBudgetBtn" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
            <i class="fas fa-magic me-2"></i> Auto-Generate
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
            <i class="fas fa-plus me-2"></i> Add Budget
        </button>
    </div>
</div>

<!-- Budget Overview Cards -->
<div class="row mb-4 g-3">
    <!-- Budget Health Card -->
    <div class="col-lg-4">
        <?php
        $total_budget = 0;
        $total_spent = 0;
        $total_remaining = 0;
        $investment_budget = 0;
        $investment_spent = 0;
        
        if (!empty($budget_status)) {
            foreach ($budget_status as $budget) {
                $total_budget += $budget['budget_amount'];
                $total_spent += $budget['spent'];
                
                if ($budget['is_investment']) {
                    $investment_budget = $budget['budget_amount'];
                    $investment_spent = $budget['spent'];
                }
            }
            $total_remaining = $total_budget - $total_spent;
        }
        
        // Calculate budget health as percentage
        $budget_health = $total_budget > 0 ? (($total_budget - $total_spent) / $total_budget) * 100 : 0;
        
        // Determine health status based on remaining percentage
        if ($budget_health >= 50) {
            $health_class = 'success';
            $health_icon = 'check-circle';
            $health_status = 'good';
            $health_message = 'Your budget is doing great!';
        } elseif ($budget_health >= 30) {
            $health_class = 'warning';
            $health_icon = 'exclamation-triangle';
            $health_status = 'warning';
            $health_message = 'Your budget needs attention.';
        } else {
            $health_class = 'danger';
            $health_icon = 'exclamation-circle';
            $health_status = 'danger';
            $health_message = 'Warning: Your budget is nearly depleted!';
        }
        ?>
        <div class="card shadow-sm border-0 h-100 health-card" data-health="<?php echo $health_status; ?>">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-wrapper bg-soft-<?php echo $health_class; ?> me-3">
                        <i class="fas fa-<?php echo $health_icon; ?> text-<?php echo $health_class; ?>"></i>
                    </div>
                    <h5 class="card-title mb-0">Budget Health</h5>
                </div>
                
                <div class="gauge-wrapper position-relative mb-4">
                    <div class="gauge"></div>
                    <div class="gauge-value">
                        <h2 class="fw-bold text-<?php echo $health_class; ?>"><?php echo number_format($budget_health, 0); ?>%</h2>
                        <p class="text-muted">remaining budget</p>
                    </div>
                </div>
                
                <div class="row budget-stats text-center g-0">
                    <div class="col-4 budget-stat">
                        <p class="small text-muted mb-1">Total Budget</p>
                        <h5 class="text-primary mb-0">$<?php echo number_format($total_budget, 0); ?></h5>
                    </div>
                    <div class="col-4 budget-stat">
                        <p class="small text-muted mb-1">Spent</p>
                        <h5 class="text-danger mb-0">$<?php echo number_format($total_spent, 0); ?></h5>
                    </div>
                    <div class="col-4 budget-stat">
                        <p class="small text-muted mb-1">Remaining</p>
                        <h5 class="text-success mb-0">$<?php echo number_format($total_remaining, 0); ?></h5>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <p class="text-<?php echo $health_class; ?> fw-medium mb-0">
                        <?php echo $health_message; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Investment Focus Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-wrapper bg-soft-info me-3">
                        <i class="fas fa-coins text-info"></i>
                    </div>
                    <h5 class="card-title mb-0">Monthly Investments</h5>
                </div>
                
                <?php if ($investment_budget > 0): ?>
                    <div class="investment-progress mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Investment Progress</span>
                            <span class="fw-medium"><?php echo number_format(($investment_spent / $investment_budget) * 100, 0); ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: <?php echo min(100, ($investment_spent / $investment_budget) * 100); ?>%" 
                                 aria-valuenow="<?php echo ($investment_spent / $investment_budget) * 100; ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Allocated</p>
                            <h4 class="text-info mb-0">$<?php echo number_format($investment_budget, 2); ?></h4>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Invested</p>
                            <h4 class="text-success mb-0">$<?php echo number_format($investment_spent, 2); ?></h4>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <?php if ($investment_spent < $investment_budget): ?>
                            <p class="text-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                $<?php echo number_format($investment_budget - $investment_spent, 2); ?> left to invest this month
                            </p>
                        <?php else: ?>
                            <p class="text-success mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                Investment goal reached!
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <p class="text-muted mb-3">No investment budget set</p>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addBudgetModal" 
                                onclick="selectInvestmentCategory()">
                            <i class="fas fa-plus me-1"></i> Add Investment Budget
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Budget vs. Actual Chart Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Budget Analysis
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 250px;">
                    <canvas id="budgetDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Budget Categories Status -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="fas fa-tasks text-primary me-2"></i>
            Budget Categories Status
        </h5>
        <div class="search-container">
            <span class="input-group-text">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" class="form-control" 
                   placeholder="Search categories..." 
                   id="categorySearch">
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($budget_status)): ?>
            <div class="text-center py-5 empty-state">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h4 class="mb-2">No budgets defined yet</h4>
                <p class="text-muted mb-4">Start by creating a budget for each expense category.</p>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                        <i class="fas fa-plus me-2"></i> Add Your First Budget
                    </button>
                    <span class="mx-1">or</span>
                    <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                        <i class="fas fa-magic me-2"></i> Auto-Generate Budget
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle budget-table mb-0" id="budgetCategoriesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Category</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Remaining</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budget_status as $budget): ?>
                            <tr class="<?php echo $budget['is_investment'] ? 'table-info' : ''; ?>">
                                <td class="ps-4 fw-medium">
                                    <?php if ($budget['is_investment']): ?>
                                        <i class="fas fa-coins text-info me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($budget['category_name']); ?>
                                </td>
                                <td class="budget-amount">
                                    $<?php echo number_format($budget['budget_amount'], 2); ?>
                                </td>
                                <td class="spent-amount">
                                    $<?php echo number_format($budget['spent'], 2); ?>
                                </td>
                                <td class="remaining-amount">
                                    <span class="<?php echo $budget['available'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                        $<?php echo number_format($budget['available'], 2); ?>
                                    </span>
                                </td>
                                <td style="width: 20%;">
                                    <?php
                                    $progress_class = 'bg-success';
                                    if ($budget['percentage'] >= 90) {
                                        $progress_class = 'bg-danger';
                                    } elseif ($budget['percentage'] >= 70) {
                                        $progress_class = 'bg-warning';
                                    }
                                    
                                    if ($budget['is_investment']) {
                                        $progress_class = 'bg-info';
                                    }
                                    ?>
                                    <div class="progress budget-progress">
                                        <div class="progress-bar <?php echo $progress_class; ?>" 
                                            role="progressbar" 
                                            style="width: <?php echo min(100, $budget['percentage']); ?>%" 
                                            aria-valuenow="<?php echo $budget['percentage']; ?>" 
                                            aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($budget['percentage'], 0); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    if ($budget['is_investment']) {
                                        if ($budget['percentage'] >= 100) {
                                            echo '<span class="badge bg-soft-success text-success">On Track</span>';
                                        } else {
                                            echo '<span class="badge bg-soft-warning text-warning">Invest More</span>';
                                        }
                                    } else {
                                        if ($budget['percentage'] >= 90) {
                                            echo '<span class="badge bg-soft-danger text-danger">Critical</span>';
                                        } elseif ($budget['percentage'] >= 70) {
                                            echo '<span class="badge bg-soft-warning text-warning">Warning</span>';
                                        } else {
                                            echo '<span class="badge bg-soft-success text-success">Good</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="budget-actions">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-budget" 
                                                data-budget-id="<?php echo $budget['budget_id']; ?>" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-budget" 
                                                data-budget-id="<?php echo $budget['budget_id']; ?>" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="fas fa-lightbulb text-primary me-2"></i>
            Smart Budget Recommendations
        </h5>
        <button class="btn btn-sm btn-success" id="adoptAllRecommendations">
            <i class="fas fa-check me-1"></i> Adopt All Recommendations
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-3 fs-4"></i>
            <div>
                Based on your income of <strong>$<?php echo number_format($monthly_income, 2); ?></strong> per month, 
                we recommend prioritizing <strong>investments</strong> and optimizing your budget allocation.
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle recommendations-table">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Recommended Budget</th>
                        <th>Percentage of Income</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budget_plan as $recommendation): ?>
                        <tr class="<?php echo $recommendation['is_investment'] ? 'table-info' : ''; ?>">
                            <td class="fw-medium">
                                <?php if ($recommendation['is_investment']): ?>
                                    <i class="fas fa-coins text-info me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($recommendation['category_name']); ?>
                            </td>
                            <td>
                                $<?php echo number_format($recommendation['allocated_amount'], 2); ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar <?php echo $recommendation['is_investment'] ? 'bg-info' : 'bg-primary'; ?>" 
                                            role="progressbar" 
                                            style="width: <?php echo $recommendation['percentage']; ?>%" 
                                            aria-valuenow="<?php echo $recommendation['percentage']; ?>" 
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-muted small"><?php echo number_format($recommendation['percentage'], 1); ?>%</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-success adopt-recommendation" 
                                        data-category-id="<?php echo $recommendation['category_id']; ?>"
                                        data-amount="<?php echo $recommendation['allocated_amount']; ?>">
                                    <i class="fas fa-check me-1"></i> Adopt
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
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-0 py-3">
        <h5 class="mb-0">
            <i class="fas fa-lightbulb text-primary me-2"></i>
            Budget Recommendations
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
            <div>
                You need to add income sources before we can generate budget recommendations.
                <a href="<?php echo BASE_PATH; ?>/income" class="alert-link">Go to Income Management</a> to add your income sources.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modals -->
<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addBudgetModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Budget
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="addBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Check if categories exist
                            if (isset($categories) && $categories->num_rows > 0) {
                                // Reset the categories result pointer
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                        <?php echo ($category['name'] === 'Investments') ? 'class="text-info fw-medium"' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                </option>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Budget Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">Please select a start date.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                            <div class="invalid-feedback">Please select an end date.</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div class="modal fade" id="editBudgetModal" tabindex="-1" aria-labelledby="editBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="editBudgetModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Budget
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="editBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="edit_budget_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php 
                            // Check if categories exist
                            if (isset($categories) && $categories->num_rows > 0) {
                                // Reset the categories result pointer
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                        <?php echo ($category['name'] === 'Investments') ? 'class="text-info fw-medium"' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                </option>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Budget Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            <div class="invalid-feedback">Please select a start date.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                            <div class="invalid-feedback">Please select an end date.</div>
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

<!-- Delete Budget Modal -->
<div class="modal fade" id="deleteBudgetModal" tabindex="-1" aria-labelledby="deleteBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBudgetModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Delete Budget
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="delete-icon mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">This action cannot be undone. This will permanently delete the budget.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="deleteBudgetForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="budget_id" id="delete_budget_id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Generate Budget Modal -->
<div class="modal fade" id="generateBudgetModal" tabindex="-1" aria-labelledby="generateBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="generateBudgetModalLabel">
                    <i class="fas fa-magic me-2"></i>Auto-Generate Budget
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="generateBudgetForm">
                <input type="hidden" name="action" value="generate_plan">
                <input type="hidden" name="replace_existing" value="1">
                
                <div class="modal-body">
                    <?php if (isset($monthly_income) && $monthly_income > 0): ?>
                        <div class="alert alert-info d-flex">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                The system will generate a budget optimized for your financial growth, prioritizing investments.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-medium">Your current monthly income:</span>
                                <span class="badge bg-primary p-2 fs-6">$<?php echo number_format($monthly_income, 2); ?></span>
                            </div>
                            
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Budget will be created with:</h6>
                                    <ul class="mb-0">
                                        <li><strong>10% minimum for investments</strong></li>
                                        <li>Smart allocation based on spending patterns</li>
                                        <li>Optimal financial ratios for wealth building</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning d-flex">
                            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                            <div>
                                This will replace any existing budgets for the current month.
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger d-flex">
                            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                            <div>
                                You need to add income sources before generating a budget plan.
                                <a href="<?php echo BASE_PATH; ?>/income" class="alert-link">Go to Income Management</a> to add your income sources.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" <?php echo (isset($monthly_income) && $monthly_income > 0) ? '' : 'disabled'; ?>>
                        <i class="fas fa-magic me-1"></i> Generate Budget
                    </button>
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
        $categories[] = $budget['category_name'] . ($budget['is_investment'] ? ' 💎' : '');
        $budget_amounts[] = $budget['budget_amount'];
        $spent_amounts[] = $budget['spent'];
    }
}

// Add BASE_PATH as a global JavaScript variable to use in AJAX requests
echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';

// Pass chart data to JS
echo '<script>
// Wait for both DOM and all scripts to load
window.addEventListener("load", function() {
    const budgetData = ' . json_encode($budget_amounts) . ';
    const spentData = ' . json_encode($spent_amounts) . ';
    const categoryLabels = ' . json_encode($categories) . ';
    
    // Initialize charts after all scripts are loaded
    if (typeof window.initializeBudgetCharts === "function" && categoryLabels.length > 0) {
        // Small delay to ensure Chart.js is fully initialized
        setTimeout(function() {
            window.initializeBudgetCharts(budgetData, spentData, categoryLabels);
        }, 100);
    }
});
</script>';

// Additional JS
$additional_js = ['/assets/js/budget.js'];

// Include footer
require_once 'includes/footer.php';
?>