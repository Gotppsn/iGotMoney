<?php
// Set page title and current page for menu highlighting
$page_title = 'Income Management - iGotMoney';
$current_page = 'income';

// Additional JS
$additional_js = ['/assets/js/income.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Income Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
            <i class="fas fa-plus"></i> Add Income
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 dashboard-card income">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Monthly Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_income, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-success shadow h-100 py-2 dashboard-card income">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Annual Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($yearly_income, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Income Sources Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Income Sources</h6>
        <div class="input-group input-group-sm" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search income sources..." id="incomeSearch" data-table-search="incomeTable">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($income_sources) && $income_sources->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered data-table" id="incomeTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($income = $income_sources->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($income['name']); ?></td>
                                <td>$<?php echo number_format($income['amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $frequency = ucfirst(str_replace('-', ' ', $income['frequency']));
                                    echo $frequency; 
                                    ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($income['start_date'])); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($income['end_date'])) {
                                        echo date('M j, Y', strtotime($income['end_date']));
                                    } else {
                                        echo '<span class="text-muted">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($income['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info edit-income" data-income-id="<?php echo $income['income_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-income" data-income-id="<?php echo $income['income_id']; ?>">
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
                <p>No income sources found.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                    <i class="fas fa-plus"></i> Add Your First Income Source
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIncomeModalLabel">Add Income Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/income" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Income Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">Example: Salary, Freelance Work, Rental Income</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="frequency" class="form-label">Frequency</label>
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
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <div class="form-text">Leave blank for ongoing income sources</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Income</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Income Modal -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIncomeModalLabel">Edit Income Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/income" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="income_id" id="edit_income_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Income Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_frequency" class="form-label">Frequency</label>
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
                    
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        <div class="form-text">Leave blank for ongoing income sources</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
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

<!-- Delete Income Modal -->
<div class="modal fade" id="deleteIncomeModal" tabindex="-1" aria-labelledby="deleteIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteIncomeModalLabel">Delete Income Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this income source? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/income" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="income_id" id="delete_income_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for income page
$page_scripts = "
// Handle edit income button
document.querySelectorAll('.edit-income').forEach(button => {
    button.addEventListener('click', function() {
        const incomeId = this.getAttribute('data-income-id');
        
        // Show loading spinner
        showSpinner(document.querySelector('#editIncomeModal .modal-body'));
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
        modal.show();
        
        // Fetch income data
        fetch('/income?action=get_income&income_id=' + incomeId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_income_id').value = data.income.income_id;
                    document.getElementById('edit_name').value = data.income.name;
                    document.getElementById('edit_amount').value = data.income.amount;
                    document.getElementById('edit_frequency').value = data.income.frequency;
                    document.getElementById('edit_start_date').value = data.income.start_date;
                    document.getElementById('edit_end_date').value = data.income.end_date || '';
                    document.getElementById('edit_is_active').checked = data.income.is_active == 1;
                    
                    // Remove spinner
                    hideSpinner(document.querySelector('#editIncomeModal .modal-body'));
                } else {
                    // Show error
                    alert('Failed to load income data: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error fetching income data:', error);
                alert('An error occurred while loading income data.');
                modal.hide();
            });
    });
});

// Handle delete income button
document.querySelectorAll('.delete-income').forEach(button => {
    button.addEventListener('click', function() {
        const incomeId = this.getAttribute('data-income-id');
        document.getElementById('delete_income_id').value = incomeId;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteIncomeModal'));
        modal.show();
    });
});
";

// Include footer
require_once 'includes/footer.php';
?>