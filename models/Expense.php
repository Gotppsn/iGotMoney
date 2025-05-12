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
        try {
            // Begin transaction
            $this->conn->begin_transaction();
            
            // Validate input
            if (!$this->validateInputs()) {
                $this->conn->rollback();
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
                $this->conn->rollback();
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
                $this->conn->rollback();
                return false;
            }
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Query execution failed: " . $stmt->error);
                $this->conn->rollback();
                return false;
            }
            
            // Get the inserted expense ID
            $this->expense_id = $this->conn->insert_id;
            
            // Record transaction
            if (!$this->recordTransaction()) {
                $this->conn->rollback();
                return false;
            }
            
            // If this is a recurring expense, generate future entries
            if ($this->is_recurring) {
                $this->generateRecurringEntries($this->expense_id, $this->expense_date, 12); // Generate for next 12 occurrences
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            if ($this->conn->connect_errno) {
                $this->conn->rollback();
            }
            error_log("Error creating expense: " . $e->getMessage());
            return false;
        }
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
    
    // Get translated category name - NEW METHOD
    public function getTranslatedCategoryName($category_name) {
        // Check if the translation key exists, otherwise return original name
        $translated = __('expense_category_' . $category_name);
        
        // If the translation key doesn't exist and just returns the original key
        if ($translated === 'expense_category_' . $category_name) {
            return $category_name;
        }
        
        return $translated;
    }
    
    // Get translated category description - NEW METHOD
    public function getTranslatedCategoryDescription($category_name) {
        // Check if the translation key exists, otherwise return original description
        $translated = __('expense_category_desc_' . $category_name);
        
        // If the translation key doesn't exist, try to get the original description
        if ($translated === 'expense_category_desc_' . $category_name) {
            // Get original description from database
            $query = "SELECT description FROM " . $this->categories_table . " WHERE name = ?";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("s", $category_name);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return $row['description'];
                }
            }
            
            return $category_name;
        }
        
        return $translated;
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
    
    // Get filtered expenses
    public function getFilteredExpenses($user_id, $month = 0, $year = 0, $category_id = 0) {
        try {
            // Build the base query
            $query = "SELECT e.*, c.name as category_name 
                      FROM " . $this->table . " e
                      JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                      WHERE e.user_id = ?";
            
            // Add month filter if specified
            if ($month > 0) {
                $query .= " AND MONTH(e.expense_date) = ?";
            }
            
            // Add year filter if specified
            if ($year > 0) {
                $query .= " AND YEAR(e.expense_date) = ?";
            }
            
            // Add category filter if specified
            if ($category_id > 0) {
                $query .= " AND e.category_id = ?";
            }
            
            $query .= " ORDER BY e.expense_date DESC";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Get filtered expenses query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters based on filters
            $types = "i";
            $params = [$user_id];
            
            if ($month > 0) {
                $types .= "i";
                $params[] = $month;
            }
            
            if ($year > 0) {
                $types .= "i";
                $params[] = $year;
            }
            
            if ($category_id > 0) {
                $types .= "i";
                $params[] = $category_id;
            }
            
            // Use reflection to bind parameters dynamically
            if (count($params) > 1) {
                $bindParams = array($types);
                foreach ($params as $key => $value) {
                    $bindParams[] = &$params[$key];
                }
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
            } else {
                $stmt->bind_param($types, $user_id);
            }
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Filter query execution failed: " . $stmt->error);
            }
            
            // Get result
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting filtered expenses: " . $e->getMessage());
            return false;
        }
    }
    
    // Get expense by ID
    public function getById($expense_id, $user_id) {
        try {
            // SQL query
            $query = "SELECT e.*, c.name as category_name 
                    FROM " . $this->table . " e
                    JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                    WHERE e.expense_id = ? AND e.user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Get expense by ID query preparation failed: " . $this->conn->error);
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
        } catch (Exception $e) {
            error_log("Error getting expense by ID: " . $e->getMessage());
            return false;
        }
    }
    
    // Update expense
    public function update() {
        try {
            // Begin transaction
            $this->conn->begin_transaction();
            
            // Validate input
            if (!$this->validateInputs()) {
                $this->conn->rollback();
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
                throw new Exception("Update query preparation failed: " . $this->conn->error);
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
                throw new Exception("Update parameter binding failed: " . $stmt->error);
            }
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Update execution failed: " . $stmt->error);
            }
            
            // Update related transaction
            if (!$this->updateTransaction()) {
                $this->conn->rollback();
                return false;
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            if ($this->conn->connect_errno) {
                $this->conn->rollback();
            }
            error_log("Error updating expense: " . $e->getMessage());
            return false;
        }
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
            if (!$stmt->execute()) {
                throw new Exception("Delete transaction execution failed: " . $stmt->error);
            }
            
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
            if (!$stmt->execute()) {
                throw new Exception("Delete expense execution failed: " . $stmt->error);
            }
            
            // Check if any rows were affected
            if ($stmt->affected_rows == 0) {
                throw new Exception("No expense found with ID: " . $expense_id);
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->connect_errno) {
                $this->conn->rollback();
            }
            error_log("Delete failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Get monthly total expenses - FIXED VERSION
    public function getMonthlyTotal($user_id) {
        $monthly_total = 0;
        
        try {
            // Add debug logging
            error_log("Calculating monthly expenses for user_id: $user_id");
            
            // Get current month expenses (one-time)
            $current_month = date('Y-m');
            
            // Check if user_id is valid
            if (!is_numeric($user_id) || $user_id <= 0) {
                error_log("Invalid user_id: $user_id");
                return 0;
            }
            
            // Get one-time expenses for current month
            $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                      WHERE user_id = ? AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
            
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
            
            // Add one-time expenses
            $one_time_total = $row['total'] ?? 0;
            $monthly_total += $one_time_total;
            
            error_log("One-time expenses total: $one_time_total");
            
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
            
            $recurring_total = 0;
            
            while ($row = $result->fetch_assoc()) {
                // Calculate monthly equivalent based on frequency
                $recurring_amount = 0;
                
                switch ($row['frequency']) {
                    case 'daily':
                        $recurring_amount = $row['amount'] * 30; // Approximate days in a month
                        break;
                    case 'weekly':
                        $recurring_amount = $row['amount'] * 4.33; // Approximate weeks in a month
                        break;
                    case 'bi-weekly':
                        $recurring_amount = $row['amount'] * 2.17; // Approximate bi-weeks in a month
                        break;
                    case 'monthly':
                        $recurring_amount = $row['amount'];
                        break;
                    case 'quarterly':
                        $recurring_amount = $row['amount'] / 3;
                        break;
                    case 'annually':
                        $recurring_amount = $row['amount'] / 12;
                        break;
                }
                
                $recurring_total += $recurring_amount;
                
                error_log("Recurring expense: {$row['description']}, Frequency: {$row['frequency']}, Amount: {$row['amount']}, Monthly equivalent: $recurring_amount");
            }
            
            $monthly_total += $recurring_total;
            error_log("Recurring expenses total: $recurring_total");
            error_log("Final monthly total: $monthly_total");
            
            return $monthly_total;
        } catch (Exception $e) {
            error_log("Error calculating monthly total: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get yearly total expenses - FIXED VERSION
    public function getYearlyTotal($user_id) {
        $yearly_total = 0;
        
        try {
            // Add debug logging
            error_log("Calculating yearly expenses for user_id: $user_id");
            
            // Check if user_id is valid
            if (!is_numeric($user_id) || $user_id <= 0) {
                error_log("Invalid user_id: $user_id");
                return 0;
            }
            
            // Get current year expenses (one-time)
            $current_year = date('Y');
            $query = "SELECT SUM(amount) as total FROM " . $this->table . " 
                      WHERE user_id = ? AND YEAR(expense_date) = ?";
            
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
            
            // Add one-time expenses
            $one_time_total = $row['total'] ?? 0;
            $yearly_total += $one_time_total;
            
            error_log("One-time expenses yearly total: $one_time_total");
            
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
            
            $recurring_total = 0;
            
            while ($row = $result->fetch_assoc()) {
                // Calculate yearly equivalent based on frequency
                $recurring_amount = 0;
                
                switch ($row['frequency']) {
                    case 'daily':
                        $recurring_amount = $row['amount'] * 365;
                        break;
                    case 'weekly':
                        $recurring_amount = $row['amount'] * 52;
                        break;
                    case 'bi-weekly':
                        $recurring_amount = $row['amount'] * 26;
                        break;
                    case 'monthly':
                        $recurring_amount = $row['amount'] * 12;
                        break;
                    case 'quarterly':
                        $recurring_amount = $row['amount'] * 4;
                        break;
                    case 'annually':
                        $recurring_amount = $row['amount'];
                        break;
                }
                
                $recurring_total += $recurring_amount;
                
                error_log("Recurring expense (yearly): {$row['description']}, Frequency: {$row['frequency']}, Amount: {$row['amount']}, Yearly equivalent: $recurring_amount");
            }
            
            $yearly_total += $recurring_total;
            error_log("Recurring expenses yearly total: $recurring_total");
            error_log("Final yearly total: $yearly_total");
            
            return $yearly_total;
        } catch (Exception $e) {
            error_log("Error calculating yearly total: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get top expense categories
    public function getTopCategories($user_id, $limit = 5) {
        try {
            // Get current month
            $current_month = date('Y-m');
            
            // SQL query for current month
            $query = "SELECT c.name as category_name, SUM(e.amount) as total 
                      FROM " . $this->table . " e
                      JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                      WHERE e.user_id = ? AND DATE_FORMAT(e.expense_date, '%Y-%m') = ?
                      GROUP BY e.category_id 
                      ORDER BY total DESC 
                      LIMIT ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Top categories query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param("isi", $user_id, $current_month, $limit);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            // If no results for current month, try the last 3 months
            if ($result->num_rows === 0) {
                // Get date 3 months ago
                $three_months_ago = date('Y-m-d', strtotime('-3 months'));
                
                // SQL query for last 3 months
                $query = "SELECT c.name as category_name, SUM(e.amount) as total 
                          FROM " . $this->table . " e
                          JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                          WHERE e.user_id = ? AND e.expense_date >= ?
                          GROUP BY e.category_id 
                          ORDER BY total DESC 
                          LIMIT ?";
                
                // Prepare statement
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Top categories query preparation failed: " . $this->conn->error);
                }
                
                // Bind parameters
                $stmt->bind_param("isi", $user_id, $three_months_ago, $limit);
                
                // Execute query
                $stmt->execute();
                
                // Get result
                $result = $stmt->get_result();
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching top categories: " . $e->getMessage());
            return false;
        }
    }
    
    // Get top expense categories by date range
    public function getTopCategoriesByDateRange($user_id, $start_date, $end_date, $limit = 5) {
        try {
            // SQL query for date range period
            $query = "SELECT c.name as category_name, SUM(e.amount) as total 
                      FROM " . $this->table . " e
                      JOIN " . $this->categories_table . " c ON e.category_id = c.category_id
                      WHERE e.user_id = ? AND e.expense_date BETWEEN ? AND ?
                      GROUP BY e.category_id 
                      ORDER BY total DESC 
                      LIMIT ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Top categories query preparation failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param("issi", $user_id, $start_date, $end_date, $limit);
            
            // Execute query
            $stmt->execute();
            
            // Get result
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching top categories by date range: " . $e->getMessage());
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
            
            // Check if transaction exists
            $check_query = "SELECT COUNT(*) as count FROM " . $this->transactions_table . " 
                           WHERE expense_id = ? AND user_id = ? AND type = 'expense'";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Transaction check query preparation failed: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("ii", $this->expense_id, $this->user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_row = $check_result->fetch_assoc();
            
            if ($check_row['count'] > 0) {
                // Update existing transaction
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
            } else {
                // Create new transaction record
                return $this->recordTransaction();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating transaction: " . $e->getMessage());
            return false;
        }
    }
    
    // Generate recurring expense entries
    public function generateRecurringEntries($parent_expense_id, $start_date, $occurrences = 12) {
        try {
            // Get the parent expense details
            $query = "SELECT * FROM " . $this->table . " WHERE expense_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $parent_expense_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Parent expense not found");
            }
            
            $parent_expense = $result->fetch_assoc();
            
            // Start from the next occurrence
            $current_date = new DateTime($start_date);
            
            for ($i = 0; $i < $occurrences; $i++) {
                // Calculate next date based on frequency
                switch ($parent_expense['frequency']) {
                    case 'daily':
                        $current_date->add(new DateInterval('P1D'));
                        break;
                    case 'weekly':
                        $current_date->add(new DateInterval('P1W'));
                        break;
                    case 'bi-weekly':
                        $current_date->add(new DateInterval('P2W'));
                        break;
                    case 'monthly':
                        $current_date->add(new DateInterval('P1M'));
                        break;
                    case 'quarterly':
                        $current_date->add(new DateInterval('P3M'));
                        break;
                    case 'annually':
                        $current_date->add(new DateInterval('P1Y'));
                        break;
                    default:
                        return; // Skip if not a valid recurring frequency
                }
                
                // Create the new expense entry
                $insert_query = "INSERT INTO " . $this->table . " 
                                (user_id, category_id, amount, description, expense_date, frequency, is_recurring, next_due_date) 
                                VALUES (?, ?, ?, ?, ?, ?, 0, NULL)";
                
                $insert_stmt = $this->conn->prepare($insert_query);
                
                // Add "(Recurring)" to description to indicate it's a generated entry
                $description = $parent_expense['description'] . " (Recurring)";
                
                $insert_stmt->bind_param("iidsss", 
                                       $parent_expense['user_id'], 
                                       $parent_expense['category_id'], 
                                       $parent_expense['amount'], 
                                       $description, 
                                       $current_date->format('Y-m-d'), 
                                       $parent_expense['frequency']);
                
                if (!$insert_stmt->execute()) {
                    error_log("Failed to create recurring expense entry: " . $insert_stmt->error);
                    continue;
                }
                
                // Record transaction for the new expense
                $new_expense_id = $this->conn->insert_id;
                $this->recordTransactionForRecurring($new_expense_id, $parent_expense);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error generating recurring entries: " . $e->getMessage());
            return false;
        }
    }
    
    // Record transaction for recurring expense
    private function recordTransactionForRecurring($expense_id, $expense_data) {
        try {
            // Get category name
            $category_query = "SELECT name FROM " . $this->categories_table . " WHERE category_id = ?";
            $category_stmt = $this->conn->prepare($category_query);
            $category_stmt->bind_param("i", $expense_data['category_id']);
            $category_stmt->execute();
            $category_result = $category_stmt->get_result();
            $category_row = $category_result->fetch_assoc();
            $category_name = $category_row['name'] ?? 'Unknown Category';
            
            // Set description
            $full_description = "Expense: " . $expense_data['description'] . " (Category: " . $category_name . ")";
            
            // SQL query
            $query = "INSERT INTO " . $this->transactions_table . " 
                      (user_id, type, amount, description, transaction_date, category_id, expense_id) 
                      VALUES (?, 'expense', ?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bind_param("idssii", 
                            $expense_data['user_id'], 
                            $expense_data['amount'], 
                            $full_description, 
                            $expense_data['expense_date'], 
                            $expense_data['category_id'], 
                            $expense_id);
            
            // Execute query
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error recording transaction for recurring expense: " . $e->getMessage());
            return false;
        }
    }
    
    // Process all due recurring expenses
    public function processDueRecurringExpenses($user_id = null) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE is_recurring = 1 
                      AND (next_due_date IS NULL OR next_due_date <= CURDATE())";
            
            if ($user_id !== null) {
                $query .= " AND user_id = ?";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($user_id !== null) {
                $stmt->bind_param("i", $user_id);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($expense = $result->fetch_assoc()) {
                // Generate entries for this expense
                $this->generateRecurringEntries($expense['expense_id'], $expense['expense_date'], 12);
                
                // Update the next_due_date for the parent expense
                $update_query = "UPDATE " . $this->table . " 
                                SET next_due_date = ? 
                                WHERE expense_id = ?";
                
                $update_stmt = $this->conn->prepare($update_query);
                
                // Calculate the next due date 12 periods from now
                $next_date = new DateTime($expense['expense_date']);
                for ($i = 0; $i < 12; $i++) {
                    switch ($expense['frequency']) {
                        case 'daily':
                            $next_date->add(new DateInterval('P1D'));
                            break;
                        case 'weekly':
                            $next_date->add(new DateInterval('P1W'));
                            break;
                        case 'bi-weekly':
                            $next_date->add(new DateInterval('P2W'));
                            break;
                        case 'monthly':
                            $next_date->add(new DateInterval('P1M'));
                            break;
                        case 'quarterly':
                            $next_date->add(new DateInterval('P3M'));
                            break;
                        case 'annually':
                            $next_date->add(new DateInterval('P1Y'));
                            break;
                    }
                }
                
                $next_due_date = $next_date->format('Y-m-d');
                $update_stmt->bind_param("si", $next_due_date, $expense['expense_id']);
                $update_stmt->execute();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error processing due recurring expenses: " . $e->getMessage());
            return false;
        }
    }
}