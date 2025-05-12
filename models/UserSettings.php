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
        
        $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $settings = $result->fetch_assoc();
            
            $this->setting_id = $settings['setting_id'];
            $this->user_id = $settings['user_id'];
            $this->currency = $settings['currency'];
            // Check if language column exists
            if (isset($settings['language'])) {
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
    }
    
    /**
     * Create default settings for user
     * 
     * @param int $user_id User ID
     * @return bool
     */
    public function createDefault($user_id) {
        $conn = $this->connectDB();
        
        // Check if settings already exist
        $stmt = $conn->prepare("SELECT setting_id FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Settings already exist
            $this->closeDB($conn);
            return false;
        }
        
        // Create default settings
        $stmt = $conn->prepare("INSERT INTO user_settings (user_id, currency, language, theme, notification_enabled, email_notification_enabled, budget_alert_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $currency = $this->currency;
        $language = $this->language;
        $theme = $this->theme;
        $notification_enabled = $this->notification_enabled ? 1 : 0;
        $email_notification_enabled = $this->email_notification_enabled ? 1 : 0;
        $budget_alert_threshold = $this->budget_alert_threshold;
        
        $stmt->bind_param("isssiii", $user_id, $currency, $language, $theme, $notification_enabled, $email_notification_enabled, $budget_alert_threshold);
        
        $result = $stmt->execute();
        
        $this->closeDB($conn);
        
        if ($result) {
            $this->user_id = $user_id;
            return true;
        }
        
        return false;
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
        
        $conn = $this->connectDB();
        
        // Check if settings exist
        $stmt = $conn->prepare("SELECT setting_id FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Settings don't exist, create them
            $this->closeDB($conn);
            return $this->createDefault($this->user_id);
        }
        
        // Update existing settings
        $stmt = $conn->prepare("UPDATE user_settings SET currency = ?, language = ?, theme = ?, notification_enabled = ?, email_notification_enabled = ?, budget_alert_threshold = ? WHERE user_id = ?");
        
        $notification_enabled = $this->notification_enabled ? 1 : 0;
        $email_notification_enabled = $this->email_notification_enabled ? 1 : 0;
        
        $stmt->bind_param("sssiiii", $this->currency, $this->language, $this->theme, $notification_enabled, $email_notification_enabled, $this->budget_alert_threshold, $this->user_id);
        
        $result = $stmt->execute();
        
        $this->closeDB($conn);
        
        return $result;
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
            'AUD' => 'Australian Dollar ($)'
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
}
?>