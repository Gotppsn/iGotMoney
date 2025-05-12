<?php
/**
 * Base Model Class
 * 
 * Provides common functionality for all models including database connection
 */

class BaseModel {
    /**
     * Connect to the database
     * 
     * @return mysqli Database connection
     */
    protected function connectDB() {
        // Include database configuration if not already included
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        try {
            return connectDB();
        } catch (Exception $e) {
            error_log("Database connection error in BaseModel: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Close the database connection
     * 
     * @param mysqli $conn Database connection
     * @return void
     */
    protected function closeDB($conn) {
        if ($conn instanceof mysqli) {
            closeDB($conn);
        }
    }
    
    /**
     * Execute a database query with error handling
     * 
     * @param string $query SQL query
     * @param array $params Parameters for prepared statement
     * @param string $types Types of parameters (i: integer, s: string, d: double, b: blob)
     * @return mysqli_result|bool Query result or false on failure
     */
    protected function executeQuery($query, $params = [], $types = '') {
        $conn = $this->connectDB();
        
        try {
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Error preparing statement: " . $conn->error);
                $this->closeDB($conn);
                return false;
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                error_log("Error executing statement: " . $stmt->error);
                $this->closeDB($conn);
                return false;
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            $this->closeDB($conn);
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in executeQuery: " . $e->getMessage());
            $this->closeDB($conn);
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     * 
     * @return int Last inserted ID
     */
    protected function getLastInsertId() {
        $conn = $this->connectDB();
        $lastId = $conn->insert_id;
        $this->closeDB($conn);
        return $lastId;
    }
    
    /**
     * Format date for SQL
     * 
     * @param string $date Date string
     * @return string Formatted date for SQL
     */
    protected function formatDate($date) {
        return date('Y-m-d', strtotime($date));
    }
    
    /**
     * Format datetime for SQL
     * 
     * @param string $datetime Datetime string
     * @return string Formatted datetime for SQL
     */
    protected function formatDateTime($datetime) {
        return date('Y-m-d H:i:s', strtotime($datetime));
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $input Input string
     * @return string Sanitized string
     */
    protected function sanitizeInput($input) {
        $conn = $this->connectDB();
        $sanitized = $conn->real_escape_string($input);
        $this->closeDB($conn);
        return $sanitized;
    }
}
?>