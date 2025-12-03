<?php

class AuditLog {
    private static $db = null;
    
    private static function getDb() {
        if (self::$db === null) {
            self::$db = (new Database())->getConnection();
        }
        return self::$db;
    }
    
    // Log an action
    public static function log($action, $entityType, $entityId, $oldValues = null, $newValues = null) {
        $db = self::getDb();
        
        $query = "INSERT INTO audit_log 
                  (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                  VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)";
        
        $stmt = $db->prepare($query);
        
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':entity_type', $entityType);
        $stmt->bindParam(':entity_id', $entityId);
        $stmt->bindParam(':old_values', $oldValuesJson);
        $stmt->bindParam(':new_values', $newValuesJson);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        
        return $stmt->execute();
    }
    
    // Get audit log entries with filters
    public static function getLog($filters = [], $limit = 100) {
        $db = self::getDb();
        
        $query = "SELECT al.*, u.name as user_name, u.email as user_email
                  FROM audit_log al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['entity_type'])) {
            $query .= " AND al.entity_type = :entity_type";
            $params[':entity_type'] = $filters['entity_type'];
        }
        
        if (!empty($filters['entity_id'])) {
            $query .= " AND al.entity_id = :entity_id";
            $params[':entity_id'] = $filters['entity_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND al.created_at >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND al.created_at <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT :limit";
        
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get log for specific entity
    public static function getEntityLog($entityType, $entityId, $limit = 50) {
        return self::getLog([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ], $limit);
    }
    
    // Get recent activity
    public static function getRecentActivity($limit = 20) {
        return self::getLog([], $limit);
    }
    
    // Helper methods for common actions
    public static function logCreate($entityType, $entityId, $data) {
        return self::log('create', $entityType, $entityId, null, $data);
    }
    
    public static function logUpdate($entityType, $entityId, $oldData, $newData) {
        return self::log('update', $entityType, $entityId, $oldData, $newData);
    }
    
    public static function logDelete($entityType, $entityId, $data) {
        return self::log('delete', $entityType, $entityId, $data, null);
    }
    
    public static function logLogin($userId) {
        return self::log('login', 'user', $userId, null, ['timestamp' => date('Y-m-d H:i:s')]);
    }
    
    public static function logLogout($userId) {
        return self::log('logout', 'user', $userId, null, ['timestamp' => date('Y-m-d H:i:s')]);
    }
}
