<?php

class AccountingAccount {
    private $conn;
    private $table = 'accounting_accounts';
    
    public $id;
    public $code;
    public $name;
    public $description;
    public $account_type;
    public $parent_id;
    public $level;
    public $balance_type;
    public $is_active;
    public $is_system;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all accounts
    public function readAll($filters = []) {
        $query = "SELECT a.*, 
                         p.name as parent_name,
                         (SELECT COUNT(*) FROM accounting_accounts WHERE parent_id = a.id) as children_count
                  FROM " . $this->table . " a
                  LEFT JOIN " . $this->table . " p ON a.parent_id = p.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['account_type']) && $filters['account_type'] !== '') {
            $query .= " AND a.account_type = :account_type";
            $params[':account_type'] = $filters['account_type'];
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query .= " AND a.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }
        
        $query .= " ORDER BY a.code ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get account by ID
    public function readOne($id) {
        $query = "SELECT a.*, p.name as parent_name
                  FROM " . $this->table . " a
                  LEFT JOIN " . $this->table . " p ON a.parent_id = p.id
                  WHERE a.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->code = $row['code'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->account_type = $row['account_type'];
            $this->parent_id = $row['parent_id'];
            $this->level = $row['level'];
            $this->balance_type = $row['balance_type'];
            $this->is_active = $row['is_active'];
            $this->is_system = $row['is_system'];
            return true;
        }
        
        return false;
    }
    
    // Create account
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (code, name, description, account_type, parent_id, level, balance_type, is_active)
                  VALUES (:code, :name, :description, :account_type, :parent_id, :level, :balance_type, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':account_type', $this->account_type);
        $stmt->bindParam(':parent_id', $this->parent_id);
        $stmt->bindParam(':level', $this->level);
        $stmt->bindParam(':balance_type', $this->balance_type);
        $stmt->bindParam(':is_active', $this->is_active);
        
        return $stmt->execute();
    }
    
    // Update account
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET code = :code,
                      name = :name,
                      description = :description,
                      account_type = :account_type,
                      parent_id = :parent_id,
                      level = :level,
                      balance_type = :balance_type,
                      is_active = :is_active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':account_type', $this->account_type);
        $stmt->bindParam(':parent_id', $this->parent_id);
        $stmt->bindParam(':level', $this->level);
        $stmt->bindParam(':balance_type', $this->balance_type);
        $stmt->bindParam(':is_active', $this->is_active);
        
        return $stmt->execute();
    }
    
    // Delete account (only if not used and not system)
    public function delete($id) {
        // Check if account is used in entries
        $query = "SELECT COUNT(*) as count FROM accounting_entry_lines WHERE account_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] > 0) {
            return false; // Cannot delete account with entries
        }
        
        // Check if account is system account
        $query = "SELECT is_system FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $row['is_system'] == 1) {
            return false; // Cannot delete system account
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Get account balance for a period
    public function getBalance($accountId, $startDate, $endDate) {
        $query = "SELECT 
                      COALESCE(SUM(el.debit), 0) as total_debit,
                      COALESCE(SUM(el.credit), 0) as total_credit
                  FROM accounting_entry_lines el
                  INNER JOIN accounting_entries e ON el.entry_id = e.id
                  WHERE el.account_id = :account_id
                    AND e.status = 'posted'
                    AND e.entry_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'debit' => $row['total_debit'],
            'credit' => $row['total_credit'],
            'balance' => $row['total_debit'] - $row['total_credit']
        ];
    }
    
    // Get accounts by type
    public static function getByType($db, $type) {
        $query = "SELECT * FROM accounting_accounts 
                  WHERE account_type = :type AND is_active = 1 
                  ORDER BY code ASC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
