<?php

class Permission {
    private $conn;
    private $table = 'permissions';
    
    public $id;
    public $name;
    public $display_name;
    public $description;
    public $module;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all permissions
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY module ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get permissions grouped by module
    public static function getAllGroupedByModule($db) {
        $query = "SELECT * FROM permissions ORDER BY module ASC, name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    // Get permission by name
    public function findByName($name) {
        $query = "SELECT * FROM " . $this->table . " WHERE name = :name LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->display_name = $row['display_name'];
            $this->description = $row['description'];
            $this->module = $row['module'];
            return true;
        }
        return false;
    }
}
