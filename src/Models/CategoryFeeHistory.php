<?php

class CategoryFeeHistory {
    private $conn;
    private $table_name = "category_fee_history";

    public $id;
    public $category_id;
    public $year;
    public $fee_amount;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create or update a fee for a category and year
     */
    public function createOrUpdate() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (category_id, year, fee_amount) 
                  VALUES (:category_id, :year, :fee_amount)
                  ON DUPLICATE KEY UPDATE 
                  fee_amount = :fee_amount_update";
        
        $stmt = $this->conn->prepare($query);
        
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->fee_amount = htmlspecialchars(strip_tags($this->fee_amount));
        
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":fee_amount", $this->fee_amount);
        $stmt->bindParam(":fee_amount_update", $this->fee_amount);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get all fee history for a specific category
     */
    public function readByCategory($categoryId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE category_id = :category_id 
                  ORDER BY year DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $categoryId);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get all category fees for a specific year
     */
    public function readByYear($year) {
        $query = "SELECT h.*, c.name as category_name, c.color 
                  FROM " . $this->table_name . " h
                  LEFT JOIN member_categories c ON h.category_id = c.id
                  WHERE h.year = :year 
                  ORDER BY c.display_order ASC, c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get fee for a specific category and year
     */
    public function readByCategoryAndYear($categoryId, $year) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE category_id = :category_id 
                  AND year = :year 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $categoryId);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->category_id = $row['category_id'];
            $this->year = $row['year'];
            $this->fee_amount = $row['fee_amount'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Get fee evolution for analytics (all categories, range of years)
     */
    public function getFeeEvolution($startYear = null, $endYear = null) {
        if ($startYear === null) {
            $startYear = date('Y') - 5; // Default: last 5 years
        }
        if ($endYear === null) {
            $endYear = date('Y');
        }
        
        $query = "SELECT h.year, h.fee_amount, h.category_id,
                         c.name as category_name, c.color
                  FROM " . $this->table_name . " h
                  LEFT JOIN member_categories c ON h.category_id = c.id
                  WHERE h.year BETWEEN :start_year AND :end_year
                  AND c.is_active = 1
                  ORDER BY h.year ASC, c.display_order ASC, c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_year", $startYear);
        $stmt->bindParam(":end_year", $endYear);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Delete fee history entry
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get current year fee for a category
     */
    public function getCurrentYearFee($categoryId) {
        $currentYear = date('Y');
        return $this->readByCategoryAndYear($categoryId, $currentYear);
    }
}
?>
