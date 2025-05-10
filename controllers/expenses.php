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
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'get_expense':
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
            
        case 'get_expenses_by_date':
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
                
                // Calculate total for the period
                $total = 0;
                foreach ($expenses_data as $exp) {
                    $total += floatval($exp['amount']);
                }
                
                echo json_encode([
                    'success' => true,
                    'expenses' => $expenses_data,
                    'total' => $total
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'expenses' => [],
                    'total' => 0
                ]);
            }
            exit();
            
        case 'get_expense_analytics':
            $period = isset($_GET['period']) ? $_GET['period'] : 'current-month';
            
            // Calculate date range based on period
            $start_date = null;
            $end_date = null;
            
            $now = new DateTime();
            
            switch ($period) {
                case 'current-month':
                    $start_date = $now->format('Y-m-01');
                    $end_date = $now->format('Y-m-t');
                    break;
                case 'last-month':
                    $now->modify('first day of last month');
                    $start_date = $now->format('Y-m-01');
                    $now->modify('last day of this month');
                    $end_date = $now->format('Y-m-d');
                    break;
                case 'last-3-months':
                    $now->modify('-3 months');
                    $start_date = $now->format('Y-m-d');
                    $end_date = date('Y-m-d');
                    break;
                case 'current-year':
                    $start_date = $now->format('Y-01-01');
                    $end_date = $now->format('Y-12-31');
                    break;
                case 'custom':
                    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $now->format('Y-m-01');
                    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : $now->format('Y-m-d');
                    break;
            }
            
            // Get expenses for the period
            $result = $expense->getByDateRange($user_id, $start_date, $end_date);
            
            if ($result && $result->num_rows > 0) {
                $expenses_data = [];
                $total_amount = 0;
                $highest_amount = 0;
                $highest_category = '';
                $category_totals = [];
                
                while ($row = $result->fetch_assoc()) {
                    $expenses_data[] = $row;
                    
                    // Calculate total
                    $amount = floatval($row['amount']);
                    $total_amount += $amount;
                    
                    // Track highest expense
                    if ($amount > $highest_amount) {
                        $highest_amount = $amount;
                        $highest_category = $row['category_name'];
                    }
                    
                    // Track category totals
                    if (!isset($category_totals[$row['category_name']])) {
                        $category_totals[$row['category_name']] = 0;
                    }
                    $category_totals[$row['category_name']] += $amount;
                }
                
                // Sort categories by total
                arsort($category_totals);
                
                // Calculate days in period
                $date1 = new DateTime($start_date);
                $date2 = new DateTime($end_date);
                $interval = $date1->diff($date2);
                $days_in_period = $interval->days + 1;
                
                // Calculate daily average
                $daily_average = $days_in_period > 0 ? $total_amount / $days_in_period : 0;
                
                // Calculate projected monthly
                $projected_monthly = $daily_average * 30;
                
                echo json_encode([
                    'success' => true,
                    'analytics' => [
                        'total_amount' => $total_amount,
                        'highest_amount' => $highest_amount,
                        'highest_category' => $highest_category,
                        'daily_average' => $daily_average,
                        'projected_monthly' => $projected_monthly,
                        'category_totals' => $category_totals,
                        'days_in_period' => $days_in_period,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'expense_count' => count($expenses_data)
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'analytics' => [
                        'total_amount' => 0,
                        'highest_amount' => 0,
                        'highest_category' => 'N/A',
                        'daily_average' => 0,
                        'projected_monthly' => 0,
                        'category_totals' => [],
                        'days_in_period' => 0,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'expense_count' => 0
                    ]
                ]);
            }
            exit();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an AJAX request
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($is_ajax) {
        header('Content-Type: application/json');
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            $errors = [];
            
            if (empty($_POST['description'])) {
                $errors[] = 'Description is required.';
            }
            
            if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
                $errors[] = 'Please enter a valid amount greater than zero.';
            }
            
            if (empty($_POST['category_id'])) {
                $errors[] = 'Please select a category.';
            }
            
            if (empty($_POST['expense_date'])) {
                $errors[] = 'Please select a date.';
            }
            
            if (!empty($errors)) {
                $error = implode(' ', $errors);
                
                if ($is_ajax) {
                    echo json_encode([
                        'success' => false,
                        'message' => $error
                    ]);
                    exit();
                }
            } else {
                $expense->user_id = $user_id;
                $expense->category_id = $_POST['category_id'];
                $expense->amount = floatval($_POST['amount']); 
                $expense->description = $_POST['description'];
                $expense->expense_date = $_POST['expense_date'];
                $expense->frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'one-time';
                $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
                
                if ($expense->create()) {
                    $success = 'Expense added successfully!';
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => true,
                            'message' => $success
                        ]);
                        exit();
                    }
                } else {
                    $error = 'Failed to add expense. Please try again.';
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => false,
                            'message' => $error
                        ]);
                        exit();
                    }
                }
            }
            break;
            
        case 'edit':
            $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
            
            $errors = [];
            
            if (empty($_POST['description'])) {
                $errors[] = 'Description is required.';
            }
            
            if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
                $errors[] = 'Please enter a valid amount greater than zero.';
            }
            
            if (empty($_POST['category_id'])) {
                $errors[] = 'Please select a category.';
            }
            
            if (empty($_POST['expense_date'])) {
                $errors[] = 'Please select a date.';
            }
            
            if (!$expense_id) {
                $errors[] = 'Invalid expense.';
            }
            
            if (!empty($errors)) {
                $error = implode(' ', $errors);
                
                if ($is_ajax) {
                    echo json_encode([
                        'success' => false,
                        'message' => $error
                    ]);
                    exit();
                }
            } else {
                if ($expense->getById($expense_id, $user_id)) {
                    $expense->category_id = $_POST['category_id'];
                    $expense->amount = floatval($_POST['amount']);
                    $expense->description = $_POST['description'];
                    $expense->expense_date = $_POST['expense_date'];
                    $expense->frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'one-time';
                    $expense->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
                    
                    if ($expense->update()) {
                        $success = 'Expense updated successfully!';
                        
                        if ($is_ajax) {
                            echo json_encode([
                                'success' => true,
                                'message' => $success
                            ]);
                            exit();
                        }
                    } else {
                        $error = 'Failed to update expense. Please try again.';
                        
                        if ($is_ajax) {
                            echo json_encode([
                                'success' => false,
                                'message' => $error
                            ]);
                            exit();
                        }
                    }
                } else {
                    $error = 'Expense not found.';
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => false,
                            'message' => $error
                        ]);
                        exit();
                    }
                }
            }
            break;
            
        case 'delete':
            $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
            
            if (!$expense_id) {
                $error = 'Invalid expense.';
                
                if ($is_ajax) {
                    echo json_encode([
                        'success' => false,
                        'message' => $error
                    ]);
                    exit();
                }
            } else {
                if ($expense->delete($expense_id, $user_id)) {
                    $success = 'Expense deleted successfully!';
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => true,
                            'message' => $success
                        ]);
                        exit();
                    }
                } else {
                    $error = 'Failed to delete expense. Please try again.';
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => false,
                            'message' => $error
                        ]);
                        exit();
                    }
                }
            }
            break;
    }
    
    // Non-AJAX redirect
    if (!$is_ajax) {
        header('Location: ' . BASE_PATH . '/expenses');
        exit();
    }
}

// Get filter parameters
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get all expense categories
$categories = $expense->getAllCategories();

// Get expenses for the current period
$expenses = $expense->getByDateRange($user_id, $filter_start_date, $filter_end_date);

// Calculate total monthly and yearly expenses
$monthly_expenses = $expense->getMonthlyTotal($user_id);
$yearly_expenses = $expense->getYearlyTotal($user_id);

// Get top expense categories
$top_expenses = $expense->getTopCategories($user_id, 5);

// Include view
require_once 'views/expenses.php';
?>