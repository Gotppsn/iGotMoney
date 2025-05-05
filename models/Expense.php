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
        // Validate input
        if (!$this->validateInputs()) {
            return false;
        }
        
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
        
        // Calculate next due date for recurring expenses
        $this->calculateNextDueDate();
        
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
    
    // Validate expense inputs
    private function validateInputs() {
        // Check required fields
        if (empty($this->user_id) || empty($this->category_id) || 
            empty($this->amount) || empty($this->description) || 
            empty($this->expense_date) || empty($this->frequency)) {
            error_log("Expense validation failed: Required fields missing");
            return false;
        }
        
        // Validate amount - must be positive
        if ($this->amount <= 0) {
            error_log("Expense validation failed: Amount must be positive");
            return false;
        }
        
        // Validate frequency
        $valid_frequencies = ['one-time', 'daily', 'weekly', 'bi-weekly', 'monthly', 'quarterly', 'annually'];
        if (!in_array($this->frequency, $valid_frequencies)) {
            error_log("Expense validation failed: Invalid frequency");
            return false;
        }
        
        // If not recurring, force frequency to be one-time
        if (!$this->is_recurring) {
            $this->frequency = 'one-time';
        }
        
        // Sanitize inputs
        $this->user_id = filter_var($this->user_id, FILTER_SANITIZE_NUMBER_INT);
        $this->category_id = filter_var($this->category_id, FILTER_SANITIZE_NUMBER_INT);
        $this->amount = filter_var($this->amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->expense_date = htmlspecialchars(strip_tags($this->expense_date));
        $this->frequency = htmlspecialchars(strip_tags($this->frequency));
        $this->is_recurring = $this->is_recurring ? 1 : 0;
        
        return true;
    }
    
    // Calculate next due date based on frequency
    private function calculateNextDueDate() {
        $this->next_due_date = null;
        
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
                default:
                    // One-time expenses don't have a next due date
                    return;
            }
            
            $this->next_due_date = $date->format('Y-m-d');
        }
    }
    
    // Get all expense categories
    public function getAllCategories() {
        // SQL query
        $query = "SELECT * FROM " . $this->categories_table . " ORDER BY name";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Get categories query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get all expenses for a user
    public function getAll($user_id, $limit = null) {
        // SQL query
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table . " e
                  JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                  WHERE e.user_id = ? 
                  ORDER BY e.expense_date DESC";
                  
        if ($limit !== null && is_numeric($limit)) {
            $query .= " LIMIT " . intval($limit);
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Get all expenses query preparation failed: " . $this->conn->error);
            return false;
        }
        
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
        if (!$stmt) {
            error_log("Get expense by ID query preparation failed: " . $this->conn->error);
            return false;
        }
        
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
        // Validate input
        if (!$this->validateInputs()) {
            return false;
        }
        
        // Calculate next due date for recurring expenses
        $this->calculateNextDueDate();
        
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
            // Update related transaction
            $this->updateTransaction();
            return true;
        }
        
        // Log error if something goes wrong
        error_log("Update execution failed: " . $stmt->error);
        return false;
    }
    
    // Delete expense
    public function delete($expense_id, $user_id) {
        try {
            // Begin transaction
            $this->conn->begin_transaction();
            
            // First, delete related transactions
            $query = "DELETE FROM " . $this->transactions_table . " 
                      WHERE expense_id = ? AND user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Delete transaction query preparation failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ii", $expense_id, $user_id);
            $stmt->execute();
            
            // Now delete the expense
            $query = "DELETE FROM " . $this->table . " 
                      WHERE expense_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Delete expense query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $expense_id, $user_id);
            
            // Execute query
            $stmt->execute();
            
            // Check if any rows were affected
            if ($stmt->affected_rows == 0) {
                throw new Exception("No expense found with ID: " . $expense_id);
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Delete failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Get monthly total expenses
    public function getMonthlyTotal($user_id) {
        $monthly_total = 0;
        
        try {
            // Get current month expenses (one-time)
            $current_month = date('Y-m');
            $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                      WHERE user_id = ? AND frequency = 'one-time' 
                      AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Monthly total query preparation failed: " . $this->conn->error);
            }
            
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
            if (!$stmt) {
                throw new Exception("Recurring expenses query preparation failed: " . $this->conn->error);
            }
            
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
        } catch (Exception $e) {
            error_log("Error calculating monthly total: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get yearly total expenses
    public function getYearlyTotal($user_id) {
        $yearly_total = 0;
        
        try {
            // Get current year expenses (one-time)
            $current_year = date('Y');
            $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                      WHERE user_id = ? AND frequency = 'one-time' 
                      AND YEAR(expense_date) = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Yearly total query preparation failed: " . $this->conn->error);
            }
            
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
            if (!$stmt) {
                throw new Exception("Recurring expenses query preparation failed: " . $this->conn->error);
            }
            
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
        } catch (Exception $e) {
            error_log("Error calculating yearly total: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get top expense categories
    public function getTopCategories($user_id, $limit = 5) {
        try {
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
            if (!$stmt) {
                throw new Exception("Top categories query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $user_id, $limit);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching top categories: " . $e->getMessage());
            return false;
        }
    }
    
    // Get expenses by date range
    public function getByDateRange($user_id, $start_date, $end_date) {
        try {
            // SQL query
            $query = "SELECT e.*, c.name as category_name 
                      FROM " . $this->table . " e
                      JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                      WHERE e.user_id = ? 
                      AND e.expense_date BETWEEN ? AND ?
                      ORDER BY e.expense_date DESC";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Date range query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param("iss", $user_id, $start_date, $end_date);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching expenses by date range: " . $e->getMessage());
            return false;
        }
    }
    
    // Record transaction for expense
    private function recordTransaction() {
        try {
            // SQL query
            $query = "INSERT INTO " . $this->transactions_table . " 
                      (user_id, type, amount, description, transaction_date, category_id, expense_id) 
                      VALUES (?, 'expense', ?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Transaction record preparation failed: " . $this->conn->error);
            }
            
            // Get category name
            $category_query = "SELECT name FROM " . $this->categories_table . " WHERE category_id = ?";
            $category_stmt = $this->conn->prepare($category_query);
            if (!$category_stmt) {
                throw new Exception("Category query preparation failed: " . $this->conn->error);
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
                throw new Exception("Transaction parameter binding failed: " . $stmt->error);
            }
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Transaction record execution failed: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error recording transaction: " . $e->getMessage());
            return false;
        }
    }
    
    // Update transaction for expense
    private function updateTransaction() {
        try {
            // Get category name
            $category_query = "SELECT name FROM " . $this->categories_table . " WHERE category_id = ?";
            $category_stmt = $this->conn->prepare($category_query);
            if (!$category_stmt) {
                throw new Exception("Category query preparation failed: " . $this->conn->error);
            }
            
            $category_stmt->bind_param("i", $this->category_id);
            $category_stmt->execute();
            $category_result = $category_stmt->get_result();
            $category_row = $category_result->fetch_assoc();
            $category_name = $category_row['name'] ?? 'Unknown Category';
            
            // Set description
            $full_description = "Expense: " . $this->description . " (Category: " . $category_name . ")";
            
            // SQL query
            $query = "UPDATE " . $this->transactions_table . " 
                      SET amount = ?, description = ?, transaction_date = ?, category_id = ?
                      WHERE expense_id = ? AND user_id = ? AND type = 'expense'";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Transaction update preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            if (!$stmt->bind_param("dssiii", 
                              $this->amount, 
                              $full_description, 
                              $this->expense_date, 
                              $this->category_id, 
                              $this->expense_id,
                              $this->user_id)) {
                throw new Exception("Transaction update parameter binding failed: " . $stmt->error);
            }
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Transaction update execution failed: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating transaction: " . $e->getMessage());
            return false;
        }
    }
}