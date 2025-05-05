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
        closeDB($this->conn);
    }
    
    // Create new budget
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, category_id, amount, start_date, end_date) 
                  VALUES (?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        
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
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all budgets for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT b.*, c.name as category_name 
                  FROM " . $this->table . " b
                  JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                  WHERE b.user_id = ? 
                  ORDER BY b.start_date DESC";
        
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
    
    // Get budget by ID
    public function getById($budget_id, $user_id) {
        // SQL query
        $query = "SELECT b.*, c.name as category_name 
                  FROM " . $this->table . " b
                  JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                  WHERE b.budget_id = ? AND b.user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $budget_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
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
    }
    
    // Update budget
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET category_id = ?, amount = ?, start_date = ?, end_date = ? 
                  WHERE budget_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        
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
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete budget
    public function delete($budget_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE budget_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $budget_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get current budget status
    public function getCurrentStatus($user_id) {
        $today = date('Y-m-d');
        $current_month = date('Y-m');
        $budget_status = array();
        
        // Get active budgets
        $query = "SELECT b.*, c.name as category_name 
                  FROM " . $this->table . " b
                  JOIN " . $this->categories_table . " c ON b.category_id = c.category_id
                  WHERE b.user_id = ? 
                  AND ? BETWEEN b.start_date AND b.end_date";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("is", $user_id, $today);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $budgets = $stmt->get_result();
        
        // Loop through budgets
        while ($budget = $budgets->fetch_assoc()) {
            // Get expenses for this category in current month
            $expense_query = "SELECT SUM(amount) as spent 
                             FROM " . $this->expenses_table . " 
                             WHERE user_id = ? 
                             AND category_id = ? 
                             AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
            
            $expense_stmt = $this->conn->prepare($expense_query);
            $expense_stmt->bind_param("iis", $user_id, $budget['category_id'], $current_month);
            $expense_stmt->execute();
            $expense_result = $expense_stmt->get_result();
            $expense_row = $expense_result->fetch_assoc();
            
            $spent = $expense_row['spent'] ?? 0;
            $available = $budget['amount'] - $spent;
            $percentage = ($spent / $budget['amount']) * 100;
            
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
                'end_date' => $budget['end_date']
            );
        }
        
        return $budget_status;
    }
    
    // Generate monthly budget based on income and past expenses
    public function generateBudgetPlan($user_id, $monthly_income) {
        // Get expense categories
        $categories_query = "SELECT * FROM " . $this->categories_table;
        $categories_stmt = $this->conn->prepare($categories_query);
        $categories_stmt->execute();
        $categories = $categories_stmt->get_result();
        
        // Get spending patterns for the past 3 months
        $three_months_ago = date('Y-m-d', strtotime('-3 months'));
        $expense_query = "SELECT category_id, SUM(amount) as total, COUNT(*) as count
                          FROM " . $this->expenses_table . "
                          WHERE user_id = ? AND expense_date >= ?
                          GROUP BY category_id";
        
        $expense_stmt = $this->conn->prepare($expense_query);
        $expense_stmt->bind_param("is", $user_id, $three_months_ago);
        $expense_stmt->execute();
        $expenses = $expense_stmt->get_result();
        
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
            'Insurance' => 10,
            'Healthcare' => 5,
            'Entertainment' => 5,
            'Personal Care' => 5,
            'Education' => 5,
            'Debt Payments' => 0, // Will be calculated based on actual debts
            'Investments' => 0, // Will be added if income permits
            'Miscellaneous' => 5
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
            
            // Check past spending
            if (isset($expense_map[$category_id])) {
                $past_average = $expense_map[$category_id]['average'];
                
                // Adjust allocation based on past spending
                $allocated_amount = max($allocated_amount, $past_average);
            }
            
            // Add to budget plan
            $budget_plan[] = array(
                'category_id' => $category_id,
                'category_name' => $category_name,
                'allocated_amount' => round($allocated_amount, 2),
                'percentage' => $percentage
            );
            
            $total_allocated += $allocated_amount;
        }
        
        // Adjust if total allocation exceeds income
        if ($total_allocated > $monthly_income) {
            $adjustment_factor = $monthly_income / $total_allocated;
            
            foreach ($budget_plan as &$item) {
                $item['allocated_amount'] = round($item['allocated_amount'] * $adjustment_factor, 2);
                $item['percentage'] = round(($item['allocated_amount'] / $monthly_income) * 100, 2);
            }
        }
        
        return $budget_plan;
    }
}
?>