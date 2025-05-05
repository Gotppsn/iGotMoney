<?php
/**
 * Main Application Entry Point
 * 
 * This file handles all requests and routes them to the appropriate controllers
 */

// Start session
session_start();

// Load configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Define routes
$request_uri = $_SERVER['REQUEST_URI'];

// Remove base path from request URI
$base_path = BASE_PATH;
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Handle query parameters
$request_uri = parse_url($request_uri, PHP_URL_PATH);

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
        
    default:
        // Handle 404 error
        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Page Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex flex-column min-vh-100">
            <div class="container text-center py-5">
                <h1 class="display-1">404</h1>
                <h2>Page Not Found</h2>
                <p class="lead">The page you are looking for does not exist.</p>
                <a href="' . BASE_PATH . '" class="btn btn-primary">Return to Home</a>
            </div>
        </body>
        </html>';
        break;
}