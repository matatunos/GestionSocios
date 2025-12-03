<?php

class MemberCategory {
    private $conn;
    private $table = 'member_categories';
    
    public $id;
    public $name;
    public $description;
    public $default_fee;
    public $color;
    public $is_active;
    public $display_order;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all active categories
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 
                  ORDER BY display_order ASC, name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all categories (including inactive)
    public function readAllIncludingInactive() {
        $query = "SELECT * FROM " . $this->table . " 
                  ORDER BY display_order ASC, name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single category
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->default_fee = $row['default_fee'];
            $this->color = $row['color'];
            $this->is_active = $row['is_active'];
            $this->display_order = $row['display_order'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create category
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, description, default_fee, color, is_active, display_order) 
                  VALUES (:name, :description, :default_fee, :color, :is_active, :display_order)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':default_fee', $this->default_fee);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':display_order', $this->display_order);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update category
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name,
                      description = :description,
                      default_fee = :default_fee,
                      color = :color,
                      is_active = :is_active,
                      display_order = :display_order
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':default_fee', $this->default_fee);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Delete category (soft delete by setting is_active = 0)
    public function delete() {
        $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Count members in category
    public function countMembers() {
        $query = "SELECT COUNT(*) as total FROM members WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Get statistics by category
    public static function getStatistics($db) {
        $query = "SELECT 
                    mc.id,
                    mc.name,
                    mc.color,
                    mc.default_fee,
                    COUNT(m.id) as member_count,
                    SUM(CASE WHEN m.status = 'active' THEN 1 ELSE 0 END) as active_members
                  FROM member_categories mc
                  LEFT JOIN members m ON mc.id = m.category_id
                  WHERE mc.is_active = 1
                  GROUP BY mc.id
                  ORDER BY mc.display_order ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
