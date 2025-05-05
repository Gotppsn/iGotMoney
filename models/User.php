<?php
/**
 * User Model
 * 
 * Handles user-related database operations
 */

require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    // User properties
    public $user_id;
    public $username;
    public $password;
    public $email;
    public $first_name;
    public $last_name;
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
    
    // Register new user
    public function register() {
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // SQL query
        $query = "INSERT INTO " . $this->table . " (username, password, email, first_name, last_name)
                  VALUES (?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("sssss", $this->username, $this->password, $this->email, $this->first_name, $this->last_name);
        
        // Execute query
        if ($stmt->execute()) {
            $this->user_id = $this->conn->insert_id;
            $this->createUserSettings($this->user_id);
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Login user
    public function login($username, $password) {
        // Sanitize input
        $username = htmlspecialchars(strip_tags($username));
        
        // SQL query
        $query = "SELECT user_id, username, password, email, first_name, last_name FROM " . $this->table . " WHERE username = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("s", $username);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set properties
                $this->user_id = $user['user_id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->first_name = $user['first_name'];
                $this->last_name = $user['last_name'];
                
                return true;
            }
        }
        
        return false;
    }
    
    // Get user by ID
    public function getUserById($id) {
        // SQL query
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bind_param("i", $id);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Set properties
            $this->user_id = $user['user_id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];
            $this->created_at = $user['created_at'];
            $this->updated_at = $user['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Update user information
    public function update() {
        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        
        // SQL query
        $query = "UPDATE " . $this->table . " 
                  SET email = ?, first_name = ?, last_name = ? 
                  WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("sssi", $this->email, $this->first_name, $this->last_name, $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Change password
    public function changePassword($current_password, $new_password) {
        // Verify current password
        $query = "SELECT password FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($current_password, $user['password'])) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $update_query = "UPDATE " . $this->table . " SET password = ? WHERE user_id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bind_param("si", $hashed_password, $this->user_id);
                
                if ($update_stmt->execute()) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Create default user settings
    private function createUserSettings($user_id) {
        $query = "INSERT INTO user_settings (user_id) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}
?>