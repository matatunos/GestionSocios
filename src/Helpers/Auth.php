<?php

class Auth {
    private static $db = null;
    
    private static function getDb() {
        if (self::$db === null) {
            self::$db = (new Database())->getConnection();
        }
        return self::$db;
    }
    
    // Check if user has specific permission
    public static function hasPermission($permissionName) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Get user's role_id from session or database
        if (!isset($_SESSION['role_id'])) {
            self::loadUserRole();
        }
        
        if (!isset($_SESSION['role_id'])) {
            return false;
        }
        
        $db = self::getDb();
        $query = "SELECT COUNT(*) as count FROM role_permissions rp
                  INNER JOIN permissions p ON rp.permission_id = p.id
                  WHERE rp.role_id = :role_id AND p.name = :permission_name";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role_id', $_SESSION['role_id']);
        $stmt->bindParam(':permission_name', $permissionName);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
    
    // Check if user has any of the permissions
    public static function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    // Check if user has all permissions
    public static function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    // Check if user has specific role
    public static function hasRole($roleName) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        if (!isset($_SESSION['role_name'])) {
            self::loadUserRole();
        }
        
        return isset($_SESSION['role_name']) && $_SESSION['role_name'] === $roleName;
    }
    
    // Check if user is admin
    public static function isAdmin() {
        return self::hasRole('admin');
    }
    
    // Load user's role into session
    private static function loadUserRole() {
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $db = self::getDb();
        $query = "SELECT u.role_id, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.id = :user_id LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['role_name'] = $row['role_name'];
        }
    }
    
    // Require permission or redirect
    public static function requirePermission($permissionName, $redirectUrl = 'index.php?page=dashboard') {
        if (!self::hasPermission($permissionName)) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta función';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    // Require any permission or redirect
    public static function requireAnyPermission($permissions, $redirectUrl = 'index.php?page=dashboard') {
        if (!self::hasAnyPermission($permissions)) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta función';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    // Get user's permissions
    public static function getUserPermissions() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
            return [];
        }
        
        $db = self::getDb();
        $query = "SELECT p.name FROM permissions p
                  INNER JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role_id = :role_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role_id', $_SESSION['role_id']);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
