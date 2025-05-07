<?php
// Set page title and current page for menu highlighting
$page_title = 'Financial Reports - iGotMoney';
$current_page = 'reports';

// Additional CSS and JS
$additional_css = ['/assets/css/reports-modern.css'];
$additional_js = ['/assets/js/reports.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="reports-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
    <h1>Financial Reports</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_PATH; ?>/reports?report_type=<?php echo $selected_report_type; ?>&date_range=<?php echo $selected_date_range; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=csv" class="btn btn-outline-secondary me-2">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<!-- Report Selection Form -->
<div class="card report-selection-card shadow mb-4">
    <div class="card-body">
        <form id="reportForm" method="get" action="<?php echo BASE_PATH; ?>/reports" class="row g-3">
            <div class="col-md-3">
                <label for="report_type" class="form-label">Report Type</label>
                <select class="form-select" id="report_type" name="report_type">
                    <?php foreach ($report_types as $type => $label): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($selected_report_type == $type) ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_range" class="form-label">Date Range</label>
                <select class="form-select" id="date_range" name="date_range">
                    <?php foreach ($date_ranges as $range => $data): ?>
                        <option value="<?php echo $range; ?>" <?php echo ($selected_date_range == $range) ? 'selected' : ''; ?>>
                            <?php echo $data['label']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3" id="start_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3" id="end_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="report-content">
    <?php if ($selected_report_type == 'income'): ?>
        <!-- Income Report -->
        <div class="card report-content-card income-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Income Summary</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </div>
                
                <?php if (isset($report_data['income_summary']) && $report_data['income_summary']->num_rows > 0): ?>
                    <div class="table-responsive mb-4">
                        <table class="table report-table">
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
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $row['frequency'])); ?></td>
                                        <td><?php echo $row['transaction_count']; ?></td>
                                        <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-primary">
                                    <td colspan="4"><strong>Total</strong></td>
                                    <td><strong>$<?php echo number_format($total_amount, 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($chart_data['income_trend'])): ?>
                        <h6 class="mb-3">Income Trend</h6>
                        <div class="report-chart-container">
                            <canvas id="incomeTrendChart"></canvas>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No income data found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php elseif ($selected_report_type == 'expense'): ?>
        <!-- Expense Report -->
        <div class="card report-content-card expense-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Expense Summary</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </div>
                
                <?php if (isset($report_data['expense_by_category']) && $report_data['expense_by_category']->num_rows > 0): ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="table-responsive mb-4">
                                <table class="table report-table">
                                    <thead>
                                        <tr>
                                            <th>Expense Category</th>
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
                                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                                <td><?php echo $row['transaction_count']; ?></td>
                                                <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                                <td><?php echo number_format($percentage, 2); ?>%</td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-primary">
                                            <td colspan="2"><strong>Total</strong></td>
                                            <td><strong>$<?php echo number_format($total_amount, 2); ?></strong></td>
                                            <td><strong>100%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <?php if (isset($chart_data['expense_by_category'])): ?>
                                <div class="report-chart-container">
                                    <canvas id="expenseCategoryChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($chart_data['expense_trend'])): ?>
                        <h6 class="mb-3">Expense Trend</h6>
                        <div class="report-chart-container">
                            <canvas id="expenseTrendChart"></canvas>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No expense data found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php elseif ($selected_report_type == 'budget'): ?>
        <!-- Budget Report -->
        <div class="card report-content-card budget-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Budget vs. Actual</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </div>
                
                <?php if (isset($report_data['budget_vs_actual']) && $report_data['budget_vs_actual']->num_rows > 0): ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="table-responsive mb-4">
                                <table class="table report-table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Budget</th>
                                            <th>Actual</th>
                                            <th>Difference</th>
                                            <th>% Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_budget = 0;
                                        $total_actual = 0;
                                        
                                        while ($row = $report_data['budget_vs_actual']->fetch_assoc()):
                                            $difference = $row['budget_amount'] - $row['actual_amount'];
                                            $percentage = $row['budget_amount'] > 0 ? ($row['actual_amount'] / $row['budget_amount']) * 100 : 0;
                                            $total_budget += $row['budget_amount'];
                                            $total_actual += $row['actual_amount'];
                                            
                                            $class = '';
                                            if ($percentage >= 100) {
                                                $class = 'table-danger';
                                            } elseif ($percentage >= 90) {
                                                $class = 'table-warning';
                                            }
                                        ?>
                                            <tr class="<?php echo $class; ?>">
                                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                                <td>$<?php echo number_format($row['budget_amount'], 2); ?></td>
                                                <td>$<?php echo number_format($row['actual_amount'], 2); ?></td>
                                                <td>$<?php echo number_format($difference, 2); ?></td>
                                                <td><?php echo number_format($percentage, 2); ?>%</td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-primary">
                                            <td><strong>Total</strong></td>
                                            <td><strong>$<?php echo number_format($total_budget, 2); ?></strong></td>
                                            <td><strong>$<?php echo number_format($total_actual, 2); ?></strong></td>
                                            <td><strong>$<?php echo number_format($total_budget - $total_actual, 2); ?></strong></td>
                                            <td><strong><?php echo $total_budget > 0 ? number_format(($total_actual / $total_budget) * 100, 2) : 0; ?>%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <?php if (isset($chart_data['budget_vs_actual'])): ?>
                                <div class="report-chart-container">
                                    <canvas id="budgetVsActualChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No budget data found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php elseif ($selected_report_type == 'cash_flow'): ?>
        <!-- Cash Flow Report -->
        <div class="card report-content-card cash-flow-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Cash Flow Summary</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </div>
                
                <?php if (isset($report_data['monthly_cash_flow']) && $report_data['monthly_cash_flow']->num_rows > 0): ?>
                    <div class="table-responsive mb-4">
                        <table class="table report-table">
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
                                $total_income = 0;
                                $total_expenses = 0;
                                $total_net = 0;
                                
                                while ($row = $report_data['monthly_cash_flow']->fetch_assoc()):
                                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                                    $total_income += $row['income'];
                                    $total_expenses += $row['expenses'];
                                    $total_net += $row['net'];
                                    
                                    $class = '';
                                    if ($row['net'] < 0) {
                                        $class = 'table-danger';
                                    } elseif ($row['net'] > 0) {
                                        $class = 'table-success';
                                    }
                                ?>
                                    <tr class="<?php echo $class; ?>">
                                        <td><?php echo $month_label; ?></td>
                                        <td>$<?php echo number_format($row['income'], 2); ?></td>
                                        <td>$<?php echo number_format($row['expenses'], 2); ?></td>
                                        <td>$<?php echo number_format($row['net'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td><strong>$<?php echo number_format($total_income, 2); ?></strong></td>
                                    <td><strong>$<?php echo number_format($total_expenses, 2); ?></strong></td>
                                    <td><strong>$<?php echo number_format($total_net, 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($chart_data['monthly_cash_flow'])): ?>
                        <div class="report-chart-container">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No cash flow data found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php elseif ($selected_report_type == 'investment'): ?>
        <!-- Investment Report -->
        <div class="card report-content-card investment-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Investment Summary</h6>
            </div>
            <div class="card-body">
                <?php if (isset($report_data['investment_summary']) && !empty($report_data['investment_summary'])): ?>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Total Invested:</strong> $<?php echo number_format($report_data['investment_summary']['total_invested'], 2); ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Current Value:</strong> $<?php echo number_format($report_data['investment_summary']['current_value'], 2); ?>
                                    </div>
                                    <div class="mb-3">
                                        <?php 
                                        $gain_loss = $report_data['investment_summary']['total_gain_loss'];
                                        $gain_loss_percent = $report_data['investment_summary']['percent_gain_loss'];
                                        $gain_loss_class = $gain_loss >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <strong>Total Gain/Loss:</strong> 
                                        <span class="<?php echo $gain_loss_class; ?>">
                                            <?php echo $gain_loss >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($gain_loss), 2); ?>
                                            (<?php echo number_format(abs($gain_loss_percent), 2); ?>%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <?php if (isset($chart_data['investment_by_type'])): ?>
                                <div class="report-chart-container">
                                    <canvas id="investmentByTypeChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($report_data['investment_summary']['by_type'])): ?>
                        <h6 class="mb-3 font-weight-bold">Investment Breakdown by Type</h6>
                        <div class="table-responsive">
                            <table class="table report-table">
                                <thead>
                                    <tr>
                                        <th>Investment Type</th>
                                        <th>Invested Amount</th>
                                        <th>Current Value</th>
                                        <th>Gain/Loss</th>
                                        <th>Allocation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data['investment_summary']['by_type'] as $type => $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($type); ?></td>
                                            <td>$<?php echo number_format($data['invested'], 2); ?></td>
                                            <td>$<?php echo number_format($data['current'], 2); ?></td>
                                            <td>
                                                <?php 
                                                $type_gain_loss = $data['gain_loss'];
                                                $type_gain_loss_percent = $data['percent_gain_loss'];
                                                $type_gain_loss_class = $type_gain_loss >= 0 ? 'text-success' : 'text-danger';
                                                ?>
                                                <span class="<?php echo $type_gain_loss_class; ?>">
                                                    <?php echo $type_gain_loss >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($type_gain_loss), 2); ?>
                                                    (<?php echo number_format(abs($type_gain_loss_percent), 2); ?>%)
                                                </span>
                                            </td>
                                            <td><?php echo number_format($data['percent'], 2); ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No investment data found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php else: ?>
        <!-- Comprehensive Financial Report -->
        <div class="card report-content-card financial-report shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Financial Summary</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </div>
                
                <?php if (isset($report_data['summary'])): ?>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Total Income:</strong> $<?php echo number_format($report_data['summary']['total_income'], 2); ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Total Expenses:</strong> $<?php echo number_format($report_data['summary']['total_expenses'], 2); ?>
                                    </div>
                                    <div class="mb-3">
                                        <?php 
                                        $net_savings = $report_data['summary']['net_savings'];
                                        $saving_rate = $report_data['summary']['saving_rate'];
                                        $net_savings_class = $net_savings >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <strong>Net Savings:</strong> 
                                        <span class="<?php echo $net_savings_class; ?>">
                                            <?php echo $net_savings >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($net_savings), 2); ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Saving Rate:</strong> 
                                        <span class="<?php echo $saving_rate >= 10 ? 'text-success' : ($saving_rate > 0 ? 'text-warning' : 'text-danger'); ?>">
                                            <?php echo number_format($saving_rate, 2); ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <?php if (isset($chart_data['income_vs_expense'])): ?>
                                <div class="report-chart-container">
                                    <canvas id="incomeVsExpenseChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($chart_data['monthly_trends'])): ?>
                        <h6 class="mb-3 font-weight-bold">Monthly Trends</h6>
                        <div class="report-chart-container">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($report_data['cash_flow_data']['monthly_cash_flow']) && $report_data['cash_flow_data']['monthly_cash_flow']->num_rows > 0): ?>
                        <h6 class="mt-4 mb-3 font-weight-bold">Monthly Cash Flow</h6>
                        <div class="table-responsive">
                            <table class="table report-table">
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
                                    $report_data['cash_flow_data']['monthly_cash_flow']->data_seek(0);
                                    while ($row = $report_data['cash_flow_data']['monthly_cash_flow']->fetch_assoc()):
                                        $month_label = date('M Y', strtotime($row['month'] . '-01'));
                                        $class = '';
                                        if ($row['net'] < 0) {
                                            $class = 'table-danger';
                                        } elseif ($row['net'] > 0) {
                                            $class = 'table-success';
                                        }
                                    ?>
                                        <tr class="<?php echo $class; ?>">
                                            <td><?php echo $month_label; ?></td>
                                            <td>$<?php echo number_format($row['income'], 2); ?></td>
                                            <td>$<?php echo number_format($row['expenses'], 2); ?></td>
                                            <td>$<?php echo number_format($row['net'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No financial data found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Add chart initialization JavaScript
$page_scripts = "
// Initialize date range selection
document.addEventListener('DOMContentLoaded', function() {
    var dateRangeSelector = document.getElementById('date_range');
    if (dateRangeSelector) {
        dateRangeSelector.addEventListener('change', function() {
            var customDateFields = document.getElementById('start_date_container');
            var customDateFields2 = document.getElementById('end_date_container');
            
            if (this.value === 'custom') {
                customDateFields.style.display = '';
                customDateFields2.style.display = '';
            } else {
                customDateFields.style.display = 'none';
                customDateFields2.style.display = 'none';
            }
        });
    }
";

// Income Trend Chart
if (isset($chart_data['income_trend'])) {
    $page_scripts .= "
    var incomeTrendCtx = document.getElementById('incomeTrendChart');
    if (incomeTrendCtx) {
        var incomeTrendChart = new Chart(incomeTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['income_trend']['labels']) . ",
                datasets: [{
                    label: 'Monthly Income',
                    data: " . json_encode($chart_data['income_trend']['data']) . ",
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(67, 97, 238, 1)',
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Income: $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Expense Category Chart
if (isset($chart_data['expense_by_category'])) {
    $page_scripts .= "
    var expenseCategoryCtx = document.getElementById('expenseCategoryChart');
    if (expenseCategoryCtx) {
        var expenseCategoryChart = new Chart(expenseCategoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: " . json_encode($chart_data['expense_by_category']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['expense_by_category']['data']) . ",
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(26, 188, 156, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(230, 126, 34, 0.7)',
                        'rgba(149, 165, 166, 0.7)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(67, 97, 238, 0.9)',
                        'rgba(46, 204, 113, 0.9)',
                        'rgba(52, 152, 219, 0.9)',
                        'rgba(155, 89, 182, 0.9)',
                        'rgba(243, 156, 18, 0.9)',
                        'rgba(231, 76, 60, 0.9)',
                        'rgba(26, 188, 156, 0.9)',
                        'rgba(241, 196, 15, 0.9)',
                        'rgba(230, 126, 34, 0.9)',
                        'rgba(149, 165, 166, 0.9)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 10,
                            font: {
                                size: 11
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Expense Trend Chart
if (isset($chart_data['expense_trend'])) {
    $page_scripts .= "
    var expenseTrendCtx = document.getElementById('expenseTrendChart');
    if (expenseTrendCtx) {
        var expenseTrendChart = new Chart(expenseTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['expense_trend']['labels']) . ",
                datasets: [{
                    label: 'Monthly Expenses',
                    data: " . json_encode($chart_data['expense_trend']['data']) . ",
                    backgroundColor: 'rgba(231, 76, 60, 0.2)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(231, 76, 60, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(231, 76, 60, 1)',
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Expenses: $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Budget vs Actual Chart
if (isset($chart_data['budget_vs_actual'])) {
    $page_scripts .= "
    var budgetVsActualCtx = document.getElementById('budgetVsActualChart');
    if (budgetVsActualCtx) {
        var budgetVsActualChart = new Chart(budgetVsActualCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['budget_vs_actual']['labels']) . ",
                datasets: [
                    {
                        label: 'Budget',
                        data: " . json_encode($chart_data['budget_vs_actual']['budget_data']) . ",
                        backgroundColor: 'rgba(67, 97, 238, 0.7)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: 'Actual',
                        data: " . json_encode($chart_data['budget_vs_actual']['actual_data']) . ",
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            },
                            padding: 15
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Cash Flow Chart
if (isset($chart_data['monthly_cash_flow'])) {
    $page_scripts .= "
    var cashFlowCtx = document.getElementById('cashFlowChart');
    if (cashFlowCtx) {
        var cashFlowChart = new Chart(cashFlowCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['monthly_cash_flow']['labels']) . ",
                datasets: [
                    {
                        label: 'Income',
                        data: " . json_encode($chart_data['monthly_cash_flow']['income_data']) . ",
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: 'Expenses',
                        data: " . json_encode($chart_data['monthly_cash_flow']['expense_data']) . ",
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: 'Net Cash Flow',
                        data: " . json_encode($chart_data['monthly_cash_flow']['net_data']) . ",
                        type: 'line',
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(67, 97, 238, 1)',
                        tension: 0.3,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            },
                            padding: 15
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Investment By Type Chart
if (isset($chart_data['investment_by_type'])) {
    $page_scripts .= "
    var investmentByTypeCtx = document.getElementById('investmentByTypeChart');
    if (investmentByTypeCtx) {
        var investmentByTypeChart = new Chart(investmentByTypeCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: " . json_encode($chart_data['investment_by_type']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['investment_by_type']['data']) . ",
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(26, 188, 156, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(230, 126, 34, 0.7)',
                        'rgba(149, 165, 166, 0.7)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(67, 97, 238, 0.9)',
                        'rgba(46, 204, 113, 0.9)',
                        'rgba(52, 152, 219, 0.9)',
                        'rgba(155, 89, 182, 0.9)',
                        'rgba(243, 156, 18, 0.9)',
                        'rgba(231, 76, 60, 0.9)',
                        'rgba(26, 188, 156, 0.9)',
                        'rgba(241, 196, 15, 0.9)',
                        'rgba(230, 126, 34, 0.9)',
                        'rgba(149, 165, 166, 0.9)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 10,
                            font: {
                                size: 11
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Income vs Expense Chart
if (isset($chart_data['income_vs_expense'])) {
    $page_scripts .= "
    var incomeVsExpenseCtx = document.getElementById('incomeVsExpenseChart');
    if (incomeVsExpenseCtx) {
        var incomeVsExpenseChart = new Chart(incomeVsExpenseCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: " . json_encode($chart_data['income_vs_expense']['labels']) . ",
                datasets: [{
                    data: " . json_encode($chart_data['income_vs_expense']['data']) . ",
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(46, 204, 113, 0.9)',
                        'rgba(231, 76, 60, 0.9)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

// Monthly Trends Chart
if (isset($chart_data['monthly_trends'])) {
    $page_scripts .= "
    var monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        var monthlyTrendsChart = new Chart(monthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['monthly_trends']['labels']) . ",
                datasets: [
                    {
                        label: 'Income',
                        data: " . json_encode($chart_data['monthly_trends']['income_data']) . ",
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(46, 204, 113, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(46, 204, 113, 1)',
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Expenses',
                        data: " . json_encode($chart_data['monthly_trends']['expense_data']) . ",
                        backgroundColor: 'rgba(231, 76, 60, 0.2)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(231, 76, 60, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(231, 76, 60, 1)',
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Net',
                        data: " . json_encode($chart_data['monthly_trends']['net_data']) . ",
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(67, 97, 238, 1)',
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 12
                            },
                            padding: 15
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    ";
}

$page_scripts .= "
    // Initialize table responsive indicators
    var tables = document.querySelectorAll('.table-responsive');
    tables.forEach(function(table) {
        if (table.scrollWidth > table.clientWidth) {
            table.classList.add('has-overflow');
        }
    });
});
";

// Include footer
require_once 'includes/footer.php';
?>