<?php
/**
 * Income Model
 * 
 * Handles income-related database operations
 */

require_once 'config/database.php';

class Income {
    private $conn;
    private $table = 'income_sources';
    private $transactions_table = 'transactions';
    
    // Income properties
    public $income_id;
    public $user_id;
    public $name;
    public $amount;
    public $frequency;
    public $start_date;
    public $end_date;
    public $is_active;
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
    
    // Create new income source
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, name, amount, frequency, start_date, end_date, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->frequency = htmlspecialchars(strip_tags($this->frequency));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->is_active = $this->is_active ? 1 : 0;
        
        // Format end date or set to null
        $end_date_param = !empty($this->end_date) ? $this->end_date : null;
        
        // Bind parameters
        if (!$stmt->bind_param("isdssis", 
                          $this->user_id, 
                          $this->name, 
                          $this->amount, 
                          $this->frequency, 
                          $this->start_date, 
                          $end_date_param,
                          $this->is_active)) {
            error_log("Parameter binding failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if ($stmt->execute()) {
            $this->income_id = $this->conn->insert_id;
            
            // Record transaction
            $this->recordTransaction();
            
            return true;
        }
        
        // Print error if something goes wrong
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    // Get all income sources for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC";
        
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
    
    // Get income source by ID
    public function getById($income_id, $user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE income_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $income_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->income_id = $row['income_id'];
            $this->user_id = $row['user_id'];
            $this->name = $row['name'];
            $this->amount = $row['amount'];
            $this->frequency = $row['frequency'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update income source
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET name = ?, amount = ?, frequency = ?, 
                      start_date = ?, end_date = ?, is_active = ? 
                  WHERE income_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Update query preparation failed: " . $this->conn->error);
            return false;
        }
        
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->frequency = htmlspecialchars(strip_tags($this->frequency));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->is_active = $this->is_active ? 1 : 0;
        
        // Format end date or set to null
        $end_date_param = !empty($this->end_date) ? $this->end_date : null;
        
        // Bind parameters
        if (!$stmt->bind_param("sdssiii", 
                           $this->name, 
                           $this->amount, 
                           $this->frequency, 
                           $this->start_date, 
                           $end_date_param,
                           $this->is_active,
                           $this->income_id,
                           $this->user_id)) {
            error_log("Update parameter binding failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        error_log("Update execution failed: " . $stmt->error);
        return false;
    }
    
    // Delete income source
    public function delete($income_id, $user_id) {
        // First, delete related transactions
        $query = "DELETE FROM " . $this->transactions_table . " 
                  WHERE income_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $income_id, $user_id);
        $stmt->execute();
        
        // Now delete the income source
        $query = "DELETE FROM " . $this->table . " 
                  WHERE income_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $income_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        error_log("Delete failed: " . $stmt->error);
        return false;
    }
    
    // Get monthly total income
    public function getMonthlyTotal($user_id) {
        $monthly_total = 0;
        
        // Get all active income sources
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? AND is_active = 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Check if income has expired
            if (!empty($row['end_date'])) {
                $end_date = new DateTime($row['end_date']);
                $now = new DateTime();
                if ($end_date < $now) {
                    continue; // Skip expired income
                }
            }
            
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
                case 'one-time':
                    // For one-time income, only count if it's in the current month
                    $start_date = new DateTime($row['start_date']);
                    $now = new DateTime();
                    if ($start_date->format('Y-m') === $now->format('Y-m')) {
                        $monthly_total += $row['amount'];
                    }
                    break;
            }
        }
        
        return $monthly_total;
    }
    
    // Get yearly total income
    public function getYearlyTotal($user_id) {
        $yearly_total = 0;
        
        // Get all active income sources
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? AND is_active = 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Check if income has expired
            if (!empty($row['end_date'])) {
                $end_date = new DateTime($row['end_date']);
                $now = new DateTime();
                if ($end_date < $now) {
                    continue; // Skip expired income
                }
            }
            
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
                case 'one-time':
                    // For one-time income, only count if it's in the current year
                    $start_date = new DateTime($row['start_date']);
                    $now = new DateTime();
                    if ($start_date->format('Y') === $now->format('Y')) {
                        $yearly_total += $row['amount'];
                    }
                    break;
            }
        }
        
        return $yearly_total;
    }
    
    // Record transaction for income
    private function recordTransaction() {
        // SQL query
        $query = "INSERT INTO " . $this->transactions_table . " 
                  (user_id, type, amount, description, transaction_date, income_id) 
                  VALUES (?, 'income', ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set description
        $description = "Income from: " . $this->name;
        
        // Bind parameters
        $stmt->bind_param("idssi", 
                          $this->user_id, 
                          $this->amount, 
                          $description, 
                          $this->start_date, 
                          $this->income_id);
        
        // Execute query
        $stmt->execute();
    }
}