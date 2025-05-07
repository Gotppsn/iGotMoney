<?php
/**
 * Stocks Controller
 * 
 * Handles stock watchlist and analysis functionality
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
                
                // Prepare stock price data for chart
                $stockPriceData = [
                    'dates' => [],
                    'prices' => []
                ];
                
                foreach (array_slice($price_history, -30) as $day) {
                    $stockPriceData['dates'][] = date('M j', strtotime($day['date']));
                    $stockPriceData['prices'][] = $day['close'];
                }
            } else {
                $error = $analysis['message'];
            }
        } else {
            $error = 'Please enter a valid stock ticker.';
        }
    } elseif ($action === 'update_watchlist_item') {
        // Get watchlist ID
        $watchlist_id = $_POST['watchlist_id'] ?? 0;
        
        // Prepare data for update
        $data = [
            'ticker_symbol' => $_POST['ticker_symbol'] ?? '',
            'company_name' => $_POST['company_name'] ?? '',
            'target_buy_price' => $_POST['target_buy_price'] ?? null,
            'target_sell_price' => $_POST['target_sell_price'] ?? null,
            'current_price' => $_POST['current_price'] ?? 0,
            'notes' => $_POST['notes'] ?? ''
        ];
        
        // Update watchlist item
        if ($investment->updateWatchlistItem($watchlist_id, $user_id, $data)) {
            $success = 'Watchlist item updated successfully!';
        } else {
            $error = 'Failed to update watchlist item.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'get_stock_data') {
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
    } elseif ($_GET['action'] === 'get_stock_price') {
        header('Content-Type: application/json');
        
        $ticker = $_GET['ticker'] ?? '';
        
        if (!empty($ticker)) {
            // Mock current price data
            $price_history = getMockStockData($ticker);
            $current_price = end($price_history)['close'];
            
            echo json_encode([
                'status' => 'success',
                'ticker' => $ticker,
                'price' => $current_price
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid ticker symbol.'
            ]);
        }
        
        exit();
    }
}

// Get watchlist
$watchlist = $investment->getWatchlist($user_id);

// Function to generate mock stock data for demonstration
function getMockStockData($ticker) {
    $data = [];
    $base_price = 100.0;
    
    // Use ticker symbol to generate a semi-deterministic base price
    // This ensures the same ticker always starts from roughly the same price
    $ticker_sum = 0;
    for ($i = 0; $i < strlen($ticker); $i++) {
        $ticker_sum += ord($ticker[$i]);
    }
    $base_price = 50 + ($ticker_sum % 200); // Price between $50 and $250
    
    // Generate random stock data for past 60 days
    for ($i = 60; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        // Create a semi-deterministic daily change based on date and ticker
        $date_sum = strtotime($date) % 100;
        $seed = ($ticker_sum + $date_sum) % 1000;
        mt_srand($seed);
        
        $change = (mt_rand(-300, 300) / 100);
        $base_price += $change;
        $base_price = max(10, $base_price); // ensure price doesn't go below 10
        
        $open = $base_price - (mt_rand(-100, 100) / 100);
        $high = max($base_price, $open) + (mt_rand(50, 200) / 100);
        $low = min($base_price, $open) - (mt_rand(50, 200) / 100);
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