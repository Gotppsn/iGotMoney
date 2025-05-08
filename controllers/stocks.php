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

// Finnhub API key
define('FINNHUB_API_KEY', 'd0edd11r01qj9mg6802gd0edd11r01qj9mg68030');
define('USE_DEMO_DATA_ON_API_FAILURE', true); // Set to false in production
define('CACHE_EXPIRATION', 300); // 5 minutes for price data
define('CACHE_EXPIRATION_ANALYSIS', 3600); // 1 hour for full analysis data
define('MAX_API_CALLS_PER_MINUTE', 60); // Finnhub free tier limit

// Set default page layout variables
$page_title = 'Stock Analysis - iGotMoney';
$current_page = 'stocks';
$additional_js = ['/assets/js/stocks-modern.js'];
$additional_css = ['/assets/css/stocks-modern.css'];

// Initialize API call limiter
initializeApiRateLimiter();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_watchlist') {
        // Set stock properties
        $investment->user_id = $user_id;
        $investment->ticker_symbol = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
        $investment->name = $_POST['company_name'] ?? '';
        $investment->current_price = $_POST['current_price'] ?? 0;
        $investment->notes = $_POST['notes'] ?? '';
        
        // Add optional target prices if provided
        if (!empty($_POST['target_buy_price'])) {
            $investment->target_buy_price = $_POST['target_buy_price'];
        }
        if (!empty($_POST['target_sell_price'])) {
            $investment->target_sell_price = $_POST['target_sell_price'];
        }
        
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
        $ticker = strtoupper(trim($_POST['ticker_symbol'] ?? ''));
        
        if (!empty($ticker)) {
            // Check for valid ticker format
            if (!preg_match('/^[A-Z0-9.]{1,10}$/', $ticker)) {
                $error = 'Please enter a valid stock ticker symbol (e.g., AAPL, MSFT, GOOG).';
            } else {
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
            }
        } else {
            $error = 'Please enter a valid stock ticker.';
        }
    } elseif ($action === 'update_watchlist_item') {
        // Get watchlist ID
        $watchlist_id = $_POST['watchlist_id'] ?? 0;
        
        // Prepare data for update
        $data = [
            'ticker_symbol' => strtoupper(trim($_POST['ticker_symbol'] ?? '')),
            'company_name' => $_POST['company_name'] ?? '',
            'target_buy_price' => !empty($_POST['target_buy_price']) ? $_POST['target_buy_price'] : null,
            'target_sell_price' => !empty($_POST['target_sell_price']) ? $_POST['target_sell_price'] : null,
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
        $ticker = strtoupper(trim($_GET['ticker'] ?? ''));
        
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
        $ticker = strtoupper(trim($_GET['ticker'] ?? ''));
        
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
    } elseif ($_GET['action'] === 'batch_stock_quotes') {
        $symbols = array_filter(array_map('trim', explode(',', $_GET['symbols'] ?? '')));
        
        if (!empty($symbols)) {
            $quotes = getBatchStockQuotes($symbols);
            echo json_encode($quotes);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No valid ticker symbols provided.'
            ]);
        }
        
        exit();
    }
}

// Get watchlist
$watchlist = $investment->getWatchlist($user_id);

// Update watchlist prices selectively to avoid API limits
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
        // Check if we can make an API call
        if (canMakeApiCall()) {
            // Determine which symbol to update based on last update time
            $symbol_to_update = null;
            $oldest_update_time = time();
            
            foreach ($watchlist_items as $item) {
                $cache_file = getCachePath($item['ticker_symbol'], 'price');
                
                // If cache doesn't exist or is older than current oldest, update this one
                if (!file_exists($cache_file) || (time() - filemtime($cache_file) > $oldest_update_time)) {
                    $symbol_to_update = $item['ticker_symbol'];
                    if (file_exists($cache_file)) {
                        $oldest_update_time = time() - filemtime($cache_file);
                    } else {
                        $oldest_update_time = PHP_INT_MAX; // Force update for items with no cache
                    }
                }
            }
            
            if ($symbol_to_update) {
                $current_price = getCurrentPrice($symbol_to_update);
                
                // Find the item for this symbol
                foreach ($watchlist_items as $item) {
                    if ($item['ticker_symbol'] == $symbol_to_update && $current_price > 0) {
                        $investment->updateWatchlistPrice($item['watchlist_id'], $user_id, $current_price);
                        break;
                    }
                }
            }
        }
    }
    
    // Refresh watchlist after updates
    $watchlist = $investment->getWatchlist($user_id);
}

