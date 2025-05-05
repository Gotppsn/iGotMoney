<?php
/**
 * Home Controller
 * 
 * Landing page for non-authenticated users
 */

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirect to dashboard if already logged in
    header('Location: /dashboard');
    exit();
}

// Include view
require_once 'views/home.php';
?>