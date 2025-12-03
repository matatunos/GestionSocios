<?php

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $configFile = __DIR__ . '/config.php';
        if (!file_exists($configFile)) {
            return null; // Trigger installer
        }

        require_once $configFile;

        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error for debugging
            error_log("DB Connection Error: " . $exception->getMessage());
            // Connection failed
            return null;
        }

        return $this->conn;
    }
}
