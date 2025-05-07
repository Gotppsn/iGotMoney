<?php
// Set page title and current page for menu highlighting
$page_title = 'Tax Planning - iGotMoney';
$current_page = 'taxes';

// Additional CSS and JS
$additional_css = ['/assets/css/tax-modern.css'];
$additional_js = ['/assets/js/taxes.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="tax-page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <div>
        <h1 class="tax-page-title">Tax Planning</h1>
        <p class="tax-page-subtitle">Manage your tax information and get personalized advice</p>
    </div>
    <div class="tax-header-actions">
        <div class="d-flex">
            <div class="tax-year-dropdown dropdown me-2">
                <button class="btn dropdown-toggle d-flex align-items-center" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span><?php echo $selected_year; ?></span>
                </button>
                <ul class="dropdown-menu shadow" aria-labelledby="yearDropdown">
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
                        echo '<li><a class="dropdown-item' . $active . '" href="' . BASE_PATH . '/taxes?year=' . $year . '">' . $year . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <button type="button" class="tax-auto-fill-btn btn btn-primary" id="autoFillTaxInfo">
                <i class="fas fa-magic"></i> Auto-Fill
            </button>
        </div>
    </div>
</div>

<!-- Tax Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="tax-summary-card card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="summary-card-icon summary-card-income">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="summary-card-title">Estimated Income</div>
                        <div class="summary-card-value">$<?php echo number_format($has_tax_info ? $tax->estimated_income : 0, 2); ?></div>
                        <div class="small text-muted"><?php echo $selected_year; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="tax-summary-card card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="summary-card-icon summary-card-liability">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="summary-card-title">Tax Liability</div>
                        <div class="summary-card-value">$<?php echo number_format($tax_liability, 2); ?></div>
                        <div class="small text-muted">Estimated for <?php echo $selected_year; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="tax-summary-card card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="summary-card-icon summary-card-remaining">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="summary-card-title">Remaining Tax</div>
                        <div class="summary-card-value">$<?php echo number_format($remaining_tax, 2); ?></div>
                        <div class="small text-muted">Still owed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="tax-summary-card card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="summary-card-icon summary-card-rate">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <div class="summary-card-title">Effective Tax Rate</div>
                        <div class="summary-card-value"><?php echo number_format($effective_tax_rate, 2); ?>%</div>
                        <div class="small text-muted">Of gross income</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tax Information Form -->
    <div class="col-lg-6">
        <div class="tax-form-card card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">Tax Information (<?php echo $selected_year; ?>)</h6>
                <?php if ($has_tax_info): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" type="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="deleteTaxInfo" data-tax-id="<?php echo $tax->tax_id; ?>">
                                    <i class="fas fa-trash fa-sm me-2"></i>Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form id="taxInfoForm" method="post" action="<?php echo BASE_PATH; ?>/taxes">
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
                        <small class="form-text">Your total estimated gross income for <?php echo $selected_year; ?>.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_paid_to_date" class="form-label">Tax Paid to Date</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="tax_paid_to_date" name="tax_paid_to_date" 
                                   value="<?php echo $has_tax_info ? $tax->tax_paid_to_date : 0; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text">Total tax you've already paid through withholding or estimated payments.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deductions" class="form-label">Total Deductions</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="deductions" name="deductions" 
                                   value="<?php echo $has_tax_info ? $tax->deductions : 12950; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text">Standard deduction or the sum of your itemized deductions.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="credits" class="form-label">Total Tax Credits</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="credits" name="credits" 
                                   value="<?php echo $has_tax_info ? $tax->credits : 0; ?>" min="0" step="0.01" required>
                        </div>
                        <small class="form-text">Sum of all tax credits you qualify for.</small>
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
        <div class="tax-tips-card card h-100">
            <div class="card-header">
                <h6 class="m-0">Tax Saving Tips</h6>
            </div>
            <div class="card-body">
                <?php if ($has_tax_info && !empty($tax_saving_tips)): ?>
                    <div class="tax-tips-accordion accordion" id="taxTipsAccordion">
                        <?php foreach ($tax_saving_tips as $index => $tip): ?>
                            <div class="accordion-item mb-3">
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
                    <div class="tax-tips-empty">
                        <i class="fas fa-lightbulb tax-tips-empty-icon"></i>
                        <p>Add your tax information to see personalized tax saving tips.</p>
                        <button class="btn btn-outline-primary" id="autoFillTaxInfoEmpty">
                            <i class="fas fa-magic me-2"></i> Auto-Fill Tax Information
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tax Summary Chart -->
<div class="row">
    <div class="col-12">
        <div class="tax-chart-card card">
            <div class="card-header">
                <h6 class="m-0">Tax Breakdown</h6>
            </div>
            <div class="card-body">
                <?php if ($has_tax_info): ?>
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="chart-container">
                                <canvas id="taxBreakdownChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="table-responsive">
                                <table class="tax-breakdown-table table">
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
                                            <td><?php echo number_format(($tax->tax_paid_to_date / max(0.01, $tax_liability)) * 100, 2); ?>%</td>
                                        </tr>
                                        <tr class="<?php echo $remaining_tax > 0 ? 'table-danger' : 'table-success'; ?>">
                                            <td><strong><?php echo $remaining_tax > 0 ? 'Tax Still Owed' : 'Tax Refund Expected'; ?></strong></td>
                                            <td><strong>$<?php echo number_format(abs($remaining_tax), 2); ?></strong></td>
                                            <td><strong><?php echo number_format((abs($remaining_tax) / max(0.01, $tax_liability)) * 100, 2); ?>%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tax-empty-state">
                        <p>No tax information available for <?php echo $selected_year; ?>. Please add your tax information to see the breakdown.</p>
                        <button class="btn btn-primary" id="scrollToForm">
                            <i class="fas fa-edit me-2"></i> Add Tax Information
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Auto-Fill Modal -->
<div class="tax-modal modal fade" id="autoFillModal" tabindex="-1" aria-labelledby="autoFillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="autoFillModalLabel">Auto-Fill Tax Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-magic text-primary" style="font-size: 3rem;"></i>
                </div>
                <p>This will auto-fill your tax information based on your income sources. Any existing tax information for the selected year will be updated.</p>
                <form id="autoFillForm" action="<?php echo BASE_PATH; ?>/taxes" method="post">
                    <input type="hidden" name="action" value="auto_fill">
                    <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="autoFillForm" class="btn btn-primary auto-fill-submit-btn">
                    <i class="fas fa-magic me-2"></i>Auto-Fill
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tax Info Modal -->
<div class="tax-modal modal fade" id="deleteTaxInfoModal" tabindex="-1" aria-labelledby="deleteTaxInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaxInfoModalLabel">Delete Tax Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                </div>
                <p>Are you sure you want to delete the tax information for <?php echo $selected_year; ?>? This action cannot be undone.</p>
                <form id="deleteTaxInfoForm" action="<?php echo BASE_PATH; ?>/taxes" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tax_id" id="deleteTaxId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteTaxInfoForm" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Delete
                </button>
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
    type: 'doughnut',
    data: {
        labels: ['Net Income After Tax', 'Total Tax'],
        datasets: [{
            data: [" . ($tax->estimated_income - $tax_liability) . ", " . $tax_liability . "],
            backgroundColor: [
                'rgba(67, 97, 238, 0.8)',
                'rgba(231, 76, 60, 0.8)'
            ],
            borderColor: [
                'rgba(67, 97, 238, 1)',
                'rgba(231, 76, 60, 1)'
            ],
            borderWidth: 1,
            hoverOffset: 5
        }],
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        var value = context.raw || 0;
                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                        var percentage = Math.round((value / total) * 100);
                        return label + ': $' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '60%'
    },
});
" : "") . "

