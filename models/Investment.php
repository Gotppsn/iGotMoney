<?php
/**
 * Investment Model
 * 
 * Handles investment-related database operations and analysis
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
            
            // If has ticker symbol, add to watchlist if not already present
            if (!empty($this->ticker_symbol)) {
                $this->addToWatchlist();
            }
            
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
            
            // If has ticker symbol, add to watchlist if not already present
            if (!empty($this->ticker_symbol)) {
                $this->addToWatchlist();
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
                // Get the ticker symbol for this investment
                if ($this->getById($investment_id, $user_id)) {
                    // Update watchlist if ticker exists
                    if (!empty($this->ticker_symbol)) {
                        $this->updateWatchlistPrice($this->ticker_symbol, $current_price);
                    }
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in updatePrice: " . $e->getMessage());
            return false;
        }
    }
    
    // Update all prices for investments with ticker symbols
    public function updateAllPrices($user_id) {
        try {
            // Validate inputs
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return false;
            }
            
            // Get all investments with ticker symbols
            $query = "SELECT investment_id, ticker_symbol FROM " . $this->table . " 
                      WHERE user_id = ? AND ticker_symbol IS NOT NULL AND ticker_symbol != ''";
            
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
            $updated_count = 0;
            
            while ($row = $result->fetch_assoc()) {
                $investment_id = $row['investment_id'];
                $ticker_symbol = $row['ticker_symbol'];
                
                // Get current price from external API (mock for now)
                $current_price = $this->fetchCurrentPrice($ticker_symbol);
                
                if ($current_price > 0) {
                    // Update price
                    if ($this->updatePrice($investment_id, $user_id, $current_price)) {
                        $updated_count++;
                    }
                }
            }
            
            return $updated_count;
        } catch (Exception $e) {
            error_log("Error in updateAllPrices: " . $e->getMessage());
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
    
    // Get performance data for portfolio over time
    public function getPerformanceData($user_id, $period = '1y') {
        try {
            // Validate inputs
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return null;
            }
            
            // Determine the date range based on period
            $end_date = date('Y-m-d');
            $start_date = '';
            
            switch ($period) {
                case '1m':
                    $start_date = date('Y-m-d', strtotime('-1 month'));
                    break;
                case '3m':
                    $start_date = date('Y-m-d', strtotime('-3 months'));
                    break;
                case '6m':
                    $start_date = date('Y-m-d', strtotime('-6 months'));
                    break;
                case '1y':
                    $start_date = date('Y-m-d', strtotime('-1 year'));
                    break;
                case 'ytd':
                    $start_date = date('Y-01-01');
                    break;
                case 'all':
                    // Get the earliest purchase date
                    $query = "SELECT MIN(purchase_date) as earliest FROM " . $this->table . " WHERE user_id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $earliest = $result->fetch_assoc()['earliest'];
                    $start_date = $earliest ?? date('Y-m-d', strtotime('-2 years'));
                    break;
                default:
                    $start_date = date('Y-m-d', strtotime('-1 year'));
            }
            
            // In a real application, we would query historical data from a price history table
            // For demo purposes, we'll generate mock data
            
            return $this->generateMockPerformanceData($start_date, $end_date);
            
        } catch (Exception $e) {
            error_log("Error in getPerformanceData: " . $e->getMessage());
            return null;
        }
    }
    
    // Get stock watchlist for a user
    public function getWatchlist($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return false;
            }
            
            // SQL query
            $query = "SELECT * FROM " . $this->watchlist_table . " 
                      WHERE user_id = ? ORDER BY ticker_symbol ASC";
            
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
    
    // Add a stock to the watchlist
    public function addToWatchlist() {
        try {
            // Check if ticker already exists in watchlist
            $query = "SELECT watchlist_id FROM " . $this->watchlist_table . " 
                      WHERE user_id = ? AND ticker_symbol = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("is", $this->user_id, $this->ticker_symbol);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // If already exists, update current price
            if ($result->num_rows > 0) {
                $this->updateWatchlistPrice($this->ticker_symbol, $this->current_price);
                return true;
            }
            
            // If not exists, add to watchlist
            $query = "INSERT INTO " . $this->watchlist_table . " 
                     (user_id, ticker_symbol, company_name, current_price, last_updated)
                     VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("issd", $this->user_id, $this->ticker_symbol, $this->name, $this->current_price);
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in addToWatchlist: " . $e->getMessage());
            return false;
        }
    }
    
    // Update watchlist price
    public function updateWatchlistPrice($ticker_symbol, $current_price) {
        try {
            $query = "UPDATE " . $this->watchlist_table . " 
                     SET current_price = ?, last_updated = NOW()
                     WHERE user_id = ? AND ticker_symbol = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("dis", $current_price, $this->user_id, $ticker_symbol);
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in updateWatchlistPrice: " . $e->getMessage());
            return false;
        }
    }
    
    // Set price targets for a stock in the watchlist
    public function setPriceTargets($ticker_symbol, $user_id, $buy_price, $sell_price) {
        try {
            $query = "UPDATE " . $this->watchlist_table . " 
                     SET target_buy_price = ?, target_sell_price = ?
                     WHERE user_id = ? AND ticker_symbol = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("ddis", $buy_price, $sell_price, $user_id, $ticker_symbol);
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in setPriceTargets: " . $e->getMessage());
            return false;
        }
    }
    
    // Get ROI (Return on Investment) data
    public function getROIData($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return null;
            }
            
            // Get all investments
            $investments = $this->getAll($user_id);
            
            if (!$investments || $investments->num_rows === 0) {
                return [
                    'average_annual_roi' => 0,
                    'best_performing' => null,
                    'worst_performing' => null,
                    'cagr' => 0,
                    'investments_roi' => []
                ];
            }
            
            $total_invested = 0;
            $total_current = 0;
            $total_days = 0;
            $investments_roi = [];
            $best_performing = null;
            $worst_performing = null;
            $best_roi = -9999;
            $worst_roi = 9999;
            
            // Loop through investments
            while ($investment = $investments->fetch_assoc()) {
                // Calculate invested and current values
                $invested = floatval($investment['purchase_price']) * floatval($investment['quantity']);
                $current = floatval($investment['current_price']) * floatval($investment['quantity']);
                
                // Calculate days held
                $purchase_date = new DateTime($investment['purchase_date']);
                $today = new DateTime();
                $days_held = $purchase_date->diff($today)->days;
                
                // Calculate ROI
                $roi = $invested > 0 ? (($current - $invested) / $invested) * 100 : 0;
                
                // Calculate annualized ROI
                $years_held = max(0.01, $days_held / 365); // Avoid division by zero
                $annualized_roi = (pow(1 + ($roi / 100), 1 / $years_held) - 1) * 100;
                
                // Add to arrays
                $investments_roi[] = [
                    'id' => $investment['investment_id'],
                    'name' => $investment['name'],
                    'ticker' => $investment['ticker_symbol'],
                    'type' => $investment['type_name'],
                    'days_held' => $days_held,
                    'years_held' => $years_held,
                    'invested' => $invested,
                    'current' => $current,
                    'roi' => $roi,
                    'annualized_roi' => $annualized_roi
                ];
                
                // Update best/worst performers
                if ($roi > $best_roi) {
                    $best_roi = $roi;
                    $best_performing = [
                        'name' => $investment['name'],
                        'ticker' => $investment['ticker_symbol'],
                        'roi' => $roi,
                        'annualized_roi' => $annualized_roi
                    ];
                }
                
                if ($roi < $worst_roi) {
                    $worst_roi = $roi;
                    $worst_performing = [
                        'name' => $investment['name'],
                        'ticker' => $investment['ticker_symbol'],
                        'roi' => $roi,
                        'annualized_roi' => $annualized_roi
                    ];
                }
                
                // Add to totals
                $total_invested += $invested;
                $total_current += $current;
                $total_days += $days_held;
            }
            
            // Calculate portfolio average annual ROI
            $avg_days_held = count($investments_roi) > 0 ? $total_days / count($investments_roi) : 0;
            $avg_years_held = max(0.01, $avg_days_held / 365); // Avoid division by zero
            
            // Calculate overall ROI for portfolio
            $portfolio_roi = $total_invested > 0 ? (($total_current - $total_invested) / $total_invested) * 100 : 0;
            
            // Calculate CAGR (Compound Annual Growth Rate)
            $cagr = (pow(($total_current / $total_invested), (1 / $avg_years_held)) - 1) * 100;
            
            // Calculate average annual ROI (simple average of annualized ROIs)
            $total_annual_roi = 0;
            foreach ($investments_roi as $inv) {
                $total_annual_roi += $inv['annualized_roi'];
            }
            $average_annual_roi = count($investments_roi) > 0 ? $total_annual_roi / count($investments_roi) : 0;
            
            return [
                'average_annual_roi' => $average_annual_roi,
                'portfolio_roi' => $portfolio_roi,
                'best_performing' => $best_performing,
                'worst_performing' => $worst_performing,
                'cagr' => $cagr,
                'investments_roi' => $investments_roi
            ];
            
        } catch (Exception $e) {
            error_log("Error in getROIData: " . $e->getMessage());
            return null;
        }
    }
    
    // Get stock analysis for investments with ticker symbols
    public function getStockAnalysis($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                return null;
            }
            
            // Get investments with ticker symbols
            $query = "SELECT i.*, t.name as type_name, t.risk_level, w.target_buy_price, w.target_sell_price 
                     FROM " . $this->table . " i
                     JOIN " . $this->types_table . " t ON i.type_id = t.type_id
                     LEFT JOIN " . $this->watchlist_table . " w ON i.ticker_symbol = w.ticker_symbol AND i.user_id = w.user_id
                     WHERE i.user_id = ? AND i.ticker_symbol IS NOT NULL AND i.ticker_symbol != ''
                     ORDER BY i.name ASC";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return null;
            }
            
            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $stocks = [];
            
            while ($investment = $result->fetch_assoc()) {
                // Get analysis data (in a real app, this would use real analysis methods)
                $analysis = $this->calculateStockAnalysis(
                    $investment['ticker_symbol'], 
                    $investment['current_price'],
                    $investment['target_buy_price'] ?? null,
                    $investment['target_sell_price'] ?? null
                );
                
                $stocks[] = [
                    'id' => $investment['investment_id'],
                    'name' => $investment['name'],
                    'ticker' => $investment['ticker_symbol'],
                    'current_price' => $investment['current_price'],
                    'fair_value' => $analysis['fair_value'],
                    'buy_target' => $analysis['buy_target'],
                    'sell_target' => $analysis['sell_target'],
                    'recommendation' => $analysis['recommendation'],
                    'analysis_date' => date('Y-m-d')
                ];
            }
            
            return $stocks;
            
        } catch (Exception $e) {
            error_log("Error in getStockAnalysis: " . $e->getMessage());
            return null;
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
     * Mock function to fetch current price from an external API
     * In a real application, this would connect to a stock market API
     * 
     * @param string $ticker_symbol The stock ticker symbol
     * @return float The current price
     */
    private function fetchCurrentPrice($ticker_symbol) {
        // For demo purposes, generate a random price based on ticker
        $base_price = 0;
        for ($i = 0; $i < strlen($ticker_symbol); $i++) {
            $base_price += ord($ticker_symbol[$i]);
        }
        $base_price = ($base_price % 300) + 20; // Between $20 and $320
        
        // Add some randomness
        $price = $base_price * (1 + ((mt_rand(-50, 50) / 1000)));
        
        return round($price, 2);
    }
    
    /**
     * Generate mock performance data for portfolio
     * 
     * @param string $start_date The start date (Y-m-d)
     * @param string $end_date The end date (Y-m-d)
     * @return array Performance data
     */
    private function generateMockPerformanceData($start_date, $end_date) {
        $data = [
            'dates' => [],
            'values' => []
        ];
        
        // Convert dates to timestamps
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        // Generate data points (daily)
        $current_timestamp = $start_timestamp;
        $value = 10000; // Starting value
        $volatility = 0.01; // Daily volatility
        $trend = 0.0002; // Daily upward trend
        
        while ($current_timestamp <= $end_timestamp) {
            // Format date
            $date_str = date('Y-m-d', $current_timestamp);
            
            // Add to arrays
            $data['dates'][] = $date_str;
            $data['values'][] = round($value, 2);
            
            // Generate next value with random fluctuation and trend
            $random_change = (mt_rand(-100, 100) / 100) * $volatility;
            $value = $value * (1 + $random_change + $trend);
            
            // Move to next day
            $current_timestamp = strtotime('+1 day', $current_timestamp);
        }
        
        return $data;
    }
    
    /**
     * Calculate stock analysis
     * 
     * @param string $ticker Stock ticker symbol
     * @param float $current_price Current stock price
     * @param float $target_buy_price Target buy price (or null)
     * @param float $target_sell_price Target sell price (or null)
     * @return array Analysis data
     */
    private function calculateStockAnalysis($ticker, $current_price, $target_buy_price = null, $target_sell_price = null) {
        // In a real app, this would use fundamental and technical analysis
        // For demo purposes, we'll use simple calculations
        
        // Generate a consistent fair value based on ticker
        $seed_value = 0;
        for ($i = 0; $i < strlen($ticker); $i++) {
            $seed_value += ord($ticker[$i]);
        }
        mt_srand($seed_value);
        
        // Calculate fair value with slight randomization
        $fair_value = $current_price * (1 + ((mt_rand(-20, 40) / 100)));
        
        // Use existing targets if provided, otherwise calculate
        $buy_target = $target_buy_price ?? $fair_value * 0.85; // 15% below fair value
        $sell_target = $target_sell_price ?? $fair_value * 1.20; // 20% above fair value
        
        // Determine recommendation
        $recommendation = $this->determineRecommendation($current_price, $fair_value, $buy_target, $sell_target);
        
        return [
            'fair_value' => round($fair_value, 2),
            'buy_target' => round($buy_target, 2),
            'sell_target' => round($sell_target, 2),
            'recommendation' => $recommendation
        ];
    }
    
    /**
     * Determine stock recommendation
     * 
     * @param float $current_price Current stock price
     * @param float $fair_value Estimated fair value
     * @param float $buy_target Target buy price
     * @param float $sell_target Target sell price
     * @return string Recommendation
     */
    private function determineRecommendation($current_price, $fair_value, $buy_target, $sell_target) {
        if ($current_price < $buy_target * 0.9) {
            return 'Strong Buy';
        } else if ($current_price < $buy_target) {
            return 'Buy';
        } else if ($current_price > $sell_target * 1.1) {
            return 'Strong Sell';
        } else if ($current_price > $sell_target) {
            return 'Sell';
        } else {
            return 'Hold';
        }
    }
}
?>