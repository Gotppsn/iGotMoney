<?php
/**
 * Income Controller
 * 
 * Handles income management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/Income.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize Income object
$income = new Income();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Validate input
        if (empty($_POST['name']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
            $error = 'Please provide a valid name and amount for your income source.';
        } else {
            // Set income properties
            $income->user_id = $user_id;
            $income->name = $_POST['name'] ?? '';
            $income->amount = $_POST['amount'] ?? 0;
            $income->frequency = $_POST['frequency'] ?? 'monthly';
            $income->start_date = $_POST['start_date'] ?? date('Y-m-d');
            
            // Handle end date - check if date is valid before assigning
            if (!empty($_POST['end_date'])) {
                $endDateValue = $_POST['end_date'];
                // Validate the date
                if (strtotime($endDateValue) !== false) {
                    $income->end_date = $endDateValue;
                } else {
                    $income->end_date = null;
                }
            } else {
                $income->end_date = null;
            }
            
            $income->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Debug log to check values
            error_log("Adding income with end_date: " . ($income->end_date ?? 'null'));
            
            // Create new income
            if ($income->create()) {
                $success = 'Income source added successfully!';
            } else {
                $error = 'Failed to add income source.';
            }
        }
    } elseif ($action === 'edit') {
        // Get income ID
        $income_id = $_POST['income_id'] ?? 0;
        
        // Validate input
        if (empty($_POST['name']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
            $error = 'Please provide a valid name and amount for your income source.';
        } elseif (!$income_id) {
            $error = 'Invalid income source.';
        } else {
            // Get income data
            if ($income->getById($income_id, $user_id)) {
                // Debug log to see what's happening
                error_log("Editing income ID: " . $income_id);
                error_log("POST data: " . print_r($_POST, true));
                
                // Update income properties
                $income->name = $_POST['name'] ?? $income->name;
                $income->amount = $_POST['amount'] ?? $income->amount;
                $income->frequency = $_POST['frequency'] ?? $income->frequency;
                $income->start_date = $_POST['start_date'] ?? $income->start_date;
                
                // Handle end date - check if date is valid before assigning
                if (!empty($_POST['end_date'])) {
                    $endDateValue = $_POST['end_date'];
                    // Validate the date
                    if (strtotime($endDateValue) !== false) {
                        $income->end_date = $endDateValue;
                    } else {
                        $income->end_date = null;
                    }
                } else {
                    $income->end_date = null;
                }
                
                $income->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Debug log of properties before update
                error_log("Income object before update: " . print_r([
                    'income_id' => $income->income_id,
                    'user_id' => $income->user_id,
                    'name' => $income->name,
                    'amount' => $income->amount,
                    'frequency' => $income->frequency,
                    'start_date' => $income->start_date,
                    'end_date' => $income->end_date,
                    'is_active' => $income->is_active
                ], true));
                
                // Update income
                if ($income->update()) {
                    $success = 'Income source updated successfully!';
                } else {
                    $error = 'Failed to update income source. Please try again.';
                    // Log the error for debugging
                    error_log("Failed to update income source ID: " . $income_id);
                }
            } else {
                $error = 'Income source not found.';
            }
        }
    } elseif ($action === 'delete') {
        // Get income ID
        $income_id = $_POST['income_id'] ?? 0;
        
        if (!$income_id) {
            $error = 'Invalid income source.';
        } else {
            // Delete income
            if ($income->delete($income_id, $user_id)) {
                $success = 'Income source deleted successfully!';
            } else {
                $error = 'Failed to delete income source.';
            }
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_income') {
    header('Content-Type: application/json');
    
    $income_id = $_GET['income_id'] ?? 0;
    
    if (!$income_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid income source ID.'
        ]);
        exit();
    }
    
    if ($income->getById($income_id, $user_id)) {
        // Ensure end_date is properly formatted or null
        $end_date = null;
        if (!empty($income->end_date) && $income->end_date !== '0000-00-00') {
            $timestamp = strtotime($income->end_date);
            if ($timestamp !== false && $timestamp > 0) {
                $end_date = $income->end_date;
            }
        }
        
        echo json_encode([
            'success' => true,
            'income' => [
                'income_id' => $income->income_id,
                'name' => $income->name,
                'amount' => $income->amount,
                'frequency' => $income->frequency,
                'start_date' => $income->start_date,
                'end_date' => $end_date,
                'is_active' => $income->is_active
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Income source not found.'
        ]);
    }
    
    exit();
}

// Get all income sources
$income_sources = $income->getAll($user_id);

// Calculate total monthly and yearly income
$monthly_income = $income->getMonthlyTotal($user_id);
$yearly_income = $income->getYearlyTotal($user_id);

// Include view
require_once 'views/income.php';
?>