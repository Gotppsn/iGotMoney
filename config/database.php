<?php
/**
 * Database Connection Configuration
 * 
 * Configuration file for establishing connection to the MySQL database.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', ''); // Use your actual MySQL username here
define('DB_PASSWORD', '');

// Create connection with improved error handling
function connectDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            $error_message = "Database connection failed: " . $conn->connect_error;
            error_log($error_message);
            throw new Exception($error_message);
        }
        
        // Set charset to ensure proper handling of special characters
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e; // Re-throw to allow proper handling by calling code
    }
}

// Close connection
function closeDB($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}

// Test connection function to use for debugging
function testDatabaseConnection() {
    try {
        $conn = connectDB();
        $result = "Connection successful to database: " . DB_NAME;
        closeDB($conn);
        return ['success' => true, 'message' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
