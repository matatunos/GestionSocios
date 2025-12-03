<?php

class AccountingPeriod {
    private $conn;
    private $table = 'accounting_periods';
    
    public $id;
    public $name;
    public $start_date;
    public $end_date;
    public $fiscal_year;
    public $status;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all periods
    public function readAll($filters = []) {
        $query = "SELECT p.*, u.name as created_by_name,
                         (SELECT COUNT(*) FROM accounting_entries WHERE period_id = p.id) as entries_count
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.created_by = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['fiscal_year']) && $filters['fiscal_year'] !== '') {
            $query .= " AND p.fiscal_year = :fiscal_year";
            $params[':fiscal_year'] = $filters['fiscal_year'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $query .= " ORDER BY p.fiscal_year DESC, p.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get period by ID
    public function readOne($id) {
        $query = "SELECT p.*, u.name as created_by_name
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.created_by = u.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->fiscal_year = $row['fiscal_year'];
            $this->status = $row['status'];
            $this->created_by = $row['created_by'];
            return true;
        }
        
        return false;
    }
    
    // Create period
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (name, start_date, end_date, fiscal_year, status, created_by)
                  VALUES (:name, :start_date, :end_date, :fiscal_year, :status, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':fiscal_year', $this->fiscal_year);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created_by', $this->created_by);
        
        return $stmt->execute();
    }
    
    // Update period
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET name = :name,
                      start_date = :start_date,
                      end_date = :end_date,
                      fiscal_year = :fiscal_year,
                      status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':fiscal_year', $this->fiscal_year);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }
    
    // Close period
    public function close() {
        $query = "UPDATE " . $this->table . "
                  SET status = 'closed'
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Get open period for a date
    public static function getOpenPeriodForDate($db, $date) {
        $query = "SELECT * FROM accounting_periods
                  WHERE status = 'open'
                    AND :date BETWEEN start_date AND end_date
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get current open period
    public static function getCurrentOpenPeriod($db) {
        $today = date('Y-m-d');
        return self::getOpenPeriodForDate($db, $today);
    }
}
