<?php
// Set page title and current page for menu highlighting
$page_title = 'Tax Planning - iGotMoney';
$current_page = 'taxes';

// Additional CSS and JS
$additional_css = ['/assets/css/taxes-modern.css'];
$additional_js = ['/assets/js/taxes-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="taxes-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Tax Planning</h1>
                <p class="page-subtitle">Manage your tax information and get personalized advice</p>
            </div>
            <div class="header-actions">
                <div class="tax-year-dropdown">
                    <button class="btn-year-dropdown" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo $selected_year; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu tax-dropdown-menu" aria-labelledby="yearDropdown">
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
                <button type="button" class="btn-auto-fill" id="autoFillTaxInfo">
                    <i class="fas fa-magic"></i>
                    <span>Auto-Fill</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats-section">
        <div class="stats-grid">
            <div class="stat-card income">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Estimated Income</h3>
                    <p class="stat-value">$<?php echo number_format($has_tax_info ? $tax->estimated_income : 0, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo $selected_year; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card liability">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Tax Liability</h3>
                    <p class="stat-value">$<?php echo number_format($tax_liability, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-calculator"></i>
                        <span>Estimated for <?php echo $selected_year; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card remaining">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Remaining Tax</h3>
                    <p class="stat-value">$<?php echo number_format($remaining_tax, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-clock"></i>
                        <span>Still owed</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card rate">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Effective Tax Rate</h3>
                    <p class="stat-value"><?php echo number_format($effective_tax_rate, 2); ?>%</p>
                    <div class="stat-info">
                        <i class="fas fa-chart-pie"></i>
                        <span>Of gross income</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="main-content-section">
        <div class="content-grid">
            <!-- Tax Information Form -->
            <div class="content-card tax-form">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-edit"></i>
                        <h3>Tax Information (<?php echo $selected_year; ?>)</h3>
                    </div>
                    <?php if ($has_tax_info): ?>
                        <div class="card-actions">
                            <button class="btn-action delete" id="deleteTaxInfo" data-tax-id="<?php echo $tax->tax_id; ?>" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form id="taxInfoForm" method="post" action="<?php echo BASE_PATH; ?>/taxes" class="tax-information-form">
                        <input type="hidden" name="action" value="add_update">
                        <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                        <?php if ($has_tax_info): ?>
                            <input type="hidden" name="tax_id" value="<?php echo $tax->tax_id; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-field">
                                <label for="filing_status">Filing Status</label>
                                <select class="modern-select" id="filing_status" name="filing_status" required>
                                    <option value="single" <?php echo ($has_tax_info && $tax->filing_status == 'single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="married_joint" <?php echo ($has_tax_info && $tax->filing_status == 'married_joint') ? 'selected' : ''; ?>>Married Filing Jointly</option>
                                    <option value="married_separate" <?php echo ($has_tax_info && $tax->filing_status == 'married_separate') ? 'selected' : ''; ?>>Married Filing Separately</option>
                                    <option value="head_of_household" <?php echo ($has_tax_info && $tax->filing_status == 'head_of_household') ? 'selected' : ''; ?>>Head of Household</option>
                                </select>
                            </div>
                            
                            <div class="form-field">
                                <label for="estimated_income">Estimated Annual Income</label>
                                <div class="amount-input">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="estimated_income" name="estimated_income" 
                                           value="<?php echo $has_tax_info ? $tax->estimated_income : $yearly_income; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help">Your total estimated gross income for <?php echo $selected_year; ?>.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="tax_paid_to_date">Tax Paid to Date</label>
                                <div class="amount-input">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="tax_paid_to_date" name="tax_paid_to_date" 
                                           value="<?php echo $has_tax_info ? $tax->tax_paid_to_date : 0; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help">Total tax you've already paid through withholding or estimated payments.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="deductions">Total Deductions</label>
                                <div class="amount-input">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="deductions" name="deductions" 
                                           value="<?php echo $has_tax_info ? $tax->deductions : 12950; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help">Standard deduction or the sum of your itemized deductions.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="credits">Total Tax Credits</label>
                                <div class="amount-input">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="credits" name="credits" 
                                           value="<?php echo $has_tax_info ? $tax->credits : 0; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help">Sum of all tax credits you qualify for.</small>
                            </div>
                            
                            <div class="form-field full-width">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i>
                                    <?php echo $has_tax_info ? 'Update Tax Information' : 'Add Tax Information'; ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tax Saving Tips -->
            <div class="content-card tax-tips">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Tax Saving Tips</h3>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($has_tax_info && !empty($tax_saving_tips)): ?>
                        <div class="tips-accordion">
                            <?php foreach ($tax_saving_tips as $index => $tip): ?>
                                <div class="tip-item">
                                    <button class="tip-header" type="button" data-tip-index="<?php echo $index; ?>">
                                        <span class="tip-title"><?php echo htmlspecialchars($tip['title']); ?></span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="tip-content <?php echo $index === 0 ? 'active' : ''; ?>" id="tipContent<?php echo $index; ?>">
                                        <p><?php echo htmlspecialchars($tip['description']); ?></p>
                                        <div class="savings-info">
                                            <strong>Potential Savings:</strong> 
                                            <span><?php echo htmlspecialchars($tip['potential_savings']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-tips">
                            <i class="fas fa-lightbulb"></i>
                            <h4>No tips available yet</h4>
                            <p>Add your tax information to see personalized tax saving tips.</p>
                            <button type="button" class="btn-primary" id="autoFillEmpty">
                                <i class="fas fa-magic"></i>
                                Auto-Fill Tax Information
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Analysis Section -->
    <div class="tax-analysis-section">
        <div class="analysis-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    <h3>Tax Breakdown Analysis</h3>
                </div>
            </div>
            <div class="card-body">
                <?php if ($has_tax_info): ?>
                    <div class="analysis-grid">
                        <div class="chart-container">
                            <canvas id="taxBreakdownChart"></canvas>
                        </div>
                        <div class="breakdown-table">
                            <table>
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
                                    <tr class="highlight">
                                        <td><strong>Final Tax Liability</strong></td>
                                        <td><strong>$<?php echo number_format($tax_liability, 2); ?></strong></td>
                                        <td><strong><?php echo number_format($effective_tax_rate, 2); ?>%</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Paid to Date</td>
                                        <td>$<?php echo number_format($tax->tax_paid_to_date, 2); ?></td>
                                        <td><?php echo number_format(($tax->tax_paid_to_date / max(0.01, $tax_liability)) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr class="<?php echo $remaining_tax > 0 ? 'negative' : 'positive'; ?>">
                                        <td><strong><?php echo $remaining_tax > 0 ? 'Tax Still Owed' : 'Tax Refund Expected'; ?></strong></td>
                                        <td><strong>$<?php echo number_format(abs($remaining_tax), 2); ?></strong></td>
                                        <td><strong><?php echo number_format((abs($remaining_tax) / max(0.01, $tax_liability)) * 100, 2); ?>%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-analysis">
                        <i class="fas fa-chart-bar"></i>
                        <h4>No tax data available</h4>
                        <p>Add your tax information to see the breakdown analysis.</p>
                        <button type="button" class="btn-primary" id="scrollToForm">
                            <i class="fas fa-edit"></i>
                            Add Tax Information
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Auto-Fill Modal -->
<div class="modal fade modern-modal" id="autoFillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <h5 class="modal-title">Auto-Fill Tax Information</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>This will auto-fill your tax information based on your income sources. Any existing tax information for the selected year will be updated.</p>
                <div class="warning-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Make sure you have income sources defined for accurate data.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form id="autoFillForm" action="<?php echo BASE_PATH; ?>/taxes" method="post">
                    <input type="hidden" name="action" value="auto_fill">
                    <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-magic"></i>
                        Auto-Fill
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tax Info Modal -->
<div class="modal fade modern-modal" id="deleteTaxInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title">Delete Tax Information</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete the tax information for <?php echo $selected_year; ?>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/taxes" method="post" id="deleteTaxInfoForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tax_id" id="delete_tax_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Chart data
if ($has_tax_info) {
    $chart_labels = ['Net Income After Tax', 'Total Tax'];
    $chart_data = [
        $tax->estimated_income - $tax_liability,
        $tax_liability
    ];
    $chart_colors = ['#10b981', '#ef4444'];
} else {
    $chart_labels = [];
    $chart_data = [];
    $chart_colors = [];
}

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode($chart_colors)) . '">';

require_once 'includes/footer.php';
?>