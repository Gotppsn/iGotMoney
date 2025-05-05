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
    
    // Create new investment
    public function create() {
        // SQL query
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, type_id, name, ticker_symbol, purchase_date, 
                   purchase_price, quantity, current_price, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->type_id = htmlspecialchars(strip_tags($this->type_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->purchase_price = htmlspecialchars(strip_tags($this->purchase_price));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->current_price = htmlspecialchars(strip_tags($this->current_price));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Set current price equal to purchase price if not provided
        if (empty($this->current_price)) {
            $this->current_price = $this->purchase_price;
        }
        
        // Bind parameters
        $stmt->bind_param("iisssddds", 
                          $this->user_id, 
                          $this->type_id, 
                          $this->name, 
                          $this->ticker_symbol, 
                          $this->purchase_date, 
                          $this->purchase_price, 
                          $this->quantity, 
                          $this->current_price, 
                          $this->notes);
        
        // Execute query
        if ($stmt->execute()) {
            $this->investment_id = $this->conn->insert_id;
            
            // Record transaction
            $this->recordTransaction();
            
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all investment types
    public function getAllTypes() {
        // SQL query
        $query = "SELECT * FROM " . $this->types_table . " ORDER BY risk_level";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get all investments for a user
    public function getAll($user_id) {
        // SQL query
        $query = "SELECT i.*, t.name as type_name, t.risk_level 
                  FROM " . $this->table . " i
                  JOIN " . $this->types_table . " t ON i.type_id = t.type_id
                  WHERE i.user_id = ? 
                  ORDER BY i.purchase_date DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get investment by ID
    public function getById($investment_id, $user_id) {
        // SQL query
        $query = "SELECT i.*, t.name as type_name, t.risk_level 
                  FROM " . $this->table . " i
                  JOIN " . $this->types_table . " t ON i.type_id = t.type_id
                  WHERE i.investment_id = ? AND i.user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $investment_id, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
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
    }
    
    // Update investment
    public function update() {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET type_id = ?, name = ?, ticker_symbol = ?, 
                      purchase_date = ?, purchase_price = ?, quantity = ?, 
                      current_price = ?, notes = ? 
                  WHERE investment_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->type_id = htmlspecialchars(strip_tags($this->type_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->purchase_price = htmlspecialchars(strip_tags($this->purchase_price));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->current_price = htmlspecialchars(strip_tags($this->current_price));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind parameters
        $stmt->bind_param("isssdddsii", 
                          $this->type_id, 
                          $this->name, 
                          $this->ticker_symbol, 
                          $this->purchase_date, 
                          $this->purchase_price, 
                          $this->quantity, 
                          $this->current_price, 
                          $this->notes, 
                          $this->investment_id, 
                          $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Update current price
    public function updatePrice($investment_id, $user_id, $current_price) {
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET current_price = ?, last_updated = NOW() 
                  WHERE investment_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $current_price = htmlspecialchars(strip_tags($current_price));
        
        // Bind parameters
        $stmt->bind_param("dii", $current_price, $investment_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete investment
    public function delete($investment_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->table . " 
                  WHERE investment_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $investment_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get investment summary
    public function getSummary($user_id) {
        // Get all investments
        $investments = $this->getAll($user_id);
        
        $summary = array(
            'total_invested' => 0,
            'current_value' => 0,
            'total_gain_loss' => 0,
            'percent_gain_loss' => 0,
            'by_type' => array(),
            'by_risk' => array(),
            'top_performers' => array(),
            'worst_performers' => array()
        );
        
        $all_investments = array();
        
        while ($investment = $investments->fetch_assoc()) {
            $invested = $investment['purchase_price'] * $investment['quantity'];
            $current = $investment['current_price'] * $investment['quantity'];
            $gain_loss = $current - $invested;
            $percent_gain_loss = ($invested > 0) ? ($gain_loss / $invested) * 100 : 0;
            
            $all_investments[] = array(
                'id' => $investment['investment_id'],
                'name' => $investment['name'],
                'ticker' => $investment['ticker_symbol'],
                'type' => $investment['type_name'],
                'risk' => $investment['risk_level'],
                'invested' => $invested,
                'current' => $current,
                'gain_loss' => $gain_loss,
                'percent_gain_loss' => $percent_gain_loss
            );
            
            // Add to summary totals
            $summary['total_invested'] += $invested;
            $summary['current_value'] += $current;
            
            // Group by type
            if (!isset($summary['by_type'][$investment['type_name']])) {
                $summary['by_type'][$investment['type_name']] = array(
                    'invested' => 0,
                    'current' => 0,
                    'gain_loss' => 0
                );
            }
            $summary['by_type'][$investment['type_name']]['invested'] += $invested;
            $summary['by_type'][$investment['type_name']]['current'] += $current;
            $summary['by_type'][$investment['type_name']]['gain_loss'] += $gain_loss;
            
            // Group by risk
            if (!isset($summary['by_risk'][$investment['risk_level']])) {
                $summary['by_risk'][$investment['risk_level']] = array(
                    'invested' => 0,
                    'current' => 0,
                    'gain_loss' => 0
                );
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
        $summary['top_performers'] = array_slice($all_investments, 0, 5);
        
        // Get worst 5 performers
        $worst = array_slice($all_investments, -5);
        $summary['worst_performers'] = array_reverse($worst);
        
        return $summary;
    }
    
    // Add stock to watchlist
    public function addToWatchlist() {
        // SQL query
        $query = "INSERT INTO " . $this->watchlist_table . " 
                  (user_id, ticker_symbol, company_name, target_buy_price, target_sell_price, current_price, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->ticker_symbol = htmlspecialchars(strip_tags($this->ticker_symbol));
        $this->name = htmlspecialchars(strip_tags($this->name)); // Company name
        $target_buy_price = htmlspecialchars(strip_tags($_POST['target_buy_price'] ?? 0));
        $target_sell_price = htmlspecialchars(strip_tags($_POST['target_sell_price'] ?? 0));
        $this->current_price = htmlspecialchars(strip_tags($this->current_price));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind parameters
        $stmt->bind_param("issddds", 
                          $this->user_id, 
                          $this->ticker_symbol, 
                          $this->name, 
                          $target_buy_price, 
                          $target_sell_price, 
                          $this->current_price, 
                          $this->notes);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get watchlist for a user
    public function getWatchlist($user_id) {
        // SQL query
        $query = "SELECT * FROM " . $this->watchlist_table . " 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Remove stock from watchlist
    public function removeFromWatchlist($watchlist_id, $user_id) {
        // SQL query
        $query = "DELETE FROM " . $this->watchlist_table . " 
                  WHERE watchlist_id = ? AND user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ii", $watchlist_id, $user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Calculate optimal buy/sell points for a stock
    public function calculateBuySellPoints($ticker, $price_history) {
        // This is a simplified implementation
        // In a real application, this would use more sophisticated algorithms
        
        if (empty($price_history) || count($price_history) < 20) {
            return array(
                'status' => 'error',
                'message' => 'Insufficient price history data'
            );
        }
        
        // Calculate moving averages
        $short_ma = $this->calculateMovingAverage($price_history, 20);
        $long_ma = $this->calculateMovingAverage($price_history, 50);
        
        // Calculate current price
        $current_price = end($price_history)['close'];
        
        // Calculate support and resistance levels
        $support = $this->calculateSupport($price_history, 20);
        $resistance = $this->calculateResistance($price_history, 20);
        
        // Calculate RSI
        $rsi = $this->calculateRSI($price_history, 14);
        
        // Determine recommendation
        $recommendation = '';
        $buy_points = array();
        $sell_points = array();
        
        // Simple moving average crossover strategy
        if ($short_ma > $long_ma && $short_ma - $long_ma < $long_ma * 0.02) {
            // Golden cross - recent crossover
            $recommendation = 'buy';
            $buy_points[] = array(
                'price' => $current_price,
                'reason' => 'Golden cross (short-term MA crossed above long-term MA)'
            );
        } elseif ($short_ma < $long_ma && $long_ma - $short_ma < $long_ma * 0.02) {
            // Death cross - recent crossover
            $recommendation = 'sell';
            $sell_points[] = array(
                'price' => $current_price,
                'reason' => 'Death cross (short-term MA crossed below long-term MA)'
            );
        }
        
        // Support and resistance levels
        if ($current_price < $support * 1.05) {
            $buy_points[] = array(
                'price' => $support,
                'reason' => 'Near support level'
            );
        }
        
        if ($current_price > $resistance * 0.95) {
            $sell_points[] = array(
                'price' => $resistance,
                'reason' => 'Near resistance level'
            );
        }
        
        // RSI based recommendations
        if ($rsi < 30) {
            $buy_points[] = array(
                'price' => $current_price,
                'reason' => 'Oversold (RSI below 30)'
            );
        } elseif ($rsi > 70) {
            $sell_points[] = array(
                'price' => $current_price,
                'reason' => 'Overbought (RSI above 70)'
            );
        }
        
        // If no clear recommendation, use the strongest signal
        if (empty($recommendation)) {
            if (count($buy_points) > count($sell_points)) {
                $recommendation = 'buy';
            } elseif (count($sell_points) > count($buy_points)) {
                $recommendation = 'sell';
            } else {
                $recommendation = 'hold';
            }
        }
        
        return array(
            'status' => 'success',
            'ticker' => $ticker,
            'current_price' => $current_price,
            'short_ma' => $short_ma,
            'long_ma' => $long_ma,
            'support' => $support,
            'resistance' => $resistance,
            'rsi' => $rsi,
            'recommendation' => $recommendation,
            'buy_points' => $buy_points,
            'sell_points' => $sell_points
        );
    }
    
    // Calculate moving average
    private function calculateMovingAverage($price_history, $period) {
        $prices = array_slice($price_history, -$period);
        $sum = 0;
        
        foreach ($prices as $price) {
            $sum += $price['close'];
        }
        
        return $sum / count($prices);
    }
    
    // Calculate support level (simplified)
    private function calculateSupport($price_history, $period) {
        $prices = array_slice($price_history, -$period);
        $lows = array_column($prices, 'low');
        
        return min($lows);
    }
    
    // Calculate resistance level (simplified)
    private function calculateResistance($price_history, $period) {
        $prices = array_slice($price_history, -$period);
        $highs = array_column($prices, 'high');
        
        return max($highs);
    }
    
    // Calculate RSI (Relative Strength Index)
    private function calculateRSI($price_history, $period) {
        $prices = array_slice($price_history, -($period + 1));
        $gains = 0;
        $losses = 0;
        
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i]['close'] - $prices[$i-1]['close'];
            
            if ($change >= 0) {
                $gains += $change;
            } else {
                $losses -= $change;
            }
        }
        
        $avg_gain = $gains / $period;
        $avg_loss = $losses / $period;
        
        if ($avg_loss == 0) {
            return 100;
        }
        
        $rs = $avg_gain / $avg_loss;
        $rsi = 100 - (100 / (1 + $rs));
        
        return $rsi;
    }
    
    // Record transaction for investment
    private function recordTransaction() {
        // SQL query
        $query = "INSERT INTO " . $this->transactions_table . " 
                  (user_id, type, amount, description, transaction_date, investment_id) 
                  VALUES (?, 'investment', ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Calculate total investment amount
        $amount = $this->purchase_price * $this->quantity;
        
        // Set description
        $description = "Investment in: " . $this->name;
        if (!empty($this->ticker_symbol)) {
            $description .= " (" . $this->ticker_symbol . ")";
        }
        
        // Bind parameters
        $stmt->bind_param("idssi", 
                          $this->user_id, 
                          $amount, 
                          $description, 
                          $this->purchase_date, 
                          $this->investment_id);
        
        // Execute query
        $stmt->execute();
    }
}
?>