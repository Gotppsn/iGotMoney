<?php
/**
 * Expenses Controller
 * 
 * Handles expense management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ' . BASE_PATH . '/login');
    exit();
}

// Include required models
require_once 'models/Expense.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize Expense object
$expense = new Expense();

// Handle AJAX requests
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'get_expense') {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        $expense_id = isset($_GET['expense_id']) ? intval($_GET['expense_id']) : 0;
        
        if (!$expense_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid expense ID.'
            ]);
            exit();
        }
        
        if ($expense->getById($expense_id, $user_id)) {
            echo json_encode([
                'success' => true,
                'expense' => [
                    'expense_id' => $expense->expense_id,
                    'category_id' => $expense->category_id,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date,
                    'frequency' => $expense->frequency,
                    'is_recurring' => $expense->is_recurring
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Expense not found.'
            ]);
        }
        
        exit();
    } elseif ($action === 'get_expenses_by_date') {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        
        if (!$start_date || !$end_date) {
            echo json_encode([
                'success' => false,
                'message' => 'Start and end dates are required.'
            ]);
            exit();
        }
        
        $result = $expense->getByDateRange($user_id, $start_date, $end_date);
        
        if ($result && $result->num_rows > 0) {
            $expenses_data = [];
            while ($row = $result->fetch_assoc()) {
                $expenses_data[] = [
                    'expense_id' => $row['expense_id'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'amount' => $row['amount'],
                    'description' => $row['description'],
                    'expense_date' => $row['expense_date'],
                    'frequency' => $row['frequency'],
                    'is_recurring' => $row['is_recurring']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'expenses' => $expenses_data
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'expenses' => []
            ]);
        }
        
        exit();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    try {
        switch ($action) {
            case 'add':
                // Validate input
                $errors = [];
                
                if (empty($_POST['description'])) {
                    $errors[] = 'Description is required.';
                }
                
                if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
                    $errors[] = 'Please enter a valid amount greater than zero.';
                }
                
                if (empty($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
                    $errors[] = 'Please select a category.';
                }
                
                if (empty($_POST['expense_date'])) {
                    $errors[] = 'Please select a date.';
                }
                
                // If there are validation errors
                if (!empty($errors)) {
                    $error = implode(' ', $errors);
                } else {
                    // Set expense properties
                    $expense->user_id = $user_id;
                    $expense->category_id = $_POST['category_id'];
                    $expense->amount = $_POST['amount'];
                    $expense->description = $_POST['description'];
                    $expense->expense_date = $_POST['expense_date'];
                    $expense->frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'one-time';
                    $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
                    
                    // Create new expense
                    if ($expense->create()) {
                        $success = 'Expense added successfully!';
                    } else {
                        $error = 'Failed to add expense. Please try again.';
                    }
                }
                break;
                
            case 'edit':
                // Get expense ID
                $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
                
                // Validate input
                $errors = [];
                
                if (empty($_POST['description'])) {
                    $errors[] = 'Description is required.';
                }
                
                if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
                    $errors[] = 'Please enter a valid amount greater than zero.';
                }
                
                if (empty($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
                    $errors[] = 'Please select a category.';
                }
                
                if (empty($_POST['expense_date'])) {
                    $errors[] = 'Please select a date.';
                }
                
                if (!$expense_id) {
                    $errors[] = 'Invalid expense.';
                }
                
                // If there are validation errors
                if (!empty($errors)) {
                    $error = implode(' ', $errors);
                } else {
                    // Get expense data
                    if ($expense->getById($expense_id, $user_id)) {
                        // Update expense properties
                        $expense->category_id = $_POST['category_id'];
                        $expense->amount = $_POST['amount'];
                        $expense->description = $_POST['description'];
                        $expense->expense_date = $_POST['expense_date'];
                        $expense->frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'one-time';
                        $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
                        
                        // Update expense
                        if ($expense->update()) {
                            $success = 'Expense updated successfully!';
                        } else {
                            $error = 'Failed to update expense. Please try again.';
                        }
                    } else {
                        $error = 'Expense not found.';
                    }
                }
                break;
                
            case 'delete':
                // Get expense ID
                $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
                
                // Validate input
                if (!$expense_id) {
                    $error = 'Invalid expense.';
                } else {
                    // Delete expense
                    if ($expense->delete($expense_id, $user_id)) {
                        $success = 'Expense deleted successfully!';
                    } else {
                        $error = 'Failed to delete expense. Please try again.';
                    }
                }
                break;
                
            default:
                $error = 'Invalid action.';
                break;
        }
    } catch (Exception $e) {
        $error = 'An unexpected error occurred: ' . $e->getMessage();
        error_log('Expense controller error: ' . $e->getMessage());
    }
    
    // Handle AJAX form submissions
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        
        if (isset($error)) {
            echo json_encode([
                'success' => false,
                'message' => $error
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => $success ?? 'Operation completed successfully.'
            ]);
        }
        
        exit();
    }
}

// Get all expense categories
$categories = $expense->getAllCategories();

// Get all expenses - limiting to the most recent 100 for performance
$expenses = $expense->getAll($user_id, 100);

// Calculate total monthly and yearly expenses
$monthly_expenses = $expense->getMonthlyTotal($user_id);
$yearly_expenses = $expense->getYearlyTotal($user_id);

// Get top expense categories
$top_expenses = $expense->getTopCategories($user_id, 5);

// Page-specific scripts
$page_scripts = "
    // Initialize tooltips and other Bootstrap components
    document.addEventListener('DOMContentLoaded', function() {
        // Custom script to ensure Bootstrap is properly loaded
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap is loaded successfully');
            
            // Initialize all tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        } else {
            console.error('Bootstrap is not loaded. Some functionality may not work correctly.');
        }
    });
";

// Include view
require_once 'views/expenses.php';
?>