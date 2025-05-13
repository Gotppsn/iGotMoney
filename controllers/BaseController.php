<?php
/**
 * Base Model to be extended by all models
 * Updated to include formatted money methods
 */

// Define a BaseController class with common functionality
class BaseController {
    /**
     * Format money amount with the user's currency settings
     * 
     * @param float $amount Amount to format
     * @param bool $includeSymbol Whether to include currency symbol
     * @return string Formatted amount
     */
    protected function formatMoney($amount, $includeSymbol = true) {
        // Get user settings if not already loaded
        if (!isset($this->settings) || !$this->settings) {
            require_once 'models/UserSettings.php';
            $this->settings = new UserSettings();
            if (isset($_SESSION['user_id'])) {
                $this->settings->getSettings($_SESSION['user_id']);
            }
        }
        
        // Use the settings model to format money
        return $this->settings->formatMoney($amount, $includeSymbol);
    }
    
    /**
     * Get the currency symbol from user settings
     * 
     * @return string Currency symbol
     */
    protected function getCurrencySymbol() {
        // Get user settings if not already loaded
        if (!isset($this->settings) || !$this->settings) {
            require_once 'models/UserSettings.php';
            $this->settings = new UserSettings();
            if (isset($_SESSION['user_id'])) {
                $this->settings->getSettings($_SESSION['user_id']);
            }
        }
        
        return $this->settings->getCurrencySymbol();
    }
    
    /**
     * Format a value as money in JavaScript (for charts and dynamic content)
     * 
     * @param string $variable JavaScript variable name
     * @return string JavaScript formatting code
     */
    protected function getJsMoneyFormatter($variable) {
        // Get user settings if not already loaded
        if (!isset($this->settings) || !$this->settings) {
            require_once 'models/UserSettings.php';
            $this->settings = new UserSettings();
            if (isset($_SESSION['user_id'])) {
                $this->settings->getSettings($_SESSION['user_id']);
            }
        }
        
        $symbol = $this->settings->getCurrencySymbol();
        
        switch ($this->settings->currency) {
            case 'JPY':
            case 'CNY':
                // No decimal places for Yen and Yuan
                return "'" . $symbol . "' + " . $variable . ".toLocaleString(undefined, {maximumFractionDigits: 0})";
                
            case 'EUR':
                // European format with spaces and commas
                return "'" . $symbol . " ' + " . $variable . ".toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})";
                
            default:
                // Standard format
                return "'" . $symbol . "' + " . $variable . ".toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})";
        }
    }
}