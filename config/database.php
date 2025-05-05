<?php
/**
 * Database Connection Configuration
 * 
 * Configuration file for establishing connection to the MySQL database.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'gotppsnc_igotmoney');
define('DB_USER', 'gotppsnc_Panupol'); // Use your actual MySQL username here
define('DB_PASSWORD', '039464@Got');

// Create connection
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Close connection
function closeDB($conn) {
    $conn->close();
}
?>