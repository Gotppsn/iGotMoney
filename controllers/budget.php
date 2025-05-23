<?php
/**
 * Budget Controller
 * 
 * Handles budget management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ' . BASE_PATH . '/login');
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

// Handle AJAX requests
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    header('Content-Type: application/json');
    
    if ($action === 'get_budget') {
        $budget_id = isset($_GET['budget_id']) ? intval($_GET['budget_id']) : 0;
        
        if (!$budget_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid budget ID.'
            ]);
            exit();
        }
        
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
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    
    // Set default response for AJAX requests
    $response = [
        'success' => false,
        'message' => 'Unknown action'
    ];
    
    if ($action === 'add') {
        // Set budget properties
        $budget->user_id = $user_id;
        $budget->category_id = $_POST['category_id'] ?? 0;
        $budget->amount = $_POST['amount'] ?? 0;
        $budget->start_date = $_POST['start_date'] ?? date('Y-m-d');
        $budget->end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 month'));
        
        // Validate input
        $errors = [];
        
        if (empty($budget->category_id)) {
            $errors[] = 'Please select a category.';
        }
        
        if (empty($budget->amount) || !is_numeric($budget->amount) || $budget->amount <= 0) {
            $errors[] = 'Please enter a valid amount greater than zero.';
        }
        
        if (empty($budget->start_date)) {
            $errors[] = 'Please select a start date.';
        }
        
        if (empty($budget->end_date)) {
            $errors[] = 'Please select an end date.';
        }
        
        if (!empty($budget->start_date) && !empty($budget->end_date) && strtotime($budget->end_date) < strtotime($budget->start_date)) {
            $errors[] = 'End date must be after start date.';
        }
        
        if (!empty($errors)) {
            $error = implode(' ', $errors);
            
            if ($isAjax) {
                $response = [
                    'success' => false,
                    'message' => $error
                ];
            }
        } else {
            // Create new budget
            if ($budget->create()) {
                $success = 'Budget added successfully!';
                
                if ($isAjax) {
                    $response = [
                        'success' => true,
                        'message' => $success
                    ];
                }
            } else {
                $error = 'Failed to add budget.';
                
                if ($isAjax) {
                    $response = [
                        'success' => false,
                        'message' => $error
                    ];
                }
            }
        }
    } elseif ($action === 'edit') {
        // Get budget ID
        $budget_id = isset($_POST['budget_id']) ? intval($_POST['budget_id']) : 0;
        
        // Get budget data
        if ($budget->getById($budget_id, $user_id)) {
            // Update budget properties
            $budget->category_id = $_POST['category_id'] ?? $budget->category_id;
            $budget->amount = $_POST['amount'] ?? $budget->amount;
            $budget->start_date = $_POST['start_date'] ?? $budget->start_date;
            $budget->end_date = $_POST['end_date'] ?? $budget->end_date;
            
            // Validate input
            $errors = [];
            
            if (empty($budget->category_id)) {
                $errors[] = 'Please select a category.';
            }
            
            if (empty($budget->amount) || !is_numeric($budget->amount) || $budget->amount <= 0) {
                $errors[] = 'Please enter a valid amount greater than zero.';
            }
            
            if (empty($budget->start_date)) {
                $errors[] = 'Please select a start date.';
            }
            
            if (empty($budget->end_date)) {
                $errors[] = 'Please select an end date.';
            }
            
            if (!empty($budget->start_date) && !empty($budget->end_date) && strtotime($budget->end_date) < strtotime($budget->start_date)) {
                $errors[] = 'End date must be after start date.';
            }
            
            if (!empty($errors)) {
                $error = implode(' ', $errors);
                
                if ($isAjax) {
                    $response = [
                        'success' => false,
                        'message' => $error
                    ];
                }
            } else {
                // Update budget
                if ($budget->update()) {
                    $success = 'Budget updated successfully!';
                    
                    if ($isAjax) {
                        $response = [
                            'success' => true,
                            'message' => $success
                        ];
                    }
                } else {
                    $error = 'Failed to update budget.';
                    
                    if ($isAjax) {
                        $response = [
                            'success' => false,
                            'message' => $error
                        ];
                    }
                }
            }
        } else {
            $error = 'Budget not found.';
            
            if ($isAjax) {
                $response = [
                    'success' => false,
                    'message' => $error
                ];
            }
        }
    } elseif ($action === 'delete') {
        // Get budget ID
        $budget_id = isset($_POST['budget_id']) ? intval($_POST['budget_id']) : 0;
        
        // Delete budget
        if ($budget->delete($budget_id, $user_id)) {
            $success = 'Budget deleted successfully!';
            
            if ($isAjax) {
                $response = [
                    'success' => true,
                    'message' => $success
                ];
            }
        } else {
            $error = 'Failed to delete budget.';
            
            if ($isAjax) {
                $response = [
                    'success' => false,
                    'message' => $error
                ];
            }
        }
    } elseif ($action === 'generate_plan') {
        // Get monthly income
        $monthly_income = $income->getMonthlyTotal($user_id);
        
        if ($monthly_income <= 0) {
            $error = 'You need to add income sources before generating a budget plan.';
            
            if ($isAjax) {
                $response = [
                    'success' => false,
                    'message' => $error
                ];
            }
        } else {
            // Generate budget plan
            $budget_plan = $budget->generateBudgetPlan($user_id, $monthly_income);
            
            if (!empty($budget_plan)) {
                // Delete existing budgets for the current month if this is a full generation
                if (isset($_POST['replace_existing']) && $_POST['replace_existing'] == '1') {
                    // Get current month start and end dates
                    $current_month_start = date('Y-m-01');
                    $current_month_end = date('Y-m-t');
                    
                    // Delete existing budgets in this date range
                    $budget->deleteByDateRange($user_id, $current_month_start, $current_month_end);
                }
                
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
                    
                    if ($isAjax) {
                        $response = [
                            'success' => true,
                            'message' => $success
                        ];
                    }
                } else {
                    $error = 'Failed to add budget items.';
                    
                    if ($isAjax) {
                        $response = [
                            'success' => false,
                            'message' => $error
                        ];
                    }
                }
            } else {
                $error = 'Failed to generate budget plan.';
                
                if ($isAjax) {
                    $response = [
                        'success' => false,
                        'message' => $error
                    ];
                }
            }
        }
    }
    
    // Output JSON response for AJAX requests
    if ($isAjax) {
        // Ensure proper JSON encoding
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        exit();
    }
    
    // Redirect to budget page for non-AJAX requests
    header('Location: ' . BASE_PATH . '/budget');
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