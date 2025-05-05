<?php
/**
 * Logout Controller
 * 
 * Handles user logout functionality
 */

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . BASE_PATH . '/login');
exit();
?>