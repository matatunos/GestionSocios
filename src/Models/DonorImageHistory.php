<?php

class DonorImageHistory {
    private $conn;
    private $table_name = "donor_image_history";

    public $id;
    public $donor_id;
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
                  (donor_id, image_url, is_current, uploaded_at, replaced_at) 
                  VALUES (:donor_id, :image_url, :is_current, :uploaded_at, :replaced_at)";
        
        $stmt = $this->conn->prepare($query);

        $this->donor_id = htmlspecialchars(strip_tags($this->donor_id));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        $stmt->bindParam(":donor_id", $this->donor_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":is_current", $this->is_current);
        $stmt->bindParam(":uploaded_at", $this->uploaded_at);
        $stmt->bindParam(":replaced_at", $this->replaced_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get all images for a specific donor ordered by date (newest first)
     */
    public function getByDonor($donorId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE donor_id = :donor_id 
                  ORDER BY uploaded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":donor_id", $donorId);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Get the current image for a donor
     */
    public function getCurrentImage($donorId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE donor_id = :donor_id AND is_current = 1 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":donor_id", $donorId);
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
     * Mark all images for a donor as not current
     */
    public function markAllAsNotCurrent($donorId) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_current = 0, replaced_at = NOW() 
                  WHERE donor_id = :donor_id AND is_current = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":donor_id", $donorId);

        return $stmt->execute();
    }

    /**
     * Set a specific image as current
     */
    public function setAsCurrent($id, $donorId) {
        // First, mark all images for this donor as not current
        $this->markAllAsNotCurrent($donorId);

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
            $this->donor_id = $row['donor_id'];
            $this->image_url = $row['image_url'];
            $this->uploaded_at = $row['uploaded_at'];
            $this->is_current = $row['is_current'];
            $this->replaced_at = $row['replaced_at'];
            return true;
        }

        return false;
    }

    /**
     * Count the number of images in history for a donor
     */
    public function countByDonor($donorId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE donor_id = :donor_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":donor_id", $donorId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
