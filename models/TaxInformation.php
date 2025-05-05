<?php
/**
 * Tax Information Model
 * 
 * Handles tax information-related database operations
 */

require_once 'config/database.php';
require_once 'models/Income.php';

class TaxInformation {
    private $conn;
    private $table = 'tax_information';
    
    // Tax information properties
    public $tax_id;
    public $user_id;
    public $tax_year;
    public $filing_status;
    public $estimated_income;
    public $tax_paid_to_date;
    public $deductions;
    public $credits;
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
    
    // Create new tax information
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, tax_year, filing_status, estimated_income, tax_paid_to_date, deductions, credits) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->tax_year = htmlspecialchars(strip_tags($this->tax_year));
        $this->filing_status = htmlspecialchars(strip_tags($this->filing_status));
        $this->estimated_income = htmlspecialchars(strip_tags($this->estimated_income));
        $this->tax_paid_to_date = htmlspecialchars(strip_tags($this->tax_paid_to_date));
        $this->deductions = htmlspecialchars(strip_tags($this->deductions));
        $this->credits = htmlspecialchars(strip_tags($this->credits));
        
        // Bind parameters
        $stmt->bind_param("iisdddd", 
                          $this->user_id, 
                          $this->tax_year, 
                          $this->filing_status, 
                          $this->estimated_income, 
                          $this->tax_paid_to_date, 
                          $this->deductions, 
                          $this->credits);
        
        // Execute query
        if ($stmt->execute()) {
            $this->tax_id = $this->conn->insert_id;
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all tax information for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = ? 
                  ORDER BY tax_year DESC";
        
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
    
    // Get tax information by ID
    public function getById($tax_id, $user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE tax_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $tax_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->tax_id = $row['tax_id'];
            $this->user_id = $row['user_id'];
            $this->tax_year = $row['tax_year'];
            $this->filing_status = $row['filing_status'];
            $this->estimated_income = $row['estimated_income'];
            $this->tax_paid_to_date = $row['tax_paid_to_date'];
            $this->deductions = $row['deductions'];
            $this->credits = $row['credits'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Get tax information by year
    public function getByYear($tax_year, $user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE tax_year = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $tax_year, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->tax_id = $row['tax_id'];
            $this->user_id = $row['user_id'];
            $this->tax_year = $row['tax_year'];
            $this->filing_status = $row['filing_status'];
            $this->estimated_income = $row['estimated_income'];
            $this->tax_paid_to_date = $row['tax_paid_to_date'];
            $this->deductions = $row['deductions'];
            $this->credits = $row['credits'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update tax information
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET filing_status = ?, estimated_income = ?, tax_paid_to_date = ?, 
                      deductions = ?, credits = ? 
                  WHERE tax_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->filing_status = htmlspecialchars(strip_tags($this->filing_status));
        $this->estimated_income = htmlspecialchars(strip_tags($this->estimated_income));
        $this->tax_paid_to_date = htmlspecialchars(strip_tags($this->tax_paid_to_date));
        $this->deductions = htmlspecialchars(strip_tags($this->deductions));
        $this->credits = htmlspecialchars(strip_tags($this->credits));
        
        // Bind parameters
        $stmt->bind_param("sddddii", 
                          $this->filing_status, 
                          $this->estimated_income, 
                          $this->tax_paid_to_date, 
                          $this->deductions, 
                          $this->credits, 
                          $this->tax_id, 
                          $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete tax information
    public function delete($tax_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE tax_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $tax_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Calculate estimated tax liability
    public function calculateTaxLiability() {
        // This is a simplified tax calculation
        // In a real application, this would use actual tax brackets and rules
        
        if (!isset($this->estimated_income) || !isset($this->filing_status)) {
            return 0;
        }
        
        $income = $this->estimated_income;
        $filing_status = $this->filing_status;
        $deductions = $this->deductions ?? 0;
        $credits = $this->credits ?? 0;
        
        // Calculate taxable income
        $taxable_income = max(0, $income - $deductions);
        
        // Calculate tax based on filing status and income brackets
        $tax = 0;
        
        if ($filing_status == 'single') {
            // 2024 tax brackets for single filers (simplified)
            if ($taxable_income <= 11000) {
                $tax = $taxable_income * 0.10;
            } else if ($taxable_income <= 44725) {
                $tax = 1100 + ($taxable_income - 11000) * 0.12;
            } else if ($taxable_income <= 95375) {
                $tax = 5147 + ($taxable_income - 44725) * 0.22;
            } else if ($taxable_income <= 182100) {
                $tax = 16290 + ($taxable_income - 95375) * 0.24;
            } else if ($taxable_income <= 231250) {
                $tax = 37104 + ($taxable_income - 182100) * 0.32;
            } else if ($taxable_income <= 578125) {
                $tax = 52832 + ($taxable_income - 231250) * 0.35;
            } else {
                $tax = 174238.25 + ($taxable_income - 578125) * 0.37;
            }
        } else if ($filing_status == 'married_joint') {
            // 2024 tax brackets for married filing jointly (simplified)
            if ($taxable_income <= 22000) {
                $tax = $taxable_income * 0.10;
            } else if ($taxable_income <= 89450) {
                $tax = 2200 + ($taxable_income - 22000) * 0.12;
            } else if ($taxable_income <= 190750) {
                $tax = 10294 + ($taxable_income - 89450) * 0.22;
            } else if ($taxable_income <= 364200) {
                $tax = 32580 + ($taxable_income - 190750) * 0.24;
            } else if ($taxable_income <= 462500) {
                $tax = 74208 + ($taxable_income - 364200) * 0.32;
            } else if ($taxable_income <= 693750) {
                $tax = 105664 + ($taxable_income - 462500) * 0.35;
            } else {
                $tax = 186601.5 + ($taxable_income - 693750) * 0.37;
            }
        } else if ($filing_status == 'married_separate') {
            // 2024 tax brackets for married filing separately (simplified)
            if ($taxable_income <= 11000) {
                $tax = $taxable_income * 0.10;
            } else if ($taxable_income <= 44725) {
                $tax = 1100 + ($taxable_income - 11000) * 0.12;
            } else if ($taxable_income <= 95375) {
                $tax = 5147 + ($taxable_income - 44725) * 0.22;
            } else if ($taxable_income <= 182100) {
                $tax = 16290 + ($taxable_income - 95375) * 0.24;
            } else if ($taxable_income <= 231250) {
                $tax = 37104 + ($taxable_income - 182100) * 0.32;
            } else if ($taxable_income <= 346875) {
                $tax = 52832 + ($taxable_income - 231250) * 0.35;
            } else {
                $tax = 93300.75 + ($taxable_income - 346875) * 0.37;
            }
        } else if ($filing_status == 'head_of_household') {
            // 2024 tax brackets for head of household (simplified)
            if ($taxable_income <= 15700) {
                $tax = $taxable_income * 0.10;
            } else if ($taxable_income <= 59850) {
                $tax = 1570 + ($taxable_income - 15700) * 0.12;
            } else if ($taxable_income <= 95350) {
                $tax = 6868 + ($taxable_income - 59850) * 0.22;
            } else if ($taxable_income <= 182100) {
                $tax = 14678 + ($taxable_income - 95350) * 0.24;
            } else if ($taxable_income <= 231250) {
                $tax = 35498 + ($taxable_income - 182100) * 0.32;
            } else if ($taxable_income <= 578100) {
                $tax = 51226 + ($taxable_income - 231250) * 0.35;
            } else {
                $tax = 172623.5 + ($taxable_income - 578100) * 0.37;
            }
        }
        
        // Apply tax credits
        $tax = max(0, $tax - $credits);
        
        return $tax;
    }
    
    // Get remaining tax owed
    public function getRemainingTax() {
        $tax_liability = $this->calculateTaxLiability();
        $tax_paid = $this->tax_paid_to_date ?? 0;
        
        return max(0, $tax_liability - $tax_paid);
    }
    
    // Calculate effective tax rate
    public function getEffectiveTaxRate() {
        if (!isset($this->estimated_income) || $this->estimated_income <= 0) {
            return 0;
        }
        
        $tax_liability = $this->calculateTaxLiability();
        
        return ($tax_liability / $this->estimated_income) * 100;
    }
    
    // Generate tax saving tips
    public function generateTaxSavingTips() {
        $tips = array();
        
        // Basic tax tips based on filing status and income
        if ($this->filing_status == 'single' || $this->filing_status == 'head_of_household') {
            $tips[] = array(
                'title' => 'Consider Traditional IRA Contributions',
                'description' => 'You may be eligible to deduct traditional IRA contributions, reducing your taxable income.',
                'potential_savings' => 'Up to $6,500 in deductions ($7,500 if age 50+).'
            );
        }
        
        if ($this->filing_status == 'married_joint') {
            $tips[] = array(
                'title' => 'Consider Filing Status Optimization',
                'description' => 'Compare your tax liability filing jointly versus separately to determine the most advantageous option.',
                'potential_savings' => 'Varies based on income disparity between spouses.'
            );
        }
        
        // Income-based tips
        if ($this->estimated_income > 100000) {
            $tips[] = array(
                'title' => 'Maximize Retirement Contributions',
                'description' => 'Contribute the maximum to your 401(k) or other employer-sponsored retirement plans.',
                'potential_savings' => 'Up to $22,500 in pre-tax contributions ($30,000 if age 50+).'
            );
            
            $tips[] = array(
                'title' => 'Consider Tax-Loss Harvesting',
                'description' => 'Offset capital gains by selling investments at a loss.',
                'potential_savings' => 'Up to $3,000 can be deducted against ordinary income.'
            );
        } else {
            $tips[] = array(
                'title' => 'Check Eligibility for Saver\'s Credit',
                'description' => 'You may qualify for the Saver\'s Credit for retirement contributions.',
                'potential_savings' => 'Up to $1,000 credit for individuals, $2,000 for married filing jointly.'
            );
        }
        
        // Deduction-related tips
        if ($this->deductions < 10000) {
            $tips[] = array(
                'title' => 'Track Eligible Deductions',
                'description' => 'Keep receipts for medical expenses, charitable donations, and other eligible deductions.',
                'potential_savings' => 'Varies based on your tax bracket and eligible expenses.'
            );
        }
        
        // Credit-related tips
        $tips[] = array(
            'title' => 'Check Eligibility for Tax Credits',
            'description' => 'Research tax credits like the Earned Income Tax Credit, Child Tax Credit, or education credits.',
            'potential_savings' => 'Credits directly reduce tax owed, potentially by thousands.'
        );
        
        return $tips;
    }
    
    // Auto-fill tax information from income sources
    public function autoFillFromIncome($user_id, $tax_year) {
        // Get income data
        $income = new Income();
        $yearly_income = $income->getYearlyTotal($user_id);
        
        // Check if tax information already exists for this year
        if ($this->getByYear($tax_year, $user_id)) {
            // Update existing record
            $this->estimated_income = $yearly_income;
            return $this->update();
        } else {
            // Create new record
            $this->user_id = $user_id;
            $this->tax_year = $tax_year;
            $this->filing_status = 'single'; // Default
            $this->estimated_income = $yearly_income;
            $this->tax_paid_to_date = 0;
            $this->deductions = 0;
            $this->credits = 0;
            
            return $this->create();
        }
    }
}
?>