<?php
/**
 * Expenses Controller
 * 
 * Handles expense management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/Expense.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize Expense object
$expense = new Expense();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Set expense properties
        $expense->user_id = $user_id;
        $expense->category_id = $_POST['category_id'] ?? 0;
        $expense->amount = $_POST['amount'] ?? 0;
        $expense->description = $_POST['description'] ?? '';
        $expense->expense_date = $_POST['expense_date'] ?? date('Y-m-d');
        $expense->frequency = $_POST['frequency'] ?? 'one-time';
        $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        
        // Create new expense
        if ($expense->create()) {
            $success = 'Expense added successfully!';
        } else {
            $error = 'Failed to add expense.';
        }
    } elseif ($action === 'edit') {
        // Get expense ID
        $expense_id = $_POST['expense_id'] ?? 0;
        
        // Get expense data
        if ($expense->getById($expense_id, $user_id)) {
            // Update expense properties
            $expense->category_id = $_POST['category_id'] ?? $expense->category_id;
            $expense->amount = $_POST['amount'] ?? $expense->amount;
            $expense->description = $_POST['description'] ?? $expense->description;
            $expense->expense_date = $_POST['expense_date'] ?? $expense->expense_date;
            $expense->frequency = $_POST['frequency'] ?? $expense->frequency;
            $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
            
            // Update expense
            if ($expense->update()) {
                $success = 'Expense updated successfully!';
            } else {
                $error = 'Failed to update expense.';
            }
        } else {
            $error = 'Expense not found.';
        }
    } elseif ($action === 'delete') {
        // Get expense ID
        $expense_id = $_POST['expense_id'] ?? 0;
        
        // Delete expense
        if ($expense->delete($expense_id, $user_id)) {
            $success = 'Expense deleted successfully!';
        } else {
            $error = 'Failed to delete expense.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_expense') {
    header('Content-Type: application/json');
    
    $expense_id = $_GET['expense_id'] ?? 0;
    
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
}

// Get all expense categories
$categories = $expense->getAllCategories();

// Get all expenses
$expenses = $expense->getAll($user_id);

// Calculate total monthly and yearly expenses
$monthly_expenses = $expense->getMonthlyTotal($user_id);
$yearly_expenses = $expense->getYearlyTotal($user_id);

// Get top expense categories
$top_expenses = $expense->getTopCategories($user_id, 5);

// Include view
require_once 'views/expenses.php';
?>