/**
 * Initialize API rate limiter
 * Creates a session variable to track API calls
 */
function initializeApiRateLimiter() {
    if (!isset($_SESSION['api_calls'])) {
        $_SESSION['api_calls'] = [
            'count' => 0,
            'reset_time' => time() + 60 // Reset after 1 minute
        ];
    }
    
    // Reset counter if time has passed
    if (time() > $_SESSION['api_calls']['reset_time']) {
        $_SESSION['api_calls'] = [
            'count' => 0,
            'reset_time' => time() + 60
        ];
    }
}

/**
 * Check if we can make an API call based on rate limits
 * 
 * @return bool True if API call is allowed, false otherwise
 */
function canMakeApiCall() {
    // If we're using demo data, always allow "API calls"
    if (USE_DEMO_DATA_ON_API_FAILURE) {
        return true;
    }
    
    // Check if we're under the limit (60 calls per minute for Finnhub free tier)
    if ($_SESSION['api_calls']['count'] < MAX_API_CALLS_PER_MINUTE) {
        $_SESSION['api_calls']['count']++;
        return true;
    }
    
    return false;
}

/**
 * Get cache file path for a stock symbol
 * 
 * @param string $ticker Stock ticker symbol
 * @param string $type Cache type (price, analysis)
 * @return string Path to cache file
 */
function getCachePath($ticker, $type = 'price') {
    $ticker = strtoupper(trim($ticker));
    $cache_dir = sys_get_temp_dir();
    
    // Try to create a subdirectory for our app's cache if it doesn't exist
    $app_cache_dir = $cache_dir . DIRECTORY_SEPARATOR . 'igotmoney_cache';
    if (!is_dir($app_cache_dir) && is_writable($cache_dir)) {
        mkdir($app_cache_dir);
        if (is_dir($app_cache_dir)) {
            $cache_dir = $app_cache_dir;
        }
    }
    
    return $cache_dir . DIRECTORY_SEPARATOR . 'stock_' . $type . '_' . $ticker . '.json';
}

/**
 * Get current stock price from Finnhub API
 * Uses caching to avoid API rate limits
 * 
 * @param string $ticker Stock ticker symbol
 * @return float Current stock price
 */
function getCurrentPrice($ticker) {
    try {
        $ticker = strtoupper(trim($ticker));
        
        // Check if we have a cached price
        $cache_file = getCachePath($ticker, 'price');
        
        // Check if cache exists and isn't expired
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < CACHE_EXPIRATION)) {
            $cached_data = file_get_contents($cache_file);
            $price_data = json_decode($cached_data, true);
            if ($price_data && isset($price_data['price']) && $price_data['price'] > 0) {
                return $price_data['price'];
            }
        }
        
        // If we can't make an API call but have old cached data, use it
        if (!canMakeApiCall() && file_exists($cache_file)) {
            $cached_data = file_get_contents($cache_file);
            $price_data = json_decode($cached_data, true);
            if ($price_data && isset($price_data['price']) && $price_data['price'] > 0) {
                return $price_data['price'];
            }
        }
        
        // If we can't make an API call and don't have cached data, use demo data
        if (!canMakeApiCall()) {
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return generateDemoPrice($ticker);
            }
            return 0;
        }
        
        // Call the Finnhub API for quote data
        $url = "https://finnhub.io/api/v1/quote?symbol={$ticker}&token=" . FINNHUB_API_KEY;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $http_code != 200) {
            error_log("Finnhub API Error fetching price for $ticker: HTTP Code: $http_code");
            
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return generateDemoPrice($ticker);
            }
            return 0;
        }
        
        $data = json_decode($response, true);
        
        // For Finnhub, current price is in 'c' field
        if (isset($data['c']) && $data['c'] > 0) {
            $price = floatval($data['c']);
            
            // Cache the result with timestamp
            $cache_data = json_encode([
                'price' => $price,
                'timestamp' => time()
            ]);
            file_put_contents($cache_file, $cache_data);
            
            return $price;
        }
        
        // If API returned invalid data, log and use fallback
        error_log("Invalid data from Finnhub for $ticker: " . json_encode($data));
        
        // Fall back to demo data
        if (USE_DEMO_DATA_ON_API_FAILURE) {
            return generateDemoPrice($ticker);
        }
        
        return 0;
    } catch (Exception $e) {
        error_log("Error fetching stock price for $ticker: " . $e->getMessage());
        
        if (USE_DEMO_DATA_ON_API_FAILURE) {
            return generateDemoPrice($ticker);
        }
        return 0;
    }
}

