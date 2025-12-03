<?php

class LoginAttempt {
    private $conn;
    private $table_name = "login_attempts";

    public $id;
    public $username;
    public $ip_address;
    public $attempted_at;
    public $success;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Record a login attempt
     */
    public function recordAttempt($username, $ipAddress, $success = false) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, ip_address, success, attempted_at) 
                  VALUES (:username, :ip_address, :success, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Get recent failed attempts for a username
     */
    public function getRecentFailedAttempts($username, $minutes = 15) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  AND success = 0 
                  AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    /**
     * Clear all attempts for a username (after successful login)
     */
    public function clearAttempts($username) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);

        return $stmt->execute();
    }

    /**
     * Clean up old attempt records
     */
    public function cleanOldAttempts($days = 30) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE attempted_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get all attempts for a username (for audit purposes)
     */
    public function getAttemptHistory($username, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE username = :username 
                  ORDER BY attempted_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}
