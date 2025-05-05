<?php
/**
 * Expense Model
 * 
 * Handles expense-related database operations
 */

require_once 'config/database.php';

class Expense {
    private $conn;
    private $table = 'expenses';
    private $categories_table = 'expense_categories';
    private $transactions_table = 'transactions';
    
    // Expense properties
    public $expense_id;
    public $user_id;
    public $category_id;
    public $amount;
    public $description;
    public $expense_date;
    public $frequency;
    public $is_recurring;
    public $next_due_date;
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
    
    // Create new expense
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, category_id, amount, description, expense_date, frequency, is_recurring, next_due_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->expense_date = htmlspecialchars(strip_tags($this->expense_date));
        $this->frequency = htmlspecialchars(strip_tags($this->frequency));
        $this->is_recurring = $this->is_recurring ? 1 : 0;
        
        // Calculate next due date for recurring expenses
        $next_due_date = null;
        if ($this->is_recurring) {
            $date = new DateTime($this->expense_date);
            
            switch ($this->frequency) {
                case 'daily':
                    $date->add(new DateInterval('P1D'));
                    break;
                case 'weekly':
                    $date->add(new DateInterval('P1W'));
                    break;
                case 'bi-weekly':
                    $date->add(new DateInterval('P2W'));
                    break;
                case 'monthly':
                    $date->add(new DateInterval('P1M'));
                    break;
                case 'quarterly':
                    $date->add(new DateInterval('P3M'));
                    break;
                case 'annually':
                    $date->add(new DateInterval('P1Y'));
                    break;
            }
            
            $next_due_date = $date->format('Y-m-d');
        }
        
        $this->next_due_date = $next_due_date;
        
