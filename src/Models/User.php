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

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername($username) {
        // Buscar por email o name para compatibilidad
        $query = "SELECT id, email, name, password, role, active FROM " . $this->table_name . " WHERE email = ? OR name = ? LIMIT 0,1";
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
            return true;
        }
        return false;
    }
}
