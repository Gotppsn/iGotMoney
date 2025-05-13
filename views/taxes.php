<?php
// Set page title and current page for menu highlighting
$page_title = __('tax_planning') . ' - ' . __('app_name');
$current_page = 'taxes';

// Additional CSS and JS
$additional_css = ['/assets/css/taxes-modern.css'];
$additional_js = ['/assets/js/taxes-modern.js'];

// Include header
require_once 'includes/header.php';

// Include currency helper
require_once 'includes/currency_helper.php';
?>

<!-- Main Content Wrapper -->
<div class="taxes-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('tax_planning'); ?></h1>
                <p class="page-subtitle"><?php echo __('tax_planning_subtitle'); ?></p>
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
                    <span><?php echo __('auto_fill'); ?></span>
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
                    <h3 class="stat-label"><?php echo __('estimated_income'); ?></h3>
                    <p class="stat-value"><?php echo getCurrencySymbol(); ?><?php echo formatMoney($has_tax_info ? $tax->estimated_income : 0, false); ?></p>
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
                    <h3 class="stat-label"><?php echo __('tax_liability'); ?></h3>
                    <p class="stat-value"><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax_liability, false); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-calculator"></i>
                        <span><?php echo __('estimated_for'); ?> <?php echo $selected_year; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card remaining">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('remaining_tax'); ?></h3>
                    <p class="stat-value"><?php echo getCurrencySymbol(); ?><?php echo formatMoney($remaining_tax, false); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-clock"></i>
                        <span><?php echo __('still_owed'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card rate">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('effective_tax_rate'); ?></h3>
                    <p class="stat-value"><?php echo number_format($effective_tax_rate, 2); ?>%</p>
                    <div class="stat-info">
                        <i class="fas fa-chart-pie"></i>
                        <span><?php echo __('of_gross_income'); ?></span>
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
                        <h3><?php echo __('tax_information'); ?> (<?php echo $selected_year; ?>)</h3>
                    </div>
                    <?php if ($has_tax_info): ?>
                        <div class="card-actions">
                            <button class="btn-action delete" id="deleteTaxInfo" data-tax-id="<?php echo $tax->tax_id; ?>" title="<?php echo __('delete'); ?>">
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
                                <label for="filing_status"><?php echo __('filing_status'); ?></label>
                                <select class="modern-select" id="filing_status" name="filing_status" required>
                                    <option value="single" <?php echo ($has_tax_info && $tax->filing_status == 'single') ? 'selected' : ''; ?>><?php echo __('single'); ?></option>
                                    <option value="married_joint" <?php echo ($has_tax_info && $tax->filing_status == 'married_joint') ? 'selected' : ''; ?>><?php echo __('married_joint'); ?></option>
                                    <option value="married_separate" <?php echo ($has_tax_info && $tax->filing_status == 'married_separate') ? 'selected' : ''; ?>><?php echo __('married_separate'); ?></option>
                                    <option value="head_of_household" <?php echo ($has_tax_info && $tax->filing_status == 'head_of_household') ? 'selected' : ''; ?>><?php echo __('head_of_household'); ?></option>
                                </select>
                            </div>
                            
                            <div class="form-field">
                                <label for="estimated_income"><?php echo __('estimated_annual_income'); ?></label>
                                <div class="amount-input">
                                    <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                    <input type="number" id="estimated_income" name="estimated_income" 
                                           value="<?php echo $has_tax_info ? $tax->estimated_income : $yearly_income; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help"><?php echo __('total_estimated_gross_income'); ?> <?php echo $selected_year; ?>.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="tax_paid_to_date"><?php echo __('tax_paid_to_date'); ?></label>
                                <div class="amount-input">
                                    <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                    <input type="number" id="tax_paid_to_date" name="tax_paid_to_date" 
                                           value="<?php echo $has_tax_info ? $tax->tax_paid_to_date : 0; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help"><?php echo __('tax_paid_description'); ?>.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="deductions"><?php echo __('total_deductions'); ?></label>
                                <div class="amount-input">
                                    <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                    <input type="number" id="deductions" name="deductions" 
                                           value="<?php echo $has_tax_info ? $tax->deductions : 12950; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help"><?php echo __('deductions_description'); ?>.</small>
                            </div>
                            
                            <div class="form-field">
                                <label for="credits"><?php echo __('total_tax_credits'); ?></label>
                                <div class="amount-input">
                                    <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                    <input type="number" id="credits" name="credits" 
                                           value="<?php echo $has_tax_info ? $tax->credits : 0; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-help"><?php echo __('tax_credits_description'); ?>.</small>
                            </div>
                            
                            <div class="form-field full-width">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i>
                                    <?php echo $has_tax_info ? __('update_tax_information') : __('add_tax_information'); ?>
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
                        <h3><?php echo __('tax_saving_tips'); ?></h3>
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
                                            <strong><?php echo __('potential_savings'); ?>:</strong> 
                                            <span><?php echo htmlspecialchars($tip['potential_savings']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-tips">
                            <i class="fas fa-lightbulb"></i>
                            <h4><?php echo __('no_tips_available'); ?></h4>
                            <p><?php echo __('add_tax_info_for_tips'); ?></p>
                            <button type="button" class="btn-primary" id="autoFillEmpty">
                                <i class="fas fa-magic"></i>
                                <?php echo __('auto_fill_tax_information'); ?>
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
                    <h3><?php echo __('tax_breakdown_analysis'); ?></h3>
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
                                        <th><?php echo __('item'); ?></th>
                                        <th><?php echo __('amount'); ?></th>
                                        <th><?php echo __('percentage'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo __('gross_income'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax->estimated_income, false); ?></td>
                                        <td>100%</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('deductions'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax->deductions, false); ?></td>
                                        <td><?php echo number_format(($tax->deductions / $tax->estimated_income) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('taxable_income'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney(max(0, $tax->estimated_income - $tax->deductions), false); ?></td>
                                        <td><?php echo number_format((max(0, $tax->estimated_income - $tax->deductions) / $tax->estimated_income) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('tax_before_credits'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax_liability + $tax->credits, false); ?></td>
                                        <td><?php echo number_format((($tax_liability + $tax->credits) / $tax->estimated_income) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('tax_credits'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax->credits, false); ?></td>
                                        <td><?php echo number_format(($tax->credits / $tax->estimated_income) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr class="highlight">
                                        <td><strong><?php echo __('final_tax_liability'); ?></strong></td>
                                        <td><strong><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax_liability, false); ?></strong></td>
                                        <td><strong><?php echo number_format($effective_tax_rate, 2); ?>%</strong></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('tax_paid_to_date'); ?></td>
                                        <td><?php echo getCurrencySymbol(); ?><?php echo formatMoney($tax->tax_paid_to_date, false); ?></td>
                                        <td><?php echo number_format(($tax->tax_paid_to_date / max(0.01, $tax_liability)) * 100, 2); ?>%</td>
                                    </tr>
                                    <tr class="<?php echo $remaining_tax > 0 ? 'negative' : 'positive'; ?>">
                                        <td><strong><?php echo $remaining_tax > 0 ? __('tax_still_owed') : __('tax_refund_expected'); ?></strong></td>
                                        <td><strong><?php echo getCurrencySymbol(); ?><?php echo formatMoney(abs($remaining_tax), false); ?></strong></td>
                                        <td><strong><?php echo number_format((abs($remaining_tax) / max(0.01, $tax_liability)) * 100, 2); ?>%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-analysis">
                        <i class="fas fa-chart-bar"></i>
                        <h4><?php echo __('no_tax_data'); ?></h4>
                        <p><?php echo __('add_tax_info_to_see_breakdown'); ?></p>
                        <button type="button" class="btn-primary" id="scrollToForm">
                            <i class="fas fa-edit"></i>
                            <?php echo __('add_tax_information'); ?>
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
                <h5 class="modal-title"><?php echo __('auto_fill_tax_info'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><?php echo __('auto_fill_description'); ?></p>
                <div class="warning-message">
                    <i class="fas fa-info-circle"></i>
                    <span><?php echo __('make_sure_income_sources'); ?></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form id="autoFillForm" action="<?php echo BASE_PATH; ?>/taxes" method="post">
                    <input type="hidden" name="action" value="auto_fill">
                    <input type="hidden" name="tax_year" value="<?php echo $selected_year; ?>">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-magic"></i>
                        <?php echo __('auto_fill'); ?>
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
                <h5 class="modal-title"><?php echo __('delete_tax_information'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('confirm_delete_tax'); ?> <?php echo $selected_year; ?>?</p>
                <p class="text-muted"><?php echo __('this_action_cannot_be_undone'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/taxes" method="post" id="deleteTaxInfoForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tax_id" id="delete_tax_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo __('delete'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Chart data
if ($has_tax_info) {
    $chart_labels = [__('net_income_after_tax'), __('total_tax')];
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
echo '<meta name="currency-symbol" content="' . getCurrencySymbol() . '">';

require_once 'includes/footer.php';
?>