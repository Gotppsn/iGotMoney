<?php
/**
 * User Settings Model
 * 
 * Handles user settings operations
 */

require_once 'BaseModel.php';

class UserSettings extends BaseModel {
    // Properties
    public $setting_id;
    public $user_id;
    public $currency = 'USD';
    public $language = 'en';
    public $theme = 'system';
    public $notification_enabled = true;
    public $email_notification_enabled = true;
    public $budget_alert_threshold = 80;
    
    /**
     * Get user settings
     * 
     * @param int $user_id User ID
     * @return bool
     */
    public function getSettings($user_id) {
        $conn = $this->connectDB();
        
        try {
            // First, let's check if the language column exists
            $tableCheck = $conn->query("SHOW COLUMNS FROM user_settings LIKE 'language'");
            
            if ($tableCheck && $tableCheck->num_rows === 0) {
                // Language column doesn't exist, add it
                $alterTable = $conn->query("ALTER TABLE user_settings ADD COLUMN language VARCHAR(5) DEFAULT 'en' AFTER currency");
                if (!$alterTable) {
                    error_log("Failed to add language column: " . $conn->error);
                }
            }
            
            $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            if (!$stmt) {
                error_log("Error preparing statement: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                error_log("Error executing statement: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $settings = $result->fetch_assoc();
                
                $this->setting_id = $settings['setting_id'];
                $this->user_id = $settings['user_id'];
                $this->currency = $settings['currency'];
                
                // Check if language column exists in the result
                if (array_key_exists('language', $settings) && $settings['language']) {
                    $this->language = $settings['language'];
                }
                
                $this->theme = $settings['theme'];
                $this->notification_enabled = (bool)$settings['notification_enabled'];
                $this->email_notification_enabled = (bool)$settings['email_notification_enabled'];
                $this->budget_alert_threshold = $settings['budget_alert_threshold'];
                
                $this->closeDB($conn);
                return true;
            }
            
            $this->closeDB($conn);
            return false;
        } catch (Exception $e) {
            error_log("Exception in getSettings: " . $e->getMessage());
            $this->closeDB($conn);
            return false;
        }
    }
    
    /**
     * Create default settings for user
     * 
     * @param int $user_id User ID
     * @return bool
     */
    public function createDefault($user_id) {
        $conn = $this->connectDB();
        
        try {
            // Check if settings already exist
            $stmt = $conn->prepare("SELECT setting_id FROM user_settings WHERE user_id = ?");
            if (!$stmt) {
                error_log("Error preparing statement in createDefault: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                error_log("Error executing statement in createDefault: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Settings already exist
                $this->closeDB($conn);
                return false;
            }
            
            // Create default settings
            $stmt = $conn->prepare("INSERT INTO user_settings (user_id, currency, language, theme, notification_enabled, email_notification_enabled, budget_alert_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                error_log("Error preparing insert statement: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            $currency = $this->currency;
            $language = $this->language;
            $theme = $this->theme;
            $notification_enabled = $this->notification_enabled ? 1 : 0;
            $email_notification_enabled = $this->email_notification_enabled ? 1 : 0;
            $budget_alert_threshold = $this->budget_alert_threshold;
            
            $stmt->bind_param("isssiii", $user_id, $currency, $language, $theme, $notification_enabled, $email_notification_enabled, $budget_alert_threshold);
            
            if (!$stmt->execute()) {
                error_log("Error executing insert statement: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $this->closeDB($conn);
            $this->user_id = $user_id;
            return true;
        } catch (Exception $e) {
            error_log("Exception in createDefault: " . $e->getMessage());
            $this->closeDB($conn);
            return false;
        }
    }
    
    /**
     * Update user settings
     * 
     * @return bool
     */
    public function update() {
        if (!$this->user_id) {
            return false;
        }
        
        try {
            $conn = $this->connectDB();
            
            // Check if settings exist
            $stmt = $conn->prepare("SELECT setting_id FROM user_settings WHERE user_id = ?");
            if (!$stmt) {
                error_log("Error preparing statement: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            $stmt->bind_param("i", $this->user_id);
            if (!$stmt->execute()) {
                error_log("Error executing statement: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Settings don't exist, create them
                $this->closeDB($conn);
                return $this->createDefault($this->user_id);
            }
            
            // Update existing settings
            $stmt = $conn->prepare("UPDATE user_settings SET currency = ?, language = ?, theme = ?, notification_enabled = ?, email_notification_enabled = ?, budget_alert_threshold = ? WHERE user_id = ?");
            if (!$stmt) {
                error_log("Error preparing update statement: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            $notification_enabled = $this->notification_enabled ? 1 : 0;
            $email_notification_enabled = $this->email_notification_enabled ? 1 : 0;
            
            $stmt->bind_param("sssiiii", $this->currency, $this->language, $this->theme, $notification_enabled, $email_notification_enabled, $this->budget_alert_threshold, $this->user_id);
            
            if (!$stmt->execute()) {
                error_log("Error executing update statement: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $this->closeDB($conn);
            return true;
        } catch (Exception $e) {
            error_log("Exception in update method: " . $e->getMessage());
            if (isset($conn)) {
                $this->closeDB($conn);
            }
            return false;
        }
    }
    
    /**
     * Reset settings to default values
     * 
     * @return bool
     */
    public function resetToDefault() {
        if (!$this->user_id) {
            return false;
        }
        
        // Set default values
        $this->currency = 'USD';
        $this->language = 'en';
        $this->theme = 'system';
        $this->notification_enabled = true;
        $this->email_notification_enabled = true;
        $this->budget_alert_threshold = 80;
        
        // Update settings
        return $this->update();
    }
    
    /**
     * Get available currencies
     * 
     * @return array
     */
    public function getAvailableCurrencies() {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'JPY' => 'Japanese Yen (¥)',
            'THB' => 'Thai Baht (฿)',
            'CNY' => 'Chinese Yuan (¥)',
            'AUD' => 'Australian Dollar (A$)',
            'CAD' => 'Canadian Dollar (C$)',
            'INR' => 'Indian Rupee (₹)',
            'BRL' => 'Brazilian Real (R$)',
            'MXN' => 'Mexican Peso (Mex$)'
        ];
    }
    
    /**
     * Get available languages
     * 
     * @return array
     */
    public function getAvailableLanguages() {
        return [
            'en' => 'English',
            'th' => 'ไทย'
        ];
    }
    
    /**
     * Get currency symbol based on currency code
     * 
     * @return string
     */
    public function getCurrencySymbol() {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'THB' => '฿',
            'CNY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'INR' => '₹',
            'BRL' => 'R$',
            'MXN' => 'Mex$'
        ];
        
        return $symbols[$this->currency] ?? '$';
    }
    
    /**
     * Format money amount with user's currency symbol
     * 
     * @param float $amount Amount to format
     * @param bool $includeSymbol Whether to include currency symbol
     * @return string Formatted amount
     */
    public function formatMoney($amount, $includeSymbol = true) {
        $symbol = $includeSymbol ? $this->getCurrencySymbol() : '';
        
        // Handle different currency formatting conventions
        switch ($this->currency) {
            case 'JPY':
            case 'CNY':
                // No decimal places for Yen and Yuan
                return $symbol . number_format($amount, 0);
                
            case 'EUR':
                // European format: € 1.234,56
                return $symbol . ' ' . number_format($amount, 2, ',', '.');
                
            case 'THB':
                // Thai Baht format
                return $symbol . number_format($amount, 2);
                
            default:
                // Default format: $1,234.56
                return $symbol . number_format($amount, 2);
        }
    }
}