        // Bind parameters
        if (!$stmt->bind_param("iidssiss", 
                          $this->user_id, 
                          $this->category_id, 
                          $this->amount, 
                          $this->description, 
                          $this->expense_date, 
                          $this->frequency, 
                          $this->is_recurring, 
                          $this->next_due_date)) {
            error_log("Parameter binding failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if ($stmt->execute()) {
            $this->expense_id = $this->conn->insert_id;
            
            // Record transaction
            $this->recordTransaction();
            
            return true;
        }
        
        // Log error if something goes wrong
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    // Get all expense categories
    public function getAllCategories() {
        // SQL query
        $query = "SELECT * FROM " . $this->categories_table . " ORDER BY name";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get all expenses for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table . " e
                  JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                  WHERE e.user_id = ? 
                  ORDER BY e.expense_date DESC";
        
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
    
    // Get expense by ID
    public function getById($expense_id, $user_id) {
        // SQL query
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table . " e
                  JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                  WHERE e.expense_id = ? AND e.user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $expense_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->expense_id = $row['expense_id'];
            $this->user_id = $row['user_id'];
            $this->category_id = $row['category_id'];
            $this->amount = $row['amount'];
            $this->description = $row['description'];
            $this->expense_date = $row['expense_date'];
            $this->frequency = $row['frequency'];
            $this->is_recurring = $row['is_recurring'];
            $this->next_due_date = $row['next_due_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update expense
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET category_id = ?, amount = ?, description = ?, 
                      expense_date = ?, frequency = ?, is_recurring = ?, next_due_date = ? 
                  WHERE expense_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Update query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Sanitize input
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->expense_date = htmlspecialchars(strip_tags($this->expense_date));
        $this->frequency = htmlspecialchars(strip_tags($this->frequency));
        $this->is_recurring = $this->is_recurring ? 1 : 0;
        
        // Calculate next due date for recurring expenses
        $next_due_date = null;
        if ($this->is_recurring) {
            $date = new DateTime($this->expense_date);
            
            switch ($this->frequency) {
                case 'daily':
                    $date->add(new DateInterval('P1D'));
                    break;
                case 'weekly':
                    $date->add(new DateInterval('P1W'));
                    break;
                case 'bi-weekly':
                    $date->add(new DateInterval('P2W'));
                    break;
                case 'monthly':
                    $date->add(new DateInterval('P1M'));
                    break;
                case 'quarterly':
                    $date->add(new DateInterval('P3M'));
                    break;
                case 'annually':
                    $date->add(new DateInterval('P1Y'));
                    break;
            }
            
            $next_due_date = $date->format('Y-m-d');
        }
        
        $this->next_due_date = $next_due_date;
        
        // Bind parameters
        if (!$stmt->bind_param("idsssissi", 
                          $this->category_id, 
                          $this->amount, 
                          $this->description, 
                          $this->expense_date, 
                          $this->frequency, 
                          $this->is_recurring, 
                          $this->next_due_date, 
                          $this->expense_id, 
                          $this->user_id)) {
            error_log("Update parameter binding failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Log error if something goes wrong
        error_log("Update execution failed: " . $stmt->error);
        return false;
    }
    
    // Delete expense
    public function delete($expense_id, $user_id) {
        // First, delete related transactions
        $query = "DELETE FROM " . $this->transactions_table . " 
                  WHERE expense_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $expense_id, $user_id);
            $stmt->execute();
        }
        
        // Now delete the expense
        $query = "DELETE FROM " . $this->table . " 
                  WHERE expense_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Delete query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Bind parameters
        $stmt->bind_param("ii", $expense_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Log error if something goes wrong
        error_log("Delete execution failed: " . $stmt->error);
        return false;
    }
    
    // Get monthly total expenses
    public function getMonthlyTotal($user_id) {
        $monthly_total = 0;
        
        // Get current month expenses (one-time)
        $current_month = date('Y-m');
        $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                  WHERE user_id = ? AND frequency = 'one-time' 
                  AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("is", $user_id, $current_month);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $monthly_total += $row['total'] ?? 0;
        
        // Get recurring expenses
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? AND is_recurring = 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Calculate monthly equivalent based on frequency
            switch ($row['frequency']) {
                case 'daily':
                    $monthly_total += $row['amount'] * 30; // Approximate days in a month
                    break;
                case 'weekly':
                    $monthly_total += $row['amount'] * 4.33; // Approximate weeks in a month
                    break;
                case 'bi-weekly':
                    $monthly_total += $row['amount'] * 2.17; // Approximate bi-weeks in a month
                    break;
                case 'monthly':
                    $monthly_total += $row['amount'];
                    break;
                case 'quarterly':
                    $monthly_total += $row['amount'] / 3;
                    break;
                case 'annually':
                    $monthly_total += $row['amount'] / 12;
                    break;
            }
        }
        
        return $monthly_total;
    }
    
    // Get yearly total expenses
    public function getYearlyTotal($user_id) {
        $yearly_total = 0;
        
        // Get current year expenses (one-time)
        $current_year = date('Y');
        $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                  WHERE user_id = ? AND frequency = 'one-time' 
                  AND YEAR(expense_date) = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $user_id, $current_year);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $yearly_total += $row['total'] ?? 0;
        
        // Get recurring expenses
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? AND is_recurring = 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Calculate yearly equivalent based on frequency
            switch ($row['frequency']) {
                case 'daily':
                    $yearly_total += $row['amount'] * 365;
                    break;
                case 'weekly':
                    $yearly_total += $row['amount'] * 52;
                    break;
                case 'bi-weekly':
                    $yearly_total += $row['amount'] * 26;
                    break;
                case 'monthly':
                    $yearly_total += $row['amount'] * 12;
                    break;
                case 'quarterly':
                    $yearly_total += $row['amount'] * 4;
                    break;
                case 'annually':
                    $yearly_total += $row['amount'];
                    break;
            }
        }
        
        return $yearly_total;
    }
    
    // Get top expense categories
    public function getTopCategories($user_id, $limit = 5) {
        // SQL query
        $query = "SELECT c.name as category_name, SUM(e.amount) as total 
                  FROM " . $this->table . " e
                  JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                  WHERE e.user_id = ? 
                  GROUP BY e.category_id 
                  ORDER BY total DESC 
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
    
    // Record transaction for expense
    private function recordTransaction() {
        // SQL query
        $query = "INSERT INTO " . $this->transactions_table . " 
                  (user_id, type, amount, description, transaction_date, category_id, expense_id) 
                  VALUES (?, 'expense', ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Transaction record preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Get category name
        $category_query = "SELECT name FROM " . $this->categories_table . " WHERE category_id = ?";
        $category_stmt = $this->conn->prepare($category_query);
        if (!$category_stmt) {
            error_log("Category query preparation failed: " . $this->conn->error);
            return false;
        }
        
        $category_stmt->bind_param("i", $this->category_id);
        $category_stmt->execute();
        $category_result = $category_stmt->get_result();
        $category_row = $category_result->fetch_assoc();
        $category_name = $category_row['name'] ?? 'Unknown Category';
        
        // Set description
        $full_description = "Expense: " . $this->description . " (Category: " . $category_name . ")";
        
        // Bind parameters
        if (!$stmt->bind_param("idssii", 
                          $this->user_id, 
                          $this->amount, 
                          $full_description, 
                          $this->expense_date, 
                          $this->category_id, 
                          $this->expense_id)) {
            error_log("Transaction parameter binding failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if (!$stmt->execute()) {
            error_log("Transaction record execution failed: " . $stmt->error);
            return false;
        }
        
        return true;
    }
}