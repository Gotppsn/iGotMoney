<?php
/**
 * Goals Controller
 * 
 * Handles financial goals functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Set goal properties
        $goal->user_id = $user_id;
        $goal->name = $_POST['name'] ?? '';
        $goal->target_amount = $_POST['target_amount'] ?? 0;
        $goal->current_amount = $_POST['current_amount'] ?? 0;
        $goal->start_date = $_POST['start_date'] ?? date('Y-m-d');
        $goal->target_date = $_POST['target_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $goal->description = $_POST['description'] ?? '';
        $goal->priority = $_POST['priority'] ?? 'medium';
        $goal->status = $_POST['status'] ?? 'in-progress';
        
        // Create new goal
        if ($goal->create()) {
            $success = 'Financial goal added successfully!';
        } else {
            $error = 'Failed to add financial goal.';
        }
    } elseif ($action === 'edit') {
        // Get goal ID
        $goal_id = $_POST['goal_id'] ?? 0;
        
        // Get goal data
        if ($goal->getById($goal_id, $user_id)) {
            // Update goal properties
            $goal->name = $_POST['name'] ?? $goal->name;
            $goal->target_amount = $_POST['target_amount'] ?? $goal->target_amount;
            $goal->current_amount = $_POST['current_amount'] ?? $goal->current_amount;
            $goal->start_date = $_POST['start_date'] ?? $goal->start_date;
            $goal->target_date = $_POST['target_date'] ?? $goal->target_date;
            $goal->description = $_POST['description'] ?? $goal->description;
            $goal->priority = $_POST['priority'] ?? $goal->priority;
            $goal->status = $_POST['status'] ?? $goal->status;
            
            // Update goal
            if ($goal->update()) {
                $success = 'Financial goal updated successfully!';
            } else {
                $error = 'Failed to update financial goal.';
            }
        } else {
            $error = 'Financial goal not found.';
        }
    } elseif ($action === 'delete') {
        // Get goal ID
        $goal_id = $_POST['goal_id'] ?? 0;
        
        // Delete goal
        if ($goal->delete($goal_id, $user_id)) {
            $success = 'Financial goal deleted successfully!';
        } else {
            $error = 'Failed to delete financial goal.';
        }
    } elseif ($action === 'update_progress') {
        // Get goal ID and amount
        $goal_id = $_POST['goal_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        
        // Update progress
        if ($goal->updateProgress($goal_id, $user_id, $amount)) {
            $success = 'Goal progress updated successfully!';
            
            // Get updated goal to check if completed
            $goal->getById($goal_id, $user_id);
            
            if ($goal->status === 'completed') {
                $success = 'Congratulations! You\'ve completed your goal!';
            }
        } else {
            $error = 'Failed to update goal progress.';
        }
    } elseif ($action === 'recommend_goals') {
        // Get monthly income
        $monthly_income = $income->getMonthlyTotal($user_id);
        
        // Generate recommended goals
        $recommended_goals = $goal->recommendGoals($user_id, $monthly_income);
        
        if (!empty($recommended_goals)) {
            // We're just displaying recommendations, not automatically creating them
            $show_recommendations = true;
        } else {
            $error = 'Failed to generate goal recommendations.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_goal') {
    header('Content-Type: application/json');
    
    $goal_id = $_GET['goal_id'] ?? 0;
    
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
            'message' => 'Financial goal not found.'
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

// Include view
require_once 'views/goals.php';
?>