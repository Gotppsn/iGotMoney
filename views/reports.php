<?php
// Set page title and current page for menu highlighting
$page_title = 'Financial Reports - iGotMoney';
$current_page = 'reports';

// Additional CSS and JS
$additional_css = ['/assets/css/reports-modern.css'];
$additional_js = ['/assets/js/reports-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="reports-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Financial Reports</h1>
                <p class="page-subtitle">Comprehensive insights into your financial performance</p>
            </div>
            <div class="page-actions">
                <a href="<?php echo BASE_PATH; ?>/reports?report_type=<?php echo $selected_report_type; ?>&date_range=<?php echo $selected_date_range; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=csv" class="btn-export">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </a>
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Report Selection Section -->
    <div class="report-selection-section">
        <div class="selection-card">
            <div class="selection-header">
                <div class="selection-title">
                    <i class="fas fa-filter"></i>
                    <h3>Report Parameters</h3>
                </div>
            </div>
            <div class="selection-body">
                <form id="reportForm" method="get" action="<?php echo BASE_PATH; ?>/reports">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="report_type">Report Type</label>
                            <select class="modern-select" id="report_type" name="report_type">
                                <?php foreach ($report_types as $type => $label): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($selected_report_type == $type) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="date_range">Date Range</label>
                            <select class="modern-select" id="date_range" name="date_range">
                                <?php foreach ($date_ranges as $range => $data): ?>
                                    <option value="<?php echo $range; ?>" <?php echo ($selected_date_range == $range) ? 'selected' : ''; ?>>
                                        <?php echo $data['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-field custom-date-field" id="start_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="modern-input" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        
                        <div class="form-field custom-date-field" id="end_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                            <label for="end_date">End Date</label>
                            <input type="date" class="modern-input" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-generate">
                                <i class="fas fa-chart-bar"></i>
                                <span>Generate Report</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Content Section -->
    <div class="report-content-section">
        <?php if ($selected_report_type == 'income'): ?>
            <!-- Income Report -->
            <div class="report-card income-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-wallet"></i>
                        <h3>Income Summary Report</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['income_summary']) && $report_data['income_summary']->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Income Source</th>
                                        <th>Amount</th>
                                        <th>Frequency</th>
                                        <th>Transactions</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_amount = 0;
                                    while ($row = $report_data['income_summary']->fetch_assoc()):
                                        $total_amount += $row['total_amount'];
                                    ?>
                                        <tr>
                                            <td class="source-cell"><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td class="amount-cell">$<?php echo number_format($row['amount'], 2); ?></td>
                                            <td class="frequency-cell">
                                                <span class="frequency-badge"><?php echo ucfirst(str_replace('_', ' ', $row['frequency'])); ?></span>
                                            </td>
                                            <td class="count-cell"><?php echo $row['transaction_count']; ?></td>
                                            <td class="total-cell">$<?php echo number_format($row['total_amount'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="4"><strong>Total Income</strong></td>
                                        <td><strong>$<?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php if (isset($chart_data['income_trend'])): ?>
                            <div class="chart-section">
                                <h4 class="chart-title">
                                    <i class="fas fa-chart-line"></i>
                                    Income Trend Analysis
                                </h4>
                                <div class="chart-container">
                                    <canvas id="incomeTrendChart"></canvas>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <h4>No Income Data Available</h4>
                            <p>No income records found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'expense'): ?>
            <!-- Expense Report -->
            <div class="report-card expense-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-credit-card"></i>
                        <h3>Expense Analysis Report</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['expense_by_category']) && $report_data['expense_by_category']->num_rows > 0): ?>
                        <div class="report-grid">
                            <div class="table-section">
                                <div class="table-responsive">
                                    <table class="modern-table">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Transactions</th>
                                                <th>Total Amount</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_amount = 0;
                                            $expense_by_category = $report_data['expense_by_category'];
                                            
                                            // Calculate total first
                                            $expense_by_category->data_seek(0);
                                            while ($row = $expense_by_category->fetch_assoc()) {
                                                $total_amount += $row['total_amount'];
                                            }
                                            
                                            // Display data
                                            $expense_by_category->data_seek(0);
                                            while ($row = $expense_by_category->fetch_assoc()):
                                                $percentage = $total_amount > 0 ? ($row['total_amount'] / $total_amount) * 100 : 0;
                                            ?>
                                                <tr>
                                                    <td class="category-cell">
                                                        <span class="category-badge"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                                    </td>
                                                    <td class="count-cell"><?php echo $row['transaction_count']; ?></td>
                                                    <td class="amount-cell">$<?php echo number_format($row['total_amount'], 2); ?></td>
                                                    <td class="percentage-cell">
                                                        <div class="percentage-bar">
                                                            <div class="percentage-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                            <span class="percentage-text"><?php echo number_format($percentage, 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="2"><strong>Total Expenses</strong></td>
                                                <td><strong>$<?php echo number_format($total_amount, 2); ?></strong></td>
                                                <td><strong>100%</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="chart-section">
                                <?php if (isset($chart_data['expense_by_category'])): ?>
                                    <div class="chart-container category-chart">
                                        <canvas id="expenseCategoryChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (isset($chart_data['expense_trend'])): ?>
                            <div class="chart-section full-width">
                                <h4 class="chart-title">
                                    <i class="fas fa-chart-area"></i>
                                    Expense Trend Analysis
                                </h4>
                                <div class="chart-container">
                                    <canvas id="expenseTrendChart"></canvas>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <h4>No Expense Data Available</h4>
                            <p>No expense records found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'budget'): ?>
            <!-- Budget Report -->
            <div class="report-card budget-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Budget Performance Report</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['budget_vs_actual']) && $report_data['budget_vs_actual']->num_rows > 0): ?>
                        <div class="budget-summary">
                            <?php
                            $report_data['budget_vs_actual']->data_seek(0);
                            $total_budget = 0;
                            $total_actual = 0;
                            while ($row = $report_data['budget_vs_actual']->fetch_assoc()) {
                                $total_budget += $row['budget_amount'];
                                $total_actual += $row['actual_amount'];
                            }
                            $overall_percentage = $total_budget > 0 ? ($total_actual / $total_budget) * 100 : 0;
                            $budget_status = '';
                            if ($overall_percentage >= 100) {
                                $budget_status = 'over-budget';
                            } elseif ($overall_percentage >= 90) {
                                $budget_status = 'warning';
                            } else {
                                $budget_status = 'on-track';
                            }
                            ?>
                            <div class="summary-card <?php echo $budget_status; ?>">
                                <div class="summary-icon">
                                    <?php if ($budget_status == 'over-budget'): ?>
                                        <i class="fas fa-exclamation-triangle"></i>
                                    <?php elseif ($budget_status == 'warning'): ?>
                                        <i class="fas fa-exclamation-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="summary-content">
                                    <h4>Overall Budget Status</h4>
                                    <div class="summary-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">Total Budget</span>
                                            <span class="stat-value">$<?php echo number_format($total_budget, 2); ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Actual Spent</span>
                                            <span class="stat-value">$<?php echo number_format($total_actual, 2); ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Budget Used</span>
                                            <span class="stat-value"><?php echo number_format($overall_percentage, 1); ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="modern-table budget-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Budget</th>
                                        <th>Actual</th>
                                        <th>Variance</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $report_data['budget_vs_actual']->data_seek(0);
                                    while ($row = $report_data['budget_vs_actual']->fetch_assoc()):
                                        $difference = $row['budget_amount'] - $row['actual_amount'];
                                        $percentage = $row['budget_amount'] > 0 ? ($row['actual_amount'] / $row['budget_amount']) * 100 : 0;
                                        
                                        $status_class = '';
                                        if ($percentage >= 100) {
                                            $status_class = 'over-budget';
                                        } elseif ($percentage >= 90) {
                                            $status_class = 'warning';
                                        } else {
                                            $status_class = 'on-track';
                                        }
                                    ?>
                                        <tr class="<?php echo $status_class; ?>">
                                            <td class="category-cell">
                                                <span class="category-badge"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                            </td>
                                            <td class="amount-cell">$<?php echo number_format($row['budget_amount'], 2); ?></td>
                                            <td class="amount-cell">$<?php echo number_format($row['actual_amount'], 2); ?></td>
                                            <td class="variance-cell">
                                                <span class="variance-amount <?php echo $difference >= 0 ? 'positive' : 'negative'; ?>">
                                                    <?php echo $difference >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($difference), 2); ?>
                                                </span>
                                            </td>
                                            <td class="progress-cell">
                                                <div class="budget-progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill <?php echo $status_class; ?>" style="width: <?php echo min(100, $percentage); ?>%"></div>
                                                    </div>
                                                    <span class="progress-text"><?php echo number_format($percentage, 1); ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (isset($chart_data['budget_vs_actual'])): ?>
                            <div class="chart-section">
                                <h4 class="chart-title">
                                    <i class="fas fa-chart-bar"></i>
                                    Budget vs Actual Comparison
                                </h4>
                                <div class="chart-container">
                                    <canvas id="budgetVsActualChart"></canvas>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <h4>No Budget Data Available</h4>
                            <p>No budget information found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'cash_flow'): ?>
            <!-- Cash Flow Report -->
            <div class="report-card cash-flow-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-exchange-alt"></i>
                        <h3>Cash Flow Statement</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['monthly_cash_flow']) && !empty($report_data['monthly_cash_flow'])): ?>
                        <div class="cash-flow-summary">
                            <?php
                            $total_income = 0;
                            $total_expenses = 0;
                            $total_net = 0;
                            
                            foreach ($report_data['monthly_cash_flow'] as $row) {
                                $total_income += $row['income'];
                                $total_expenses += $row['expenses'];
                                $total_net += $row['net'];
                            }
                            ?>
                            <div class="summary-grid">
                                <div class="summary-item income">
                                    <div class="summary-icon">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <div class="summary-details">
                                        <span class="summary-label">Total Income</span>
                                        <span class="summary-value">$<?php echo number_format($total_income, 2); ?></span>
                                    </div>
                                </div>
                                <div class="summary-item expense">
                                    <div class="summary-icon">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <div class="summary-details">
                                        <span class="summary-label">Total Expenses</span>
                                        <span class="summary-value">$<?php echo number_format($total_expenses, 2); ?></span>
                                    </div>
                                </div>
                                <div class="summary-item net <?php echo $total_net >= 0 ? 'positive' : 'negative'; ?>">
                                    <div class="summary-icon">
                                        <i class="fas fa-<?php echo $total_net >= 0 ? 'plus' : 'minus'; ?>-circle"></i>
                                    </div>
                                    <div class="summary-details">
                                        <span class="summary-label">Net Cash Flow</span>
                                        <span class="summary-value">$<?php echo number_format(abs($total_net), 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Income</th>
                                        <th>Expenses</th>
                                        <th>Net Cash Flow</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($report_data['monthly_cash_flow'] as $row):
                                        $month_label = date('M Y', strtotime($row['month'] . '-01'));
                                        $net_class = $row['net'] >= 0 ? 'positive' : 'negative';
                                    ?>
                                        <tr>
                                            <td class="month-cell"><?php echo $month_label; ?></td>
                                            <td class="income-cell">$<?php echo number_format($row['income'], 2); ?></td>
                                            <td class="expense-cell">$<?php echo number_format($row['expenses'], 2); ?></td>
                                            <td class="net-cell">
                                                <span class="net-amount <?php echo $net_class; ?>">
                                                    <?php echo $row['net'] >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($row['net']), 2); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td><strong>Total</strong></td>
                                        <td><strong>$<?php echo number_format($total_income, 2); ?></strong></td>
                                        <td><strong>$<?php echo number_format($total_expenses, 2); ?></strong></td>
                                        <td>
                                            <strong class="<?php echo $total_net >= 0 ? 'positive' : 'negative'; ?>">
                                                <?php echo $total_net >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($total_net), 2); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php if (isset($chart_data['monthly_cash_flow'])): ?>
                            <div class="chart-section">
                                <h4 class="chart-title">
                                    <i class="fas fa-chart-line"></i>
                                    Cash Flow Visualization
                                </h4>
                                <div class="chart-container">
                                    <canvas id="cashFlowChart"></canvas>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-exchange-alt"></i>
                            <h4>No Cash Flow Data Available</h4>
                            <p>No cash flow records found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'investment'): ?>
            <!-- Investment Report -->
            <div class="report-card investment-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-chart-line"></i>
                        <h3>Investment Portfolio Report</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            As of <?php echo date('M j, Y'); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['investment_summary']) && !empty($report_data['investment_summary'])): ?>
                        <div class="investment-overview">
                            <div class="overview-grid">
                                <div class="overview-card">
                                    <div class="overview-icon">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="overview-content">
                                        <span class="overview-label">Total Invested</span>
                                        <span class="overview-value">$<?php echo number_format($report_data['investment_summary']['total_invested'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="overview-card">
                                    <div class="overview-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="overview-content">
                                        <span class="overview-label">Current Value</span>
                                        <span class="overview-value">$<?php echo number_format($report_data['investment_summary']['current_value'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="overview-card">
                                    <div class="overview-icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <?php 
                                    $gain_loss = $report_data['investment_summary']['total_gain_loss'];
                                    $gain_loss_percent = $report_data['investment_summary']['percent_gain_loss'];
                                    $gain_loss_class = $gain_loss >= 0 ? 'positive' : 'negative';
                                    ?>
                                    <div class="overview-content">
                                        <span class="overview-label">Total Gain/Loss</span>
                                        <span class="overview-value <?php echo $gain_loss_class; ?>">
                                            <?php echo $gain_loss >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($gain_loss), 2); ?>
                                            <small>(<?php echo number_format(abs($gain_loss_percent), 2); ?>%)</small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isset($report_data['investment_summary']['by_type'])): ?>
                            <div class="report-grid">
                                <div class="table-section">
                                    <h4 class="section-title">Investment Breakdown by Type</h4>
                                    <div class="table-responsive">
                                        <table class="modern-table">
                                            <thead>
                                                <tr>
                                                    <th>Investment Type</th>
                                                    <th>Invested</th>
                                                    <th>Current Value</th>
                                                    <th>Gain/Loss</th>
                                                    <th>Allocation</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data['investment_summary']['by_type'] as $type => $data): ?>
                                                    <tr>
                                                        <td class="type-cell">
                                                            <span class="type-badge"><?php echo htmlspecialchars($type); ?></span>
                                                        </td>
                                                        <td class="amount-cell">$<?php echo number_format($data['invested'], 2); ?></td>
                                                        <td class="amount-cell">$<?php echo number_format($data['current'], 2); ?></td>
                                                        <td class="gain-loss-cell">
                                                            <?php 
                                                            $type_gain_loss = $data['gain_loss'];
                                                            $type_gain_loss_percent = $data['percent_gain_loss'];
                                                            $type_gain_loss_class = $type_gain_loss >= 0 ? 'positive' : 'negative';
                                                            ?>
                                                            <span class="gain-loss <?php echo $type_gain_loss_class; ?>">
                                                                <?php echo $type_gain_loss >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($type_gain_loss), 2); ?>
                                                                <small>(<?php echo number_format(abs($type_gain_loss_percent), 2); ?>%)</small>
                                                            </span>
                                                        </td>
                                                        <td class="allocation-cell">
                                                            <div class="allocation-bar">
                                                                <div class="allocation-fill" style="width: <?php echo $data['percent']; ?>%"></div>
                                                                <span class="allocation-text"><?php echo number_format($data['percent'], 1); ?>%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="chart-section">
                                    <?php if (isset($chart_data['investment_by_type'])): ?>
                                        <h4 class="section-title">Portfolio Allocation</h4>
                                        <div class="chart-container allocation-chart">
                                            <canvas id="investmentByTypeChart"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h4>No Investment Data Available</h4>
                            <p>No investment information found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php else: ?>
            <!-- Comprehensive Financial Report -->
            <div class="report-card financial-report">
                <div class="report-header">
                    <div class="report-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3>Comprehensive Financial Report</h3>
                    </div>
                    <div class="report-meta">
                        <span class="date-range">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="report-body">
                    <?php if (isset($report_data['summary'])): ?>
                        <div class="financial-overview">
                            <div class="overview-grid">
                                <div class="overview-card">
                                    <div class="overview-icon income">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="overview-content">
                                        <span class="overview-label">Total Income</span>
                                        <span class="overview-value">$<?php echo number_format($report_data['summary']['total_income'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="overview-card">
                                    <div class="overview-icon expense">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="overview-content">
                                        <span class="overview-label">Total Expenses</span>
                                        <span class="overview-value">$<?php echo number_format($report_data['summary']['total_expenses'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="overview-card">
                                    <div class="overview-icon savings">
                                        <i class="fas fa-piggy-bank"></i>
                                    </div>
                                    <?php 
                                    $net_savings = $report_data['summary']['net_savings'];
                                    $net_savings_class = $net_savings >= 0 ? 'positive' : 'negative';
                                    ?>
                                    <div class="overview-content">
                                        <span class="overview-label">Net Savings</span>
                                        <span class="overview-value <?php echo $net_savings_class; ?>">
                                            <?php echo $net_savings >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($net_savings), 2); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="overview-card">
                                    <div class="overview-icon rate">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <?php 
                                    $saving_rate = $report_data['summary']['saving_rate'];
                                    $rate_class = $saving_rate >= 20 ? 'excellent' : ($saving_rate >= 10 ? 'good' : ($saving_rate > 0 ? 'fair' : 'poor'));
                                    ?>
                                    <div class="overview-content">
                                        <span class="overview-label">Saving Rate</span>
                                        <span class="overview-value <?php echo $rate_class; ?>">
                                            <?php echo number_format($saving_rate, 1); ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-sections">
                            <?php if (isset($chart_data['income_vs_expense'])): ?>
                                <div class="chart-section half-width">
                                    <h4 class="section-title">
                                        <i class="fas fa-balance-scale"></i>
                                        Income vs Expenses
                                    </h4>
                                    <div class="chart-container donut-chart">
                                        <canvas id="incomeVsExpenseChart"></canvas>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($chart_data['monthly_trends'])): ?>
                                <div class="chart-section full-width">
                                    <h4 class="section-title">
                                        <i class="fas fa-chart-area"></i>
                                        Monthly Financial Trends
                                    </h4>
                                    <div class="chart-container">
                                        <canvas id="monthlyTrendsChart"></canvas>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($report_data['cash_flow_data']['monthly_cash_flow']) && !empty($report_data['cash_flow_data']['monthly_cash_flow'])): ?>
                                <div class="cash-flow-section">
                                    <h4 class="section-title">
                                        <i class="fas fa-stream"></i>
                                        Monthly Cash Flow Details
                                    </h4>
                                    <div class="table-responsive">
                                        <table class="modern-table">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Income</th>
                                                    <th>Expenses</th>
                                                    <th>Net Cash Flow</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                foreach ($report_data['cash_flow_data']['monthly_cash_flow'] as $row):
                                                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                                                    $net_class = $row['net'] >= 0 ? 'positive' : 'negative';
                                                ?>
                                                    <tr>
                                                        <td class="month-cell"><?php echo $month_label; ?></td>
                                                        <td class="income-cell">$<?php echo number_format($row['income'], 2); ?></td>
                                                        <td class="expense-cell">$<?php echo number_format($row['expenses'], 2); ?></td>
                                                        <td class="net-cell">
                                                            <span class="net-amount <?php echo $net_class; ?>">
                                                                <?php echo $row['net'] >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($row['net']), 2); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <h4>No Financial Data Available</h4>
                            <p>No financial records found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Add chart initialization JavaScript
