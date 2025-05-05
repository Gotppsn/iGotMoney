<?php
/**
 * Main entry point for the iGotMoney application
 */

// Start session
session_start();

// Include configuration files
require_once 'config/database.php';

// Define base URL
define('BASE_URL', '/');

// Define routes
$routes = [
    '/' => 'home',
    '/login' => 'login',
    '/register' => 'register',
    '/dashboard' => 'dashboard',
    '/income' => 'income',
    '/expenses' => 'expenses',
    '/budget' => 'budget',
    '/investments' => 'investments',
    '/goals' => 'goals',
    '/stocks' => 'stocks',
    '/taxes' => 'taxes',
    '/reports' => 'reports',
    '/profile' => 'profile',
    '/settings' => 'settings',
    '/logout' => 'logout'
];

// Get current URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash if it exists (except for root)
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// Route to appropriate controller
if (array_key_exists($uri, $routes)) {
    $controller = $routes[$uri];
    require_once "controllers/{$controller}.php";
} else {
    // Handle 404
    header("HTTP/1.0 404 Not Found");
    require_once "views/404.php";
    exit();
}
?>