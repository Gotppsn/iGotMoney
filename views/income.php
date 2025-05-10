<?php
// Set page title and current page for menu highlighting
$page_title = 'Income Management - iGotMoney';
$current_page = 'income';

// Additional CSS and JS
$additional_css = ['/assets/css/income.css'];
$additional_js = ['/assets/js/income.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-primary">Income Management</h1>
        <p class="text-muted mb-0">Track and manage all your income sources</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
        <i class="fa fa-plus me-2"></i> Add Income Source
    </button>
</div>

<!-- Income Overview Section -->
<div class="row mb-4 g-3">
    <!-- Monthly Income Card -->
    <div class="col-md-6 col-xl-4">
        <div class="card income-summary-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="income-icon-wrapper me-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="card-title text-dark mb-0">Monthly Income</h5>
                </div>
                <h2 class="income-amount mb-2">$<?php echo number_format($monthly_income, 2); ?></h2>
                <div class="income-trend">
                    <?php
                    // Determine if income is up/down (placeholder calculation)
                    // In a real implementation, you would compare with previous month
                    $previous_month = isset($prev_monthly_income) ? $prev_monthly_income : $monthly_income * 0.95;
                    $is_increased = $monthly_income >= $previous_month;
                    $percent_change = abs(($monthly_income - $previous_month) / max(1, $previous_month) * 100);
                    ?>
                    
                    <span class="badge bg-<?php echo $is_increased ? 'success' : 'danger'; ?> p-2">
                        <i class="fas fa-<?php echo $is_increased ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                        <?php echo number_format($percent_change, 1); ?>%
                    </span>
                    <span class="text-muted ms-2">vs. previous month</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Annual Income Card -->
    <div class="col-md-6 col-xl-4">
        <div class="card income-summary-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="income-icon-wrapper me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-title text-dark mb-0">Annual Income</h5>
                </div>
                <h2 class="income-amount mb-2">$<?php echo number_format($yearly_income, 2); ?></h2>
                <div class="income-trend">
                    <?php
                    // Get the number of active income sources
                    $active_count = 0;
                    if (isset($income_sources) && $income_sources->num_rows > 0) {
                        // Save the position of the result set
                        $income_sources->data_seek(0);
                        
                        // Count active sources
                        while ($row = $income_sources->fetch_assoc()) {
                            if ($row['is_active'] == 1) {
                                $active_count++;
                            }
                        }
                        
                        // Reset the result pointer
                        $income_sources->data_seek(0);
                    }
                    ?>
                    <span class="badge bg-info p-2">
                        <i class="fas fa-stream me-1"></i> <?php echo $active_count; ?>
                    </span>
                    <span class="text-muted ms-2">active income sources</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Income Breakdown Card -->
    <div class="col-md-12 col-xl-4">
        <div class="card income-summary-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="income-icon-wrapper me-3">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h5 class="card-title text-dark mb-0">Income Breakdown</h5>
                </div>
                
                <?php
                // Get income sources by frequency
                $frequency_data = [];
                $frequency_totals = [];
                
                // Initialize with zero for each frequency
                $all_frequencies = ['monthly', 'annually', 'bi-weekly', 'weekly', 'one-time', 'daily', 'quarterly'];
                foreach ($all_frequencies as $freq) {
                    $frequency_totals[$freq] = 0;
                }
                
                // Calculate totals by frequency
                if (isset($income_sources) && $income_sources->num_rows > 0) {
                    // Save the position
                    $income_sources->data_seek(0);
                    
                    while ($row = $income_sources->fetch_assoc()) {
                        if ($row['is_active'] == 1) {
                            $freq = $row['frequency'];
                            
                            // Convert to monthly equivalent
                            $monthly_amount = $row['amount'];
                            if ($freq == 'annually') {
                                $monthly_amount = $row['amount'] / 12;
                            } elseif ($freq == 'quarterly') {
                                $monthly_amount = $row['amount'] / 3;
                            } elseif ($freq == 'bi-weekly') {
                                $monthly_amount = $row['amount'] * 2.17;
                            } elseif ($freq == 'weekly') {
                                $monthly_amount = $row['amount'] * 4.33;
                            } elseif ($freq == 'daily') {
                                $monthly_amount = $row['amount'] * 30;
                            }
                            
                            $frequency_totals[$freq] += $monthly_amount;
                        }
                    }
                    
                    // Reset the pointer
                    $income_sources->data_seek(0);
                }
                
                if (array_sum($frequency_totals) > 0) {
                    $colors = [
                        'monthly' => 'primary',
                        'annually' => 'success', 
                        'bi-weekly' => 'info',
                        'weekly' => 'warning',
                        'one-time' => 'danger',
                        'daily' => 'secondary',
                        'quarterly' => 'dark'
                    ];
                    
                    $total = array_sum($frequency_totals);
                    
                    foreach ($frequency_totals as $freq => $amount) {
                        if ($amount > 0) {
                            $freq_name = ucfirst(str_replace('-', ' ', $freq));
                            $percentage = min(100, round(($amount / $total) * 100));
                            $color = isset($colors[$freq]) ? $colors[$freq] : 'primary';
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-medium"><?php echo $freq_name; ?></span>
                                    <span class="text-muted small">$<?php echo number_format($amount, 2); ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%" 
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo '<div class="text-center text-muted my-4">No active income sources</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Income Sources Table Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-list me-2"></i> Income Sources
            </h5>
            <div class="income-search-wrapper">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" 
                           placeholder="Search income sources..." 
                           id="incomeSearch" 
                           data-table-search="incomeTable">
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (isset($income_sources) && $income_sources->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle income-table mb-0" id="incomeTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($income_source = $income_sources->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    <?php echo htmlspecialchars($income_source['name']); ?>
                                </td>
                                <td class="income-amount-cell">
                                    $<?php echo number_format($income_source['amount'], 2); ?>
                                </td>
                                <td>
                                    <?php 
                                    $frequency = ucfirst(str_replace('-', ' ', $income_source['frequency']));
                                    echo $frequency; 
                                    ?>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($income_source['start_date'])); ?>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($income_source['end_date']) && $income_source['end_date'] !== null && $income_source['end_date'] !== '0000-00-00') {
                                        $timestamp = strtotime($income_source['end_date']);
                                        if ($timestamp > 0) {
                                            echo date('M j, Y', $timestamp);
                                        } else {
                                            echo '<span class="text-muted">Ongoing</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">Ongoing</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($income_source['is_active']): ?>
                                        <span class="badge bg-soft-success text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-soft-secondary text-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-income" 
                                                data-income-id="<?php echo $income_source['income_id']; ?>"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-income" 
                                                data-income-id="<?php echo $income_source['income_id']; ?>"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 empty-state">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-wallet"></i>
                </div>
                <h4 class="empty-state-title mb-2">No income sources found</h4>
                <p class="empty-state-description text-muted mb-4">
                    Start tracking your income by adding your first income source.
                </p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                    <i class="fas fa-plus me-2"></i> Add Your First Income Source
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addIncomeModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Income Source
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation income-form" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Income Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-tag text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-text">For example: Salary, Freelance Work, Rental Income</div>
                        <div class="invalid-feedback">Please provide an income name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-sync-alt text-muted"></i>
                            </span>
                            <select class="form-select" id="frequency" name="frequency" required>
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
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-day text-muted"></i>
                                </span>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="invalid-feedback">Please provide a start date.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-check text-muted"></i>
                                </span>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="form-text">Leave blank for ongoing income</div>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                        <div class="form-text">Inactive income sources won't be included in calculations</div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Income Modal -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editIncomeModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Income Source
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation income-form" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="income_id" id="edit_income_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Income Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-tag text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="invalid-feedback">Please provide an income name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-sync-alt text-muted"></i>
                            </span>
                            <select class="form-select" id="edit_frequency" name="frequency" required>
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
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-day text-muted"></i>
                                </span>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                            <div class="invalid-feedback">Please provide a start date.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_end_date" class="form-label">End Date <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-check text-muted"></i>
                                </span>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                            <div class="form-text">Leave blank for ongoing income</div>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                        <div class="form-text">Inactive income sources won't be included in calculations</div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Income Modal -->
<div class="modal fade" id="deleteIncomeModal" tabindex="-1" aria-labelledby="deleteIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteIncomeModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Delete Income
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="delete-icon-wrapper mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">This action cannot be undone. This will permanently delete the income source.</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/income" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="income_id" id="delete_income_id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>