// Handle auto-fill button
document.getElementById('autoFillTaxInfo').addEventListener('click', function() {
    var modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
    modal.show();
});

// Handle empty state auto-fill button if it exists
var emptyAutoFillBtn = document.getElementById('autoFillTaxInfoEmpty');
if (emptyAutoFillBtn) {
    emptyAutoFillBtn.addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
        modal.show();
    });
}

// Handle scroll to form button if it exists
var scrollToFormBtn = document.getElementById('scrollToForm');
if (scrollToFormBtn) {
    scrollToFormBtn.addEventListener('click', function() {
        document.querySelector('.tax-form-card').scrollIntoView({ behavior: 'smooth' });
    });
}

// Handle delete tax info button
" . ($has_tax_info ? "
document.getElementById('deleteTaxInfo').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('deleteTaxId').value = this.getAttribute('data-tax-id');
    var modal = new bootstrap.Modal(document.getElementById('deleteTaxInfoModal'));
    modal.show();
});
" : "") . "

// Initialize form calculations
calculateTaxEstimates();

// Handle form input changes
document.getElementById('filing_status').addEventListener('change', calculateTaxEstimates);
document.getElementById('estimated_income').addEventListener('input', calculateTaxEstimates);
document.getElementById('deductions').addEventListener('input', calculateTaxEstimates);
document.getElementById('credits').addEventListener('input', calculateTaxEstimates);
document.getElementById('tax_paid_to_date').addEventListener('input', calculateTaxEstimates);

// Add some animation
document.querySelectorAll('.tax-summary-card').forEach(function(card, index) {
    card.style.animationDelay = (index * 0.1) + 's';
    card.classList.add('animate__animated', 'animate__fadeInUp');
});
";

// Include footer
require_once 'includes/footer.php';
?>