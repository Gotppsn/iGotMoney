<?php
/**
 * Register Controller
 * 
 * Handles user registration functionality
 */

// Include required files
require_once 'models/User.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Instantiate user object
    $user = new User();
    
    // Get form data
    $user->username = $_POST['username'] ?? '';
    $user->password = $_POST['password'] ?? '';
    $user->email = $_POST['email'] ?? '';
    $user->first_name = $_POST['first_name'] ?? '';
    $user->last_name = $_POST['last_name'] ?? '';
    
    // Validate data
    $errors = [];
    
    if (empty($user->username)) {
        $errors[] = 'Username is required';
    } else if (strlen($user->username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (empty($user->password)) {
        $errors[] = 'Password is required';
    } else if (strlen($user->password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if (empty($user->email)) {
        $errors[] = 'Email is required';
    } else if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is invalid';
    }
    
    // Register user if no errors
    if (empty($errors)) {
        if ($user->register()) {
            // Set success message
            $success = 'Registration successful! You can now login.';
            
            // Redirect to login page after a delay
            header('Refresh: 3; URL=/login');
        } else {
            $errors[] = 'Registration failed. Username or email may already exist.';
        }
    }
}

// Include view
require_once 'views/register.php';
?>