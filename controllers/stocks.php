<?php
/**
 * Stocks Controller
 * 
 * Handles stock watchlist and analysis functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
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
    
    if ($action === 'add_to_watchlist') {
        // Set stock properties
        $investment->user_id = $user_id;
        $investment->ticker_symbol = $_POST['ticker_symbol'] ?? '';
        $investment->name = $_POST['company_name'] ?? '';
        $investment->current_price = $_POST['current_price'] ?? 0;
        $investment->notes = $_POST['notes'] ?? '';
        
        // Add to watchlist
        if ($investment->addToWatchlist()) {
            $success = 'Stock added to watchlist successfully!';
        } else {
            $error = 'Failed to add stock to watchlist.';
        }
    } elseif ($action === 'remove_from_watchlist') {
        // Get watchlist ID
        $watchlist_id = $_POST['watchlist_id'] ?? 0;
        
        // Remove from watchlist
        if ($investment->removeFromWatchlist($watchlist_id, $user_id)) {
            $success = 'Stock removed from watchlist successfully!';
        } else {
            $error = 'Failed to remove stock from watchlist.';
        }
    } elseif ($action === 'analyze_stock') {
        // Get stock ticker
        $ticker = $_POST['ticker_symbol'] ?? '';
        
        if (!empty($ticker)) {
            // This would normally call an external API to get stock data
            // For demonstration, we'll use a simplified mockup
            $price_history = getMockStockData($ticker);
            
            // Calculate buy/sell points
            $analysis = $investment->calculateBuySellPoints($ticker, $price_history);
            
            if ($analysis['status'] === 'success') {
                $stock_analysis = $analysis;
            } else {
                $error = $analysis['message'];
            }
        } else {
            $error = 'Please enter a valid stock ticker.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_stock_data') {
    header('Content-Type: application/json');
    
    $ticker = $_GET['ticker'] ?? '';
    
    if (!empty($ticker)) {
        // Mock stock data
        $price_history = getMockStockData($ticker);
        $analysis = $investment->calculateBuySellPoints($ticker, $price_history);
        
        echo json_encode($analysis);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid ticker symbol.'
        ]);
    }
    
    exit();
}

// Get watchlist
$watchlist = $investment->getWatchlist($user_id);

// Function to generate mock stock data for demonstration
function getMockStockData($ticker) {
    $data = [];
    $base_price = 100.0;
    
    // Generate random stock data for past 60 days
    for ($i = 60; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $change = (mt_rand(-300, 300) / 100);
        $base_price += $change;
        $base_price = max(10, $base_price); // ensure price doesn't go below 10
        
        $open = $base_price - (mt_rand(-100, 100) / 100);
        $high = $base_price + (mt_rand(50, 200) / 100);
        $low = $base_price - (mt_rand(50, 200) / 100);
        $close = $base_price;
        $volume = mt_rand(1000000, 10000000);
        
        $data[] = [
            'date' => $date,
            'open' => round($open, 2),
            'high' => round($high, 2),
            'low' => round($low, 2),
            'close' => round($close, 2),
            'volume' => $volume
        ];
    }
    
    return $data;
}

// Include view
require_once 'views/stocks.php';
?>