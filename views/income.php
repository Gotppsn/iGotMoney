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
        <button type="button" class="btn btn-sm btn-primary" id="addIncomeBtn">
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
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info edit-income-btn" data-income-id="<?php echo $income['income_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-income-btn" data-income-id="<?php echo $income['income_id']; ?>">
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
            <div class="text-center py-4">
                <p>No income sources found.</p>
                <button type="button" class="btn btn-primary" id="addIncomeEmptyBtn">
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
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Income Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">Example: Salary, Freelance Work, Rental Income</div>
                        <div class="invalid-feedback">Please provide an income name.</div>
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
                        <div class="invalid-feedback">Please provide a start date.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <div class="form-text">Leave blank for ongoing income sources</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                        <div class="form-text">Inactive income sources won't be included in your income calculations</div>
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
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="income_id" id="edit_income_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Income Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback">Please provide an income name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">Please enter a valid amount greater than zero.</div>
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
                        <div class="invalid-feedback">Please provide a start date.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        <div class="form-text">Leave blank for ongoing income sources</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                        <div class="form-text">Inactive income sources won't be included in your income calculations</div>
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
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Deleting this income source will also remove it from your financial calculations.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/income" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="income_id" id="delete_income_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for the income page - define BASE_PATH for JS
$page_scripts = "
// Define BASE_PATH for JavaScript
var BASE_PATH = '" . BASE_PATH . "';

// Initialize modals
var addIncomeModal = new bootstrap.Modal(document.getElementById('addIncomeModal'), {
    keyboard: false
});

var editIncomeModal = new bootstrap.Modal(document.getElementById('editIncomeModal'), {
    keyboard: false
});

var deleteIncomeModal = new bootstrap.Modal(document.getElementById('deleteIncomeModal'), {
    keyboard: false
});

// Add Income button handlers
document.getElementById('addIncomeBtn').addEventListener('click', function() {
    addIncomeModal.show();
});

var addIncomeEmptyBtn = document.getElementById('addIncomeEmptyBtn');
if (addIncomeEmptyBtn) {
    addIncomeEmptyBtn.addEventListener('click', function() {
        addIncomeModal.show();
    });
}

// Edit Income button handlers
document.querySelectorAll('.edit-income-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        var incomeId = this.getAttribute('data-income-id');
        document.getElementById('edit_income_id').value = incomeId;
        
        // Show the modal
        editIncomeModal.show();
        
        // Show loading state
        var modalBody = document.querySelector('#editIncomeModal .modal-body');
        modalBody.innerHTML = '<div class=\"text-center py-3\"><div class=\"spinner-border text-primary\" role=\"status\"><span class=\"visually-hidden\">Loading...</span></div><p class=\"mt-2\">Loading income data...</p></div>';
        
        // Fetch income data
        fetch(BASE_PATH + '/income?action=get_income&income_id=' + incomeId)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    // Restore form content
                    modalBody.innerHTML = `
                        <div class=\"mb-3\">
                            <label for=\"edit_name\" class=\"form-label\">Income Name</label>
                            <input type=\"text\" class=\"form-control\" id=\"edit_name\" name=\"name\" required>
                            <div class=\"invalid-feedback\">Please provide an income name.</div>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"edit_amount\" class=\"form-label\">Amount</label>
                            <div class=\"input-group\">
                                <span class=\"input-group-text\">$</span>
                                <input type=\"number\" class=\"form-control\" id=\"edit_amount\" name=\"amount\" step=\"0.01\" min=\"0.01\" required>
                                <div class=\"invalid-feedback\">Please enter a valid amount greater than zero.</div>
                            </div>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"edit_frequency\" class=\"form-label\">Frequency</label>
                            <select class=\"form-select\" id=\"edit_frequency\" name=\"frequency\" required>
                                <option value=\"one-time\">One Time</option>
                                <option value=\"daily\">Daily</option>
                                <option value=\"weekly\">Weekly</option>
                                <option value=\"bi-weekly\">Bi-Weekly</option>
                                <option value=\"monthly\">Monthly</option>
                                <option value=\"quarterly\">Quarterly</option>
                                <option value=\"annually\">Annually</option>
                            </select>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"edit_start_date\" class=\"form-label\">Start Date</label>
                            <input type=\"date\" class=\"form-control\" id=\"edit_start_date\" name=\"start_date\" required>
                            <div class=\"invalid-feedback\">Please provide a start date.</div>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"edit_end_date\" class=\"form-label\">End Date (Optional)</label>
                            <input type=\"date\" class=\"form-control\" id=\"edit_end_date\" name=\"end_date\">
                            <div class=\"form-text\">Leave blank for ongoing income sources</div>
                        </div>
                        
                        <div class=\"mb-3 form-check\">
                            <input type=\"checkbox\" class=\"form-check-input\" id=\"edit_is_active\" name=\"is_active\">
                            <label class=\"form-check-label\" for=\"edit_is_active\">Active</label>
                            <div class=\"form-text\">Inactive income sources won't be included in your income calculations</div>
                        </div>
                    `;
                    
                    // Populate form fields
                    document.getElementById('edit_income_id').value = data.income.income_id;
                    document.getElementById('edit_name').value = data.income.name;
                    document.getElementById('edit_amount').value = data.income.amount;
                    document.getElementById('edit_frequency').value = data.income.frequency;
                    document.getElementById('edit_start_date').value = data.income.start_date;
                    document.getElementById('edit_end_date').value = data.income.end_date || '';
                    document.getElementById('edit_is_active').checked = data.income.is_active == 1;
                    
                    // Reinitialize frequency calculation
                    updateAmountLabel(document.getElementById('edit_frequency'));
                    
                    // Create preview
                    updateIncomePreview(document.querySelector('#editIncomeModal form'));
                } else {
                    // Show error message
                    alert('Failed to load income data: ' + data.message);
                    editIncomeModal.hide();
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('An error occurred while fetching income data. Please try again.');
                editIncomeModal.hide();
            });
    });
});

