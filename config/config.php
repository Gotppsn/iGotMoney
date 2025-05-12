<?php
/**
 * Global Configuration File
 * 
 * Contains global configuration settings for the iGotMoney application
 */

// Base path configuration for sub-directory installation
// Change this to match your actual installation directory
define('BASE_PATH', '/igotmoney');

// Application configuration
define('APP_NAME', 'iGotMoney');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://gotppsn.com' . BASE_PATH);

// Session settings
define('SESSION_LIFETIME', 86400); // 24 hours

// Timezone setting
date_default_timezone_set('Asia/Bangkok');

// Debugging options
define('DEBUG_MODE', true); // Set to false in production

if (DEBUG_MODE) {
    // Enable error reporting in debug mode
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Disable error reporting in production
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Note: Database credentials are now only in database.php
// to avoid duplication and potential inconsistencies