/**
 * Generate a deterministic demo price for a ticker
 * 
 * @param string $ticker Stock ticker symbol
 * @return float Generated price
 */
function generateDemoPrice($ticker) {
    $ticker = strtoupper(trim($ticker));
    
    // Generate a deterministic base price from the ticker
    $ticker_sum = 0;
    for ($i = 0; $i < strlen($ticker); $i++) {
        $ticker_sum += ord($ticker[$i]);
    }
    
    // Base price between $10 and $500
    $base_price = 10 + ($ticker_sum % 490);
    
    // Add some daily variation (±3%) based on the day
    $day_seed = date('Ymd');
    $daily_factor = (intval($day_seed) % 60) / 1000;
    if (intval($day_seed) % 2 === 0) {
        $daily_factor = -$daily_factor;
    }
    
    $price = $base_price * (1 + $daily_factor);
    
    return round($price, 2);
}

/**
 * Get batch stock quotes for multiple symbols
 * 
 * @param array $symbols Array of stock ticker symbols
 * @return array Array of stock quotes with prices
 */
function getBatchStockQuotes($symbols) {
    // Clean and validate symbols
    $symbols = array_filter(array_map('strtoupper', array_map('trim', $symbols)));
    
    if (empty($symbols)) {
        return [
            'status' => 'error',
            'message' => 'No valid ticker symbols provided.'
        ];
    }
    
    try {
        $quotes = [];
        $success = true;
        
        // Finnhub doesn't have a batch API, so we'll query each symbol individually
        // We'll use cached data where possible to minimize API calls
        foreach ($symbols as $symbol) {
            $price = getCurrentPrice($symbol);
            
            if ($price > 0) {
                // Get more details if we can make another API call
                if (canMakeApiCall()) {
                    $url = "https://finnhub.io/api/v1/quote?symbol={$symbol}&token=" . FINNHUB_API_KEY;
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    if ($response) {
                        $data = json_decode($response, true);
                        if (isset($data['c']) && isset($data['d']) && isset($data['dp'])) {
                            $quotes[$symbol] = [
                                'price' => $data['c'],
                                'change' => $data['d'],
                                'change_percent' => $data['dp']
                            ];
                            continue;
                        }
                    }
                }
                
                // Fallback if API call fails
                $change = round(mt_rand(-100, 100) / 100 * $price / 20, 2);
                $change_percent = round($change / $price * 100, 2);
                
                $quotes[$symbol] = [
                    'price' => $price,
                    'change' => $change,
                    'change_percent' => $change_percent
                ];
            } else {
                $success = false;
            }
        }
        
        return [
            'status' => $success ? 'success' : 'partial',
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
        $ticker = strtoupper(trim($ticker));
        
        // Check cache first
        $cache_file = getCachePath($ticker, 'analysis');
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < CACHE_EXPIRATION_ANALYSIS)) {
            $cached_data = file_get_contents($cache_file);
            $analysis_data = json_decode($cached_data, true);
            if ($analysis_data && isset($analysis_data['status']) && $analysis_data['status'] === 'success') {
                return $analysis_data;
            }
        }
        
        // Check if we can make API calls
        if (!canMakeApiCall()) {
            // If we have old cached data, use it
            if (file_exists($cache_file)) {
                $cached_data = file_get_contents($cache_file);
                $analysis_data = json_decode($cached_data, true);
                if ($analysis_data && isset($analysis_data['status']) && $analysis_data['status'] === 'success') {
                    return $analysis_data;
                }
            }
            
            // If we still don't have data, use demo data
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'API rate limit reached. Please try again later.'
            ];
        }
        
        // Step 1: Get the basic quote data
        $quote_url = "https://finnhub.io/api/v1/quote?symbol={$ticker}&token=" . FINNHUB_API_KEY;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $quote_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
        $quote_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($quote_response === false || $http_code != 200) {
            error_log("Finnhub API Error: HTTP Code: $http_code");
            
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'Failed to connect to stock data API. Please try again later.'
            ];
        }
        
        $quote_data = json_decode($quote_response, true);
        
        // Check for valid response with current price
        if (!isset($quote_data['c']) || $quote_data['c'] <= 0) {
            if (USE_DEMO_DATA_ON_API_FAILURE) {
                return getDemoStockData($ticker);
            }
            
            return [
                'status' => 'error',
                'message' => 'Invalid ticker symbol or data not available.'
            ];
        }
        
        // Extract price data
        $current_price = floatval($quote_data['c']);
        $price_change = floatval($quote_data['d']);
        $price_change_percent = floatval($quote_data['dp']);
        
        // Step 2: Get company profile for the name
        $company_name = $ticker; // Default to ticker
        if (canMakeApiCall()) {
            $profile_url = "https://finnhub.io/api/v1/stock/profile2?symbol={$ticker}&token=" . FINNHUB_API_KEY;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $profile_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
            $profile_response = curl_exec($ch);
            curl_close($ch);
            
            if ($profile_response) {
                $profile_data = json_decode($profile_response, true);
                if (isset($profile_data['name'])) {
                    $company_name = $profile_data['name'];
                }
            }
        }
        
        // Step 3: Get historical data for charts and indicators
        $historical_dates = [];
        $historical_prices = [];
        $historical_volumes = [];
        
        if (canMakeApiCall()) {
            // Calculate time range (1 year)
            $end_time = time();
            $start_time = $end_time - (365 * 24 * 60 * 60); // 1 year ago
            
            $candle_url = "https://finnhub.io/api/v1/stock/candle?symbol={$ticker}&resolution=D&from={$start_time}&to={$end_time}&token=" . FINNHUB_API_KEY;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $candle_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
            $candle_response = curl_exec($ch);
            curl_close($ch);
            
            if ($candle_response) {
                $candle_data = json_decode($candle_response, true);
                
                if (isset($candle_data['c']) && is_array($candle_data['c']) && !empty($candle_data['c'])) {
                    // Finnhub returns candle data in separate arrays
                    $close_prices = $candle_data['c'];
                    $timestamps = $candle_data['t'];
                    
                    // Get volumes if available
                    $volumes = isset($candle_data['v']) ? $candle_data['v'] : [];
                    
                    // Format timestamps to readable dates
                    foreach ($timestamps as $key => $timestamp) {
                        if (isset($close_prices[$key])) {
                            $historical_dates[] = date('M j', $timestamp);
                            $historical_prices[] = $close_prices[$key];
                            
                            if (isset($volumes[$key])) {
                                $historical_volumes[] = $volumes[$key];
                            } else {
                                $historical_volumes[] = 0;
                            }
                        }
                    }
                }
            }
        }
        
        // If we couldn't get historical data, use demo data
        if (empty($historical_dates)) {
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
        
        // Calculate technical indicators
        $short_ma = calculateMovingAverage($historical_prices, 20);
        $long_ma = calculateMovingAverage($historical_prices, 50);
        $rsi = calculateRSI($historical_prices);
        
        // Calculate additional technical indicators
        $ema = calculateEMA($historical_prices, 12);
        $macd = calculateMACD($historical_prices);
        $bollinger = calculateBollingerBands($historical_prices);
        
        // Calculate support and resistance levels
        $support = calculateSupportLevel($historical_prices, $current_price);
        $resistance = calculateResistanceLevel($historical_prices, $current_price);
        
        // Determine buy/sell recommendation
        $recommendation_data = generateRecommendation($current_price, $short_ma, $long_ma, $rsi, $macd, $bollinger);
        $recommendation = $recommendation_data['recommendation'];
        $buy_points = $recommendation_data['buy_points'];
        $sell_points = $recommendation_data['sell_points'];
        $recommendation_reasons = $recommendation_data['reasons'];
        
        // Prepare result data
        $result = [
            'status' => 'success',
            'ticker' => $ticker,
            'company_name' => $company_name,
            'current_price' => $current_price,
            'price_change' => $price_change,
            'price_change_percent' => $price_change_percent,
            'short_ma' => round($short_ma, 2),
            'long_ma' => round($long_ma, 2),
            'rsi' => round($rsi, 2),
            'ema' => round($ema, 2),
            'macd' => round($macd['line'], 2),
            'macd_signal' => round($macd['signal'], 2),
            'bollinger_upper' => round($bollinger['upper'], 2),
            'bollinger_lower' => round($bollinger['lower'], 2),
            'support' => round($support, 2),
            'resistance' => round($resistance, 2),
            'recommendation' => $recommendation,
            'recommendation_reasons' => $recommendation_reasons,
            'buy_points' => $buy_points,
            'sell_points' => $sell_points,
            'historical_dates' => $historical_dates,
            'historical_prices' => $historical_prices,
            'historical_volumes' => $historical_volumes
        ];
        
        // Cache the result
        $cache_data = json_encode($result);
        file_put_contents($cache_file, $cache_data);
        
        return $result;
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
 * Generate demo stock data when API fails
 * 
 * @param string $ticker Stock ticker symbol
 * @return array Stock data for analysis
 */
function getDemoStockData($ticker) {
    $ticker = strtoupper(trim($ticker));
    
    // Generate a deterministic price based on ticker symbol
    $ticker_sum = 0;
    for ($i = 0; $i < strlen($ticker); $i++) {
        $ticker_sum += ord($ticker[$i]);
    }
    
    $base_price = 50 + ($ticker_sum % 200); // Price between $50 and $250
    
    // Add some daily variation
    $day_seed = date('Ymd');
    $daily_factor = (intval($day_seed) % 60) / 1000;
    if (intval($day_seed) % 2 === 0) {
        $daily_factor = -$daily_factor;
    }
    
    $current_price = $base_price * (1 + $daily_factor);
    $current_price = round($current_price, 2);
    
    $price_change = round($current_price * $daily_factor, 2);
    $price_change_percent = round($daily_factor * 100, 2);
    
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
    
    // Calculate technical indicators
    $short_ma = calculateMovingAverage($historical_prices, 20);
    $long_ma = calculateMovingAverage($historical_prices, min(50, count($historical_prices)));
    $rsi = calculateRSI($historical_prices);
    $ema = calculateEMA($historical_prices, 12);
    $macd = calculateMACD($historical_prices);
    $bollinger = calculateBollingerBands($historical_prices);
    
    // Calculate support and resistance
    $support = calculateSupportLevel($historical_prices, $current_price);
    $resistance = calculateResistanceLevel($historical_prices, $current_price);
    
    // Company name (for demo)
    $company_name = $ticker . ' Inc.';
    
    // Generate recommendation
    $recommendation_data = generateRecommendation($current_price, $short_ma, $long_ma, $rsi, $macd, $bollinger);
    
    return [
        'status' => 'success',
        'ticker' => $ticker,
        'company_name' => $company_name,
        'current_price' => $current_price,
        'price_change' => $price_change,
        'price_change_percent' => $price_change_percent,
        'short_ma' => round($short_ma, 2),
        'long_ma' => round($long_ma, 2),
        'rsi' => round($rsi, 2),
        'ema' => round($ema, 2),
        'macd' => round($macd['line'], 2),
        'macd_signal' => round($macd['signal'], 2),
        'bollinger_upper' => round($bollinger['upper'], 2),
        'bollinger_lower' => round($bollinger['lower'], 2),
        'support' => round($support, 2),
        'resistance' => round($resistance, 2),
        'recommendation' => $recommendation_data['recommendation'],
        'recommendation_reasons' => $recommendation_data['reasons'],
        'buy_points' => $recommendation_data['buy_points'],
        'sell_points' => $recommendation_data['sell_points'],
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
    $ticker = strtoupper(trim($ticker));
    $data = [];
    
    // Generate a deterministic base price from the ticker
    $ticker_sum = 0;
    for ($i = 0; $i < strlen($ticker); $i++) {
        $ticker_sum += ord($ticker[$i]);
    }
    
    // Base price between $50 and $250
    $base_price = 50 + ($ticker_sum % 200);
    
    // Create a trend pattern (rising, falling, volatile, etc.) based on ticker
    $trend_type = $ticker_sum % 4;
    $trend_strength = 0.5 + ($ticker_sum % 10) / 10; // 0.5 to 1.5
    
    // Trend types: 0 = rising, 1 = falling, 2 = cyclic, 3 = volatile
    $trend_direction = ($trend_type == 1) ? -1 : 1;
    $volatility = ($trend_type == 3) ? 2 : 1;
    
    // Generate random stock data for past 60 days
    for ($i = 60; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        // Create a semi-deterministic daily change based on date and ticker
        $date_sum = strtotime($date) % 100;
        $seed = ($ticker_sum + $date_sum) % 1000;
        mt_srand($seed);
        
        // Apply different trend patterns
        $trend_factor = 0;
        if ($trend_type == 0 || $trend_type == 1) {
            // Rising or falling trend
            $trend_factor = $trend_direction * $trend_strength * (60 - $i) / 600;
        } elseif ($trend_type == 2) {
            // Cyclic pattern
            $trend_factor = sin($i / 10) * $trend_strength / 10;
        }
        
        // Random daily change (more volatile at certain trend types)
        $random_change = (mt_rand(-300, 300) / 100) * $volatility;
        $change = $random_change / 100 + $trend_factor;
        
        $base_price *= (1 + $change);
        $base_price = max(10, $base_price); // ensure price doesn't go below 10
        
        // Create daily price data with open, high, low, close
        $open = $base_price - (mt_rand(-100, 100) / 100);
        $close = $base_price;
        $high = max($base_price, $open) + (mt_rand(50, 200) / 100);
        $low = min($base_price, $open) - (mt_rand(50, 200) / 100);
        
        // Generate volume based on price change magnitude
        $volume_base = 1000000 + ($ticker_sum * 10000);
        $volume_change_factor = 1 + abs($change) * 10; // Higher volume on bigger price changes
        $volume = intval($volume_base * $volume_change_factor * (mt_rand(80, 120) / 100));
        
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

/**
 * Calculate Simple Moving Average
 * 
 * @param array $prices Array of prices
 * @param int $period Period for MA calculation
 * @return float Moving average value
 */
function calculateMovingAverage($prices, $period) {
    if (empty($prices)) {
        return 0;
    }
    
    $count = count($prices);
    
    if ($count < $period) {
        // If we don't have enough data, use what we have
        return array_sum($prices) / $count;
    }
    
    // Calculate MA for the specified period
    $ma = array_sum(array_slice($prices, -$period)) / $period;
    
    return $ma;
}

/**
 * Calculate Exponential Moving Average
 * 
 * @param array $prices Array of prices
 * @param int $period Period for EMA calculation
 * @return float EMA value
 */
function calculateEMA($prices, $period) {
    if (empty($prices) || count($prices) < $period) {
        return calculateMovingAverage($prices, min(count($prices), $period));
    }
    
    // Calculate multiplier
    $multiplier = 2 / ($period + 1);
    
    // Start with SMA for the initial value
    $ema = calculateMovingAverage(array_slice($prices, 0, $period), $period);
    
    // Calculate EMA for the rest of the prices
    for ($i = $period; $i < count($prices); $i++) {
        $ema = ($prices[$i] - $ema) * $multiplier + $ema;
    }
    
    return $ema;
}

/**
 * Calculate MACD (Moving Average Convergence Divergence)
 * 
 * @param array $prices Array of prices
 * @return array MACD values (line, signal, histogram)
 */
function calculateMACD($prices) {
    if (empty($prices) || count($prices) < 26) {
        return [
            'line' => 0,
            'signal' => 0,
            'histogram' => 0
        ];
    }
    
    // Calculate 12-day and 26-day EMAs
    $ema12 = calculateEMA($prices, 12);
    $ema26 = calculateEMA($prices, 26);
    
    // Calculate MACD line
    $macd_line = $ema12 - $ema26;
    
    // For a proper signal line, we'd need historical MACD values
    // Here we'll approximate using the most recent prices
    $signal_line = $macd_line * 0.9; // Approximate 9-day EMA of MACD
    
    // Calculate histogram
    $histogram = $macd_line - $signal_line;
    
    return [
        'line' => $macd_line,
        'signal' => $signal_line,
        'histogram' => $histogram
    ];
}

/**
 * Calculate Bollinger Bands
 * 
 * @param array $prices Array of prices
 * @param int $period Period for calculation (default 20)
 * @param float $multiplier Standard deviation multiplier (default 2)
 * @return array Bollinger Bands values (middle, upper, lower)
 */
function calculateBollingerBands($prices, $period = 20, $multiplier = 2) {
    if (empty($prices)) {
        return [
            'middle' => 0,
            'upper' => 0,
            'lower' => 0
        ];
    }
    
    // Calculate middle band (SMA)
    $middle = calculateMovingAverage($prices, min(count($prices), $period));
    
    // Calculate standard deviation
    $count = count($prices);
    $slice = array_slice($prices, -min($count, $period));
    $variance = 0;
    
    foreach ($slice as $price) {
        $variance += pow($price - $middle, 2);
    }
    
    $std_dev = sqrt($variance / count($slice));
    
    // Calculate upper and lower bands
    $upper = $middle + ($multiplier * $std_dev);
    $lower = $middle - ($multiplier * $std_dev);
    
    return [
        'middle' => $middle,
        'upper' => $upper,
        'lower' => $lower
    ];
}

/**
 * Calculate Relative Strength Index (RSI)
 * 
 * @param array $prices Array of historical prices
 * @return float RSI value (0-100)
 */
function calculateRSI($prices, $period = 14) {
    try {
        // Need at least period + 1 data points to calculate RSI
        if (count($prices) <= $period) {
            return 50; // Default value
        }
        
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
        $avg_gain = $gains / $period;
        $avg_loss = $losses / $period;
        
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
 * Calculate a support level based on historical prices
 * 
 * @param array $prices Historical prices
 * @param float $current_price Current stock price
 * @return float Support level
 */
function calculateSupportLevel($prices, $current_price) {
    if (empty($prices)) {
        return $current_price * 0.9; // Default fallback
    }
    
    // Find the most recent low points
    $min_prices = [];
    $price_count = count($prices);
    
    // Look for local minimums
    for ($i = 1; $i < $price_count - 1; $i++) {
        if ($prices[$i] < $prices[$i - 1] && $prices[$i] < $prices[$i + 1]) {
            $min_prices[] = $prices[$i];
        }
    }
    
    // If we found at least one minimum, use the highest minimum below current price
    if (!empty($min_prices)) {
        $support = 0;
        foreach ($min_prices as $min) {
            if ($min < $current_price && $min > $support) {
                $support = $min;
            }
        }
        
        if ($support > 0) {
            return $support;
        }
    }
    
    // If we couldn't find a proper support level, use a percentage of current price
    return $current_price * 0.9;
}

/**
 * Calculate a resistance level based on historical prices
 * 
 * @param array $prices Historical prices
 * @param float $current_price Current stock price
 * @return float Resistance level
 */
function calculateResistanceLevel($prices, $current_price) {
    if (empty($prices)) {
        return $current_price * 1.1; // Default fallback
    }
    
    // Find the most recent high points
    $max_prices = [];
    $price_count = count($prices);
    
    // Look for local maximums
    for ($i = 1; $i < $price_count - 1; $i++) {
        if ($prices[$i] > $prices[$i - 1] && $prices[$i] > $prices[$i + 1]) {
            $max_prices[] = $prices[$i];
        }
    }
    
    // If we found at least one maximum, use the lowest maximum above current price
    if (!empty($max_prices)) {
        $resistance = PHP_FLOAT_MAX;
        foreach ($max_prices as $max) {
            if ($max > $current_price && $max < $resistance) {
                $resistance = $max;
            }
        }
        
        if ($resistance < PHP_FLOAT_MAX) {
            return $resistance;
        }
    }
    
    // If we couldn't find a proper resistance level, use a percentage of current price
    return $current_price * 1.1;
}

/**
 * Generate buy/sell recommendation based on technical indicators
 * 
 * @param float $current_price Current stock price
 * @param float $short_ma Short-term moving average
 * @param float $long_ma Long-term moving average
 * @param float $rsi Relative Strength Index
 * @param array $macd MACD values
 * @param array $bollinger Bollinger Bands values
 * @return array Recommendation data with reasons
 */
function generateRecommendation($current_price, $short_ma, $long_ma, $rsi, $macd, $bollinger) {
    $buy_points = [];
    $sell_points = [];
    $reasons = [];
    
    // Point system: 1 for weak signals, 2 for moderate signals, 3 for strong signals
    $buy_score = 0;
    $sell_score = 0;
    
    // RSI analysis
    if ($rsi < 30) {
        $buy_score += 3;
        $reasons[] = "RSI is oversold at " . round($rsi, 2);
    } elseif ($rsi < 40) {
        $buy_score += 1;
        $reasons[] = "RSI is approaching oversold territory at " . round($rsi, 2);
    } elseif ($rsi > 70) {
        $sell_score += 3;
        $reasons[] = "RSI is overbought at " . round($rsi, 2);
    } elseif ($rsi > 60) {
        $sell_score += 1;
        $reasons[] = "RSI is approaching overbought territory at " . round($rsi, 2);
    }
    
    // Moving average analysis
    if ($short_ma > $long_ma) {
        $buy_score += 2;
        $reasons[] = "Short-term MA (" . round($short_ma, 2) . ") is above long-term MA (" . round($long_ma, 2) . ")";
    } else {
        $sell_score += 2;
        $reasons[] = "Short-term MA (" . round($short_ma, 2) . ") is below long-term MA (" . round($long_ma, 2) . ")";
    }
    
    // Price vs Moving Average
    if ($current_price < $short_ma) {
        $buy_score += 1;
        $reasons[] = "Price (" . round($current_price, 2) . ") is below short-term MA (" . round($short_ma, 2) . ")";
    } else {
        $sell_score += 1;
        $reasons[] = "Price (" . round($current_price, 2) . ") is above short-term MA (" . round($short_ma, 2) . ")";
    }
    
    // MACD analysis
    if ($macd['line'] > $macd['signal']) {
        $buy_score += 2;
        $reasons[] = "MACD line is above signal line";
    } else {
        $sell_score += 2;
        $reasons[] = "MACD line is below signal line";
    }
    
    // Bollinger Bands analysis
    if ($current_price < $bollinger['lower']) {
        $buy_score += 3;
        $reasons[] = "Price is below lower Bollinger Band";
    } elseif ($current_price > $bollinger['upper']) {
        $sell_score += 3;
        $reasons[] = "Price is above upper Bollinger Band";
    }
    
    // Determine recommendation
    $recommendation = 'hold';
    if ($buy_score > $sell_score + 2) {
        $recommendation = 'buy';
    } elseif ($sell_score > $buy_score + 2) {
        $recommendation = 'sell';
    }
    
    // Set buy and sell points
    $support = $bollinger['lower'];
    $resistance = $bollinger['upper'];
    
    if ($recommendation === 'buy' || $recommendation === 'hold') {
        $buy_points[] = [
            'price' => round($support, 2),
            'reason' => 'Lower Bollinger Band support'
        ];
        $buy_points[] = [
            'price' => round($support * 0.95, 2),
            'reason' => 'Strong support level'
        ];
    }
    
    if ($recommendation === 'sell' || $recommendation === 'hold') {
        $sell_points[] = [
            'price' => round($resistance, 2),
            'reason' => 'Upper Bollinger Band resistance'
        ];
        $sell_points[] = [
            'price' => round($resistance * 1.05, 2),
            'reason' => 'Strong resistance level'
        ];
    }
    
    return [
        'recommendation' => $recommendation,
        'buy_points' => $buy_points,
        'sell_points' => $sell_points,
        'reasons' => array_slice($reasons, 0, 3) // Limit to top 3 reasons
    ];
}

/**
 * Make a Finnhub API request with proper error handling
 * 
 * @param string $endpoint API endpoint 
 * @param array $params Query parameters
 * @return mixed Response data or false on failure
 */
function makeFinnhubApiRequest($endpoint, $params = []) {
    if (!canMakeApiCall()) {
        return false;
    }
    
    // Build query string from params
    $query_string = http_build_query($params);
    
    // Construct URL with API key
    $url = "https://finnhub.io/api/v1/{$endpoint}?{$query_string}&token=" . FINNHUB_API_KEY;
    
    // Make the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'iGotMoney/1.0');
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $http_code != 200) {
        error_log("Finnhub API Error for {$endpoint}: HTTP Code: {$http_code}");
        return false;
    }
    
    return json_decode($response, true);
}

// Include view
require_once 'views/stocks.php';