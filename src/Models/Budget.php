<?php

class Budget {
    private $conn;
    private $table = 'budgets';
    
    public $id;
    public $name;
    public $description;
    public $fiscal_year;
    public $account_id;
    public $amount;
    public $period_type;
    public $period_number;
    public $status;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all budgets with filters
    public function readAll($filters = []) {
        $query = "SELECT b.*, 
                         a.code as account_code,
                         a.name as account_name,
                         u.name as created_by_name
                  FROM " . $this->table . " b
                  LEFT JOIN accounting_accounts a ON b.account_id = a.id
                  LEFT JOIN users u ON b.created_by = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['fiscal_year']) && $filters['fiscal_year'] !== '') {
            $query .= " AND b.fiscal_year = :fiscal_year";
            $params[':fiscal_year'] = $filters['fiscal_year'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND b.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (isset($filters['account_id']) && $filters['account_id'] !== '') {
            $query .= " AND b.account_id = :account_id";
            $params[':account_id'] = $filters['account_id'];
        }
        
        $query .= " ORDER BY b.fiscal_year DESC, a.code ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get budget by ID
    public function readOne($id) {
        $query = "SELECT b.*, 
                         a.code as account_code,
                         a.name as account_name
                  FROM " . $this->table . " b
                  LEFT JOIN accounting_accounts a ON b.account_id = a.id
                  WHERE b.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->fiscal_year = $row['fiscal_year'];
            $this->account_id = $row['account_id'];
            $this->amount = $row['amount'];
            $this->period_type = $row['period_type'];
            $this->period_number = $row['period_number'];
            $this->status = $row['status'];
            $this->created_by = $row['created_by'];
            return true;
        }
        
        return false;
    }
    
    // Create budget
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (name, description, fiscal_year, account_id, amount, 
                   period_type, period_number, status, created_by)
                  VALUES (:name, :description, :fiscal_year, :account_id, :amount,
                          :period_type, :period_number, :status, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':fiscal_year', $this->fiscal_year);
        $stmt->bindParam(':account_id', $this->account_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':period_type', $this->period_type);
        $stmt->bindParam(':period_number', $this->period_number);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created_by', $this->created_by);
        
        return $stmt->execute();
    }
    
    // Update budget
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET name = :name,
                      description = :description,
                      fiscal_year = :fiscal_year,
                      account_id = :account_id,
                      amount = :amount,
                      period_type = :period_type,
                      period_number = :period_number,
                      status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':fiscal_year', $this->fiscal_year);
        $stmt->bindParam(':account_id', $this->account_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':period_type', $this->period_type);
        $stmt->bindParam(':period_number', $this->period_number);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }
    
    // Delete budget
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Get budget vs actual comparison
    public static function getBudgetVsActual($db, $fiscalYear, $accountId = null) {
        $query = "SELECT 
                      b.account_id,
                      a.code as account_code,
                      a.name as account_name,
                      a.account_type,
                      SUM(b.amount) as budget_amount,
                      COALESCE((
                          SELECT SUM(el.debit - el.credit)
                          FROM accounting_entry_lines el
                          INNER JOIN accounting_entries e ON el.entry_id = e.id
                          WHERE el.account_id = b.account_id
                            AND e.status = 'posted'
                            AND YEAR(e.entry_date) = b.fiscal_year
                      ), 0) as actual_amount
                  FROM budgets b
                  INNER JOIN accounting_accounts a ON b.account_id = a.id
                  WHERE b.fiscal_year = :fiscal_year
                    AND b.status = 'active'";
        
        $params = [':fiscal_year' => $fiscalYear];
        
        if ($accountId !== null) {
            $query .= " AND b.account_id = :account_id";
            $params[':account_id'] = $accountId;
        }
        
        $query .= " GROUP BY b.account_id, a.code, a.name, a.account_type";
        
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