// Delete Income button handlers
document.querySelectorAll('.delete-income-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        var incomeId = this.getAttribute('data-income-id');
        document.getElementById('delete_income_id').value = incomeId;
        deleteIncomeModal.show();
    });
});

// Search functionality
var searchInput = document.getElementById('incomeSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        var searchTerm = this.value.toLowerCase();
        var table = document.getElementById('incomeTable');
        if (table) {
            var rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }
    });
}

// Update amount label based on frequency
function updateAmountLabel(frequencySelect) {
    if (!frequencySelect) return;
    
    var frequency = frequencySelect.value;
    var isAddForm = frequencySelect.id === 'frequency';
    var amountLabel = document.querySelector('label[for=\"' + (isAddForm ? 'amount' : 'edit_amount') + '\"]');
    
    if (!amountLabel) return;
    
    var labelText = 'Amount';
    
    switch (frequency) {
        case 'daily': labelText = 'Daily Amount'; break;
        case 'weekly': labelText = 'Weekly Amount'; break;
        case 'bi-weekly': labelText = 'Bi-Weekly Amount'; break;
        case 'monthly': labelText = 'Monthly Amount'; break;
        case 'quarterly': labelText = 'Quarterly Amount'; break;
        case 'annually': labelText = 'Annual Amount'; break;
        case 'one-time': labelText = 'One-Time Amount'; break;
    }
    
    amountLabel.textContent = labelText;
}

// Update income preview
function updateIncomePreview(form) {
    if (!form) return;
    
    var amountInput = form.querySelector('input[name=\"amount\"]');
    var frequencySelect = form.querySelector('select[name=\"frequency\"]');
    
    if (!amountInput || !frequencySelect) return;
    
    var amount = parseFloat(amountInput.value) || 0;
    var frequency = frequencySelect.value;
    
    // Calculate monthly and annual equivalents
    var monthlyEquivalent = 0;
    var annualEquivalent = 0;
    
    switch (frequency) {
        case 'daily':
            monthlyEquivalent = amount * 30;
            annualEquivalent = amount * 365;
            break;
        case 'weekly':
            monthlyEquivalent = amount * 4.33;
            annualEquivalent = amount * 52;
            break;
        case 'bi-weekly':
            monthlyEquivalent = amount * 2.17;
            annualEquivalent = amount * 26;
            break;
        case 'monthly':
            monthlyEquivalent = amount;
            annualEquivalent = amount * 12;
            break;
        case 'quarterly':
            monthlyEquivalent = amount / 3;
            annualEquivalent = amount * 4;
            break;
        case 'annually':
            monthlyEquivalent = amount / 12;
            annualEquivalent = amount;
            break;
        case 'one-time':
            monthlyEquivalent = amount;
            annualEquivalent = amount;
            break;
    }
    
    // Create or update preview element
    var previewElement = form.querySelector('.income-preview');
    if (!previewElement) {
        previewElement = document.createElement('div');
        previewElement.className = 'income-preview mt-3 alert alert-info';
        
        // Find a good place to insert it
        var amountGroup = amountInput.closest('.mb-3');
        if (amountGroup) {
            amountGroup.insertAdjacentElement('afterend', previewElement);
        }
    }
    
    if (amount > 0) {
        previewElement.innerHTML = `
            <div class=\"d-flex justify-content-between\">
                <span>Monthly equivalent:</span>
                <strong>$${monthlyEquivalent.toFixed(2)}</strong>
            </div>
            <div class=\"d-flex justify-content-between\">
                <span>Annual equivalent:</span>
                <strong>$${annualEquivalent.toFixed(2)}</strong>
            </div>
        `;
        previewElement.style.display = 'block';
    } else {
        previewElement.style.display = 'none';
    }
}

// Initialize frequency calculation for add form
var frequencySelect = document.getElementById('frequency');
if (frequencySelect) {
    frequencySelect.addEventListener('change', function() {
        updateAmountLabel(this);
        updateIncomePreview(this.closest('form'));
    });
    
    // Initialize with current value
    updateAmountLabel(frequencySelect);
}

// Add event listener to amount input
var amountInput = document.getElementById('amount');
if (amountInput) {
    amountInput.addEventListener('input', function() {
        updateIncomePreview(this.closest('form'));
    });
    
    // Create initial preview
    updateIncomePreview(amountInput.closest('form'));
}

// Form validation
document.querySelectorAll('form.needs-validation').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});

// Date validation
document.querySelectorAll('#start_date, #edit_start_date').forEach(function(startDate) {
    startDate.addEventListener('change', function() {
        var form = this.closest('form');
        var endDate = form.querySelector('#end_date, #edit_end_date');
        
        if (endDate && endDate.value) {
            var startValue = new Date(this.value);
            var endValue = new Date(endDate.value);
            
            if (endValue < startValue) {
                alert('End date must be after start date');
                endDate.value = '';
            }
        }
    });
});

document.querySelectorAll('#end_date, #edit_end_date').forEach(function(endDate) {
    endDate.addEventListener('change', function() {
        var form = this.closest('form');
        var startDate = form.querySelector('#start_date, #edit_start_date');
        
        if (startDate && this.value) {
            var startValue = new Date(startDate.value);
            var endValue = new Date(this.value);
            
            if (endValue < startValue) {
                alert('End date must be after start date');
                this.value = '';
            }
        }
    });
});

// Debug info - uncomment if needed
console.log('Income management JS loaded');
console.log('BASE_PATH:', BASE_PATH);
";

// Include footer
require_once 'includes/footer.php';
?>