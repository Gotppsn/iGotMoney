<?php
// Set page title and current page for menu highlighting
$page_title = 'Budget Management - iGotMoney';
$current_page = 'budget';

// Additional CSS for modern design
$additional_css = ['/assets/css/budget-modern.css'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="budget-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Budget Management</h1>
                <p class="page-subtitle">Plan, track, and optimize your financial future with smart budgeting</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn-generate-budget" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                    <i class="fas fa-magic"></i>
                    <span>Auto-Generate</span>
                </button>
                <button type="button" class="btn-add-budget" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Budget</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats-section">
        <div class="stats-grid">
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
            
            $budget_health = $total_budget > 0 ? (($total_budget - $total_spent) / $total_budget) * 100 : 0;
            ?>
            
            <div class="stat-card overview">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Total Budget</h3>
                    <p class="stat-value">$<?php echo number_format($total_budget, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-calendar"></i>
                        <span>This Month</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card health">
                <div class="stat-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Budget Health</h3>
                    <p class="stat-value"><?php echo number_format($budget_health, 0); ?>%</p>
                    <div class="stat-trend <?php echo $budget_health >= 50 ? 'positive' : ($budget_health >= 30 ? 'warning' : 'negative'); ?>">
                        <i class="fas fa-<?php echo $budget_health >= 50 ? 'check-circle' : ($budget_health >= 30 ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                        <span><?php echo $budget_health >= 50 ? 'Healthy' : ($budget_health >= 30 ? 'Needs Attention' : 'Critical'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card investment">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Investment Budget</h3>
                    <p class="stat-value">$<?php echo number_format($investment_budget, 2); ?></p>
                    <?php if ($investment_budget > 0): ?>
                        <div class="stat-trend <?php echo $investment_spent >= $investment_budget ? 'positive' : 'warning'; ?>">
                            <i class="fas fa-<?php echo $investment_spent >= $investment_budget ? 'check' : 'arrow-up'; ?>"></i>
                            <span><?php echo $investment_spent >= $investment_budget ? 'Goal Reached' : '$' . number_format($investment_budget - $investment_spent, 2) . ' remaining'; ?></span>
                        </div>
                    <?php else: ?>
                        <div class="stat-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Not set</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-grid">
            <!-- Budget Overview Chart -->
            <div class="chart-card budget-overview">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Budget Overview</h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="budget-gauge-container">
                        <div class="gauge-wrapper">
                            <div class="modern-gauge" data-percentage="<?php echo $budget_health; ?>">
                                <svg viewBox="0 0 200 100" class="gauge-svg">
                                    <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="20" stroke-linecap="round"/>
                                    <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="currentColor" stroke-width="20" stroke-linecap="round" 
                                          stroke-dasharray="251.2" stroke-dashoffset="<?php echo 251.2 * (1 - $budget_health / 100); ?>"
                                          class="gauge-progress"/>
                                </svg>
                                <div class="gauge-center">
                                    <span class="gauge-value"><?php echo number_format($budget_health, 0); ?>%</span>
                                    <span class="gauge-label">Remaining</span>
                                </div>
                            </div>
                        </div>
                        <div class="budget-breakdown">
                            <div class="breakdown-item">
                                <span class="breakdown-label">Total Budget</span>
                                <span class="breakdown-value">$<?php echo number_format($total_budget, 2); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Spent</span>
                                <span class="breakdown-value text-danger">$<?php echo number_format($total_spent, 2); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Remaining</span>
                                <span class="breakdown-value text-success">$<?php echo number_format($total_remaining, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Category Distribution -->
            <div class="chart-card category-distribution">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-list-ol"></i>
                        <h3>Top Budget Categories</h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="categories-list-content">
                        <?php if (!empty($budget_status)): ?>
                            <?php 
                            // Sort budgets by amount
                            usort($budget_status, function($a, $b) {
                                return $b['budget_amount'] - $a['budget_amount'];
                            });
                            
                            $rank = 1;
                            foreach (array_slice($budget_status, 0, 5) as $budget): 
                                $percentage = ($budget['spent'] / max(0.01, $budget['budget_amount'])) * 100;
                            ?>
                                <div class="category-item">
                                    <div class="category-rank"><?php echo $rank++; ?></div>
                                    <div class="category-info">
                                        <h4 class="category-name">
                                            <?php echo htmlspecialchars($budget['category_name']); ?>
                                            <?php if ($budget['is_investment']): ?>
                                                <i class="fas fa-gem text-info ms-1"></i>
                                            <?php endif; ?>
                                        </h4>
                                        <div class="category-progress">
                                            <div class="progress-track">
                                                <div class="progress-fill <?php echo $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success'); ?>" 
                                                     style="width: <?php echo min(100, $percentage); ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo number_format($percentage, 0); ?>%</span>
                                        </div>
                                    </div>
                                    <div class="category-amount">
                                        <span class="amount">$<?php echo number_format($budget['budget_amount'], 2); ?></span>
                                        <span class="spent">$<?php echo number_format($budget['spent'], 2); ?> spent</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-categories">
                                <i class="fas fa-folder-open"></i>
                                <p>No budgets created yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Categories Table Section -->
    <div class="budget-table-section">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-tasks"></i>
                    <h3>Budget Categories</h3>
                </div>
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="categorySearch" placeholder="Search categories..." data-table-search="budgetTable">
                    </div>
                </div>
            </div>
            <div class="table-body">
                <?php if (empty($budget_status)): ?>
                    <div class="table-empty">
                        <div class="empty-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4>No budgets defined yet</h4>
                        <p>Start by creating a budget for each expense category.</p>
                        <div class="empty-actions">
                            <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                <i class="fas fa-plus"></i>
                                Add Your First Budget
                            </button>
                            <span class="action-separator">or</span>
                            <button type="button" class="btn-generate-first" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                                <i class="fas fa-magic"></i>
                                Auto-Generate Budget
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="budget-table" id="budgetTable">
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
                                    <tr class="<?php echo $budget['is_investment'] ? 'investment-row' : ''; ?>">
                                        <td class="category-cell">
                                            <span class="category-text">
                                                <?php if ($budget['is_investment']): ?>
                                                    <i class="fas fa-gem text-info me-2"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($budget['category_name']); ?>
                                            </span>
                                        </td>
                                        <td class="amount-cell">$<?php echo number_format($budget['budget_amount'], 2); ?></td>
                                        <td class="amount-cell">$<?php echo number_format($budget['spent'], 2); ?></td>
                                        <td class="amount-cell">
                                            <span class="<?php echo $budget['available'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                                $<?php echo number_format($budget['available'], 2); ?>
                                            </span>
                                        </td>
                                        <td class="progress-cell">
                                            <?php
                                            $progress_class = 'success';
                                            if ($budget['percentage'] >= 90) {
                                                $progress_class = 'danger';
                                            } elseif ($budget['percentage'] >= 70) {
                                                $progress_class = 'warning';
                                            }
                                            
                                            if ($budget['is_investment']) {
                                                $progress_class = 'info';
                                            }
                                            ?>
                                            <div class="budget-progress">
                                                <div class="progress-bar <?php echo $progress_class; ?>" 
                                                     style="width: <?php echo min(100, $budget['percentage']); ?>%"></div>
                                                <span class="progress-label"><?php echo number_format($budget['percentage'], 0); ?>%</span>
                                            </div>
                                        </td>
                                        <td class="status-cell">
                                            <?php
                                            if ($budget['is_investment']) {
                                                if ($budget['percentage'] >= 100) {
                                                    echo '<span class="status-badge success">On Track</span>';
                                                } else {
                                                    echo '<span class="status-badge warning">Invest More</span>';
                                                }
                                            } else {
                                                if ($budget['percentage'] >= 90) {
                                                    echo '<span class="status-badge danger">Critical</span>';
                                                } elseif ($budget['percentage'] >= 70) {
                                                    echo '<span class="status-badge warning">Warning</span>';
                                                } else {
                                                    echo '<span class="status-badge success">Good</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn-action edit" data-budget-id="<?php echo $budget['budget_id']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action delete" data-budget-id="<?php echo $budget['budget_id']; ?>" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
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
    </div>

    <!-- Budget Recommendations -->
    <?php if (isset($monthly_income) && $monthly_income > 0 && isset($budget_plan) && !empty($budget_plan)): ?>
    <div class="recommendations-section">
        <div class="recommendations-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Smart Budget Recommendations</h3>
                </div>
                <button class="btn-adopt-all" id="adoptAllRecommendations">
                    <i class="fas fa-check"></i>
                    Adopt All
                </button>
            </div>
            <div class="card-body">
                <div class="recommendation-info">
                    <i class="fas fa-info-circle"></i>
                    <p>Based on your income of <strong>$<?php echo number_format($monthly_income, 2); ?></strong> per month, we recommend prioritizing <strong>investments</strong> and optimizing your budget allocation.</p>
                </div>
                
                <div class="recommendations-grid">
                    <?php foreach ($budget_plan as $recommendation): ?>
                        <div class="recommendation-item <?php echo $recommendation['is_investment'] ? 'investment' : ''; ?>">
                            <div class="recommendation-header">
                                <h4 class="recommendation-category">
                                    <?php if ($recommendation['is_investment']): ?>
                                        <i class="fas fa-gem text-info me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($recommendation['category_name']); ?>
                                </h4>
                                <span class="recommendation-amount">$<?php echo number_format($recommendation['allocated_amount'], 2); ?></span>
                            </div>
                            <div class="recommendation-progress">
                                <div class="progress-bar" style="width: <?php echo $recommendation['percentage']; ?>%"></div>
                                <span class="progress-percentage"><?php echo number_format($recommendation['percentage'], 1); ?>%</span>
                            </div>
                            <button class="btn-adopt adopt-recommendation" 
                                    data-category-id="<?php echo $recommendation['category_id']; ?>"
                                    data-amount="<?php echo $recommendation['allocated_amount']; ?>">
                                <i class="fas fa-check"></i>
                                Adopt
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modals -->
<!-- Add Budget Modal -->
<div class="modal fade modern-modal" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title">Add New Budget</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="addBudgetForm" class="needs-validation" novalidate>
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
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo ($category['name'] === 'Investments') ? 'class="investment-option"' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="amount">Budget Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        Add Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div class="modal fade modern-modal" id="editBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title">Edit Budget</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="editBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="edit_budget_id">
                
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
                                    <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo ($category['name'] === 'Investments') ? 'class="investment-option"' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_amount">Budget Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_start_date">Start Date</label>
                            <input type="date" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_end_date">End Date</label>
                            <input type="date" id="edit_end_date" name="end_date" required>
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

<!-- Delete Budget Modal -->
<div class="modal fade modern-modal" id="deleteBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title">Delete Budget</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete this budget?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="deleteBudgetForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="budget_id" id="delete_budget_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Generate Budget Modal -->
<div class="modal fade modern-modal" id="generateBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon generate">
                    <i class="fas fa-magic"></i>
                </div>
                <h5 class="modal-title">Auto-Generate Budget</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="generateBudgetForm">
                <input type="hidden" name="action" value="generate_plan">
                <input type="hidden" name="replace_existing" value="1">
                
                <div class="modal-body">
                    <?php if (isset($monthly_income) && $monthly_income > 0): ?>
                        <div class="alert-info">
                            <i class="fas fa-info-circle"></i>
                            <p>The system will generate a budget optimized for your financial growth, prioritizing investments.</p>
                        </div>
                        
                        <div class="income-display">
                            <span class="income-label">Your current monthly income:</span>
                            <span class="income-amount">$<?php echo number_format($monthly_income, 2); ?></span>
                        </div>
                        
                        <div class="generation-info">
                            <h6>Budget will be created with:</h6>
                            <ul>
                                <li><strong>10% minimum for investments</strong></li>
                                <li>Smart allocation based on spending patterns</li>
                                <li>Optimal financial ratios for wealth building</li>
                            </ul>
                        </div>
                        
                        <div class="alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>This will replace any existing budgets for the current month.</p>
                        </div>
                    <?php else: ?>
                        <div class="alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>You need to add income sources before generating a budget plan.
                               <a href="<?php echo BASE_PATH; ?>/income" class="alert-link">Go to Income Management</a> to add your income sources.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit" <?php echo (isset($monthly_income) && $monthly_income > 0) ? '' : 'disabled'; ?>>
                        <i class="fas fa-magic"></i>
                        Generate Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Add BASE_PATH as a global JavaScript variable to use in AJAX requests
echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';

// Additional JS
$additional_js = ['/assets/js/budget-modern.js'];

// Include footer
require_once 'includes/footer.php';
?>