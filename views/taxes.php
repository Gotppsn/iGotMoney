<?php
// Set page title and current page for menu highlighting
$page_title = 'Tax Planning - iGotMoney';
$current_page = 'taxes';

// Additional JS
$additional_js = ['/assets/js/taxes.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tax Planning</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="autoFillTaxInfo">
                <i class="fas fa-magic"></i> Auto-Fill
            </button>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo $selected_year; ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="yearDropdown">
                <?php 
                // Display available tax years and current year + next year
                $years = [];
                if ($tax_years && $tax_years->num_rows > 0) {
                    while ($row = $tax_years->fetch_assoc()) {
                        $years[] = $row['tax_year'];
                    }
                }
                // Add current year and next year if not already in the list
                if (!in_array($current_year, $years)) {
                    $years[] = $current_year;
                }
                if (!in_array($current_year + 1, $years)) {
                    $years[] = $current_year + 1;
                }
                
                // Sort years in descending order
                rsort($years);
                
                // Display years
                foreach ($years as $year) {
                    $active = $year == $selected_year ? ' active' : '';
                    echo '<li><a class="dropdown-item' . $active . '" href="/taxes?year=' . $year . '">' . $year . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<!-- Tax Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 dashboard-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Estimated Income (<?php echo $selected_year; ?>)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($has_tax_info ? $tax->estimated_income : 0, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2 dashboard-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Estimated Tax Liability</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($tax_liability, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2 dashboard-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Tax Owed (Remaining)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($remaining_tax, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2 dashboard-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Effective Tax Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($effective_tax_rate, 2); ?>%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tax Information Form -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Tax Information (<?php echo $selected_year; ?>)</h6>
                <?php if ($has_tax_info): ?>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="#" id="deleteTaxInfo" data-tax-id="<?php echo $tax->tax_id; ?>">
                                <i class="fas fa-trash fa-sm fa-fw text-danger me-2"></i>Delete
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form id="taxInfoForm" method="post" action="/taxes">
                    <input type="hidden" name="action" value="add_update">
                    <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                    <?php if ($has_tax_info): ?>
                        <input type="hidden" name="tax_id" value="<?php echo $tax->tax_id; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="filing_status" class="form-label">Filing Status</label>
                        <select class="form-select" id="filing_status" name="filing_status" required>
                            <option value="single" <?php echo ($has_tax_info && $tax->filing_status == 'single') ? 'selected' : ''; ?>>Single</option>
                            <option value="married_joint" <?php echo ($has_tax_info && $tax->filing_status == 'married_joint') ? 'selected' : ''; ?>>Married Filing Jointly</option>
                            <option value="married_separate" <?php echo ($has_tax_info && $tax->filing_status == 'married_separate') ? 'selected' : ''; ?>>Married Filing Separately</option>
                            <option value="head_of_household" <?php echo ($has_tax_info && $tax->filing_status == 'head_of_household') ? 'selected' : ''; ?>>Head of Household</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estimated_income" class="form-label">Estimated Annual Income</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="estimated_income" name="estimated_income" 
                                   value="<?php echo $has_tax_info ? $tax->estimated_income : $yearly_income; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">Your total estimated gross income for <?php echo $selected_year; ?>.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_paid_to_date" class="form-label">Tax Paid to Date</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="tax_paid_to_date" name="tax_paid_to_date" 
                                   value="<?php echo $has_tax_info ? $tax->tax_paid_to_date : 0; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">Total tax you've already paid through withholding or estimated payments.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deductions" class="form-label">Total Deductions</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="deductions" name="deductions" 
                                   value="<?php echo $has_tax_info ? $tax->deductions : 12950; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">Standard deduction or the sum of your itemized deductions.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="credits" class="form-label">Total Tax Credits</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="credits" name="credits" 
                                   value="<?php echo $has_tax_info ? $tax->credits : 0; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">Sum of all tax credits you qualify for.</small>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $has_tax_info ? 'Update Tax Information' : 'Add Tax Information'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tax Saving Tips -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tax Saving Tips</h6>
            </div>
            <div class="card-body">
                <?php if ($has_tax_info && !empty($tax_saving_tips)): ?>
                    <div class="accordion" id="taxTipsAccordion">
                        <?php foreach ($tax_saving_tips as $index => $tip): ?>
                            <div class="accordion-item mb-2">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                            aria-controls="collapse<?php echo $index; ?>">
                                        <?php echo htmlspecialchars($tip['title']); ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                     aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#taxTipsAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo htmlspecialchars($tip['description']); ?></p>
                                        <p class="mb-0"><strong>Potential Savings:</strong> <?php echo htmlspecialchars($tip['potential_savings']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>Add your tax information to see personalized tax saving tips.</p>
                        <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tax Summary Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Tax Breakdown</h6>
            </div>
            <div class="card-body">
                <?php if ($has_tax_info): ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="chart-container">
                                <canvas id="taxBreakdownChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Gross Income</td>
                                            <td>$<?php echo number_format($tax->estimated_income, 2); ?></td>
                                            <td>100%</td>
                                        </tr>
                                        <tr>
                                            <td>Deductions</td>
                                            <td>$<?php echo number_format($tax->deductions, 2); ?></td>
                                            <td><?php echo number_format(($tax->deductions / $tax->estimated_income) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>Taxable Income</td>
                                            <td>$<?php echo number_format(max(0, $tax->estimated_income - $tax->deductions), 2); ?></td>
                                            <td><?php echo number_format((max(0, $tax->estimated_income - $tax->deductions) / $tax->estimated_income) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>Tax Before Credits</td>
                                            <td>$<?php echo number_format($tax_liability + $tax->credits, 2); ?></td>
                                            <td><?php echo number_format((($tax_liability + $tax->credits) / $tax->estimated_income) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>Tax Credits</td>
                                            <td>$<?php echo number_format($tax->credits, 2); ?></td>
                                            <td><?php echo number_format(($tax->credits / $tax->estimated_income) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Final Tax Liability</strong></td>
                                            <td><strong>$<?php echo number_format($tax_liability, 2); ?></strong></td>
                                            <td><strong><?php echo number_format($effective_tax_rate, 2); ?>%</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Tax Paid to Date</td>
                                            <td>$<?php echo number_format($tax->tax_paid_to_date, 2); ?></td>
                                            <td><?php echo number_format(($tax->tax_paid_to_date / $tax_liability) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr class="<?php echo $remaining_tax > 0 ? 'table-danger' : 'table-success'; ?>">
                                            <td><strong><?php echo $remaining_tax > 0 ? 'Tax Still Owed' : 'Tax Refund Expected'; ?></strong></td>
                                            <td><strong>$<?php echo number_format(abs($remaining_tax), 2); ?></strong></td>
                                            <td><strong><?php echo number_format((abs($remaining_tax) / $tax_liability) * 100, 2); ?>%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No tax information available for <?php echo $selected_year; ?>. Please add your tax information to see the breakdown.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Auto-Fill Modal -->
<div class="modal fade" id="autoFillModal" tabindex="-1" aria-labelledby="autoFillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="autoFillModalLabel">Auto-Fill Tax Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This will auto-fill your tax information based on your income sources. Any existing tax information for the selected year will be updated.</p>
                <form id="autoFillForm" action="/taxes" method="post">
                    <input type="hidden" name="action" value="auto_fill">
                    <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="autoFillForm" class="btn btn-primary">Auto-Fill</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tax Info Modal -->
<div class="modal fade" id="deleteTaxInfoModal" tabindex="-1" aria-labelledby="deleteTaxInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaxInfoModalLabel">Delete Tax Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the tax information for <?php echo $selected_year; ?>? This action cannot be undone.</p>
                <form id="deleteTaxInfoForm" action="/taxes" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tax_id" id="deleteTaxId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteTaxInfoForm" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for tax page
$page_scripts = "
// Initialize tax breakdown chart
" . ($has_tax_info ? "
var taxCtx = document.getElementById('taxBreakdownChart').getContext('2d');
var taxBreakdownChart = new Chart(taxCtx, {
    type: 'pie',
    data: {
        labels: ['Net Income After Tax', 'Total Tax'],
        datasets: [{
            data: [" . ($tax->estimated_income - $tax_liability) . ", " . $tax_liability . "],
            backgroundColor: [
                '#4e73df',
                '#e74a3b'
            ],
            hoverBackgroundColor: [
                '#2e59d9',
                '#be2617'
            ],
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }],
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
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
    },
});
" : "") . "

// Handle auto-fill button
document.getElementById('autoFillTaxInfo').addEventListener('click', function() {
    var modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
    modal.show();
});

// Handle delete tax info button
" . ($has_tax_info ? "
document.getElementById('deleteTaxInfo').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('deleteTaxId').value = this.getAttribute('data-tax-id');
    var modal = new bootstrap.Modal(document.getElementById('deleteTaxInfoModal'));
    modal.show();
});
" : "") . "

// Handle form submission
document.getElementById('taxInfoForm').addEventListener('submit', function() {
    // Show loading spinner (implementation omitted for brevity)
    // This would normally show a loading spinner while the form is being submitted
});

// Handle calculated fields
document.getElementById('filing_status').addEventListener('change', updateTaxCalculations);
document.getElementById('estimated_income').addEventListener('input', updateTaxCalculations);
document.getElementById('deductions').addEventListener('input', updateTaxCalculations);
document.getElementById('credits').addEventListener('input', updateTaxCalculations);
document.getElementById('tax_paid_to_date').addEventListener('input', updateTaxCalculations);

function updateTaxCalculations() {
    // In a real implementation, this would make an AJAX call to recalculate taxes
    // based on the current form values
    console.log('Tax calculations would be updated here');
}
";

// Include footer
require_once 'includes/footer.php';
?>