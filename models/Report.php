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
        
        // SQL query for monthly cash flow
        $query = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month,
                        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses,
                        SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net
                  FROM transactions
                  WHERE user_id = ?
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
        $monthly_cash_flow = $stmt->get_result();
        
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
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ississ", $this->user_id, $this->start_date, $this->end_date, $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $income_vs_expense = $stmt->get_result();
        
        // Return report data
        return [
            'monthly_cash_flow' => $monthly_cash_flow,
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
        
        // Initialize required objects
        $income = new Income();
        $expense = new Expense();
        
        // Get income and expense totals
        $total_income = 0;
        $total_expenses = 0;
        
        // SQL query for total income
        $query = "SELECT SUM(amount) as total 
                  FROM transactions 
                  WHERE user_id = ? 
                  AND type = 'income' 
                  AND transaction_date BETWEEN ? AND ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $total_income = $row['total'] ?? 0;
        }
        
        // SQL query for total expenses
        $query = "SELECT SUM(amount) as total 
                  FROM transactions 
                  WHERE user_id = ? 
                  AND type = 'expense' 
                  AND transaction_date BETWEEN ? AND ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("iss", $this->user_id, $this->start_date, $this->end_date);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $total_expenses = $row['total'] ?? 0;
        }
        
        // Calculate net savings
        $net_savings = $total_income - $total_expenses;
        $saving_rate = $total_income > 0 ? ($net_savings / $total_income) * 100 : 0;
        
        // Generate individual reports
        $income_report = $this->generateIncomeReport();
        $expense_report = $this->generateExpenseReport();
        $cash_flow_report = $this->generateCashFlowReport();
        
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
            'cash_flow_data' => $cash_flow_report,
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
}
?>