$page_scripts = "
// Initialize all charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range selection
    initializeDateRangeSelector();
    
    // Animate elements on page load
    animateReportElements();
    
    // Initialize charts based on report type
    initializeReportCharts();
});

// Date range selector functionality
function initializeDateRangeSelector() {
    const dateRangeSelector = document.getElementById('date_range');
    const startDateContainer = document.getElementById('start_date_container');
    const endDateContainer = document.getElementById('end_date_container');
    
    if (dateRangeSelector) {
        dateRangeSelector.addEventListener('change', function() {
            if (this.value === 'custom') {
                startDateContainer.style.display = '';
                endDateContainer.style.display = '';
                // Smooth scroll to show custom date fields
                setTimeout(() => {
                    startDateContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            } else {
                startDateContainer.style.display = 'none';
                endDateContainer.style.display = 'none';
            }
        });
    }
}

// Animate report elements
function animateReportElements() {
    const reportCards = document.querySelectorAll('.report-card');
    const overviewCards = document.querySelectorAll('.overview-card');
    const summaryCards = document.querySelectorAll('.summary-card');
    
    // Animate report cards
    reportCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
    
    // Animate overview cards
    overviewCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 300 + (index * 100));
    });
    
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-fill, .percentage-fill, .allocation-fill');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = width;
        }, 500 + (index * 50));
    });
}

