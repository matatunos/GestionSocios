<?php

class AuditLog {
    private $conn;
    private $table_name = "audit_log";

    public $id;
    public $user_id;
    public $action;
    public $entity;
    public $entity_id;
    public $details;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $action, $entity, $entity_id = null, $details = null) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, action, entity, entity_id, details) VALUES (:user_id, :action, :entity, :entity_id, :details)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":entity", $entity);
        $stmt->bindParam(":entity_id", $entity_id);
        $stmt->bindParam(":details", $details);
        return $stmt->execute();
    }

    public function readRecent($limit = 20) {
        $query = "SELECT a.*, u.username FROM " . $this->table_name . " a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readFiltered($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT a.*, u.username FROM " . $this->table_name . " a JOIN users u ON a.user_id = u.id WHERE 1=1";
        $params = [];
        if (!empty($filters['user_id'])) {
            $query .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $query .= " AND a.action = :action";
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['entity'])) {
            $query .= " AND a.entity = :entity";
            $params[':entity'] = $filters['entity'];
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND a.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND a.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        $query .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " a WHERE 1=1";
        $params = [];
        if (!empty($filters['user_id'])) {
            $query .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $query .= " AND a.action = :action";
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['entity'])) {
            $query .= " AND a.entity = :entity";
            $params[':entity'] = $filters['entity'];
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND a.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND a.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }
}
