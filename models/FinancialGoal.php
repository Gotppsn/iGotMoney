<?php
/**
 * Financial Goal Model
 * 
 * Handles financial goal-related database operations
 */

require_once 'config/database.php';

class FinancialGoal {
    private $conn;
    private $table = 'financial_goals';
    
    // Goal properties
    public $goal_id;
    public $user_id;
    public $name;
    public $target_amount;
    public $current_amount;
    public $start_date;
    public $target_date;
    public $description;
    public $priority;
    public $status;
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
    
    // Create new financial goal
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, name, target_amount, current_amount, start_date, target_date, 
                   description, priority, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->target_amount = htmlspecialchars(strip_tags($this->target_amount));
        $this->current_amount = htmlspecialchars(strip_tags($this->current_amount));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->target_date = htmlspecialchars(strip_tags($this->target_date));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind parameters
        $stmt->bind_param("isddssss", 
                          $this->user_id, 
                          $this->name, 
                          $this->target_amount, 
                          $this->current_amount, 
                          $this->start_date, 
                          $this->target_date, 
                          $this->description, 
                          $this->priority, 
                          $this->status);
        
        // Execute query
        if ($stmt->execute()) {
            $this->goal_id = $this->conn->insert_id;
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all financial goals for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? 
                  ORDER BY priority DESC, target_date ASC";
        
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
    
    // Get active financial goals for a user
    public function getActiveGoals($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? AND status = 'in-progress' 
                  ORDER BY priority DESC, target_date ASC";
        
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
    
    // Get financial goal by ID
    public function getById($goal_id, $user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE goal_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $goal_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->goal_id = $row['goal_id'];
            $this->user_id = $row['user_id'];
            $this->name = $row['name'];
            $this->target_amount = $row['target_amount'];
            $this->current_amount = $row['current_amount'];
            $this->start_date = $row['start_date'];
            $this->target_date = $row['target_date'];
            $this->description = $row['description'];
            $this->priority = $row['priority'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update financial goal
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET name = ?, target_amount = ?, current_amount = ?, 
                      start_date = ?, target_date = ?, description = ?, 
                      priority = ?, status = ? 
                  WHERE goal_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->target_amount = htmlspecialchars(strip_tags($this->target_amount));
        $this->current_amount = htmlspecialchars(strip_tags($this->current_amount));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->target_date = htmlspecialchars(strip_tags($this->target_date));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Check if goal is completed
        if ($this->current_amount >= $this->target_amount && $this->status != 'completed') {
            $this->status = 'completed';
        }
        
        // Bind parameters
        $stmt->bind_param("sddsssssii", 
                          $this->name, 
                          $this->target_amount, 
                          $this->current_amount, 
                          $this->start_date, 
                          $this->target_date, 
                          $this->description, 
                          $this->priority, 
                          $this->status, 
                          $this->goal_id, 
                          $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Update goal progress
    public function updateProgress($goal_id, $user_id, $amount) {
        // Get current goal
        if (!$this->getById($goal_id, $user_id)) {
            return false;
        }
        
        // Update current amount
        $this->current_amount += $amount;
        
        // Check if goal is completed
        if ($this->current_amount >= $this->target_amount) {
            $this->status = 'completed';
        }
        
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET current_amount = ?, status = ? 
                  WHERE goal_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("dsii", 
                          $this->current_amount, 
                          $this->status, 
                          $this->goal_id, 
                          $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete financial goal
    public function delete($goal_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE goal_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $goal_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Calculate recommended monthly contribution
    public function calculateMonthlyContribution() {
        if (!isset($this->start_date) || !isset($this->target_date) || 
            !isset($this->target_amount) || !isset($this->current_amount)) {
            return 0;
        }
        
        // Calculate months between start and target dates
        $start = new DateTime($this->start_date);
        $target = new DateTime($this->target_date);
        $interval = $start->diff($target);
        $months = ($interval->y * 12) + $interval->m;
        
        // If past date or less than one month, return total amount needed
        if ($months <= 0) {
            return $this->target_amount - $this->current_amount;
        }
        
        // Calculate monthly contribution needed
        $remaining = $this->target_amount - $this->current_amount;
        $monthly = $remaining / $months;
        
        return $monthly;
    }
    
    // Calculate progress percentage
    public function calculateProgressPercentage() {
        if (!isset($this->target_amount) || !isset($this->current_amount) || $this->target_amount <= 0) {
            return 0;
        }
        
        return ($this->current_amount / $this->target_amount) * 100;
    }
    
    // Calculate time progress percentage
    public function calculateTimeProgressPercentage() {
        if (!isset($this->start_date) || !isset($this->target_date)) {
            return 0;
        }
        
        $start = new DateTime($this->start_date);
        $target = new DateTime($this->target_date);
        $now = new DateTime();
        
        $total_interval = $start->diff($target);
        $elapsed_interval = $start->diff($now);
        
        $total_days = $total_interval->days;
        $elapsed_days = $elapsed_interval->days;
        
        if ($total_days <= 0) {
            return 100;
        }
        
        return min(100, ($elapsed_days / $total_days) * 100);
    }
    
    // Check if goal is on track
    public function isOnTrack() {
        $progress_percentage = $this->calculateProgressPercentage();
        $time_progress_percentage = $this->calculateTimeProgressPercentage();
        
        // Goal is on track if progress percentage >= time progress percentage
        return $progress_percentage >= $time_progress_percentage;
    }
    
    // Generate recommended goals based on income and existing goals
    public function recommendGoals($user_id, $monthly_income) {
        $recommended_goals = array();
        
        // Get existing goals
        $existing_goals = $this->getActiveGoals($user_id);
        $has_emergency_fund = false;
        $has_retirement = false;
        $has_debt_payment = false;
        
        // Check existing goals
        while ($goal = $existing_goals->fetch_assoc()) {
            if (stripos($goal['name'], 'emergency') !== false) {
                $has_emergency_fund = true;
            }
            if (stripos($goal['name'], 'retirement') !== false) {
                $has_retirement = true;
            }
            if (stripos($goal['name'], 'debt') !== false) {
                $has_debt_payment = true;
            }
        }
        
        // Recommend emergency fund if not exists
        if (!$has_emergency_fund) {
            $emergency_fund = array(
                'name' => 'Emergency Fund',
                'target_amount' => $monthly_income * 6, // 6 months of income
                'description' => 'Build an emergency fund to cover 6 months of expenses.',
                'priority' => 'high',
                'timeline' => '12 months',
                'monthly_contribution' => ($monthly_income * 6) / 12 // Split over 1 year
            );
            $recommended_goals[] = $emergency_fund;
        }
        
        // Recommend retirement savings if not exists
        if (!$has_retirement) {
            $retirement_fund = array(
                'name' => 'Retirement Savings',
                'target_amount' => $monthly_income * 12 * 25, // 25 years of income
                'description' => 'Start building your retirement nest egg.',
                'priority' => 'medium',
                'timeline' => 'long-term',
                'monthly_contribution' => $monthly_income * 0.15 // 15% of monthly income
            );
            $recommended_goals[] = $retirement_fund;
        }
        
        // Recommend debt payment if not exists
        if (!$has_debt_payment) {
            $debt_payment = array(
                'name' => 'Debt Reduction',
                'target_amount' => $monthly_income * 12, // Placeholder amount
                'description' => 'Work towards paying off high-interest debt.',
                'priority' => 'high',
                'timeline' => '24 months',
                'monthly_contribution' => ($monthly_income * 12) / 24 // Split over 2 years
            );
            $recommended_goals[] = $debt_payment;
        }
        
        // Add other common financial goals
        $recommended_goals[] = array(
            'name' => 'Home Down Payment',
            'target_amount' => $monthly_income * 12 * 3, // 3 years of income
            'description' => 'Save for a down payment on a home.',
            'priority' => 'medium',
            'timeline' => '36 months',
            'monthly_contribution' => ($monthly_income * 12 * 3) / 36 // Split over 3 years
        );
        
        $recommended_goals[] = array(
            'name' => 'Education Fund',
            'target_amount' => $monthly_income * 12 * 2, // 2 years of income
            'description' => 'Save for education expenses.',
            'priority' => 'medium',
            'timeline' => '48 months',
            'monthly_contribution' => ($monthly_income * 12 * 2) / 48 // Split over 4 years
        );
        
        $recommended_goals[] = array(
            'name' => 'Vacation Fund',
            'target_amount' => $monthly_income * 3, // 3 months of income
            'description' => 'Save for a dream vacation.',
            'priority' => 'low',
            'timeline' => '12 months',
            'monthly_contribution' => ($monthly_income * 3) / 12 // Split over 1 year
        );
        
        return $recommended_goals;
    }
}
?>