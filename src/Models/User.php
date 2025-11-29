<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $name;
    public $password;
    public $role;
    public $active;
    public $locked_until;
    public $failed_attempts;
    public $username;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername($username) {
        // Buscar por email o name para compatibilidad
        $query = "SELECT id, email, name, password, role, active, locked_until, failed_attempts FROM " . $this->table_name . " WHERE email = ? OR name = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->name = $row['name'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->active = $row['active'];
            $this->locked_until = $row['locked_until'];
            $this->failed_attempts = $row['failed_attempts'];
            $this->username = $username;
            return true;
        }
        return false;
    }
    
    /**
     * Update user lockout information
     */
    public function updateLockout() {
        $query = "UPDATE " . $this->table_name . " 
                  SET locked_until = :locked_until, 
                      failed_attempts = :failed_attempts 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':locked_until', $this->locked_until);
        $stmt->bindParam(':failed_attempts', $this->failed_attempts, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
