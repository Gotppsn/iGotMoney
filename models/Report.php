<?php
/**
 * Report Model
 * 
 * Handles report generation and database operations
 */

require_once 'config/database.php';
require_once 'models/Income.php';
require_once 'models/Expense.php';
require_once 'models/Budget.php';
require_once 'models/Investment.php';

class Report {
    private $conn;
    private $table = 'transactions';
    
    // Report properties
    public $user_id;
    public $start_date;
    public $end_date;
    public $report_type;
    
    // Constructor
    public function __construct() {
        $this->conn = connectDB();
    }
    
    // Destructor
    public function __destruct() {
        closeDB($this->conn);
    }
    
    // Generate income report
    public function generateIncomeReport() {
        // Validate input
        if (!isset($this->user_id) || !isset($this->start_date) || !isset($this->end_date)) {
            return false;
        }
        
        // SQL query for income summary
        $query = "SELECT i.income_id, i.name, i.amount, i.frequency, 
                        COUNT(t.transaction_id) as transaction_count, 
                        SUM(t.amount) as total_amount 
                  FROM income_sources i
                  LEFT JOIN transactions t ON i.income_id = t.income_id 
                  WHERE i.user_id = ? 
                  AND (t.transaction_date BETWEEN ? AND ? OR t.transaction_date IS NULL)
                  GROUP BY i.income_id
                  ORDER BY total_amount DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $income_summary = $stmt->get_result();
        
        // SQL query for income trend by month
        $query = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, 
                        SUM(amount) as monthly_amount 
                  FROM transactions 
                  WHERE user_id = ? 
                  AND type = 'income' 
                  AND transaction_date BETWEEN ? AND ? 
                  GROUP BY month 
                  ORDER BY month";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $income_trend = $stmt->get_result();
        
        // Return report data
        return [
            'income_summary' => $income_summary,
            'income_trend' => $income_trend,
            'date_range' => [
                'start' => $this->start_date,
                'end' => $this->end_date
            ]
        ];
    }
    
    // Generate expense report
    public function generateExpenseReport() {
        // Validate input
        if (!isset($this->user_id) || !isset($this->start_date) || !isset($this->end_date)) {
            return false;
        }
        
        // SQL query for expense summary by category
        $query = "SELECT c.category_id, c.name as category_name, 
                        COUNT(t.transaction_id) as transaction_count, 
                        SUM(t.amount) as total_amount 
                  FROM expense_categories c
                  LEFT JOIN transactions t ON c.category_id = t.category_id 
                  WHERE t.user_id = ? 
                  AND t.type = 'expense' 
                  AND t.transaction_date BETWEEN ? AND ?
                  GROUP BY c.category_id
                  ORDER BY total_amount DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $expense_by_category = $stmt->get_result();
        
        // SQL query for expense trend by month
        $query = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, 
                        SUM(amount) as monthly_amount 
                  FROM transactions 
                  WHERE user_id = ? 
                  AND type = 'expense' 
                  AND transaction_date BETWEEN ? AND ? 
                  GROUP BY month 
                  ORDER BY month";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $expense_trend = $stmt->get_result();
        
        // Return report data
        return [
            'expense_by_category' => $expense_by_category,
            'expense_trend' => $expense_trend,
            'date_range' => [
                'start' => $this->start_date,
                'end' => $this->end_date
            ]
        ];
    }
    
    // Generate budget report
    public function generateBudgetReport() {
        // Validate input
        if (!isset($this->user_id) || !isset($this->start_date) || !isset($this->end_date)) {
            return false;
        }
        
        // Initialize Budget object
        $budget = new Budget();
        
        // Get budget status
        $budget_status = $budget->getCurrentStatus($this->user_id);
        
        // SQL query for budget vs actual by category
        $query = "SELECT c.category_id, c.name as category_name, 
                        b.amount as budget_amount, 
                        COALESCE(SUM(t.amount), 0) as actual_amount 
                  FROM budgets b
                  JOIN expense_categories c ON b.category_id = c.category_id
                  LEFT JOIN transactions t ON b.category_id = t.category_id 
                                          AND t.user_id = ? 
                                          AND t.type = 'expense' 
                                          AND t.transaction_date BETWEEN ? AND ?
                  WHERE b.user_id = ? 
                  GROUP BY c.category_id
                  ORDER BY c.name";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("issi", $this->user_id, $this->start_date, $this->end_date, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $budget_vs_actual = $stmt->get_result();
        
        // Return report data
        return [
            'budget_status' => $budget_status,
            'budget_vs_actual' => $budget_vs_actual,
            'date_range' => [
                'start' => $this->start_date,
                'end' => $this->end_date
            ]
        ];
    }
    
