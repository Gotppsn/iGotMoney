<?php
/**
 * Investments Controller
 * 
 * Handles investment management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ' . BASE_PATH . '/login');
    exit();
}

// Include required models
require_once 'models/Investment.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize Investment object
$investment = new Investment();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Set investment properties
        $investment->user_id = $user_id;
        $investment->type_id = $_POST['type_id'] ?? 0;
        $investment->name = $_POST['name'] ?? '';
        $investment->ticker_symbol = $_POST['ticker_symbol'] ?? '';
        $investment->purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
        $investment->purchase_price = $_POST['purchase_price'] ?? 0;
        $investment->quantity = $_POST['quantity'] ?? 0;
        $investment->current_price = $_POST['current_price'] ?? $_POST['purchase_price'] ?? 0;
        $investment->notes = $_POST['notes'] ?? '';
        
        // Create new investment
        if ($investment->create()) {
            $success = 'Investment added successfully!';
        } else {
            $error = 'Failed to add investment.';
        }
    } elseif ($action === 'edit') {
        // Get investment ID
        $investment_id = $_POST['investment_id'] ?? 0;
        
        // Get investment data
        if ($investment->getById($investment_id, $user_id)) {
            // Update investment properties
            $investment->type_id = $_POST['type_id'] ?? $investment->type_id;
            $investment->name = $_POST['name'] ?? $investment->name;
            $investment->ticker_symbol = $_POST['ticker_symbol'] ?? $investment->ticker_symbol;
            $investment->purchase_date = $_POST['purchase_date'] ?? $investment->purchase_date;
            $investment->purchase_price = $_POST['purchase_price'] ?? $investment->purchase_price;
            $investment->quantity = $_POST['quantity'] ?? $investment->quantity;
            $investment->current_price = $_POST['current_price'] ?? $investment->current_price;
            $investment->notes = $_POST['notes'] ?? $investment->notes;
            
            // Update investment
            if ($investment->update()) {
                $success = 'Investment updated successfully!';
            } else {
                $error = 'Failed to update investment.';
            }
        } else {
            $error = 'Investment not found.';
        }
    } elseif ($action === 'delete') {
        // Get investment ID
        $investment_id = $_POST['investment_id'] ?? 0;
        
        // Delete investment
        if ($investment->delete($investment_id, $user_id)) {
            $success = 'Investment deleted successfully!';
        } else {
            $error = 'Failed to delete investment.';
        }
    } elseif ($action === 'update_price') {
        // Get investment ID
        $investment_id = $_POST['investment_id'] ?? 0;
        $current_price = $_POST['current_price'] ?? 0;
        
        // Update price
        if ($investment->updatePrice($investment_id, $user_id, $current_price)) {
            $success = 'Price updated successfully!';
        } else {
            $error = 'Failed to update price.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_investment') {
    header('Content-Type: application/json');
    
    $investment_id = isset($_GET['investment_id']) ? (int)$_GET['investment_id'] : 0;
    
    try {
        if ($investment_id > 0 && $investment->getById($investment_id, $user_id)) {
            echo json_encode([
                'success' => true,
                'investment' => [
                    'investment_id' => $investment->investment_id,
                    'type_id' => $investment->type_id,
                    'name' => $investment->name,
                    'ticker_symbol' => $investment->ticker_symbol,
                    'purchase_date' => $investment->purchase_date,
                    'purchase_price' => $investment->purchase_price,
                    'quantity' => $investment->quantity,
                    'current_price' => $investment->current_price,
                    'notes' => $investment->notes
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Investment not found or invalid ID.'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error in fetching investment: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving investment data: ' . $e->getMessage()
        ]);
    }
    
    exit();
}

// Get all investment types
$investment_types = $investment->getAllTypes();
if (!$investment_types) {
    $error = 'Failed to load investment types.';
    $investment_types = [];
}

// Get all investments
try {
    $investments = $investment->getAll($user_id);
    
    if (!$investments && $investment->getLastError()) {
        $error = 'Failed to load investments: ' . $investment->getLastError();
    }
} catch (Exception $e) {
    error_log("Error loading investments: " . $e->getMessage());
    $error = 'An error occurred while loading investments.';
    $investments = false;
}

// Get investment summary
try {
    $investment_summary = $investment->getSummary($user_id);
} catch (Exception $e) {
    error_log("Error getting investment summary: " . $e->getMessage());
    $error = 'An error occurred while calculating investment summary.';
    $investment_summary = [
        'total_invested' => 0,
        'current_value' => 0,
        'total_gain_loss' => 0,
        'percent_gain_loss' => 0,
        'by_type' => [],
        'by_risk' => [],
        'top_performers' => [],
        'worst_performers' => []
    ];
}

// Include view
require_once 'views/investments.php';
?>