<?php
/**
 * Financial Advice Model
 * 
 * Handles financial advice-related database operations and generates advice
 */

require_once 'config/database.php';
require_once 'models/Income.php';
require_once 'models/Expense.php';
require_once 'models/Budget.php';
require_once 'models/Investment.php';

class FinancialAdvice {
    private $conn;
    private $table = 'financial_advice';
    
    // Advice properties
    public $advice_id;
    public $user_id;
    public $type;
    public $title;
    public $content;
    public $is_read;
    public $importance_level;
    public $generated_at;
    
    // Constructor
    public function __construct() {
        $this->conn = connectDB();
    }
    
    // Destructor
    public function __destruct() {
        closeDB($this->conn);
    }
    
    // Create new financial advice
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, type, title, content, importance_level) 
                  VALUES (?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->importance_level = htmlspecialchars(strip_tags($this->importance_level));
        
        // Bind parameters
        $stmt->bind_param("issss", 
                          $this->user_id, 
                          $this->type, 
                          $this->title, 
                          $this->content, 
                          $this->importance_level);
        
        // Execute query
        if ($stmt->execute()) {
            $this->advice_id = $this->conn->insert_id;
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all financial advice for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? 
                  ORDER BY generated_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get latest financial advice for a user
    public function getLatestAdvice($user_id, $limit = 3) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? 
                  ORDER BY importance_level DESC, generated_at DESC 
                  LIMIT ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $user_id, $limit);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Mark advice as read
    public function markAsRead($advice_id, $user_id) {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1 
                  WHERE advice_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $advice_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete financial advice
    public function delete($advice_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE advice_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $advice_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Generate financial advice based on user's financial data
    public function generateAdvice($user_id) {
        // Get financial data
        $income = new Income();
        $expense = new Expense();
        $budget = new Budget();
        $investment = new Investment();
        
        $monthly_income = $income->getMonthlyTotal($user_id);
        $monthly_expenses = $expense->getMonthlyTotal($user_id);
        $yearly_income = $income->getYearlyTotal($user_id);
        $yearly_expenses = $expense->getYearlyTotal($user_id);
        $budget_status = $budget->getCurrentStatus($user_id);
        $investment_summary = $investment->getSummary($user_id);
        
        $advice_list = array();
        
        // Check income vs expenses
        if ($monthly_expenses > $monthly_income * 0.9) {
            $this->user_id = $user_id;
            $this->type = 'budgeting';
            $this->title = 'Expenses Too High';
            $this->content = 'Your monthly expenses are ' . number_format(($monthly_expenses / $monthly_income) * 100, 2) . 
                            '% of your income. Consider reducing non-essential expenses to improve your financial health.';
            $this->importance_level = 'high';
            
            if ($this->create()) {
                $advice_list[] = array(
                    'advice_id' => $this->advice_id,
                    'type' => $this->type,
                    'title' => $this->title,
                    'content' => $this->content,
                    'importance_level' => $this->importance_level
                );
            }
        }
        
        // Check emergency fund (assuming 6 months of expenses is recommended)
        $emergency_fund_target = $monthly_expenses * 6;
        $has_emergency_fund = false;
        
        // This is simplified - in a real app, we would check actual savings
        if (!$has_emergency_fund) {
            $this->user_id = $user_id;
            $this->type = 'saving';
            $this->title = 'Emergency Fund Needed';
            $this->content = 'Consider establishing an emergency fund of ' . 
                            number_format($emergency_fund_target, 2) . 
                            ' (6 months of expenses) to protect against financial emergencies.';
            $this->importance_level = 'high';
            
            if ($this->create()) {
                $advice_list[] = array(
                    'advice_id' => $this->advice_id,
                    'type' => $this->type,
                    'title' => $this->title,
                    'content' => $this->content,
                    'importance_level' => $this->importance_level
                );
            }
        }
        
        // Check budget categories for overspending
        foreach ($budget_status as $category) {
            if ($category['percentage'] > 100) {
                $this->user_id = $user_id;
                $this->type = 'budgeting';
                $this->title = 'Budget Overspending: ' . $category['category_name'];
                $this->content = 'You\'ve spent ' . number_format($category['spent'], 2) . 
                                ' on ' . $category['category_name'] . 
                                ', which is ' . number_format($category['percentage'], 2) . 
                                '% of your budget. Consider adjusting your spending or increasing your budget.';
                $this->importance_level = 'medium';
                
                if ($this->create()) {
                    $advice_list[] = array(
                        'advice_id' => $this->advice_id,
                        'type' => $this->type,
                        'title' => $this->title,
                        'content' => $this->content,
                        'importance_level' => $this->importance_level
                    );
                }
            }
        }
        
        // Check investment allocation
        if (isset($investment_summary['total_invested']) && $investment_summary['total_invested'] > 0) {
            // Check if investments are too concentrated in one type
            foreach ($investment_summary['by_type'] as $type => $data) {
                if ($data['percent'] > 50) {
                    $this->user_id = $user_id;
                    $this->type = 'investment';
                    $this->title = 'Investment Diversification Needed';
                    $this->content = 'Your investments are heavily concentrated in ' . $type . 
                                    ' (' . number_format($data['percent'], 2) . 
                                    '%). Consider diversifying your portfolio to reduce risk.';
                    $this->importance_level = 'medium';
                    
                    if ($this->create()) {
                        $advice_list[] = array(
                            'advice_id' => $this->advice_id,
                            'type' => $this->type,
                            'title' => $this->title,
                            'content' => $this->content,
                            'importance_level' => $this->importance_level
                        );
                    }
                }
            }
            
            // Check if risk level matches age (simplified)
            $high_risk_percent = 0;
            if (isset($investment_summary['by_risk']['high'])) {
                $high_risk_percent += $investment_summary['by_risk']['high']['percent'];
            }
            if (isset($investment_summary['by_risk']['very high'])) {
                $high_risk_percent += $investment_summary['by_risk']['very high']['percent'];
            }
            
            // Assuming a younger investor (more details would be needed in real app)
            if ($high_risk_percent < 20) {
                $this->user_id = $user_id;
                $this->type = 'investment';
                $this->title = 'Consider Growth Investments';
                $this->content = 'Only ' . number_format($high_risk_percent, 2) . 
                                '% of your portfolio is in high-risk growth investments. ' . 
                                'If you are in an early career stage, you might consider ' . 
                                'increasing allocation to growth assets.';
                $this->importance_level = 'low';
                
                if ($this->create()) {
                    $advice_list[] = array(
                        'advice_id' => $this->advice_id,
                        'type' => $this->type,
                        'title' => $this->title,
                        'content' => $this->content,
                        'importance_level' => $this->importance_level
                    );
                }
            }
        } else {
            // No investments
            $this->user_id = $user_id;
            $this->type = 'investment';
            $this->title = 'Start Investing';
            $this->content = 'Consider starting an investment portfolio to grow your wealth. ' . 
                            'Even small regular contributions can grow significantly over time.';
            $this->importance_level = 'medium';
            
            if ($this->create()) {
                $advice_list[] = array(
                    'advice_id' => $this->advice_id,
                    'type' => $this->type,
                    'title' => $this->title,
                    'content' => $this->content,
                    'importance_level' => $this->importance_level
                );
            }
        }
        
        // Check saving rate
        $saving_rate = ($monthly_income - $monthly_expenses) / $monthly_income;
        if ($saving_rate < 0.1) { // Less than 10% savings
            $this->user_id = $user_id;
            $this->type = 'saving';
            $this->title = 'Increase Savings Rate';
            $this->content = 'Your current saving rate is ' . number_format($saving_rate * 100, 2) . 
                            '%. Try to increase this to at least 15-20% of your income for ' . 
                            'long-term financial security.';
            $this->importance_level = 'medium';
            
            if ($this->create()) {
                $advice_list[] = array(
                    'advice_id' => $this->advice_id,
                    'type' => $this->type,
                    'title' => $this->title,
                    'content' => $this->content,
                    'importance_level' => $this->importance_level
                );
            }
        }
        
        // Tax optimization advice (simplified)
        if ($yearly_income > 50000) {
            $this->user_id = $user_id;
            $this->type = 'tax';
            $this->title = 'Tax-Advantaged Accounts';
            $this->content = 'Consider maximizing contributions to tax-advantaged accounts ' . 
                            'like 401(k), IRA, or HSA to reduce your tax burden.';
            $this->importance_level = 'medium';
            
            if ($this->create()) {
                $advice_list[] = array(
                    'advice_id' => $this->advice_id,
                    'type' => $this->type,
                    'title' => $this->title,
                    'content' => $this->content,
                    'importance_level' => $this->importance_level
                );
            }
        }
        
        // General advice
        $general_advice = [
            [
                'title' => 'Review Subscriptions',
                'content' => 'Regularly review your recurring subscriptions and cancel those you don\'t use frequently.',
                'type' => 'budgeting'
            ],
            [
                'title' => 'Automate Savings',
                'content' => 'Set up automatic transfers to your savings account on payday to build savings without thinking about it.',
                'type' => 'saving'
            ],
            [
                'title' => 'Check Credit Report',
                'content' => 'Review your credit report annually for errors and to monitor your overall credit health.',
                'type' => 'general'
            ],
            [
                'title' => 'Insurance Review',
                'content' => 'Periodically review your insurance policies to ensure adequate coverage at competitive rates.',
                'type' => 'general'
            ]
        ];
        
        // Add one general advice randomly
        $random_advice = $general_advice[array_rand($general_advice)];
        
        $this->user_id = $user_id;
        $this->type = $random_advice['type'];
        $this->title = $random_advice['title'];
        $this->content = $random_advice['content'];
        $this->importance_level = 'low';
        
        if ($this->create()) {
            $advice_list[] = array(
                'advice_id' => $this->advice_id,
                'type' => $this->type,
                'title' => $this->title,
                'content' => $this->content,
                'importance_level' => $this->importance_level
            );
        }
        
        return $advice_list;
    }
}
?>