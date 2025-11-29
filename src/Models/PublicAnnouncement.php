<?php

class PublicAnnouncement {
    private $conn;
    private $table = 'public_announcements';

    public $id;
    public $title;
    public $content;
    public $type;
    public $is_active;
    public $priority;
    public $created_by;
    public $created_at;
    public $updated_at;
    public $expires_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all active announcements (not expired, ordered by priority)
    public function readActive() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 
                  AND (expires_at IS NULL OR expires_at > NOW())
                  ORDER BY priority DESC, created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get all announcements (for admin)
    public function readAll() {
        $query = "SELECT a.*, u.username as creator_name 
                  FROM " . $this->table . " a
                  LEFT JOIN users u ON a.created_by = u.id
                  ORDER BY a.priority DESC, a.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single announcement
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->type = $row['type'];
            $this->is_active = $row['is_active'];
            $this->priority = $row['priority'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->expires_at = $row['expires_at'];
            return true;
        }
        
        return false;
    }

    // Create announcement
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (title, content, type, is_active, priority, created_by, expires_at) 
                  VALUES (:title, :content, :type, :is_active, :priority, :created_by, :expires_at)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars($this->content);
        $this->type = htmlspecialchars(strip_tags($this->type));
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':created_by', $this->created_by);
        $stmt->bindParam(':expires_at', $this->expires_at);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Update announcement
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, 
                      content = :content, 
                      type = :type, 
                      is_active = :is_active, 
                      priority = :priority,
                      expires_at = :expires_at
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars($this->content);
        $this->type = htmlspecialchars(strip_tags($this->type));
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':expires_at', $this->expires_at);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Delete announcement
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Toggle active status
    public function toggleActive() {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = NOT is_active 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
}
?>