    // Generate cash flow report
    public function generateCashFlowReport() {
        // Validate input
        if (!isset($this->user_id) || !isset($this->start_date) || !isset($this->end_date)) {
            return false;
        }
        
        // First, get actual transactions
        $query = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month,
                        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
                  FROM transactions
                  WHERE user_id = ?
                  AND transaction_date BETWEEN ? AND ?
                  GROUP BY month
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        $stmt->execute();
        $actual_transactions = $stmt->get_result();
        
        // Store actual data in an array
        $actual_data = [];
        while ($row = $actual_transactions->fetch_assoc()) {
            $actual_data[$row['month']] = [
                'income' => $row['income'],
                'expenses' => $row['expenses']
            ];
        }
        
        // Create array of all months in range
        $start = new DateTime($this->start_date);
        $end = new DateTime($this->end_date);
        $period = new DatePeriod(
            new DateTime($start->format('Y-m-01')),
            new DateInterval('P1M'),
            (new DateTime($end->format('Y-m-01')))->modify('+1 month')
        );
        
        $monthly_projections = [];
        foreach ($period as $month) {
            $monthKey = $month->format('Y-m');
            $monthly_projections[$monthKey] = [
                'income' => 0,
                'expenses' => 0
            ];
        }
        
        // Get recurring income sources
        $query = "SELECT income_id, name, amount, frequency, start_date, end_date, is_active
                  FROM income_sources
                  WHERE user_id = ?
                  AND is_active = 1
                  AND (end_date IS NULL OR end_date >= ?)
                  AND start_date <= ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        $stmt->execute();
        $income_sources = $stmt->get_result();
        
        // Calculate monthly income projections
        while ($source = $income_sources->fetch_assoc()) {
            $sourceStart = new DateTime($source['start_date']);
            $sourceEnd = $source['end_date'] ? new DateTime($source['end_date']) : null;
            
            foreach ($period as $month) {
                $monthKey = $month->format('Y-m');
                $monthEnd = clone $month;
                $monthEnd->modify('last day of this month');
                
                // Check if this income source is active in this month
                if ($month >= $sourceStart && (!$sourceEnd || $month <= $sourceEnd)) {
                    // Calculate the monthly amount based on frequency
                    $monthlyAmount = $this->calculateMonthlyAmount($source['amount'], $source['frequency']);
                    $monthly_projections[$monthKey]['income'] += $monthlyAmount;
                }
            }
        }
        
        // Get recurring expenses
        $query = "SELECT e.expense_id, e.amount, e.frequency, e.expense_date, e.is_recurring, 
                         e.next_due_date, c.name as category_name
                  FROM expenses e
                  JOIN expense_categories c ON e.category_id = c.category_id
                  WHERE e.user_id = ?
                  AND e.is_recurring = 1
                  AND (e.next_due_date IS NULL OR e.expense_date <= ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $this->user_id, $this->end_date);
        $stmt->execute();
        $recurring_expenses = $stmt->get_result();
        
        // Calculate monthly expense projections for recurring expenses
        while ($expense = $recurring_expenses->fetch_assoc()) {
            $expenseStart = new DateTime($expense['expense_date']);
            
            foreach ($period as $month) {
                $monthKey = $month->format('Y-m');
                $monthStart = clone $month;
                $monthEnd = clone $month;
                $monthEnd->modify('last day of this month');
                
                // Check if this recurring expense applies to this month
                if ($monthStart >= $expenseStart) {
                    // Calculate the monthly amount based on frequency
                    $monthlyAmount = $this->calculateMonthlyAmount($expense['amount'], $expense['frequency']);
                    $monthly_projections[$monthKey]['expenses'] += $monthlyAmount;
                }
            }
        }
        
        // Merge actual data with projections (actual data takes precedence)
        foreach ($actual_data as $month => $data) {
            if (isset($monthly_projections[$month])) {
                // For expenses, add actual one-time expenses to recurring projections
                $monthly_projections[$month]['expenses'] = max($data['expenses'], $monthly_projections[$month]['expenses']);
                
                // For income, use actual if available
                if ($data['income'] > 0) {
                    $monthly_projections[$month]['income'] = $data['income'];
                }
            }
        }
        
        // Add actual one-time income that's not from recurring sources
        foreach ($actual_data as $month => $data) {
            if (isset($monthly_projections[$month])) {
                $query = "SELECT SUM(amount) as actual_income
                          FROM transactions
                          WHERE user_id = ?
                          AND type = 'income'
                          AND DATE_FORMAT(transaction_date, '%Y-%m') = ?
                          AND income_id IS NULL";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("is", $this->user_id, $month);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $monthly_projections[$month]['income'] += $row['actual_income'] ?? 0;
                }
            }
        }
        
        // Convert to result format
        $result_data = [];
        foreach ($monthly_projections as $month => $data) {
            $result_data[] = [
                'month' => $month,
                'income' => $data['income'],
                'expenses' => $data['expenses'],
                'net' => $data['income'] - $data['expenses']
            ];
        }
        
