<?php
/**
 * Global Configuration File
 * 
 * Contains global configuration settings for the iGotMoney application
 */

// Base path configuration for sub-directory installation
// Change this to '' if installed in the root directory
define('BASE_PATH', '/igotmoney');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gotppsnc_igotmoney');
define('DB_USER', 'gotppsnc_Panupol');
define('DB_PASSWORD', '039464@Got');

// Application configuration
define('APP_NAME', 'iGotMoney');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://gotppsn.com' . BASE_PATH);

// Session settings
define('SESSION_LIFETIME', 86400); // 24 hours

// Timezone setting
date_default_timezone_set('Asia/Bangkok');