<?php
/**
 * Main Application Entry Point
 * 
 * This file handles all requests and routes them to the appropriate controllers
 */

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Load configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/language.php';

// Initialize language system
$language_code = 'en'; // Default language

// Check for URL parameter first
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'th'])) {
    $language_code = $_GET['lang'];
    $_SESSION['temp_lang'] = $language_code;
}
// Check for session temporary language (from URL parameter)
elseif (isset($_SESSION['temp_lang'])) {
    $language_code = $_SESSION['temp_lang'];
}
// Check for logged-in user language preference
elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    require_once 'models/UserSettings.php';
    $userSettings = new UserSettings();
    if ($userSettings->getSettings($_SESSION['user_id'])) {
        $language_code = $userSettings->language;
    }
}

// Initialize language
$language = Language::getInstance($language_code);

// Handle quick language change from dropdown
if (isset($_GET['quick_lang']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    try {
        // Validate the language code
        $valid_languages = ['en', 'th'];
        $requested_lang = $_GET['quick_lang'];
        
        if (!in_array($requested_lang, $valid_languages)) {
            // Invalid language code, set default
            $requested_lang = 'en';
        }
        
        // Update user settings
        require_once 'models/UserSettings.php';
        $userSettings = new UserSettings();
        
        if ($userSettings->getSettings($_SESSION['user_id'])) {
            $userSettings->language = $requested_lang;
            
            if (!$userSettings->update()) {
                error_log("Failed to update user language setting to: " . $requested_lang);
                // Continue anyway, just set session language
            }
        }
        
        // Set session language as well, for redundancy
        $_SESSION['temp_lang'] = $requested_lang;
        
        // Redirect back to settings page or previous page
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
            header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
        } else {
            header('Location: ' . BASE_PATH . '/settings?lang_updated=1');
        }
        exit;
    } catch (Exception $e) {
        error_log("Exception in language switching: " . $e->getMessage());
        // Save language in session at minimum
        $_SESSION['temp_lang'] = $_GET['quick_lang'];
        // Redirect to settings page with error
        header('Location: ' . BASE_PATH . '/settings?error=language_switch_failed');
        exit;
    }
}

// Define routes
$request_uri = $_SERVER['REQUEST_URI'];

// Remove base path from request URI
$base_path = BASE_PATH;
if (!empty($base_path) && strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Handle query parameters
$query_string = '';
if (($pos = strpos($request_uri, '?')) !== false) {
    $query_string = substr($request_uri, $pos);
    $request_uri = substr($request_uri, 0, $pos);
}

// Remove trailing slash if it exists
$request_uri = rtrim($request_uri, '/');

// Set default route if empty
if (empty($request_uri)) {
    $request_uri = '/';
}

// Route to appropriate controller
switch ($request_uri) {
    case '/':
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            require_once 'controllers/dashboard.php';
        } else {
            require_once 'controllers/home.php';
        }
        break;
        
    case '/login':
        require_once 'controllers/login.php';
        break;
        
    case '/register':
        require_once 'controllers/register.php';
        break;
        
    case '/logout':
        require_once 'controllers/logout.php';
        break;
        
    case '/dashboard':
        require_once 'controllers/dashboard.php';
        break;
        
    case '/income':
        require_once 'controllers/income.php';
        break;
        
    case '/expenses':
        require_once 'controllers/expenses.php';
        break;
        
    case '/budget':
        require_once 'controllers/budget.php';
        break;
        
    case '/investments':
        require_once 'controllers/investments.php';
        break;
        
    case '/stocks':
        require_once 'controllers/stocks.php';
        break;
        
    case '/goals':
        require_once 'controllers/goals.php';
        break;
        
    case '/taxes':
        require_once 'controllers/taxes.php';
        break;
        
    case '/reports':
        require_once 'controllers/reports.php';
        break;
        
    case '/settings':
        require_once 'controllers/settings.php';
        break;
        
    case '/profile':
        require_once 'controllers/profile.php';
        break;
        
    case '/debug':
        // Add a debug route to test database connection
        echo "<h1>Database Connection Test</h1>";
        try {
            $conn = connectDB();
            echo "<p style='color:green'>Database connection successful!</p>";
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                echo "<h3>Tables in database:</h3><ul>";
                while ($row = $result->fetch_row()) {
                    echo "<li>" . htmlspecialchars($row[0]) . "</li>";
                }
                echo "</ul>";
            }
            closeDB($conn);
        } catch (Exception $e) {
            echo "<p style='color:red'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        break;
        
    default:
        // Handle 404 error
        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="' . $language->getCurrentLanguage() . '">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - ' . __('page_not_found') . '</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex flex-column min-vh-100">
            <div class="container text-center py-5">
                <h1 class="display-1">404</h1>
                <h2>' . __('page_not_found') . '</h2>
                <p class="lead">' . __('the_page_you_are_looking_for_does_not_exist') . '</p>
                <a href="' . BASE_PATH . '" class="btn btn-primary">' . __('return_to_home') . '</a>
            </div>
        </body>
        </html>';
        break;
}