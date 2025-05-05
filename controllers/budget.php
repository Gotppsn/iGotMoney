<?php
/**
 * Budget Controller
 * 
 * Handles budget management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/Budget.php';
require_once 'models/Expense.php';
require_once 'models/Income.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize objects
$budget = new Budget();
$expense = new Expense();
$income = new Income();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Set budget properties
        $budget->user_id = $user_id;
        $budget->category_id = $_POST['category_id'] ?? 0;
        $budget->amount = $_POST['amount'] ?? 0;
        $budget->start_date = $_POST['start_date'] ?? date('Y-m-d');
        $budget->end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 month'));
        
        // Create new budget
        if ($budget->create()) {
            $success = 'Budget added successfully!';
        } else {
            $error = 'Failed to add budget.';
        }
    } elseif ($action === 'edit') {
        // Get budget ID
        $budget_id = $_POST['budget_id'] ?? 0;
        
        // Get budget data
        if ($budget->getById($budget_id, $user_id)) {
            // Update budget properties
            $budget->category_id = $_POST['category_id'] ?? $budget->category_id;
            $budget->amount = $_POST['amount'] ?? $budget->amount;
            $budget->start_date = $_POST['start_date'] ?? $budget->start_date;
            $budget->end_date = $_POST['end_date'] ?? $budget->end_date;
            
            // Update budget
            if ($budget->update()) {
                $success = 'Budget updated successfully!';
            } else {
                $error = 'Failed to update budget.';
            }
        } else {
            $error = 'Budget not found.';
        }
    } elseif ($action === 'delete') {
        // Get budget ID
        $budget_id = $_POST['budget_id'] ?? 0;
        
        // Delete budget
        if ($budget->delete($budget_id, $user_id)) {
            $success = 'Budget deleted successfully!';
        } else {
            $error = 'Failed to delete budget.';
        }
    } elseif ($action === 'generate_plan') {
        // Get monthly income
        $monthly_income = $income->getMonthlyTotal($user_id);
        
        // Generate budget plan
        $budget_plan = $budget->generateBudgetPlan($user_id, $monthly_income);
        
        if (!empty($budget_plan)) {
            // Create budgets from plan
            $success_count = 0;
            
            foreach ($budget_plan as $plan) {
                $budget->user_id = $user_id;
                $budget->category_id = $plan['category_id'];
                $budget->amount = $plan['allocated_amount'];
                $budget->start_date = date('Y-m-d');
                $budget->end_date = date('Y-m-d', strtotime('+1 month'));
                
                if ($budget->create()) {
                    $success_count++;
                }
            }
            
            if ($success_count > 0) {
                $success = "Generated and added $success_count budget items!";
            } else {
                $error = 'Failed to add budget items.';
            }
        } else {
            $error = 'Failed to generate budget plan.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_budget') {
    header('Content-Type: application/json');
    
    $budget_id = $_GET['budget_id'] ?? 0;
    
    if ($budget->getById($budget_id, $user_id)) {
        echo json_encode([
            'success' => true,
            'budget' => [
                'budget_id' => $budget->budget_id,
                'category_id' => $budget->category_id,
                'amount' => $budget->amount,
                'start_date' => $budget->start_date,
                'end_date' => $budget->end_date
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Budget not found.'
        ]);
    }
    
    exit();
}

// Get all expense categories
$categories = $expense->getAllCategories();

// Get all budgets
$budgets = $budget->getAll($user_id);

// Get current budget status
$budget_status = $budget->getCurrentStatus($user_id);

// Get monthly income
$monthly_income = $income->getMonthlyTotal($user_id);

// Generate budget plan for display (not saving)
$budget_plan = $budget->generateBudgetPlan($user_id, $monthly_income);

// Include view
require_once 'views/budget.php';
?>