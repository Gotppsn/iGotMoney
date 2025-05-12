<?php
/**
 * User Settings Model
 * 
 * Handles user settings-related database operations
 */

require_once 'config/database.php';

class UserSettings {
    private $conn;
    private $table = 'user_settings';
    
    // User settings properties
    public $setting_id;
    public $user_id;
    public $currency;
    public $theme;
    public $notification_enabled;
    public $email_notification_enabled;
    public $budget_alert_threshold;
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
    
    // Get user settings
    public function getSettings($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Set properties
            $this->setting_id = $row['setting_id'];
            $this->user_id = $row['user_id'];
            $this->currency = $row['currency'];
            $this->theme = $row['theme'];
            $this->notification_enabled = $row['notification_enabled'];
            $this->email_notification_enabled = $row['email_notification_enabled'];
            $this->budget_alert_threshold = $row['budget_alert_threshold'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Create default settings for new user
    public function createDefault($user_id) {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, currency, theme, notification_enabled, email_notification_enabled, budget_alert_threshold) 
                  VALUES (?, 'USD', 'light', 1, 1, 80)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Update user settings
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET currency = ?
                  WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        
        // Bind parameters
        $stmt->bind_param("si", 
                          $this->currency, 
                          $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Reset settings to default values
    public function resetToDefault() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET currency = 'USD'
                  WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            // Update local properties
            $this->currency = 'USD';
            
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get available currencies
    public function getAvailableCurrencies() {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'JPY' => 'Japanese Yen (¥)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'CNY' => 'Chinese Yuan (¥)',
            'INR' => 'Indian Rupee (₹)',
            'BRL' => 'Brazilian Real (R$)',
            'MXN' => 'Mexican Peso (Mex$)',
            'THB' => 'Thai Baht (฿)'
        ];
    }
    
    // Get available themes
    public function getAvailableThemes() {
        return [
            'light' => 'Light Mode',
            'dark' => 'Dark Mode',
            'system' => 'System Default'
        ];
    }
    
    // Get currency symbol
    public function getCurrencySymbol($currency = null) {
        $currency = $currency ?? $this->currency;
        
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CNY' => '¥',
            'INR' => '₹',
            'BRL' => 'R$',
            'MXN' => 'Mex$',
            'THB' => '฿'
        ];
        
        return $symbols[$currency] ?? '$';
    }
}
?>