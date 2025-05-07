<?php
/**
 * Stocks Controller
 * 
 * Handles stock watchlist and analysis functionality with real-time data
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

// API key for Alpha Vantage stock API
define('ALPHA_VANTAGE_API_KEY', 'ZXRFP7GTNO6GCAXG');
define('USE_DEMO_DATA_ON_API_FAILURE', true); // Set to false in production

// Set default page layout variables
$page_title = 'Stock Analysis - iGotMoney';
$current_page = 'stocks';
$additional_js = ['/assets/js/stocks-modern.js'];
$additional_css = ['/assets/css/stocks-modern.css'];

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
            // Get real stock data from API
            $stock_data = getStockData($ticker);
            
            if ($stock_data && isset($stock_data['status']) && $stock_data['status'] === 'success') {
                $stock_analysis = $stock_data;
                
                // Prepare stock price data for chart
                $stockPriceData = [
                    'dates' => $stock_data['historical_dates'] ?? [],
                    'prices' => $stock_data['historical_prices'] ?? [],
                    'volumes' => $stock_data['historical_volumes'] ?? []
                ];
            } else {
                $error = $stock_data['message'] ?? 'Failed to retrieve stock data. Please try again.';
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
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'get_stock_data') {
        $ticker = $_GET['ticker'] ?? '';
        
        if (!empty($ticker)) {
            $stock_data = getStockData($ticker);
            echo json_encode($stock_data);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid ticker symbol.'
            ]);
        }
        
        exit();
    } elseif ($_GET['action'] === 'get_stock_price') {
        $ticker = $_GET['ticker'] ?? '';
        
        if (!empty($ticker)) {
            $current_price = getCurrentPrice($ticker);
            
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

// Update watchlist prices with real-time data if there are items
if ($watchlist && $watchlist->num_rows > 0) {
    $symbols = [];
    $watchlist_items = [];
    
    // Collect all ticker symbols
    while ($item = $watchlist->fetch_assoc()) {
        $symbols[] = $item['ticker_symbol'];
        $watchlist_items[] = $item;
    }
    
    // Due to API rate limits, only update one stock per page load
    // In production, use a background job or caching strategy
    if (!empty($symbols)) {
        $random_index = array_rand($symbols);
        $symbol_to_update = $symbols[$random_index];
        $item_to_update = null;
        
        // Find the watchlist item for this symbol
        foreach ($watchlist_items as $item) {
            if ($item['ticker_symbol'] == $symbol_to_update) {
                $item_to_update = $item;
                break;
            }
        }
        
        if ($item_to_update) {
            $current_price = getCurrentPrice($symbol_to_update);
            if ($current_price > 0) {
                $investment->updateWatchlistPrice($item_to_update['watchlist_id'], $user_id, $current_price);
            }
        }
    }
    
    // Refresh watchlist after updates
    $watchlist = $investment->getWatchlist($user_id);
}

/**
 * Get current stock price from Alpha Vantage API
 * Uses caching to avoid API rate limits
 * 
 * @param string $ticker Stock ticker symbol
 * @return float Current stock price
 */
