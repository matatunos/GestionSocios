<?php

class MemberImageHistory {
    private $conn;
    private $table_name = "member_image_history";

    public $id;
    public $member_id;
    public $image_url;
    public $uploaded_at;
    public $is_current;
    public $replaced_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new entry in the image history
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (member_id, image_url, is_current, uploaded_at, replaced_at) 
                  VALUES (:member_id, :image_url, :is_current, :uploaded_at, :replaced_at)";
        
        $stmt = $this->conn->prepare($query);

        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        
        // Convert is_current to integer (0 or 1)
        $is_current_int = $this->is_current ? 1 : 0;

        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":is_current", $is_current_int, PDO::PARAM_INT);
        $stmt->bindParam(":uploaded_at", $this->uploaded_at);
        $stmt->bindParam(":replaced_at", $this->replaced_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get all images for a specific member ordered by date (newest first)
     */
    public function getByMember($memberId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE member_id = :member_id 
                  ORDER BY uploaded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $memberId);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Get the current image for a member
     */
    public function getCurrentImage($memberId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE member_id = :member_id AND is_current = 1 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $memberId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark an image as replaced
     */
    public function markAsReplaced($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_current = 0, replaced_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Mark all images for a member as not current
     */
    public function markAllAsNotCurrent($memberId) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_current = 0, replaced_at = NOW() 
                  WHERE member_id = :member_id AND is_current = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $memberId);

        return $stmt->execute();
    }

    /**
     * Set a specific image as current
     */
    public function setAsCurrent($id, $memberId) {
        // First, mark all images for this member as not current
        $this->markAllAsNotCurrent($memberId);

        // Then set the specified image as current
        $query = "UPDATE " . $this->table_name . " 
                  SET is_current = 1, replaced_at = NULL 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Get a specific history entry by ID
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->member_id = $row['member_id'];
            $this->image_url = $row['image_url'];
            $this->uploaded_at = $row['uploaded_at'];
            $this->is_current = $row['is_current'];
            $this->replaced_at = $row['replaced_at'];
            return true;
        }

        return false;
    }

    /**
     * Count the number of images in history for a member
     */
    public function countByMember($memberId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $memberId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
