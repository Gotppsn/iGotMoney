<?php
/**
 * Settings Controller
 * 
 * Handles user settings functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/UserSettings.php';
require_once 'models/User.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize objects
$settings = new UserSettings();
$user = new User();

// Get user data
$user->getUserById($user_id);

// Get user settings
$has_settings = $settings->getSettings($user_id);

// If no settings found, create default
if (!$has_settings) {
    $settings->createDefault($user_id);
    $settings->getSettings($user_id);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_settings') {
        // Set settings properties
        $settings->currency = $_POST['currency'] ?? 'USD';
        $settings->user_id = $user_id;
        
        // Update settings
        if ($settings->update()) {
            $success = 'Settings updated successfully!';
        } else {
            $error = 'Failed to update settings.';
        }
    } elseif ($action === 'reset_settings') {
        // Reset settings to default
        $settings->user_id = $user_id;
        if ($settings->resetToDefault()) {
            $success = 'Settings reset to default values!';
        } else {
            $error = 'Failed to reset settings.';
        }
    } elseif ($action === 'update_profile') {
        // Set user properties
        $user->email = $_POST['email'] ?? $user->email;
        $user->first_name = $_POST['first_name'] ?? $user->first_name;
        $user->last_name = $_POST['last_name'] ?? $user->last_name;
        
        // Update user
        if ($user->update()) {
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile.';
        }
    } elseif ($action === 'change_password') {
        // Get passwords
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($current_password)) {
            $error = 'Current password is required.';
        } elseif (empty($new_password)) {
            $error = 'New password is required.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New password and confirmation do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            // Change password
            if ($user->changePassword($current_password, $new_password)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please make sure your current password is correct.';
            }
        }
    }
}

// Get available currencies for dropdown
$available_currencies = $settings->getAvailableCurrencies();

// Include view
require_once 'views/settings.php';
?>