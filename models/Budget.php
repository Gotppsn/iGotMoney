<?php
/**
 * Budget Model
 * 
 * Handles budget-related database operations
 */

require_once 'config/database.php';

class Budget {
    private $conn;
    private $table = 'budgets';
    private $categories_table = 'expense_categories';
    private $expenses_table = 'expenses';
    
    // Budget properties
    public $budget_id;
    public $user_id;
    public $category_id;
    public $amount;
    public $start_date;
    public $end_date;
    public $created_at;
    public $updated_at;
    
    // Constructor
    public function __construct() {
        $this->conn = connectDB();
    }
    
    // Destructor
    public function __destruct() {
        if ($this->conn) {
            closeDB($this->conn);
        }
    }
    
    // Create new budget
    public function create() {
        try {
            // SQL query
            $query = "INSERT INTO " . $this->table . " 
                    (user_id, category_id, amount, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("iidss", 
                $this->user_id, 
                $this->category_id, 
                $this->amount, 
                $this->start_date, 
                $this->end_date);
            
            // Execute query
            if ($stmt->execute()) {
                $this->budget_id = $this->conn->insert_id;
                return true;
            }
            
            // Log error if something goes wrong
            error_log("Error executing statement: " . $stmt->error);
            return false;
        } catch (Exception $e) {
            error_log("Exception in Budget::create: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all budgets for a user
    public function getAll($user_id) {
        try {
            // SQL query
            $query = "SELECT b.*, c.name as category_name 
                    FROM " . $this->table . " b
                    JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                    WHERE b.user_id = ? 
                    ORDER BY CASE WHEN c.name = 'Investments' THEN 0 ELSE 1 END, c.name";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameter
            $stmt->bind_param("i", $user_id);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in Budget::getAll: " . $e->getMessage());
            return false;
        }
    }
    
    // Get budget by ID
    public function getById($budget_id, $user_id) {
        try {
            // SQL query
            $query = "SELECT b.*, c.name as category_name 
                    FROM " . $this->table . " b
                    JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                    WHERE b.budget_id = ? AND b.user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $budget_id, $user_id);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Set properties
                $this->budget_id = $row['budget_id'];
                $this->user_id = $row['user_id'];
                $this->category_id = $row['category_id'];
                $this->amount = $row['amount'];
                $this->start_date = $row['start_date'];
                $this->end_date = $row['end_date'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Exception in Budget::getById: " . $e->getMessage());
            return false;
        }
    }
    
    // Update budget
    public function update() {
        try {
            // SQL query
            $query = "UPDATE " . $this->table . " 
                    SET category_id = ?, amount = ?, start_date = ?, end_date = ? 
                    WHERE budget_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("idssii", 
                $this->category_id, 
                $this->amount, 
                $this->start_date, 
                $this->end_date, 
                $this->budget_id, 
                $this->user_id);
            
            // Execute query
            if ($stmt->execute()) {
                return true;
            }
            
            // Log error if something goes wrong
            error_log("Error executing statement: " . $stmt->error);
            return false;
        } catch (Exception $e) {
            error_log("Exception in Budget::update: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete budget
    public function delete($budget_id, $user_id) {
        try {
            // SQL query
            $query = "DELETE FROM " . $this->table . " 
                    WHERE budget_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $budget_id, $user_id);
            
            // Execute query
            if ($stmt->execute()) {
                return true;
            }
            
            // Log error if something goes wrong
            error_log("Error executing statement: " . $stmt->error);
            return false;
        } catch (Exception $e) {
            error_log("Exception in Budget::delete: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete budgets in a date range
    public function deleteByDateRange($user_id, $start_date, $end_date) {
        try {
            // SQL query - delete budgets that overlap with the given date range
            $query = "DELETE FROM " . $this->table . " 
                    WHERE user_id = ? 
                    AND ((start_date BETWEEN ? AND ?) 
                    OR (end_date BETWEEN ? AND ?) 
                    OR (start_date <= ? AND end_date >= ?))";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("issssss", $user_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
            
            // Execute query
            if ($stmt->execute()) {
                return true;
            }
            
            // Log error if something goes wrong
            error_log("Error executing statement: " . $stmt->error);
            return false;
        } catch (Exception $e) {
            error_log("Exception in Budget::deleteByDateRange: " . $e->getMessage());
            return false;
        }
    }
    
    // Get current budget status
    public function getCurrentStatus($user_id) {
        try {
            $today = date('Y-m-d');
            $current_month = date('Y-m');
            $budget_status = array();
            
            // Get active budgets
            $query = "SELECT b.*, c.name as category_name 
                    FROM " . $this->table . " b
                    JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                    WHERE b.user_id = ? 
                    AND ? BETWEEN b.start_date AND b.end_date
                    ORDER BY CASE WHEN c.name = 'Investments' THEN 0 ELSE 1 END, c.name";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return array();
            }
            
            // Bind parameters
            $stmt->bind_param("is", $user_id, $today);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $budgets = $stmt->get_result();
            if (!$budgets) {
                error_log("No result from budget query: " . $stmt->error);
                return array();
            }
            
            // Loop through budgets
            while ($budget = $budgets->fetch_assoc()) {
                // Get expenses for this category in current month
                $expense_query = "SELECT SUM(amount) as spent 
                                FROM " . $this->expenses_table . " 
                                WHERE user_id = ? 
                                AND category_id = ? 
                                AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
                
                $expense_stmt = $this->conn->prepare($expense_query);
                if (!$expense_stmt) {
                    error_log("Failed to prepare expense statement: " . $this->conn->error);
                    continue;
                }
                
                $expense_stmt->bind_param("iis", $user_id, $budget['category_id'], $current_month);
                $expense_stmt->execute();
                $expense_result = $expense_stmt->get_result();
                
                if (!$expense_result) {
                    error_log("No result from expense query: " . $expense_stmt->error);
                    continue;
                }
                
                $expense_row = $expense_result->fetch_assoc();
                
                $spent = $expense_row['spent'] ?? 0;
                $available = $budget['amount'] - $spent;
                $percentage = $budget['amount'] > 0 ? ($spent / $budget['amount']) * 100 : 0;
                
                // Add to budget status array
                $budget_status[] = array(
                    'budget_id' => $budget['budget_id'],
                    'category_id' => $budget['category_id'],
                    'category_name' => $budget['category_name'],
                    'budget_amount' => $budget['amount'],
                    'spent' => $spent,
                    'available' => $available,
                    'percentage' => $percentage,
                    'start_date' => $budget['start_date'],
                    'end_date' => $budget['end_date'],
                    'is_investment' => ($budget['category_name'] === 'Investments')
                );
            }
            
            return $budget_status;
        } catch (Exception $e) {
            error_log("Exception in Budget::getCurrentStatus: " . $e->getMessage());
            return array();
        }
    }
    
    // Generate monthly budget based on income and past expenses
    public function generateBudgetPlan($user_id, $monthly_income) {
        try {
            // Check if monthly income is valid
            if ($monthly_income <= 0) {
                return array();
            }
            
            // Get expense categories
            $categories_query = "SELECT * FROM " . $this->categories_table . " ORDER BY CASE WHEN name = 'Investments' THEN 0 ELSE 1 END, name";
            $categories_stmt = $this->conn->prepare($categories_query);
            if (!$categories_stmt) {
                error_log("Failed to prepare categories statement: " . $this->conn->error);
                return array();
            }
            
            $categories_stmt->execute();
            $categories = $categories_stmt->get_result();
            
            if (!$categories) {
                error_log("No result from categories query: " . $categories_stmt->error);
                return array();
            }
            
            // Get spending patterns for the past 3 months
            $three_months_ago = date('Y-m-d', strtotime('-3 months'));
            $expense_query = "SELECT category_id, SUM(amount) as total, COUNT(*) as count
                            FROM " . $this->expenses_table . "
                            WHERE user_id = ? AND expense_date >= ?
                            GROUP BY category_id";
            
            $expense_stmt = $this->conn->prepare($expense_query);
            if (!$expense_stmt) {
                error_log("Failed to prepare expense statement: " . $this->conn->error);
                return array();
            }
            
            $expense_stmt->bind_param("is", $user_id, $three_months_ago);
            $expense_stmt->execute();
            $expenses = $expense_stmt->get_result();
            
            if (!$expenses) {
                error_log("No result from expenses query: " . $expense_stmt->error);
                return array();
            }
            
            // Create expenses map
            $expense_map = array();
            while ($expense = $expenses->fetch_assoc()) {
                $expense_map[$expense['category_id']] = array(
                    'total' => $expense['total'],
                    'count' => $expense['count'],
                    'average' => $expense['total'] / 3 // Average monthly spending
                );
            }
            
            // Standard budget allocation percentages by category
            $allocation_percentages = array(
                'Housing' => 30,
                'Food' => 15,
                'Transportation' => 10,
                'Utilities' => 10,
                'Insurance' => 5,
                'Healthcare' => 5,
                'Entertainment' => 5,
                'Personal Care' => 5,
                'Education' => 3,
                'Debt Payments' => 0, // Will be calculated based on actual debts
                'Investments' => 10, // Prioritize investments at 10%
                'Miscellaneous' => 2
            );
            
            $budget_plan = array();
            $total_allocated = 0;
            
            // Create budget plan
            while ($category = $categories->fetch_assoc()) {
                $category_id = $category['category_id'];
                $category_name = $category['name'];
                
                // Get allocation percentage (default to 5% if not specified)
                $percentage = $allocation_percentages[$category_name] ?? 5;
                
                // Calculate recommended budget
                $allocated_amount = ($monthly_income * $percentage) / 100;
                
                // Special handling for investments - ensure minimum allocation
                if ($category_name === 'Investments') {
                    // Ensure at least 10% goes to investments
                    $allocated_amount = max($allocated_amount, $monthly_income * 0.1);
                }
                
                // Check past spending
                if (isset($expense_map[$category_id])) {
                    $past_average = $expense_map[$category_id]['average'];
                    
                    // Adjust allocation based on past spending, except for investments
                    if ($category_name !== 'Investments') {
                        if ($past_average > $allocated_amount * 1.5) {
                            // Past spending is significantly higher - adjust upward but not fully
                            $allocated_amount = ($allocated_amount * 0.6) + ($past_average * 0.4);
                        } else if ($past_average < $allocated_amount * 0.5) {
                            // Past spending is significantly lower - adjust downward but not fully
                            $allocated_amount = ($allocated_amount * 0.7) + ($past_average * 0.3);
                        } else {
                            // Past spending is in a reasonable range - slight adjustment
                            $allocated_amount = ($allocated_amount * 0.8) + ($past_average * 0.2);
                        }
                    }
                }
                
                // Add to budget plan
                $budget_plan[] = array(
                    'category_id' => $category_id,
                    'category_name' => $category_name,
                    'allocated_amount' => round($allocated_amount, 2),
                    'percentage' => round(($allocated_amount / $monthly_income) * 100, 2),
                    'is_investment' => ($category_name === 'Investments')
                );
                
                $total_allocated += $allocated_amount;
            }
            
            // Adjust if total allocation exceeds income
            if ($total_allocated > $monthly_income) {
                $adjustment_factor = $monthly_income / $total_allocated;
                
                foreach ($budget_plan as &$item) {
                    // Protect investment allocation during adjustment
                    if ($item['category_name'] === 'Investments') {
                        // Keep investment at least 10% of income
                        $min_investment = $monthly_income * 0.1;
                        $item['allocated_amount'] = max($min_investment, round($item['allocated_amount'] * $adjustment_factor, 2));
                    } else {
                        $item['allocated_amount'] = round($item['allocated_amount'] * $adjustment_factor, 2);
                    }
                    $item['percentage'] = round(($item['allocated_amount'] / $monthly_income) * 100, 2);
                }
            }
            
            // Sort to put investments first
            usort($budget_plan, function($a, $b) {
                if ($a['is_investment'] && !$b['is_investment']) return -1;
                if (!$a['is_investment'] && $b['is_investment']) return 1;
                return strcasecmp($a['category_name'], $b['category_name']);
            });
            
            return $budget_plan;
        } catch (Exception $e) {
            error_log("Exception in Budget::generateBudgetPlan: " . $e->getMessage());
            return array();
        }
    }
    
    // Get investment history for budget tracking
    public function getInvestmentHistory($user_id) {
        try {
            $query = "SELECT 
                        DATE_FORMAT(expense_date, '%Y-%m') as month,
                        SUM(amount) as total_investment
                      FROM " . $this->expenses_table . " e
                      JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                      WHERE e.user_id = ? AND c.name = 'Investments'
                      GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                      ORDER BY month DESC
                      LIMIT 12";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . $this->conn->error);
                return array();
            }
            
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $history = array();
            while ($row = $result->fetch_assoc()) {
                $history[] = array(
                    'month' => $row['month'],
                    'amount' => $row['total_investment']
                );
            }
            
            return $history;
        } catch (Exception $e) {
            error_log("Exception in Budget::getInvestmentHistory: " . $e->getMessage());
            return array();
        }
    }
}