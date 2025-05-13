<?php
/**
 * Formatting Helper Functions
 * 
 * Contains utility functions for formatting various types of data
 */

class Formatter {
    /**
     * Format money amount based on user's currency settings
     * 
     * @param float $amount Amount to format
     * @param bool $includeSymbol Whether to include currency symbol
     * @return string Formatted amount
     */
    public static function formatMoney($amount, $includeSymbol = true) {
        require_once 'models/UserSettings.php';
        
        // Initialize user settings
        $settings = new UserSettings();
        
        // Get user settings if user is logged in
        if (isset($_SESSION['user_id'])) {
            $settings->getSettings($_SESSION['user_id']);
        }
        
        return $settings->formatMoney($amount, $includeSymbol);
    }
    
    /**
     * Get currency symbol from user settings
     * 
     * @return string Currency symbol
     */
    public static function getCurrencySymbol() {
        require_once 'models/UserSettings.php';
        
        // Initialize user settings
        $settings = new UserSettings();
        
        // Get user settings if user is logged in
        if (isset($_SESSION['user_id'])) {
            $settings->getSettings($_SESSION['user_id']);
        }
        
        return $settings->getCurrencySymbol();
    }
    
    /**
     * Format number with proper thousands separators and decimal places
     * 
     * @param float $number Number to format
     * @param int $decimals Number of decimal places
     * @return string Formatted number
     */
    public static function formatNumber($number, $decimals = 2) {
        return number_format($number, $decimals);
    }
    
    /**
     * Format percentage value
     * 
     * @param float $value Value to format as percentage
     * @param int $decimals Number of decimal places
     * @return string Formatted percentage
     */
    public static function formatPercentage($value, $decimals = 1) {
        return number_format($value, $decimals) . '%';
    }
    
    /**
     * Format date according to user's locale
     * 
     * @param string $date Date string
     * @param string $format Format string
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'M j, Y') {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
    
    /**
     * Get JavaScript code for formatting money values
     * 
     * @param string $variable JavaScript variable name to format
     * @return string JavaScript formatting code
     */
    public static function getJsMoneyFormatter($variable) {
        require_once 'models/UserSettings.php';
        
        // Initialize user settings
        $settings = new UserSettings();
        
        // Get user settings if user is logged in
        if (isset($_SESSION['user_id'])) {
            $settings->getSettings($_SESSION['user_id']);
        }
        
        $symbol = $settings->getCurrencySymbol();
        
        switch ($settings->currency) {
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