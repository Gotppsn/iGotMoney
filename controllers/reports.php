<?php
/**
 * Reports Controller
 * 
 * Handles financial reporting functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/Report.php';
require_once 'models/Income.php';
require_once 'models/Expense.php';
require_once 'models/Budget.php';
require_once 'models/Investment.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize Report object
$report = new Report();

// Set user ID
$report->user_id = $user_id;

// Get available report types and date ranges
$report_types = $report->getReportTypes();
$date_ranges = $report->getDateRanges();

// Set default report type and date range
$selected_report_type = $_GET['report_type'] ?? 'financial';
$selected_date_range = $_GET['date_range'] ?? 'this_month';

// Set date range
if ($selected_date_range == 'custom') {
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');
} else {
    $start_date = $date_ranges[$selected_date_range]['start'];
    $end_date = $date_ranges[$selected_date_range]['end'];
}

// Set report dates
$report->start_date = $start_date;
$report->end_date = $end_date;
$report->report_type = $selected_report_type;

// Generate report based on type
$report_data = [];
$chart_data = [];

switch ($selected_report_type) {
    case 'income':
        $report_data = $report->generateIncomeReport();
        
        // Prepare chart data for income trend
        if ($report_data && isset($report_data['income_trend'])) {
            $labels = [];
            $data = [];
            
            $income_trend = $report_data['income_trend'];
            if ($income_trend->num_rows > 0) {
                while ($row = $income_trend->fetch_assoc()) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    $labels[] = $month_label;
                    $data[] = $row['monthly_amount'];
                }
            }
            
            $chart_data['income_trend'] = [
                'labels' => $labels,
                'data' => $data
            ];
        }
        break;
    
    case 'expense':
        $report_data = $report->generateExpenseReport();
        
        // Prepare chart data for expense by category
        if ($report_data && isset($report_data['expense_by_category'])) {
            $labels = [];
            $data = [];
            
            $expense_by_category = $report_data['expense_by_category'];
            if ($expense_by_category->num_rows > 0) {
                $expense_by_category->data_seek(0);
                while ($row = $expense_by_category->fetch_assoc()) {
                    $labels[] = $row['category_name'];
                    $data[] = $row['total_amount'];
                }
            }
            
            $chart_data['expense_by_category'] = [
                'labels' => $labels,
                'data' => $data
            ];
            
            // Prepare chart data for expense trend
            $labels = [];
            $data = [];
            
            $expense_trend = $report_data['expense_trend'];
            if ($expense_trend->num_rows > 0) {
                $expense_trend->data_seek(0);
                while ($row = $expense_trend->fetch_assoc()) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    $labels[] = $month_label;
                    $data[] = $row['monthly_amount'];
                }
            }
            
            $chart_data['expense_trend'] = [
                'labels' => $labels,
                'data' => $data
            ];
        }
        break;
    
    case 'budget':
        $report_data = $report->generateBudgetReport();
        
        // Prepare chart data for budget vs actual
        if ($report_data && isset($report_data['budget_vs_actual'])) {
            $labels = [];
            $budget_data = [];
            $actual_data = [];
            
            $budget_vs_actual = $report_data['budget_vs_actual'];
            if ($budget_vs_actual->num_rows > 0) {
                while ($row = $budget_vs_actual->fetch_assoc()) {
                    $labels[] = $row['category_name'];
                    $budget_data[] = $row['budget_amount'];
                    $actual_data[] = $row['actual_amount'];
                }
            }
            
            $chart_data['budget_vs_actual'] = [
                'labels' => $labels,
                'budget_data' => $budget_data,
                'actual_data' => $actual_data
            ];
        }
        break;
    
    case 'cash_flow':
        $report_data = $report->generateCashFlowReport();
        
        // Prepare chart data for monthly cash flow
        if ($report_data && isset($report_data['monthly_cash_flow'])) {
            $labels = [];
            $income_data = [];
            $expense_data = [];
            $net_data = [];
            
            $monthly_cash_flow = $report_data['monthly_cash_flow'];
            if (!empty($monthly_cash_flow)) {
                foreach ($monthly_cash_flow as $row) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    $labels[] = $month_label;
                    $income_data[] = $row['income'];
                    $expense_data[] = $row['expenses'];
                    $net_data[] = $row['net'];
                }
            }
            
            $chart_data['monthly_cash_flow'] = [
                'labels' => $labels,
                'income_data' => $income_data,
                'expense_data' => $expense_data,
                'net_data' => $net_data
            ];
        }
        break;
    
    case 'investment':
        $report_data = $report->generateInvestmentReport();
        
        // Prepare chart data for investment by type
        if ($report_data && isset($report_data['investment_summary']['by_type'])) {
            $labels = [];
            $data = [];
            
            foreach ($report_data['investment_summary']['by_type'] as $type => $details) {
                $labels[] = $type;
                $data[] = $details['current'];
            }
            
            $chart_data['investment_by_type'] = [
                'labels' => $labels,
                'data' => $data
            ];
        }
        break;
    
    case 'financial':
    default:
        $report_data = $report->generateFinancialReport();
        
        // Prepare chart data for income vs expense
        if ($report_data && isset($report_data['summary'])) {
            $chart_data['income_vs_expense'] = [
                'labels' => ['Income', 'Expenses'],
                'data' => [
                    $report_data['summary']['total_income'],
                    $report_data['summary']['total_expenses']
                ]
            ];
        }
        
        // Prepare chart data for monthly trends
        if ($report_data && isset($report_data['cash_flow_data']['monthly_cash_flow'])) {
            $labels = [];
            $income_data = [];
            $expense_data = [];
            $net_data = [];
            
            $monthly_cash_flow = $report_data['cash_flow_data']['monthly_cash_flow'];
            if (!empty($monthly_cash_flow)) {
                foreach ($monthly_cash_flow as $row) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    $labels[] = $month_label;
                    $income_data[] = $row['income'];
                    $expense_data[] = $row['expenses'];
                    $net_data[] = $row['net'];
                }
            }
            
            $chart_data['monthly_trends'] = [
                'labels' => $labels,
                'income_data' => $income_data,
                'expense_data' => $expense_data,
                'net_data' => $net_data
            ];
        }
        break;
}

// Handle report export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $selected_report_type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Export based on report type
    switch ($selected_report_type) {
        case 'income':
            // Write headers
            fputcsv($output, ['Income Source', 'Amount', 'Frequency', 'Transactions', 'Total Amount']);
            
            // Write data
            if (isset($report_data['income_summary']) && $report_data['income_summary']->num_rows > 0) {
                $report_data['income_summary']->data_seek(0);
                while ($row = $report_data['income_summary']->fetch_assoc()) {
                    fputcsv($output, [
                        $row['name'],
                        $row['amount'],
                        $row['frequency'],
                        $row['transaction_count'],
                        $row['total_amount']
                    ]);
                }
            }
            break;
        
        case 'expense':
            // Write headers
            fputcsv($output, ['Expense Category', 'Transactions', 'Total Amount']);
            
            // Write data
            if (isset($report_data['expense_by_category']) && $report_data['expense_by_category']->num_rows > 0) {
                $report_data['expense_by_category']->data_seek(0);
                while ($row = $report_data['expense_by_category']->fetch_assoc()) {
                    fputcsv($output, [
                        $row['category_name'],
                        $row['transaction_count'],
                        $row['total_amount']
                    ]);
                }
            }
            break;
        
        case 'budget':
            // Write headers
            fputcsv($output, ['Category', 'Budget Amount', 'Actual Amount', 'Difference', 'Percentage Used']);
            
            // Write data
            if (isset($report_data['budget_vs_actual']) && $report_data['budget_vs_actual']->num_rows > 0) {
                $report_data['budget_vs_actual']->data_seek(0);
                while ($row = $report_data['budget_vs_actual']->fetch_assoc()) {
                    $difference = $row['budget_amount'] - $row['actual_amount'];
                    $percentage = $row['budget_amount'] > 0 ? ($row['actual_amount'] / $row['budget_amount']) * 100 : 0;
                    
                    fputcsv($output, [
                        $row['category_name'],
                        $row['budget_amount'],
                        $row['actual_amount'],
                        $difference,
                        number_format($percentage, 2) . '%'
                    ]);
                }
            }
            break;
        
        case 'cash_flow':
            // Write headers
            fputcsv($output, ['Month', 'Income', 'Expenses', 'Net Cash Flow']);
            
            // Write data
            if (isset($report_data['monthly_cash_flow']) && !empty($report_data['monthly_cash_flow'])) {
                foreach ($report_data['monthly_cash_flow'] as $row) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    
                    fputcsv($output, [
                        $month_label,
                        $row['income'],
                        $row['expenses'],
                        $row['net']
                    ]);
                }
            }
            break;
        
        case 'financial':
        default:
            // Write summary
            fputcsv($output, ['Financial Summary']);
            fputcsv($output, ['Period', date('M j, Y', strtotime($start_date)) . ' to ' . date('M j, Y', strtotime($end_date))]);
            fputcsv($output, ['Total Income', $report_data['summary']['total_income']]);
            fputcsv($output, ['Total Expenses', $report_data['summary']['total_expenses']]);
            fputcsv($output, ['Net Savings', $report_data['summary']['net_savings']]);
            fputcsv($output, ['Saving Rate', number_format($report_data['summary']['saving_rate'], 2) . '%']);
            fputcsv($output, []);
            
            // Write monthly cash flow
            fputcsv($output, ['Monthly Cash Flow']);
            fputcsv($output, ['Month', 'Income', 'Expenses', 'Net']);
            
            if (isset($report_data['cash_flow_data']['monthly_cash_flow']) && !empty($report_data['cash_flow_data']['monthly_cash_flow'])) {
                foreach ($report_data['cash_flow_data']['monthly_cash_flow'] as $row) {
                    $month_label = date('M Y', strtotime($row['month'] . '-01'));
                    
                    fputcsv($output, [
                        $month_label,
                        $row['income'],
                        $row['expenses'],
                        $row['net']
                    ]);
                }
            }
            break;
    }
    
    fclose($output);
    exit;
}

// Include view
require_once 'views/reports.php';
?>