// Initialize all charts
function initializeReportCharts() {
    // Chart.js default configuration
    Chart.defaults.font.family = \"'Inter', sans-serif\";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels.padding = 20;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
    Chart.defaults.plugins.tooltip.padding = 16;
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
    Chart.defaults.plugins.tooltip.cornerRadius = 12;
    Chart.defaults.plugins.tooltip.titleFont.size = 14;
    Chart.defaults.plugins.tooltip.bodyFont.size = 13;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxWidth = 12;
    Chart.defaults.plugins.tooltip.boxHeight = 12;
    Chart.defaults.plugins.tooltip.usePointStyle = true;
    
    // Color palette
    const colors = {
        primary: '#6366f1',
        success: '#10b981',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
        purple: '#8b5cf6',
        pink: '#ec4899',
        teal: '#14b8a6',
        cyan: '#06b6d4',
        secondary: '#64748b'
    };
    
    const chartColors = [
        colors.primary,
        colors.success,
        colors.info,
        colors.warning,
        colors.purple,
        colors.pink,
        colors.teal,
        colors.cyan,
        colors.danger,
        colors.secondary
    ];
";

// Income Trend Chart
if (isset($chart_data['income_trend']) && !empty($chart_data['income_trend']['labels'])) {
    $page_scripts .= "
    // Income Trend Chart
    const incomeTrendCtx = document.getElementById('incomeTrendChart');
    if (incomeTrendCtx) {
        new Chart(incomeTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['income_trend']['labels']) . ",
                datasets: [{
                    label: 'Monthly Income',
                    data: " . json_encode($chart_data['income_trend']['data']) . ",
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    borderWidth: 3,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Income: $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    ";
}

// Expense Category Chart
if (isset($chart_data['expense_by_category']) && !empty($chart_data['expense_by_category']['labels'])) {
    $page_scripts .= "
    // Expense Category Chart
    const expenseCategoryCtx = document.getElementById('expenseCategoryChart');
    if (expenseCategoryCtx) {
        new Chart(expenseCategoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: " . json_encode($chart_data['expense_by_category']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['expense_by_category']['data']) . ",
                    backgroundColor: chartColors,
                    hoverBackgroundColor: chartColors.map(color => color + 'dd'),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderColor: '#ffffff',
                    hoverBorderWidth: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                spacing: 2,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            padding: 15,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    ";
}

// Expense Trend Chart
if (isset($chart_data['expense_trend']) && !empty($chart_data['expense_trend']['labels'])) {
    $page_scripts .= "
    // Expense Trend Chart
    const expenseTrendCtx = document.getElementById('expenseTrendChart');
    if (expenseTrendCtx) {
        new Chart(expenseTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['expense_trend']['labels']) . ",
                datasets: [{
                    label: 'Monthly Expenses',
                    data: " . json_encode($chart_data['expense_trend']['data']) . ",
                    borderColor: colors.danger,
                    backgroundColor: colors.danger + '20',
                    borderWidth: 3,
                    pointBackgroundColor: colors.danger,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Expenses: $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    ";
}

// Budget vs Actual Chart
if (isset($chart_data['budget_vs_actual']) && !empty($chart_data['budget_vs_actual']['labels'])) {
    $page_scripts .= "
    // Budget vs Actual Chart
    const budgetVsActualCtx = document.getElementById('budgetVsActualChart');
    if (budgetVsActualCtx) {
        new Chart(budgetVsActualCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['budget_vs_actual']['labels']) . ",
                datasets: [
                    {
                        label: 'Budget',
                        data: " . json_encode($chart_data['budget_vs_actual']['budget_data']) . ",
                        backgroundColor: colors.info + 'cc',
                        borderColor: colors.info,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    },
                    {
                        label: 'Actual',
                        data: " . json_encode($chart_data['budget_vs_actual']['actual_data']) . ",
                        backgroundColor: colors.warning + 'cc',
                        borderColor: colors.warning,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            padding: 20,
                            boxWidth: 16,
                            boxHeight: 16,
                            borderRadius: 4
                        }
                    }
                }
            }
        });
    }
    ";
}

// Cash Flow Chart
if (isset($chart_data['monthly_cash_flow']) && !empty($chart_data['monthly_cash_flow']['labels'])) {
    $page_scripts .= "
    // Cash Flow Chart
    const cashFlowCtx = document.getElementById('cashFlowChart');
    if (cashFlowCtx) {
        new Chart(cashFlowCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['monthly_cash_flow']['labels']) . ",
                datasets: [
                    {
                        label: 'Income',
                        data: " . json_encode($chart_data['monthly_cash_flow']['income_data']) . ",
                        backgroundColor: colors.success + 'cc',
                        borderColor: colors.success,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    },
                    {
                        label: 'Expenses',
                        data: " . json_encode($chart_data['monthly_cash_flow']['expense_data']) . ",
                        backgroundColor: colors.danger + 'cc',
                        borderColor: colors.danger,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    },
                    {
                        label: 'Net Cash Flow',
                        data: " . json_encode($chart_data['monthly_cash_flow']['net_data']) . ",
                        type: 'line',
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        borderWidth: 3,
                        pointBackgroundColor: colors.primary,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const prefix = context.dataset.label + ': ';
                                const value = context.raw;
                                const sign = value >= 0 ? '+' : '';
                                return prefix + sign + '$' + Math.abs(value).toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            padding: 20,
                            boxWidth: 16,
                            boxHeight: 16,
                            borderRadius: 4
                        }
                    }
                }
            }
        });
    }
    ";
}

// Investment By Type Chart
if (isset($chart_data['investment_by_type']) && !empty($chart_data['investment_by_type']['labels'])) {
    $page_scripts .= "
    // Investment By Type Chart
    const investmentByTypeCtx = document.getElementById('investmentByTypeChart');
    if (investmentByTypeCtx) {
        new Chart(investmentByTypeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: " . json_encode($chart_data['investment_by_type']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['investment_by_type']['data']) . ",
                    backgroundColor: chartColors,
                    hoverBackgroundColor: chartColors.map(color => color + 'dd'),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderColor: '#ffffff',
                    hoverBorderWidth: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                spacing: 2,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            padding: 15,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    ";
}

// Income vs Expense Chart
if (isset($chart_data['income_vs_expense']) && !empty($chart_data['income_vs_expense']['labels'])) {
    $page_scripts .= "
    // Income vs Expense Chart
    const incomeVsExpenseCtx = document.getElementById('incomeVsExpenseChart');
    if (incomeVsExpenseCtx) {
        new Chart(incomeVsExpenseCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: " . json_encode($chart_data['income_vs_expense']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['income_vs_expense']['data']) . ",
                    backgroundColor: [colors.success + 'cc', colors.danger + 'cc'],
                    hoverBackgroundColor: [colors.success + 'dd', colors.danger + 'dd'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderColor: '#ffffff',
                    hoverBorderWidth: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                spacing: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    ";
}

// Monthly Trends Chart
if (isset($chart_data['monthly_trends']) && !empty($chart_data['monthly_trends']['labels'])) {
    $page_scripts .= "
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        new Chart(monthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['monthly_trends']['labels']) . ",
                datasets: [
                    {
                        label: 'Income',
                        data: " . json_encode($chart_data['monthly_trends']['income_data']) . ",
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        borderWidth: 3,
                        pointBackgroundColor: colors.success,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Expenses',
                        data: " . json_encode($chart_data['monthly_trends']['expense_data']) . ",
                        borderColor: colors.danger,
                        backgroundColor: colors.danger + '20',
                        borderWidth: 3,
                        pointBackgroundColor: colors.danger,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Net',
                        data: " . json_encode($chart_data['monthly_trends']['net_data']) . ",
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        borderWidth: 3,
                        pointBackgroundColor: colors.primary,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.4,
                        fill: true,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const prefix = context.dataset.label + ': ';
                                const value = context.raw;
                                const sign = value >= 0 ? '+' : '';
                                return prefix + sign + '$' + Math.abs(value).toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            padding: 20,
                            boxWidth: 16,
                            boxHeight: 16,
                            borderRadius: 4
                        }
                    }
                }
            }
        });
    }
    ";
}

$page_scripts .= "
}
";

// Include footer
require_once 'includes/footer.php';
?>