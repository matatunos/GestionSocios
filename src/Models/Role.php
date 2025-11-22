<?php

class Role {
    private $conn;
    private $table = 'roles';
    
    public $id;
    public $name;
    public $display_name;
    public $description;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all roles
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single role
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->display_name = $row['display_name'];
            $this->description = $row['description'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }
    
    // Get permissions for role
    public function getPermissions() {
        $query = "SELECT p.* FROM permissions p
                  INNER JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role_id = :role_id
                  ORDER BY p.module ASC, p.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get permissions grouped by module
    public function getPermissionsByModule() {
        $permissions = $this->getPermissions();
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
    
    // Check if role has specific permission
    public function hasPermission($permissionName) {
        $query = "SELECT COUNT(*) as count FROM role_permissions rp
                  INNER JOIN permissions p ON rp.permission_id = p.id
                  WHERE rp.role_id = :role_id AND p.name = :permission_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $this->id);
        $stmt->bindParam(':permission_name', $permissionName);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