function getCurrentPrice($ticker) {
    try {
        // Check if we have a cached price (cache for 5 minutes)
        $cache_file = sys_get_temp_dir() . '/stock_price_' . $ticker . '.json';
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < 300)) {
            $cached_data = file_get_contents($cache_file);
            $price_data = json_decode($cached_data, true);
            if ($price_data && isset($price_data['price']) && $price_data['price'] > 0) {
                return $price_data['price'];
            }
        }
        
        // If no cache or expired, call the API
        $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=" . urlencode($ticker) . "&apikey=" . ALPHA_VANTAGE_API_KEY;
        
        // Use curl instead of file_get_contents for better error handling
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $http_code != 200) {
            error_log("API Error: " . ($curl_error ? $curl_error : "HTTP Code: " . $http_code));
            return 0;
        }
        
        $data = json_decode($response, true);
        
        // Check for API error messages
        if (isset($data['Error Message'])) {
            error_log("Alpha Vantage API Error: " . $data['Error Message']);
            return 0;
        }
        
        // Check for rate limiting
        if (isset($data['Note']) && strpos($data['Note'], 'API call frequency') !== false) {
            error_log("Alpha Vantage API rate limit reached: " . $data['Note']);
            return 0;
        }
        
        if (isset($data['Global Quote']) && isset($data['Global Quote']['05. price'])) {
            $price = floatval($data['Global Quote']['05. price']);
            
            // Cache the result
            $cache_data = json_encode(['price' => $price, 'timestamp' => time()]);
            file_put_contents($cache_file, $cache_data);
            
            return $price;
        }
        
        return 0;
    } catch (Exception $e) {
        error_log("Error fetching stock price: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get batch stock quotes for multiple symbols
 * 
 * @param array $symbols Array of stock ticker symbols
 * @return array Array of stock quotes with prices
 */
function getBatchStockQuotes($symbols) {
    // Using Alpha Vantage batch stock quotes endpoint (note: limited by API plan)
    // For free tier, we'll call individual quotes with a delay between requests
    
    try {
        $quotes = [];
        $success = true;
        
        foreach ($symbols as $index => $symbol) {
            // Add a delay for API rate limiting (free tier has limits)
            if ($index > 0) {
                usleep(250000); // 250ms delay between requests
            }
            
            $price = getCurrentPrice($symbol);
            
            if ($price > 0) {
                $quotes[$symbol] = [
                    'price' => $price,
                    'change' => 0, // Would need additional API call for change
                    'change_percent' => 0 // Would need additional API call for percent
                ];
            }
        }
        
        return [
            'status' => 'success',
            'quotes' => $quotes
        ];
    } catch (Exception $e) {
        error_log("Error fetching batch stock quotes: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Failed to retrieve stock quotes.'
        ];
    }
}

/**
 * Get comprehensive stock data for analysis
 * 
 * @param string $ticker Stock ticker symbol
 * @return array Stock data including price, indicators, and historical data
 */
function getStockData($ticker) {
    try {
        // Use cURL for better error handling
        $ch = curl_init();
        $quote_url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=" . urlencode($ticker) . "&apikey=" . ALPHA_VANTAGE_API_KEY;
        
        curl_setopt($ch, CURLOPT_URL, $quote_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $quote_response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($quote_response === false || $http_code != 200) {
            error_log("API Error: " . ($curl_error ? $curl_error : "HTTP Code: " . $http_code));
            
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'Failed to connect to stock data API. Please try again later.'
            ];
        }
        
        $quote_data = json_decode($quote_response, true);
        
        // Check for API errors or empty response
        if (isset($quote_data['Error Message']) || (isset($quote_data['Global Quote']) && empty($quote_data['Global Quote']))) {
            error_log("Alpha Vantage API Error: " . (isset($quote_data['Error Message']) ? $quote_data['Error Message'] : "Empty response"));
            
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'Invalid ticker symbol or API limit reached. Please try again later.'
            ];
        }
        
        // Check for rate limiting
        if (isset($quote_data['Note']) && strpos($quote_data['Note'], 'API call frequency') !== false) {
            error_log("Alpha Vantage API rate limit reached: " . $quote_data['Note']);
            
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'API rate limit reached. Please try again in a minute.'
            ];
        }
        
        if (!isset($quote_data['Global Quote']) || !isset($quote_data['Global Quote']['05. price'])) {
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'Stock data not available. Please try another symbol.'
            ];
        }
        
        // Extract current price data
        $current_price = floatval($quote_data['Global Quote']['05. price']);
        $price_change = floatval($quote_data['Global Quote']['09. change']);
        $price_change_percent = floatval($quote_data['Global Quote']['10. change percent']);
        
        // Get company overview
        $overview_url = "https://www.alphavantage.co/query?function=OVERVIEW&symbol=" . urlencode($ticker) . "&apikey=" . ALPHA_VANTAGE_API_KEY;
        $overview_response = file_get_contents($overview_url);
        $overview_data = json_decode($overview_response, true);
        
        $company_name = isset($overview_data['Name']) ? $overview_data['Name'] : $ticker;
        
        // Wait to avoid API rate limiting
        sleep(1);
        
        // Get historical data (daily)
        $history_url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . urlencode($ticker) . "&outputsize=compact&apikey=" . ALPHA_VANTAGE_API_KEY;
        $history_response = file_get_contents($history_url);
        $history_data = json_decode($history_response, true);
        
        $historical_dates = [];
        $historical_prices = [];
        $historical_volumes = [];
        
        if (isset($history_data['Time Series (Daily)'])) {
            $time_series = $history_data['Time Series (Daily)'];
            $count = 0;
            
            // Get the last 30 days of data (or less if not available)
            foreach ($time_series as $date => $daily_data) {
                if ($count >= 30) break;
                
                $historical_dates[] = date('M j', strtotime($date));
                $historical_prices[] = floatval($daily_data['4. close']);
                $historical_volumes[] = intval($daily_data['5. volume']);
                $count++;
            }
            
            // Reverse arrays to show chronological order
            $historical_dates = array_reverse($historical_dates);
            $historical_prices = array_reverse($historical_prices);
            $historical_volumes = array_reverse($historical_volumes);
        } else {
            // If time series data is not available, use demo data
            $historical_data = getMockStockData($ticker);
            foreach ($historical_data as $data) {
                $historical_dates[] = date('M j', strtotime($data['date']));
                $historical_prices[] = $data['close'];
                $historical_volumes[] = $data['volume'];
            }
            $historical_dates = array_slice($historical_dates, -30);
            $historical_prices = array_slice($historical_prices, -30);
            $historical_volumes = array_slice($historical_volumes, -30);
        }
        
        // Calculate simple technical indicators
        $short_ma = 0;
        $long_ma = 0;
        $rsi = 50; // Default RSI value
        
        // Calculate simple moving averages if we have enough data
        if (count($historical_prices) >= 50) {
            // 20-day moving average
            $short_ma = array_sum(array_slice($historical_prices, -20)) / 20;
            
            // 50-day moving average
            $long_ma = array_sum($historical_prices) / count($historical_prices);
        } else if (count($historical_prices) >= 20) {
            // If we have at least 20 days
            $short_ma = array_sum(array_slice($historical_prices, -20)) / 20;
            $long_ma = array_sum($historical_prices) / count($historical_prices);
        } else if (count($historical_prices) > 0) {
            // Use whatever data we have
            $short_ma = array_sum($historical_prices) / count($historical_prices);
            $long_ma = $short_ma;
        }
        
        // Calculate simple RSI
        if (count($historical_prices) > 14) {
            $rsi = calculateRSI($historical_prices);
        }
        
        // Calculate support and resistance levels
        $support = $current_price * 0.9;  // Simple approximation
        $resistance = $current_price * 1.1;  // Simple approximation
        
        // Determine basic buy/sell recommendation
        $recommendation = 'hold';
        $buy_points = [];
        $sell_points = [];
        
        // Simple recommendation logic (this is very basic and for demonstration only)
        if ($current_price < $short_ma && $rsi < 30) {
            $recommendation = 'buy';
            $buy_points[] = [
                'price' => round($current_price * 0.95, 2),
                'reason' => 'Support level'
            ];
            $buy_points[] = [
                'price' => round($current_price * 0.9, 2),
                'reason' => 'Strong support level'
            ];
        } elseif ($current_price > $short_ma && $current_price > $long_ma && $rsi > 70) {
            $recommendation = 'sell';
            $sell_points[] = [
                'price' => round($current_price * 1.05, 2),
                'reason' => 'Resistance level'
            ];
            $sell_points[] = [
                'price' => round($current_price * 1.1, 2),
                'reason' => 'Strong resistance level'
            ];
        } else {
            // For hold recommendation, provide both potential buy and sell points
            $buy_points[] = [
                'price' => round($support, 2),
                'reason' => 'Support level'
            ];
            $sell_points[] = [
                'price' => round($resistance, 2),
                'reason' => 'Resistance level'
            ];
        }
        
        return [
            'status' => 'success',
            'ticker' => strtoupper($ticker),
            'company_name' => $company_name,
            'current_price' => $current_price,
            'price_change' => $price_change,
            'price_change_percent' => $price_change_percent,
            'short_ma' => round($short_ma, 2),
            'long_ma' => round($long_ma, 2),
            'rsi' => round($rsi, 2),
            'support' => round($support, 2),
            'resistance' => round($resistance, 2),
            'recommendation' => $recommendation,
            'buy_points' => $buy_points,
            'sell_points' => $sell_points,
            'historical_dates' => $historical_dates,
            'historical_prices' => $historical_prices,
            'historical_volumes' => $historical_volumes
        ];
    } catch (Exception $e) {
        error_log("Error analyzing stock: " . $e->getMessage());
        
        if (USE_DEMO_DATA_ON_API_FAILURE) {
            return getDemoStockData($ticker);
        }
        
        return [
            'status' => 'error',
            'message' => 'An error occurred while analyzing the stock. Please try again.'
        ];
    }
}

