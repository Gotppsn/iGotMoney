<?php
/**
 * Register Controller
 * 
 * Handles user registration functionality
 */

// Include required files
require_once 'models/User.php';

// Initialize variables
$errors = [];
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    // Validate username
    if (empty($username)) {
        $errors[] = __('username_is_required');
    } elseif (strlen($username) < 3) {
        $errors[] = __('username_min_length');
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = __('email_is_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('email_is_invalid');
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = __('password_is_required');
    } elseif (strlen($password) < 6) {
        $errors[] = __('password_min_length');
    }
    
    // Validate password confirmation
    if ($password !== $password_confirm) {
        $errors[] = __('passwords_dont_match');
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Instantiate user object
        $user = new User();
        
        // Set user properties
        $user->username = $username;
        $user->email = $email;
        $user->password = $password;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        
        // Register user
        if ($user->register()) {
            // Registration successful
            $success = __('registration_success');
        } else {
            // Registration failed
            $errors[] = __('registration_failed');
        }
    }
}

// Include view
require_once 'views/register.php';
?>