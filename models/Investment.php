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
}
?>