        // SQL query for income vs expense by type
        $query = "SELECT 
                    'Income' as transaction_type,
                    SUM(amount) as total
                  FROM transactions
                  WHERE user_id = ?
                  AND type = 'income'
                  AND transaction_date BETWEEN ? AND ?
                  UNION
                  SELECT 
                    'Expense' as transaction_type,
                    SUM(amount) as total
                  FROM transactions
                  WHERE user_id = ?
                  AND type = 'expense'
                  AND transaction_date BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ississ", $this->user_id, $this->start_date, $this->end_date, $this->user_id, $this->start_date, $this->end_date);
        $stmt->execute();
        $income_vs_expense = $stmt->get_result();
        
        // Return report data with projections
        return [
            'monthly_cash_flow' => $result_data,
            'income_vs_expense' => $income_vs_expense,
            'date_range' => [
                'start' => $this->start_date,
                'end' => $this->end_date
            ]
        ];
    }
    
    // Generate investment report
    public function generateInvestmentReport() {
        // Validate input
        if (!isset($this->user_id)) {
            return false;
        }
        
        // Initialize Investment object
        $investment = new Investment();
        
        // Get investment summary
        $investment_summary = $investment->getSummary($this->user_id);
        
        // Return report data
        return [
            'investment_summary' => $investment_summary,
            'date_range' => [
                'start' => $this->start_date ?? date('Y-m-d', strtotime('-1 year')),
                'end' => $this->end_date ?? date('Y-m-d')
            ]
        ];
    }
    
    // Generate comprehensive financial report
    public function generateFinancialReport() {
        // Validate input
        if (!isset($this->user_id) || !isset($this->start_date) || !isset($this->end_date)) {
            return false;
        }
        
        // Generate individual reports
        $income_report = $this->generateIncomeReport();
        $expense_report = $this->generateExpenseReport();
        $cash_flow_report = $this->generateCashFlowReport();
        
        // Calculate totals from cash flow data
        $total_income = 0;
        $total_expenses = 0;
        
        if (isset($cash_flow_report['monthly_cash_flow'])) {
            foreach ($cash_flow_report['monthly_cash_flow'] as $month) {
                $total_income += $month['income'];
                $total_expenses += $month['expenses'];
            }
        }
        
        // Calculate net savings
        $net_savings = $total_income - $total_expenses;
        $saving_rate = $total_income > 0 ? ($net_savings / $total_income) * 100 : 0;
        
        // Return comprehensive report
        return [
            'summary' => [
                'total_income' => $total_income,
                'total_expenses' => $total_expenses,
                'net_savings' => $net_savings,
                'saving_rate' => $saving_rate
            ],
            'income_data' => $income_report,
            'expense_data' => $expense_report,
            'cash_flow_data' => [
                'monthly_cash_flow' => $cash_flow_report['monthly_cash_flow'],
                'income_vs_expense' => $cash_flow_report['income_vs_expense']
            ],
            'date_range' => [
                'start' => $this->start_date,
                'end' => $this->end_date
            ]
        ];
    }
    
    // Get available report types
    public function getReportTypes() {
        return [
            'income' => 'Income Report',
            'expense' => 'Expense Report',
            'budget' => 'Budget Report',
            'cash_flow' => 'Cash Flow Report',
            'investment' => 'Investment Report',
            'financial' => 'Comprehensive Financial Report'
        ];
    }
    
    // Get predefined date ranges
    public function getDateRanges() {
        $current_date = date('Y-m-d');
        $ranges = [
            'this_month' => [
                'label' => 'This Month',
                'start' => date('Y-m-01'),
                'end' => date('Y-m-t')
            ],
            'last_month' => [
                'label' => 'Last Month',
                'start' => date('Y-m-01', strtotime('last month')),
                'end' => date('Y-m-t', strtotime('last month'))
            ],
            'last_3_months' => [
                'label' => 'Last 3 Months',
                'start' => date('Y-m-d', strtotime('-3 months')),
                'end' => $current_date
            ],
            'last_6_months' => [
                'label' => 'Last 6 Months',
                'start' => date('Y-m-d', strtotime('-6 months')),
                'end' => $current_date
            ],
            'this_year' => [
                'label' => 'This Year',
                'start' => date('Y-01-01'),
                'end' => date('Y-12-31')
            ],
            'last_year' => [
                'label' => 'Last Year',
                'start' => date('Y-01-01', strtotime('-1 year')),
                'end' => date('Y-12-31', strtotime('-1 year'))
            ],
            'custom' => [
                'label' => 'Custom Range',
                'start' => '',
                'end' => ''
            ]
        ];
        
        return $ranges;
    }
    
    // Helper method to calculate monthly equivalent amount based on frequency
    private function calculateMonthlyAmount($amount, $frequency) {
        switch ($frequency) {
            case 'daily':
                return $amount * 30; // Approximate days in a month
            case 'weekly':
                return $amount * 4.33; // Average weeks in a month
            case 'bi-weekly':
                return $amount * 2.17; // Average bi-weeks in a month
            case 'monthly':
                return $amount;
            case 'quarterly':
                return $amount / 3;
            case 'annually':
                return $amount / 12;
            case 'one-time':
                return 0; // One-time should not be recurring
            default:
                return 0;
        }
    }
}
?>