<?php
// Set page title and current page for menu highlighting
$page_title = __('financial_reports') . ' - ' . __('app_name');
$current_page = 'reports';

// Additional CSS and JS
$additional_css = ['/assets/css/reports-modern.css'];
$additional_js = ['/assets/js/reports.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="reports-page">
    <!-- Page Header Section -->
    <div class="reports-header">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('financial_reports'); ?></h1>
                <p class="page-subtitle"><?php echo __('comprehensive_insights'); ?></p>
            </div>
            <div class="btn-toolbar">
                <a href="<?php echo BASE_PATH; ?>/reports?report_type=<?php echo $selected_report_type; ?>&date_range=<?php echo $selected_date_range; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=csv" class="btn">
                    <i class="fas fa-download"></i> <?php echo __('export_csv'); ?>
                </a>
                <button type="button" class="btn" onclick="window.print()">
                    <i class="fas fa-print"></i> <?php echo __('print_report'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Report Selection Form -->
    <div class="card report-selection-card">
        <div class="card-body">
            <h3 class="card-title"><?php echo __('report_parameters'); ?></h3>
            <form id="reportForm" method="get" action="<?php echo BASE_PATH; ?>/reports" class="report-form">
                <div class="form-group">
                    <label for="report_type"><?php echo __('report_type'); ?></label>
                    <select class="form-select" id="report_type" name="report_type">
                        <?php foreach ($report_types as $type => $label): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($selected_report_type == $type) ? 'selected' : ''; ?>>
                                <?php echo __($type . '_report'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_range"><?php echo __('date_range'); ?></label>
                    <select class="form-select" id="date_range" name="date_range">
                        <?php foreach ($date_ranges as $range => $data): ?>
                            <option value="<?php echo $range; ?>" <?php echo ($selected_date_range == $range) ? 'selected' : ''; ?>>
                                <?php echo __($range); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="start_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                    <label for="start_date"><?php echo __('start_date'); ?></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group" id="end_date_container" style="<?php echo ($selected_date_range == 'custom') ? '' : 'display: none;'; ?>">
                    <label for="end_date"><?php echo __('end_date'); ?></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> <?php echo __('generate_report'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content -->
    <div class="report-content">
        <?php if ($selected_report_type == 'income'): ?>
            <!-- Income Report -->
            <div class="card report-content-card income-report">
                <div class="card-header">
                    <h6><i class="fas fa-wallet"></i> <?php echo __('income_summary'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> <strong><?php echo __('period'); ?>:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    
                    <?php if (isset($report_data['income_summary']) && $report_data['income_summary']->num_rows > 0): ?>
                        <div class="table-responsive mb-4">
                            <table class="table report-table">
                                <thead>
                                    <tr>
                                        <th><?php echo __('income_source'); ?></th>
                                        <th><?php echo __('amount'); ?></th>
                                        <th><?php echo __('frequency'); ?></th>
                                        <th><?php echo __('transactions'); ?></th>
                                        <th><?php echo __('total_amount'); ?></th>
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
                                        <td colspan="4"><strong><?php echo __('total'); ?></strong></td>
                                        <td><strong>$<?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (isset($chart_data['income_trend'])): ?>
                            <h6 class="mb-3"><i class="fas fa-chart-line"></i> <?php echo __('income_trend'); ?></h6>
                            <div class="report-chart-container">
                                <canvas id="incomeTrendChart"></canvas>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_income_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'expense'): ?>
            <!-- Expense Report -->
            <div class="card report-content-card expense-report">
                <div class="card-header">
                    <h6><i class="fas fa-credit-card"></i> <?php echo __('expense_summary'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> <strong><?php echo __('period'); ?>:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    
                    <?php if (isset($report_data['expense_by_category']) && $report_data['expense_by_category']->num_rows > 0): ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="table-responsive mb-4">
                                    <table class="table report-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('expense_category'); ?></th>
                                                <th><?php echo __('transactions'); ?></th>
                                                <th><?php echo __('total_amount'); ?></th>
                                                <th><?php echo __('percentage'); ?></th>
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
                                                <td colspan="2"><strong><?php echo __('total'); ?></strong></td>
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
                            <h6 class="mb-3"><i class="fas fa-chart-line"></i> <?php echo __('expense_trend'); ?></h6>
                            <div class="report-chart-container">
                                <canvas id="expenseTrendChart"></canvas>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_expense_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'budget'): ?>
            <!-- Budget Report -->
            <div class="card report-content-card budget-report">
                <div class="card-header">
                    <h6><i class="fas fa-chart-pie"></i> <?php echo __('budget_performance_report'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> <strong><?php echo __('period'); ?>:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    
                    <?php 
                    // Calculate overall budget status
                    $total_budget = 0;
                    $total_actual = 0;
                    if (isset($report_data['budget_vs_actual']) && $report_data['budget_vs_actual']->num_rows > 0) {
                        $report_data['budget_vs_actual']->data_seek(0);
                        while ($row = $report_data['budget_vs_actual']->fetch_assoc()) {
                            $total_budget += $row['budget_amount'];
                            $total_actual += $row['actual_amount'];
                        }
                        $report_data['budget_vs_actual']->data_seek(0);
                    }
                    $budget_used_percentage = $total_budget > 0 ? ($total_actual / $total_budget) * 100 : 0;
                    ?>
                    
                    <!-- Overall Budget Status Card -->
                    <div class="summary-card">
                        <h5><i class="fas fa-chart-line"></i> <?php echo __('overall_budget_status'); ?></h5>
                        <div class="summary-item">
                            <span class="summary-label"><?php echo __('total_budget'); ?>:</span>
                            <span class="summary-value">$<?php echo number_format($total_budget, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><?php echo __('actual_spent'); ?>:</span>
                            <span class="summary-value">$<?php echo number_format($total_actual, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><?php echo __('budget_used'); ?>:</span>
                            <span class="summary-value <?php echo $budget_used_percentage > 100 ? 'negative' : ($budget_used_percentage > 80 ? 'warning' : 'positive'); ?>">
                                <?php echo number_format($budget_used_percentage, 1); ?>%
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($report_data['budget_vs_actual']) && $report_data['budget_vs_actual']->num_rows > 0): ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="table-responsive mb-4">
                                    <table class="table report-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('category'); ?></th>
                                                <th><?php echo __('budget'); ?></th>
                                                <th><?php echo __('actual'); ?></th>
                                                <th><?php echo __('variance'); ?></th>
                                                <th><?php echo __('used'); ?></th>
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
                                                <td><strong><?php echo __('total'); ?></strong></td>
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
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_budget_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'cash_flow'): ?>
            <!-- Cash Flow Report -->
            <div class="card report-content-card cash-flow-report">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line"></i> <?php echo __('cash_flow_summary'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> <strong><?php echo __('period'); ?>:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    
                    <?php if (isset($report_data['monthly_cash_flow']) && !empty($report_data['monthly_cash_flow'])): ?>
                        <div class="table-responsive mb-4">
                            <table class="table report-table">
                                <thead>
                                    <tr>
                                        <th><?php echo __('month'); ?></th>
                                        <th><?php echo __('income'); ?></th>
                                        <th><?php echo __('expenses'); ?></th>
                                        <th><?php echo __('net_cash_flow'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_income = 0;
                                    $total_expenses = 0;
                                    $total_net = 0;
                                    
                                    foreach ($report_data['monthly_cash_flow'] as $row):
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
                                    <?php endforeach; ?>
                                    <tr class="table-primary">
                                        <td><strong><?php echo __('total'); ?></strong></td>
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
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_cash_flow_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($selected_report_type == 'investment'): ?>
            <!-- Investment Report -->
            <div class="card report-content-card investment-report">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line"></i> <?php echo __('investment_summary'); ?></h6>
                </div>
                <div class="card-body">
                    <?php if (isset($report_data['investment_summary']) && !empty($report_data['investment_summary'])): ?>
                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <div class="summary-card">
                                    <div class="summary-item">
                                        <span class="summary-label"><?php echo __('total_invested'); ?>:</span> 
                                        <span class="summary-value">$<?php echo number_format($report_data['investment_summary']['total_invested'], 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label"><?php echo __('current_value'); ?>:</span> 
                                        <span class="summary-value">$<?php echo number_format($report_data['investment_summary']['current_value'], 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <?php 
                                        $gain_loss = $report_data['investment_summary']['total_gain_loss'];
                                        $gain_loss_percent = $report_data['investment_summary']['percent_gain_loss'];
                                        ?>
                                        <span class="summary-label"><?php echo __('total_gain_loss'); ?>:</span> 
                                        <span class="summary-value <?php echo $gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $gain_loss >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($gain_loss), 2); ?>
                                            (<?php echo number_format(abs($gain_loss_percent), 2); ?>%)
                                        </span>
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
                            <h6 class="mb-3"><i class="fas fa-table"></i> <?php echo __('investment_breakdown_by_type'); ?></h6>
                            <div class="table-responsive">
                                <table class="table report-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('investment_type'); ?></th>
                                            <th><?php echo __('invested_amount'); ?></th>
                                            <th><?php echo __('current_value'); ?></th>
                                            <th><?php echo __('gain_loss'); ?></th>
                                            <th><?php echo __('allocation'); ?></th>
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
                                                    ?>
                                                    <span class="<?php echo $type_gain_loss >= 0 ? 'positive' : 'negative'; ?>">
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
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_investment_data'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php else: ?>
            <!-- Comprehensive Financial Report -->
            <div class="card report-content-card financial-report">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar"></i> <?php echo __('financial_summary'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> <strong><?php echo __('period'); ?>:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    
                    <?php if (isset($report_data['summary'])): ?>
                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <div class="summary-card">
                                    <div class="summary-item">
                                        <span class="summary-label"><?php echo __('total_income'); ?>:</span> 
                                        <span class="summary-value">$<?php echo number_format($report_data['summary']['total_income'], 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label"><?php echo __('total_expenses'); ?>:</span> 
                                        <span class="summary-value">$<?php echo number_format($report_data['summary']['total_expenses'], 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <?php 
                                        $net_savings = $report_data['summary']['net_savings'];
                                        ?>
                                        <span class="summary-label"><?php echo __('net_savings'); ?>:</span> 
                                        <span class="summary-value <?php echo $net_savings >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $net_savings >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($net_savings), 2); ?>
                                        </span>
                                    </div>
                                    <div class="summary-item">
                                        <?php 
                                        $saving_rate = $report_data['summary']['saving_rate'];
                                        ?>
                                        <span class="summary-label"><?php echo __('saving_rate'); ?>:</span> 
                                        <span class="summary-value <?php echo $saving_rate >= 10 ? 'positive' : ($saving_rate > 0 ? 'warning' : 'negative'); ?>">
                                            <?php echo number_format($saving_rate, 2); ?>%
                                        </span>
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
                            <h6 class="mb-3"><i class="fas fa-chart-line"></i> <?php echo __('monthly_trends'); ?></h6>
                            <div class="report-chart-container">
                                <canvas id="monthlyTrendsChart"></canvas>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($report_data['cash_flow_data']['monthly_cash_flow']) && !empty($report_data['cash_flow_data']['monthly_cash_flow'])): ?>
                            <h6 class="mt-4 mb-3"><i class="fas fa-table"></i> <?php echo __('monthly_cash_flow_details'); ?></h6>
                            <div class="table-responsive">
                                <table class="table report-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('month'); ?></th>
                                            <th><?php echo __('income'); ?></th>
                                            <th><?php echo __('expenses'); ?></th>
                                            <th><?php echo __('net_cash_flow'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        foreach ($report_data['cash_flow_data']['monthly_cash_flow'] as $row):
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
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar"></i>
                            <h4><?php echo __('no_data_available'); ?></h4>
                            <p><?php echo __('no_financial_data'); ?></p>
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
if (isset($chart_data['income_trend']) && !empty($chart_data['income_trend']['labels'])) {
    $page_scripts .= "
    var incomeTrendCtx = document.getElementById('incomeTrendChart');
    if (incomeTrendCtx) {
        var incomeTrendChart = new Chart(incomeTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['income_trend']['labels']) . ",
                datasets: [{
                    label: '" . __('monthly_income') . "',
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
                                return '" . __('income') . ": $' + context.raw.toLocaleString();
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
if (isset($chart_data['expense_by_category']) && !empty($chart_data['expense_by_category']['labels'])) {
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
if (isset($chart_data['expense_trend']) && !empty($chart_data['expense_trend']['labels'])) {
    $page_scripts .= "
    var expenseTrendCtx = document.getElementById('expenseTrendChart');
    if (expenseTrendCtx) {
        var expenseTrendChart = new Chart(expenseTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['expense_trend']['labels']) . ",
                datasets: [{
                    label: '" . __('monthly_expenses') . "',
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
                                return '" . __('expenses') . ": $' + context.raw.toLocaleString();
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
if (isset($chart_data['budget_vs_actual']) && !empty($chart_data['budget_vs_actual']['labels'])) {
    $page_scripts .= "
    var budgetVsActualCtx = document.getElementById('budgetVsActualChart');
    if (budgetVsActualCtx) {
        var budgetVsActualChart = new Chart(budgetVsActualCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['budget_vs_actual']['labels']) . ",
                datasets: [
                    {
                        label: '" . __('budget') . "',
                        data: " . json_encode($chart_data['budget_vs_actual']['budget_data']) . ",
                        backgroundColor: 'rgba(67, 97, 238, 0.7)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: '" . __('actual') . "',
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
if (isset($chart_data['monthly_cash_flow']) && !empty($chart_data['monthly_cash_flow']['labels'])) {
    $page_scripts .= "
    var cashFlowCtx = document.getElementById('cashFlowChart');
    if (cashFlowCtx) {
        var cashFlowChart = new Chart(cashFlowCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: " . json_encode($chart_data['monthly_cash_flow']['labels']) . ",
                datasets: [
                    {
                        label: '" . __('income') . "',
                        data: " . json_encode($chart_data['monthly_cash_flow']['income_data']) . ",
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: '" . __('expenses') . "',
                        data: " . json_encode($chart_data['monthly_cash_flow']['expense_data']) . ",
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 0,
                        borderRadius: 4
                    },
                    {
                        label: '" . __('net_cash_flow') . "',
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
if (isset($chart_data['investment_by_type']) && !empty($chart_data['investment_by_type']['labels'])) {
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
if (isset($chart_data['income_vs_expense']) && !empty($chart_data['income_vs_expense']['labels'])) {
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
if (isset($chart_data['monthly_trends']) && !empty($chart_data['monthly_trends']['labels'])) {
    $page_scripts .= "
    var monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        var monthlyTrendsChart = new Chart(monthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: " . json_encode($chart_data['monthly_trends']['labels']) . ",
                datasets: [
                    {
                        label: '" . __('income') . "',
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
                        label: '" . __('expenses') . "',
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
                        label: '" . __('net') . "',
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
    // Initialize animations after DOM is fully loaded
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