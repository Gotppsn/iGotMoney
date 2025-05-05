<?php
/**
 * Login Controller
 * 
 * Handles user login functionality
 */

// Include required files
require_once 'models/User.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Instantiate user object
    $user = new User();
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Attempt login
    if ($user->login($username, $password)) {
        // Set session variables
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['logged_in'] = true;
        
        // Redirect to dashboard
        header('Location: /dashboard');
        exit();
    } else {
        // Set error message
        $error = 'Invalid username or password';
    }
}

// Include view
require_once 'views/login.php';
?>