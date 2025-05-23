<?php
/**
 * Goals Controller
 * 
 * Handles financial goals functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_PATH . "/login");
    exit();
}

// Include required models
require_once 'models/FinancialGoal.php';
require_once 'models/Income.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize objects
$goal = new FinancialGoal();
$income = new Income();

// Function to respond with JSON for AJAX requests
function respondWithJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Check if it's an AJAX request
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        // Validate inputs
        if (empty($_POST['name'])) {
            $error = __('goal_name_required');
        } elseif (!is_numeric($_POST['target_amount']) || floatval($_POST['target_amount']) <= 0) {
            $error = __('target_amount_positive');
        } elseif (!is_numeric($_POST['current_amount']) || floatval($_POST['current_amount']) < 0) {
            $error = __('current_amount_valid');
        } elseif (empty($_POST['start_date'])) {
            $error = __('please_provide_start_date');
        } elseif (empty($_POST['target_date'])) {
            $error = __('please_provide_target_date');
        } elseif (strtotime($_POST['target_date']) <= strtotime($_POST['start_date'])) {
            $error = __('target_date_after_start_date');
        } else {
            // Set goal properties
            $goal->user_id = $user_id;
            $goal->name = $_POST['name'] ?? '';
            $goal->target_amount = floatval($_POST['target_amount'] ?? 0);
            $goal->current_amount = floatval($_POST['current_amount'] ?? 0);
            $goal->start_date = $_POST['start_date'] ?? date('Y-m-d');
            $goal->target_date = $_POST['target_date'] ?? date('Y-m-d', strtotime('+1 year'));
            $goal->description = $_POST['description'] ?? '';
            $goal->priority = $_POST['priority'] ?? 'medium';
            $goal->status = $_POST['status'] ?? 'in-progress';
            
            // Create new goal
            if ($goal->create()) {
                $success = __('financial_goal_added_success');
                
                // Check if this is an AJAX request
                if (isAjaxRequest()) {
                    respondWithJson([
                        'success' => true,
                        'message' => $success,
                        'goal_id' => $goal->goal_id
                    ]);
                } else {
                    // If not AJAX, redirect with proper BASE_PATH
                    header("Location: " . BASE_PATH . "/goals");
                    exit();
                }
            } else {
                $error = __('failed_to_add_financial_goal');
                
                // Check if this is an AJAX request
                if (isAjaxRequest()) {
                    respondWithJson([
                        'success' => false,
                        'message' => $error
                    ]);
                }
            }
        }
    } elseif ($action === 'edit') {
        // Get goal ID
        $goal_id = isset($_POST['goal_id']) ? intval($_POST['goal_id']) : 0;
        
        // Validate inputs
        if (empty($_POST['name'])) {
            $error = __('goal_name_required');
        } elseif (!is_numeric($_POST['target_amount']) || floatval($_POST['target_amount']) <= 0) {
            $error = __('target_amount_positive');
        } elseif (!is_numeric($_POST['current_amount']) || floatval($_POST['current_amount']) < 0) {
            $error = __('current_amount_valid');
        } elseif (empty($_POST['start_date'])) {
            $error = __('please_provide_start_date');
        } elseif (empty($_POST['target_date'])) {
            $error = __('please_provide_target_date');
        } elseif (strtotime($_POST['target_date']) <= strtotime($_POST['start_date'])) {
            $error = __('target_date_after_start_date');
        } else {
            // Get goal data
            if ($goal->getById($goal_id, $user_id)) {
                // Update goal properties
                $goal->name = $_POST['name'] ?? $goal->name;
                $goal->target_amount = floatval($_POST['target_amount'] ?? $goal->target_amount);
                $goal->current_amount = floatval($_POST['current_amount'] ?? $goal->current_amount);
                $goal->start_date = $_POST['start_date'] ?? $goal->start_date;
                $goal->target_date = $_POST['target_date'] ?? $goal->target_date;
                $goal->description = $_POST['description'] ?? $goal->description;
                $goal->priority = $_POST['priority'] ?? $goal->priority;
                $goal->status = $_POST['status'] ?? $goal->status;
                
                // Update goal
                if ($goal->update()) {
                    $success = __('financial_goal_updated_success');
                    
                    // Check if this is an AJAX request
                    if (isAjaxRequest()) {
                        respondWithJson([
                            'success' => true,
                            'message' => $success
                        ]);
                    } else {
                        // If not AJAX, redirect with proper BASE_PATH
                        header("Location: " . BASE_PATH . "/goals");
                        exit();
                    }
                } else {
                    $error = __('failed_to_update_financial_goal');
                    
                    // Check if this is an AJAX request
                    if (isAjaxRequest()) {
                        respondWithJson([
                            'success' => false,
                            'message' => $error
                        ]);
                    }
                }
            } else {
                $error = __('financial_goal_not_found');
                
                // Check if this is an AJAX request
                if (isAjaxRequest()) {
                    respondWithJson([
                        'success' => false,
                        'message' => $error
                    ]);
                }
            }
        }
    } elseif ($action === 'delete') {
        // Get goal ID
        $goal_id = isset($_POST['goal_id']) ? intval($_POST['goal_id']) : 0;
        
        // Delete goal
        if ($goal->delete($goal_id, $user_id)) {
            $success = __('financial_goal_deleted_success');
            
            // Check if this is an AJAX request
            if (isAjaxRequest()) {
                respondWithJson([
                    'success' => true,
                    'message' => $success
                ]);
            } else {
                // If not AJAX, redirect with proper BASE_PATH
                header("Location: " . BASE_PATH . "/goals");
                exit();
            }
        } else {
            $error = __('failed_to_delete_financial_goal');
            
            // Check if this is an AJAX request
            if (isAjaxRequest()) {
                respondWithJson([
                    'success' => false,
                    'message' => $error
                ]);
            }
        }
    } elseif ($action === 'update_progress') {
        // Get goal ID and amount
        $goal_id = isset($_POST['goal_id']) ? intval($_POST['goal_id']) : 0;
        $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;
        
        // Validate amount
        if (!is_numeric($amount) || floatval($amount) <= 0) {
            $error = __('please_enter_valid_amount');
            
            // Check if this is an AJAX request
            if (isAjaxRequest()) {
                respondWithJson([
                    'success' => false,
                    'message' => $error
                ]);
            }
        } else {
            // Update progress
            $amount = floatval($amount);
            if ($goal->updateProgress($goal_id, $user_id, $amount)) {
                $success = __('goal_progress_updated_success');
                
                // Get updated goal to check if completed
                $goal->getById($goal_id, $user_id);
                
                if ($goal->status === 'completed') {
                    $success = __('goal_completed_congrats');
                }
                
                // Check if this is an AJAX request
                if (isAjaxRequest()) {
                    respondWithJson([
                        'success' => true,
                        'message' => $success,
                        'goal' => [
                            'status' => $goal->status,
                            'current_amount' => $goal->current_amount,
                            'progress_percentage' => $goal->calculateProgressPercentage()
                        ]
                    ]);
                } else {
                    // If not AJAX, redirect with proper BASE_PATH
                    header("Location: " . BASE_PATH . "/goals");
                    exit();
                }
            } else {
                $error = __('failed_to_update_goal_progress');
                
                // Check if this is an AJAX request
                if (isAjaxRequest()) {
                    respondWithJson([
                        'success' => false,
                        'message' => $error
                    ]);
                }
            }
        }
    } elseif ($action === 'recommend_goals') {
        // Get monthly income
        $monthly_income = $income->getMonthlyTotal($user_id);
        
        // Generate recommended goals
        $recommended_goals = $goal->recommendGoals($user_id, $monthly_income);
        
        if (!empty($recommended_goals)) {
            // We're just displaying recommendations, not automatically creating them
            $show_recommendations = true;
            
            // Check if this is an AJAX request
            if (isAjaxRequest()) {
                respondWithJson([
                    'success' => true,
                    'recommended_goals' => $recommended_goals
                ]);
            }
        } else {
            $error = __('failed_to_generate_recommendations');
            
            // Check if this is an AJAX request
            if (isAjaxRequest()) {
                respondWithJson([
                    'success' => false,
                    'message' => $error
                ]);
            }
        }
    }
    
    // Handle AJAX form validation errors
    if (isset($error) && isAjaxRequest()) {
        respondWithJson([
            'success' => false,
            'message' => $error
        ]);
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_goal') {
    header('Content-Type: application/json');
    
    $goal_id = isset($_GET['goal_id']) ? intval($_GET['goal_id']) : 0;
    
    if ($goal->getById($goal_id, $user_id)) {
        $progress_percentage = $goal->calculateProgressPercentage();
        $time_progress_percentage = $goal->calculateTimeProgressPercentage();
        $is_on_track = $goal->isOnTrack();
        $monthly_contribution = $goal->calculateMonthlyContribution();
        
        echo json_encode([
            'success' => true,
            'goal' => [
                'goal_id' => $goal->goal_id,
                'name' => $goal->name,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'start_date' => $goal->start_date,
                'target_date' => $goal->target_date,
                'description' => $goal->description,
                'priority' => $goal->priority,
                'status' => $goal->status,
                'progress_percentage' => $progress_percentage,
                'time_progress_percentage' => $time_progress_percentage,
                'is_on_track' => $is_on_track,
                'monthly_contribution' => $monthly_contribution
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => __('financial_goal_not_found')
        ]);
    }
    
    exit();
}

// Get all goals
$goals_list = $goal->getAll($user_id);

// Get monthly income for recommendations
$monthly_income = $income->getMonthlyTotal($user_id);

// Generate goal recommendations (only if requested or if no goals exist)
$goal_count = $goals_list->num_rows;
if (!isset($recommended_goals) && $goal_count < 1) {
    $recommended_goals = $goal->recommendGoals($user_id, $monthly_income);
    $show_recommendations = true;
}

// Add additional JavaScript for modernized UI
$additional_js[] = '/assets/js/goals-modern.js';

// Include view
require_once 'views/goals.php';