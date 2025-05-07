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
    private $last_error = null;
    
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
        try {
            $this->conn = connectDB();
            if (!$this->conn) {
                throw new Exception("Failed to connect to the database");
            }
        } catch (Exception $e) {
            $this->last_error = "Database connection error: " . $e->getMessage();
            error_log($this->last_error);
        }
    }
    
    // Destructor
    public function __destruct() {
        if ($this->conn) {
            closeDB($this->conn);
        }
    }
    
    /**
     * Get the last error message
     * @return string|null The last error message or null if no error
     */
    public function getLastError() {
        return $this->last_error;
    }
    
    // Create a new investment
    public function create() {
        try {
            // Validate required fields
            if (empty($this->user_id) || empty($this->type_id) || empty($this->name) || 
                empty($this->purchase_date) || empty($this->purchase_price) || empty($this->quantity)) {
                $this->last_error = "Missing required fields";
                error_log("Investment create: " . $this->last_error);
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
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
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            // Set the newly created investment ID
            $this->investment_id = $this->conn->insert_id;
            
            // Add to transactions table
            $this->addToTransactions();
            
            return true;
        } catch (Exception $e) {
            $this->last_error = "Error in create: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Update an investment
    public function update() {
        try {
            // Validate required fields
            if (empty($this->investment_id) || empty($this->user_id)) {
                $this->last_error = "Missing required fields";
                error_log("Investment update: " . $this->last_error);
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
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
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $this->last_error = "Error in update: " . $e->getMessage();
            error_log($this->last_error);
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
                $this->last_error = "Invalid investment ID or user ID";
                return false;
            }
            
            // Delete associated transactions first
            $query = "DELETE FROM " . $this->transactions_table . " 
                      WHERE investment_id = ? AND user_id = ?";
                      
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            $stmt->bind_param("ii", $investment_id, $user_id);
            $stmt->execute();
            
            // Now delete the investment
            $query = "DELETE FROM " . $this->table . " 
                      WHERE investment_id = ? AND user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            $stmt->bind_param("ii", $investment_id, $user_id);
            
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $this->last_error = "Error in delete: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Get investment by ID
    public function getById($investment_id, $user_id) {
        try {
            // Validate input
            $investment_id = intval($investment_id);
            $user_id = intval($user_id);
            
            if ($investment_id <= 0 || $user_id <= 0) {
                $this->last_error = "Invalid investment ID or user ID";
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $investment_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
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
            
            $this->last_error = "Investment not found";
            return false;
        } catch (Exception $e) {
            $this->last_error = "Error in getById: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Get all investments for a user
    public function getAll($user_id) {
        try {
            // Validate input
            $user_id = intval($user_id);
            
            if ($user_id <= 0) {
                $this->last_error = "Invalid user ID";
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Bind parameter
            $stmt->bind_param("i", $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            
            // Check if there's a result
            if (!$result) {
                $this->last_error = "Failed to get result set: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            return $result;
        } catch (Exception $e) {
            $this->last_error = "Error in getAll: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Get all investment types
    public function getAllTypes() {
        try {
            // SQL query
            $query = "SELECT * FROM " . $this->types_table . " ORDER BY risk_level";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            return $result;
        } catch (Exception $e) {
            $this->last_error = "Error in getAllTypes: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Update investment price
    public function updatePrice($investment_id, $user_id, $current_price) {
        try {
            // Validate inputs
            $investment_id = intval($investment_id);
            $user_id = intval($user_id);
            $current_price = floatval($current_price);
            
            if ($investment_id <= 0 || $user_id <= 0 || $current_price < 0) {
                $this->last_error = "Invalid investment ID, user ID, or price";
                return false;
            }
            
            // SQL query
            $query = "UPDATE " . $this->table . " 
                    SET current_price = ?, last_updated = NOW() 
                    WHERE investment_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("dii", $current_price, $investment_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                return true;
            }
            
            $this->last_error = "No investment updated";
            return false;
        } catch (Exception $e) {
            $this->last_error = "Error in updatePrice: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
    
    // Get investment summary
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
            $this->last_error = "Error in getSummary: " . $e->getMessage();
            error_log($this->last_error);
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
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
                $this->last_error = "Execute error for transaction: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            $this->last_error = "Error in addToTransactions: " . $e->getMessage();
            error_log($this->last_error);
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
                $this->last_error = "Watchlist add: Missing required fields";
                error_log($this->last_error);
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
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
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
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            $this->last_error = "Error in addToWatchlist: " . $e->getMessage();
            error_log($this->last_error);
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
                $this->last_error = "Invalid watchlist ID or user ID";
                return false;
            }
            
            // Prepare query
            $query = "DELETE FROM " . $this->watchlist_table . " 
                      WHERE watchlist_id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param("ii", $watchlist_id, $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $this->last_error = "Error in removeFromWatchlist: " . $e->getMessage();
            error_log($this->last_error);
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
                $this->last_error = "Invalid user ID";
                return false;
            }
            
            // SQL query
            $query = "SELECT * FROM " . $this->watchlist_table . " 
                      WHERE user_id = ? 
                      ORDER BY ticker_symbol ASC";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->last_error = "Database prepare error: " . $this->conn->error;
                error_log($this->last_error);
                return false;
            }
            
            // Bind parameter
            $stmt->bind_param("i", $user_id);
            
            // Execute query
            if (!$stmt->execute()) {
                $this->last_error = "Execute error: " . $stmt->error;
                error_log($this->last_error);
                return false;
            }
            
            // Get result
            $result = $stmt->get_result();
            return $result;
        } catch (Exception $e) {
            $this->last_error = "Error in getWatchlist: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
}
?>