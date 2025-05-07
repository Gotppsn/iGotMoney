<?php
/**
 * Investment Model
 * 
 * Handles investment-related database operations
 */

require_once 'config/database.php';

class Investment {
    private $conn;
    private $table = 'investments';
    private $types_table = 'investment_types';
    private $transactions_table = 'transactions';
    private $watchlist_table = 'stock_watchlist';
    
    // Investment properties
    public $investment_id;
    public $user_id;
    public $type_id;
    public $name;
    public $ticker_symbol;
    public $purchase_date;
    public $purchase_price;
    public $quantity;
    public $current_price;
    public $last_updated;
    public $notes;
    public $created_at;
    public $updated_at;
    
    // Constructor
    public function __construct() {
        $this->conn = connectDB();
    }
    
    // Destructor
    public function __destruct() {
        closeDB($this->conn);
    }
    
    // Create a new investment
    public function create() {
        try {
            // Validate required fields
            if (empty($this->user_id) || empty($this->type_id) || empty($this->name) || 
                empty($this->purchase_date) || empty($this->purchase_price) || empty($this->quantity)) {
                error_log("Investment create: Missing required fields");
                return false;
            }
            
            // Prepare query
            $query = "INSERT INTO " . $this->table . " 
                    (user_id, type_id, name, ticker_symbol, purchase_date, purchase_price, 
                    quantity, current_price, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Sanitize inputs
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->type_id = htmlspecialchars(strip_tags($this->type_id));
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
            $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
            $this->notes = htmlspecialchars(strip_tags($this->notes));
            
            // Convert price and quantity to proper numeric format
            $this->purchase_price = floatval($this->purchase_price);
            $this->quantity = floatval($this->quantity);
            $this->current_price = floatval($this->current_price);
            
            // If current price is not set, use purchase price
            if (!$this->current_price) {
                $this->current_price = $this->purchase_price;
            }
            
            // Bind parameters
            $stmt->bind_param(
                "iisssddds", 
                $this->user_id, 
                $this->type_id, 
                $this->name, 
                $this->ticker_symbol, 
                $this->purchase_date, 
                $this->purchase_price, 
                $this->quantity, 
                $this->current_price, 
                $this->notes
            );
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Set the newly created investment ID
            $this->investment_id = $this->conn->insert_id;
            
            // Add to transactions table
            $this->addToTransactions();
            
            return true;
        } catch (Exception $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }
    
    // Update an investment
    public function update() {
        try {
            // Validate required fields
            if (empty($this->investment_id) || empty($this->user_id)) {
                error_log("Investment update: Missing required fields");
                return false;
            }
            
            // Prepare query
            $query = "UPDATE " . $this->table . " 
                      SET type_id = ?, name = ?, ticker_symbol = ?, purchase_date = ?, 
                          purchase_price = ?, quantity = ?, current_price = ?, notes = ? 
                      WHERE investment_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Sanitize inputs
            $this->type_id = htmlspecialchars(strip_tags($this->type_id));
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
            $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
            $this->notes = htmlspecialchars(strip_tags($this->notes));
            
            // Convert price and quantity to proper numeric format
            $this->purchase_price = floatval($this->purchase_price);
            $this->quantity = floatval($this->quantity);
            $this->current_price = floatval($this->current_price);
            
            // Bind parameters
            $stmt->bind_param(
                "isssdddsii", 
                $this->type_id, 
                $this->name, 
                $this->ticker_symbol, 
                $this->purchase_date, 
                $this->purchase_price, 
                $this->quantity, 
                $this->current_price, 
                $this->notes,
                $this->investment_id,
                $this->user_id
            );
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete an investment
    public function delete($investment_id, $user_id) {
        try {
            // Validate inputs
            $investment_id = intval($investment_id);
            $user_id = intval($user_id);
            
            if ($investment_id <= 0 || $user_id <= 0) {
                return false;
            }
            
            // Delete associated transactions first
            $query = "DELETE FROM " . $this->transactions_table . " 
                      WHERE investment_id = ? AND user_id = ?";
                      
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("ii", $investment_id, $user_id);
            $stmt->execute();
            
            // Now delete the investment
            $query = "DELETE FROM " . $this->table . " 
                      WHERE investment_id = ? AND user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("ii", $investment_id, $user_id);
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }
    
    // Get investment by ID - Fixed error handling
    public function getById($investment_id, $user_id) {
        try {
            // Validate input
            $investment_id = intval($investment_id);
            $user_id = intval($user_id);
            
            if ($investment_id <= 0 || $user_id <= 0) {
                return false;
            }
            
            // SQL query
            $query = "SELECT i.*, t.name as type_name, t.risk_level 
                    FROM " . $this->table . " i
                    JOIN " . $this->types_table . " t ON i.type_id = t.type_id
                    WHERE i.investment_id = ? AND i.user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $investment_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Set properties
                $this->investment_id = $row['investment_id'];
                $this->user_id = $row['user_id'];
                $this->type_id = $row['type_id'];
                $this->name = $row['name'];
                $this->ticker_symbol = $row['ticker_symbol'];
                $this->purchase_date = $row['purchase_date'];
                $this->purchase_price = $row['purchase_price'];
                $this->quantity = $row['quantity'];
                $this->current_price = $row['current_price'];
                $this->last_updated = $row['last_updated'];
                $this->notes = $row['notes'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all investments for a user - Added error handling
    public function getAll($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return false;
            }
            
            // SQL query
            $query = "SELECT i.*, t.name as type_name, t.risk_level 
                    FROM " . $this->table . " i
                    JOIN " . $this->types_table . " t ON i.type_id = t.type_id
                    WHERE i.user_id = ? 
                    ORDER BY i.purchase_date DESC";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Bind parameter
            $stmt->bind_param("i", $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all investment types - Added error handling
    public function getAllTypes() {
        try {
            // SQL query
            $query = "SELECT * FROM " . $this->types_table . " ORDER BY risk_level";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllTypes: " . $e->getMessage());
            return false;
        }
    }
    
    // Update investment price - Improved error handling
    public function updatePrice($investment_id, $user_id, $current_price) {
        try {
            // Validate inputs
            $investment_id = intval($investment_id);
            $user_id = intval($user_id);
            $current_price = floatval($current_price);
            
            if ($investment_id <= 0 || $user_id <= 0 || $current_price < 0) {
                return false;
            }
            
            // SQL query
            $query = "UPDATE " . $this->table . " 
                    SET current_price = ?, last_updated = NOW() 
                    WHERE investment_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("dii", $current_price, $investment_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in updatePrice: " . $e->getMessage());
            return false;
        }
    }
    
    // Get investment summary - Improved null handling and error prevention
    public function getSummary($user_id) {
        try {
            // Get all investments
            $investments = $this->getAll($user_id);
            
            if (!$investments) {
                return [
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
            
            $summary = [
                'total_invested' => 0,
                'current_value' => 0,
                'total_gain_loss' => 0,
                'percent_gain_loss' => 0,
                'by_type' => [],
                'by_risk' => [],
                'top_performers' => [],
                'worst_performers' => []
            ];
            
            $all_investments = [];
            
            while ($investment = $investments->fetch_assoc()) {
                $invested = floatval($investment['purchase_price']) * floatval($investment['quantity']);
                $current = floatval($investment['current_price']) * floatval($investment['quantity']);
                $gain_loss = $current - $invested;
                $percent_gain_loss = ($invested > 0) ? ($gain_loss / $invested) * 100 : 0;
                
                $all_investments[] = [
                    'id' => $investment['investment_id'],
                    'name' => $investment['name'],
                    'ticker' => $investment['ticker_symbol'],
                    'type' => $investment['type_name'],
                    'risk' => $investment['risk_level'],
                    'invested' => $invested,
                    'current' => $current,
                    'gain_loss' => $gain_loss,
                    'percent_gain_loss' => $percent_gain_loss
                ];
                
                // Add to summary totals
                $summary['total_invested'] += $invested;
                $summary['current_value'] += $current;
                
                // Group by type
                if (!isset($summary['by_type'][$investment['type_name']])) {
                    $summary['by_type'][$investment['type_name']] = [
                        'invested' => 0,
                        'current' => 0,
                        'gain_loss' => 0
                    ];
                }
                $summary['by_type'][$investment['type_name']]['invested'] += $invested;
                $summary['by_type'][$investment['type_name']]['current'] += $current;
                $summary['by_type'][$investment['type_name']]['gain_loss'] += $gain_loss;
                
                // Group by risk
                if (!isset($summary['by_risk'][$investment['risk_level']])) {
                    $summary['by_risk'][$investment['risk_level']] = [
                        'invested' => 0,
                        'current' => 0,
                        'gain_loss' => 0
                    ];
                }
                $summary['by_risk'][$investment['risk_level']]['invested'] += $invested;
                $summary['by_risk'][$investment['risk_level']]['current'] += $current;
                $summary['by_risk'][$investment['risk_level']]['gain_loss'] += $gain_loss;
            }
            
            // Calculate total gain/loss
            $summary['total_gain_loss'] = $summary['current_value'] - $summary['total_invested'];
            $summary['percent_gain_loss'] = ($summary['total_invested'] > 0) ? 
                                        ($summary['total_gain_loss'] / $summary['total_invested']) * 100 : 0;
            
            // Calculate percentages for each type and risk level
            foreach ($summary['by_type'] as &$type) {
                $type['percent'] = ($summary['total_invested'] > 0) ? 
                                ($type['invested'] / $summary['total_invested']) * 100 : 0;
                $type['percent_gain_loss'] = ($type['invested'] > 0) ? 
                                            ($type['gain_loss'] / $type['invested']) * 100 : 0;
            }
            
            foreach ($summary['by_risk'] as &$risk) {
                $risk['percent'] = ($summary['total_invested'] > 0) ? 
                                ($risk['invested'] / $summary['total_invested']) * 100 : 0;
                $risk['percent_gain_loss'] = ($risk['invested'] > 0) ? 
                                            ($risk['gain_loss'] / $risk['invested']) * 100 : 0;
            }
            
            // Sort investments by performance
            usort($all_investments, function($a, $b) {
                return $b['percent_gain_loss'] - $a['percent_gain_loss'];
            });
            
            // Get top 5 performers
            $summary['top_performers'] = array_slice($all_investments, 0, min(5, count($all_investments)));
            
            // Get worst 5 performers
            $worst = array_slice($all_investments, -min(5, count($all_investments)));
            $summary['worst_performers'] = array_reverse($worst);
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error in getSummary: " . $e->getMessage());
            return [
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
    }
    
    /**
     * Add investment to transactions table
     * @return boolean True if successful, false otherwise
     */
    private function addToTransactions() {
        try {
            // Calculate total amount
            $amount = $this->purchase_price * $this->quantity;
            
            // Create transaction query
            $query = "INSERT INTO " . $this->transactions_table . " 
                     (user_id, type, amount, description, transaction_date, investment_id)
                     VALUES (?, 'investment', ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Create description
            $description = "Investment in " . $this->name;
            if (!empty($this->ticker_symbol)) {
                $description .= " (" . $this->ticker_symbol . ")";
            }
            
            // Bind parameters
            $stmt->bind_param(
                "idssi", 
                $this->user_id, 
                $amount, 
                $description, 
                $this->purchase_date, 
                $this->investment_id
            );
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error for transaction: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in addToTransactions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add stock to watchlist
     * @return boolean True if successful, false otherwise
     */
    public function addToWatchlist() {
        try {
            // Validate required fields
            if (empty($this->user_id) || empty($this->ticker_symbol) || empty($this->name)) {
                error_log("Watchlist add: Missing required fields");
                return false;
            }
            
            // Prepare query
            if (isset($_POST['target_buy_price']) && !empty($_POST['target_buy_price']) &&
                isset($_POST['target_sell_price']) && !empty($_POST['target_sell_price'])) {
                
                // Both target prices provided
                $query = "INSERT INTO " . $this->watchlist_table . " 
                        (user_id, ticker_symbol, company_name, target_buy_price, 
                        target_sell_price, current_price, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            } else if (isset($_POST['target_buy_price']) && !empty($_POST['target_buy_price'])) {
                // Only buy target provided
                $query = "INSERT INTO " . $this->watchlist_table . " 
                        (user_id, ticker_symbol, company_name, target_buy_price, 
                        current_price, notes)
                        VALUES (?, ?, ?, ?, ?, ?)";
            } else if (isset($_POST['target_sell_price']) && !empty($_POST['target_sell_price'])) {
                // Only sell target provided
                $query = "INSERT INTO " . $this->watchlist_table . " 
                        (user_id, ticker_symbol, company_name, target_sell_price, 
                        current_price, notes)
                        VALUES (?, ?, ?, ?, ?, ?)";
            } else {
                // No target prices provided
                $query = "INSERT INTO " . $this->watchlist_table . " 
                        (user_id, ticker_symbol, company_name, current_price, notes)
                        VALUES (?, ?, ?, ?, ?)";
            }
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Sanitize inputs
            $this->user_id = intval($this->user_id);
            $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
            
            // Convert price to proper numeric format
            $this->current_price = floatval($this->current_price);
            
            // Bind parameters based on query type
            if (isset($_POST['target_buy_price']) && !empty($_POST['target_buy_price']) &&
                isset($_POST['target_sell_price']) && !empty($_POST['target_sell_price'])) {
                
                $target_buy_price = floatval($_POST['target_buy_price']);
                $target_sell_price = floatval($_POST['target_sell_price']);
                
                $stmt->bind_param(
                    "issddds", 
                    $this->user_id, 
                    $this->ticker_symbol, 
                    $this->name, 
                    $target_buy_price, 
                    $target_sell_price, 
                    $this->current_price,
                    $this->notes
                );
            } else if (isset($_POST['target_buy_price']) && !empty($_POST['target_buy_price'])) {
                $target_buy_price = floatval($_POST['target_buy_price']);
                
                $stmt->bind_param(
                    "issdds", 
                    $this->user_id, 
                    $this->ticker_symbol, 
                    $this->name, 
                    $target_buy_price,
                    $this->current_price,
                    $this->notes
                );
            } else if (isset($_POST['target_sell_price']) && !empty($_POST['target_sell_price'])) {
                $target_sell_price = floatval($_POST['target_sell_price']);
                
                $stmt->bind_param(
                    "issdds", 
                    $this->user_id, 
                    $this->ticker_symbol, 
                    $this->name, 
                    $target_sell_price,
                    $this->current_price,
                    $this->notes
                );
            } else {
                $stmt->bind_param(
                    "issds", 
                    $this->user_id, 
                    $this->ticker_symbol, 
                    $this->name, 
                    $this->current_price,
                    $this->notes
                );
            }
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in addToWatchlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove stock from watchlist
     * @param int $watchlist_id Watchlist ID
     * @param int $user_id User ID
     * @return boolean True if successful, false otherwise
     */
    public function removeFromWatchlist($watchlist_id, $user_id) {
        try {
            // Validate inputs
            $watchlist_id = intval($watchlist_id);
            $user_id = intval($user_id);
            
            if ($watchlist_id <= 0 || $user_id <= 0) {
                return false;
            }
            
            // Prepare query
            $query = "DELETE FROM " . $this->watchlist_table . " 
                      WHERE watchlist_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $watchlist_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in removeFromWatchlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get watchlist for a user
     * @param int $user_id User ID
     * @return mysqli_result Result set of watchlist items
     */
    public function getWatchlist($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return false;
            }
            
            // SQL query
            $query = "SELECT * FROM " . $this->watchlist_table . " 
                      WHERE user_id = ? 
                      ORDER BY ticker_symbol ASC";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Bind parameter
            $stmt->bind_param("i", $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            return $result;
        } catch (Exception $e) {
            error_log("Error in getWatchlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate buy and sell points for a stock
     * @param string $ticker Ticker symbol
     * @param array $price_history Array of price history data
     * @return array Analysis results
     */
    public function calculateBuySellPoints($ticker, $price_history) {
        try {
            // Validate inputs
            if (empty($ticker) || empty($price_history) || !is_array($price_history)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid input data.'
                ];
            }
            
            // Get the latest price
            $current_price = end($price_history)['close'];
            reset($price_history);
            
            // Calculate short-term moving average (20 days)
            $short_period = min(20, count($price_history));
            $short_ma = $this->calculateMovingAverage($price_history, $short_period);
            
            // Calculate long-term moving average (50 days)
            $long_period = min(50, count($price_history));
            $long_ma = $this->calculateMovingAverage($price_history, $long_period);
            
            // Calculate RSI (14 days)
            $rsi_period = min(14, count($price_history));
            $rsi = $this->calculateRSI($price_history, $rsi_period);
            
            // Calculate support and resistance levels
            list($support, $resistance) = $this->calculateSupportResistance($price_history);
            
            // Determine buy/sell recommendation
            $recommendation = $this->determineRecommendation($current_price, $short_ma, $long_ma, $rsi, $support, $resistance);
            
            // Calculate potential buy and sell points
            $buy_points = $this->calculateBuyPoints($current_price, $short_ma, $long_ma, $rsi, $support);
            $sell_points = $this->calculateSellPoints($current_price, $short_ma, $long_ma, $rsi, $resistance);
            
            // Return analysis results
            return [
                'status' => 'success',
                'ticker' => $ticker,
                'current_price' => $current_price,
                'short_ma' => $short_ma,
                'long_ma' => $long_ma,
                'rsi' => $rsi,
                'support' => $support,
                'resistance' => $resistance,
                'recommendation' => $recommendation,
                'buy_points' => $buy_points,
                'sell_points' => $sell_points,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Error in calculateBuySellPoints: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred during analysis.'
            ];
        }
    }
    
    /**
     * Calculate moving average
     * @param array $price_history Price history data
     * @param int $period Number of days
     * @return float Moving average
     */
    private function calculateMovingAverage($price_history, $period) {
        $total = 0;
        $count = 0;
        
        // Use the most recent 'period' days
        $recent_prices = array_slice($price_history, -$period);
        
        foreach ($recent_prices as $day) {
            $total += $day['close'];
            $count++;
        }
        
        return $count > 0 ? $total / $count : 0;
    }
    
    /**
     * Calculate Relative Strength Index (RSI)
     * @param array $price_history Price history data
     * @param int $period Number of days
     * @return float RSI value
     */
    private function calculateRSI($price_history, $period) {
        // Need at least period+1 days to calculate gains/losses
        if (count($price_history) <= $period) {
            return 50; // Default neutral value
        }
        
        $gains = 0;
        $losses = 0;
        
        // Calculate gains and losses over the period
        for ($i = count($price_history) - $period; $i < count($price_history); $i++) {
            $current = $price_history[$i]['close'];
            $previous = $price_history[$i - 1]['close'];
            $change = $current - $previous;
            
            if ($change >= 0) {
                $gains += $change;
            } else {
                $losses -= $change; // Make loss a positive number
            }
        }
        
        // Calculate average gain and loss
        $avg_gain = $gains / $period;
        $avg_loss = $losses / $period;
        
        // Calculate RSI
        if ($avg_loss == 0) {
            return 100; // All gains, no losses
        }
        
        $rs = $avg_gain / $avg_loss;
        $rsi = 100 - (100 / (1 + $rs));
        
        return $rsi;
    }
    
    /**
     * Calculate support and resistance levels
     * @param array $price_history Price history data
     * @return array Support and resistance levels
     */
    private function calculateSupportResistance($price_history) {
        // Find local minimums and maximums
        $minimums = [];
        $maximums = [];
        
        // Need at least 3 days to find local min/max
        if (count($price_history) < 3) {
            $last_price = end($price_history)['close'];
            return [$last_price * 0.95, $last_price * 1.05];
        }
        
        for ($i = 1; $i < count($price_history) - 1; $i++) {
            $prev = $price_history[$i - 1]['low'];
            $curr = $price_history[$i]['low'];
            $next = $price_history[$i + 1]['low'];
            
            if ($curr < $prev && $curr < $next) {
                $minimums[] = $curr;
            }
            
            $prev = $price_history[$i - 1]['high'];
            $curr = $price_history[$i]['high'];
            $next = $price_history[$i + 1]['high'];
            
            if ($curr > $prev && $curr > $next) {
                $maximums[] = $curr;
            }
        }
        
        // Calculate support level (average of local minimums)
        $support = count($minimums) > 0 ? array_sum($minimums) / count($minimums) : $price_history[0]['low'];
        
        // Calculate resistance level (average of local maximums)
        $resistance = count($maximums) > 0 ? array_sum($maximums) / count($maximums) : $price_history[0]['high'];
        
        // Ensure support is below current price and resistance is above
        $current_price = end($price_history)['close'];
        
        if ($support > $current_price) {
            $support = $current_price * 0.95;
        }
        
        if ($resistance < $current_price) {
            $resistance = $current_price * 1.05;
        }
        
        return [$support, $resistance];
    }
    
    /**
     * Determine buy/sell recommendation
     * @param float $current_price Current price
     * @param float $short_ma Short-term moving average
     * @param float $long_ma Long-term moving average
     * @param float $rsi RSI value
     * @param float $support Support level
     * @param float $resistance Resistance level
     * @return string Recommendation (buy, sell, hold)
     */
    private function determineRecommendation($current_price, $short_ma, $long_ma, $rsi, $support, $resistance) {
        $signals = [];
        
        // Moving average crossover (short > long = bullish)
        if ($short_ma > $long_ma) {
            $signals[] = 1;
        } else if ($short_ma < $long_ma) {
            $signals[] = -1;
        }
        
        // RSI overbought/oversold
        if ($rsi < 30) {
            $signals[] = 1; // Oversold, potential buy
        } else if ($rsi > 70) {
            $signals[] = -1; // Overbought, potential sell
        }
        
        // Price near support/resistance
        $support_distance = ($current_price - $support) / $current_price * 100;
        $resistance_distance = ($resistance - $current_price) / $current_price * 100;
        
        if ($support_distance < 5) {
            $signals[] = 1; // Near support, potential buy
        }
        
        if ($resistance_distance < 5) {
            $signals[] = -1; // Near resistance, potential sell
        }
        
        // Calculate overall signal
        $signal_sum = array_sum($signals);
        
        if ($signal_sum > 0) {
            return 'buy';
        } else if ($signal_sum < 0) {
            return 'sell';
        } else {
            return 'hold';
        }
    }
    
    /**
     * Calculate potential buy points
     * @param float $current_price Current price
     * @param float $short_ma Short-term moving average
     * @param float $long_ma Long-term moving average
     * @param float $rsi RSI value
     * @param float $support Support level
     * @return array Buy points with reasons
     */
    private function calculateBuyPoints($current_price, $short_ma, $long_ma, $rsi, $support) {
        $buy_points = [];
        
        // Support level
        $buy_points[] = [
            'price' => $support,
            'reason' => 'Support level indicates potential price bounce'
        ];
        
        // RSI-based buy point (if oversold)
        if ($rsi < 30) {
            // Calculate price that would result in RSI around 30
            $rsi_buy_price = $current_price * 0.97;
            $buy_points[] = [
                'price' => $rsi_buy_price,
                'reason' => 'RSI indicates oversold condition'
            ];
        }
        
        // Moving average crossover buy point
        if ($short_ma < $long_ma) {
            // Price where short MA would cross above long MA
            $ma_diff = $long_ma - $short_ma;
            $ma_buy_price = $current_price + $ma_diff;
            $buy_points[] = [
                'price' => $ma_buy_price,
                'reason' => 'Potential moving average crossover'
            ];
        }
        
        // Sort by price (ascending)
        usort($buy_points, function($a, $b) {
            return $a['price'] - $b['price'];
        });
        
        return $buy_points;
    }
    
    /**
     * Calculate potential sell points
     * @param float $current_price Current price
     * @param float $short_ma Short-term moving average
     * @param float $long_ma Long-term moving average
     * @param float $rsi RSI value
     * @param float $resistance Resistance level
     * @return array Sell points with reasons
     */
    private function calculateSellPoints($current_price, $short_ma, $long_ma, $rsi, $resistance) {
        $sell_points = [];
        
        // Resistance level
        $sell_points[] = [
            'price' => $resistance,
            'reason' => 'Resistance level indicates potential price reversal'
        ];
        
        // RSI-based sell point (if overbought)
        if ($rsi > 70) {
            // Calculate price that would result in RSI around 70
            $rsi_sell_price = $current_price * 1.03;
            $sell_points[] = [
                'price' => $rsi_sell_price,
                'reason' => 'RSI indicates overbought condition'
            ];
        }
        
        // Moving average crossover sell point
        if ($short_ma > $long_ma) {
            // Price where short MA would cross below long MA
            $ma_diff = $short_ma - $long_ma;
            $ma_sell_price = $current_price - $ma_diff;
            $sell_points[] = [
                'price' => $ma_sell_price,
                'reason' => 'Potential moving average crossover'
            ];
        }
        
        // Calculate 10% profit target
        $profit_target = $current_price * 1.1;
        $sell_points[] = [
            'price' => $profit_target,
            'reason' => '10% profit target'
        ];
        
        // Sort by price (ascending)
        usort($sell_points, function($a, $b) {
            return $a['price'] - $b['price'];
        });
        
        return $sell_points;
    }
    
    /**
     * Update a stock in watchlist
     * @param int $watchlist_id Watchlist ID
     * @param int $user_id User ID
     * @param array $data Data to update
     * @return boolean True if successful, false otherwise
     */
    public function updateWatchlistItem($watchlist_id, $user_id, $data) {
        try {
            // Validate inputs
            $watchlist_id = intval($watchlist_id);
            $user_id = intval($user_id);
            
            if ($watchlist_id <= 0 || $user_id <= 0 || empty($data)) {
                return false;
            }
            
            // Prepare query
            $query = "UPDATE " . $this->watchlist_table . " 
                      SET ticker_symbol = ?, company_name = ?, target_buy_price = ?, 
                      target_sell_price = ?, current_price = ?, notes = ? 
                      WHERE watchlist_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Sanitize inputs
            $ticker_symbol = htmlspecialchars(strip_tags($data['ticker_symbol']));
            $company_name = htmlspecialchars(strip_tags($data['company_name']));
            $target_buy_price = isset($data['target_buy_price']) && !empty($data['target_buy_price']) ? 
                                floatval($data['target_buy_price']) : null;
            $target_sell_price = isset($data['target_sell_price']) && !empty($data['target_sell_price']) ? 
                                 floatval($data['target_sell_price']) : null;
            $current_price = floatval($data['current_price']);
            $notes = htmlspecialchars(strip_tags($data['notes'] ?? ''));
            
            // Bind parameters
            $stmt->bind_param(
                "ssdddssii", 
                $ticker_symbol, 
                $company_name, 
                $target_buy_price, 
                $target_sell_price, 
                $current_price, 
                $notes,
                $watchlist_id,
                $user_id
            );
            
            // Execute query
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows >= 0;
        } catch (Exception $e) {
            error_log("Error in updateWatchlistItem: " . $e->getMessage());
            return false;
        }
    }
}
?>