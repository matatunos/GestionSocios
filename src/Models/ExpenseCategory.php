<?php

class ExpenseCategory {
    private $conn;
    private $table = 'expense_categories';
    
    public $id;
    public $name;
    public $description;
    public $color;
    public $is_active;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all active categories
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