/**
 * Calculate Relative Strength Index (RSI)
 * 
 * @param array $prices Array of historical prices
 * @return float RSI value (0-100)
 */
function calculateRSI($prices) {
    try {
        // Need at least 14 periods to calculate RSI
        if (count($prices) < 15) {
            return 50; // Default value
        }
        
        // Take the last 14 days
        $prices = array_slice($prices, -15);
        
        $gains = 0;
        $losses = 0;
        
        // Calculate price changes
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            
            if ($change >= 0) {
                $gains += $change;
            } else {
                $losses -= $change; // Convert to positive number
            }
        }
        
        // Calculate average gain and loss
        $avg_gain = $gains / 14;
        $avg_loss = $losses / 14;
        
        // Calculate RS and RSI
        if ($avg_loss == 0) {
            return 100; // No losses, RSI is 100
        }
        
        $rs = $avg_gain / $avg_loss;
        $rsi = 100 - (100 / (1 + $rs));
        
        return $rsi;
    } catch (Exception $e) {
        error_log("Error calculating RSI: " . $e->getMessage());
        return 50; // Default value on error
    }
}

/**
 * Generate demo stock data when API fails
 * 
 * @param string $ticker Stock ticker symbol
 * @return array Stock data for analysis
 */
function getDemoStockData($ticker) {
    // Generate a deterministic price based on ticker symbol
    $ticker_sum = 0;
    for ($i = 0; $i < strlen($ticker); $i++) {
        $ticker_sum += ord($ticker[$i]);
    }
    
    $base_price = 50 + ($ticker_sum % 200); // Price between $50 and $250
    $price_change = (($ticker_sum % 10) - 5) / 10 * $base_price; // -5% to +5% change
    $price_change_percent = ($price_change / $base_price) * 100;
    
    // Generate historical data
    $historical_data = getMockStockData($ticker);
    $historical_dates = [];
    $historical_prices = [];
    $historical_volumes = [];
    
    foreach ($historical_data as $data) {
        $historical_dates[] = date('M j', strtotime($data['date']));
        $historical_prices[] = $data['close'];
        $historical_volumes[] = $data['volume'];
    }
    
    // Reverse arrays to show chronological order
    $historical_dates = array_reverse($historical_dates);
    $historical_prices = array_reverse($historical_prices);
    $historical_volumes = array_reverse($historical_volumes);
    
    // Calculate technical indicators
    $short_ma = array_sum(array_slice($historical_prices, -20)) / min(20, count($historical_prices));
    $long_ma = array_sum($historical_prices) / count($historical_prices);
    
    // Simple RSI calculation
    $rsi = 50 + ($ticker_sum % 50); // 0-100 range
    if ($rsi > 100) $rsi = 100;
    
    // Support and resistance
    $support = $base_price * 0.9;
    $resistance = $base_price * 1.1;
    
    // Company name (for demo)
    $company_name = strtoupper($ticker) . ' Inc.';
    
    // Recommendation
    $recommendation = 'hold';
    $buy_points = [];
    $sell_points = [];
    
    if ($base_price < $short_ma && $rsi < 30) {
        $recommendation = 'buy';
        $buy_points[] = [
            'price' => round($base_price * 0.95, 2),
            'reason' => 'Support level'
        ];
        $buy_points[] = [
            'price' => round($base_price * 0.9, 2),
            'reason' => 'Strong support level'
        ];
    } elseif ($base_price > $short_ma && $base_price > $long_ma && $rsi > 70) {
        $recommendation = 'sell';
        $sell_points[] = [
            'price' => round($base_price * 1.05, 2),
            'reason' => 'Resistance level'
        ];
        $sell_points[] = [
            'price' => round($base_price * 1.1, 2),
            'reason' => 'Strong resistance level'
        ];
    } else {
        $buy_points[] = [
            'price' => round($support, 2),
            'reason' => 'Support level'
        ];
        $sell_points[] = [
            'price' => round($resistance, 2),
            'reason' => 'Resistance level'
        ];
    }
    
    return [
        'status' => 'success',
        'ticker' => strtoupper($ticker),
        'company_name' => $company_name,
        'current_price' => round($base_price, 2),
        'price_change' => round($price_change, 2),
        'price_change_percent' => round($price_change_percent, 2),
        'short_ma' => round($short_ma, 2),
        'long_ma' => round($long_ma, 2),
        'rsi' => round($rsi, 2),
        'support' => round($support, 2),
        'resistance' => round($resistance, 2),
        'recommendation' => $recommendation,
        'buy_points' => $buy_points,
        'sell_points' => $sell_points,
        'historical_dates' => $historical_dates,
        'historical_prices' => $historical_prices,
        'historical_volumes' => $historical_volumes,
        'is_demo_data' => true
    ];
}

/**
 * Generate mock stock data for demonstration
 * 
 * @param string $ticker Stock ticker symbol
 * @return array Array of daily stock data
 */
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