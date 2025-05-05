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
        // Set income properties
        $income->user_id = $user_id;
        $income->name = $_POST['name'] ?? '';
        $income->amount = $_POST['amount'] ?? 0;
        $income->frequency = $_POST['frequency'] ?? 'monthly';
        $income->start_date = $_POST['start_date'] ?? date('Y-m-d');
        $income->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $income->is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Create new income
        if ($income->create()) {
            $success = 'Income source added successfully!';
        } else {
            $error = 'Failed to add income source.';
        }
    } elseif ($action === 'edit') {
        // Get income ID
        $income_id = $_POST['income_id'] ?? 0;
        
        // Get income data
        if ($income->getById($income_id, $user_id)) {
            // Update income properties
            $income->name = $_POST['name'] ?? $income->name;
            $income->amount = $_POST['amount'] ?? $income->amount;
            $income->frequency = $_POST['frequency'] ?? $income->frequency;
            $income->start_date = $_POST['start_date'] ?? $income->start_date;
            $income->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $income->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Update income
            if ($income->update()) {
                $success = 'Income source updated successfully!';
            } else {
                $error = 'Failed to update income source.';
            }
        } else {
            $error = 'Income source not found.';
        }
    } elseif ($action === 'delete') {
        // Get income ID
        $income_id = $_POST['income_id'] ?? 0;
        
        // Delete income
        if ($income->delete($income_id, $user_id)) {
            $success = 'Income source deleted successfully!';
        } else {
            $error = 'Failed to delete income source.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_income') {
    header('Content-Type: application/json');
    
    $income_id = $_GET['income_id'] ?? 0;
    
    if ($income->getById($income_id, $user_id)) {
        echo json_encode([
            'success' => true,
            'income' => [
                'income_id' => $income->income_id,
                'name' => $income->name,
                'amount' => $income->amount,
                'frequency' => $income->frequency,
                'start_date' => $income->start_date,
                'end_date' => $income->end_date,
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