<?php

class Expense {
    private $conn;
    private $table = 'expenses';
    
    public $id;
    public $category_id;
    public $description;
    public $amount;
    public $expense_date;
    public $payment_method;
    public $invoice_number;
    public $provider;
    public $notes;
    public $receipt_file;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Count all expenses with filters (para paginación)
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " e WHERE 1=1";
        $params = [];
        if (!empty($filters['category_id'])) {
            $query .= " AND e.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['start_date'])) {
            $query .= " AND e.expense_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $query .= " AND e.expense_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(e.expense_date) = :year";
            $params[':year'] = $filters['year'];
        }
        if (!empty($filters['month'])) {
            $query .= " AND MONTH(e.expense_date) = :month";
            $params[':month'] = $filters['month'];
        }
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }
    // Get all expenses with filters
    public function readAll($filters = []) {
        $query = "SELECT e.*, ec.name as category_name, ec.color as category_color,
                         u.name as created_by_name
                  FROM " . $this->table . " e
                  LEFT JOIN expense_categories ec ON e.category_id = ec.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $query .= " AND e.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND e.expense_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND e.expense_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(e.expense_date) = :year";
            $params[':year'] = $filters['year'];
        }
        
        if (!empty($filters['month'])) {
            $query .= " AND MONTH(e.expense_date) = :month";
            $params[':month'] = $filters['month'];
        }
        
        $query .= " ORDER BY e.expense_date DESC, e.id DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single expense
    public function readOne() {
        $query = "SELECT e.*, ec.name as category_name, ec.color as category_color,
                         u.name as created_by_name
                  FROM " . $this->table . " e
                  LEFT JOIN expense_categories ec ON e.category_id = ec.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE e.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->category_id = $row['category_id'];
            $this->description = $row['description'];
            $this->amount = $row['amount'];
            $this->expense_date = $row['expense_date'];
            $this->payment_method = $row['payment_method'];
            $this->invoice_number = $row['invoice_number'];
            $this->provider = $row['provider'];
            $this->notes = $row['notes'];
            $this->receipt_file = $row['receipt_file'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    // Create expense
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (category_id, description, amount, expense_date, payment_method, 
                   invoice_number, provider, notes, receipt_file, created_by) 
                  VALUES (:category_id, :description, :amount, :expense_date, :payment_method,
                          :invoice_number, :provider, :notes, :receipt_file, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':expense_date', $this->expense_date);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':invoice_number', $this->invoice_number);
        $stmt->bindParam(':provider', $this->provider);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':receipt_file', $this->receipt_file);
        $stmt->bindParam(':created_by', $this->created_by);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update expense
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET category_id = :category_id,
                      description = :description,
                      amount = :amount,
                      expense_date = :expense_date,
                      payment_method = :payment_method,
                      invoice_number = :invoice_number,
                      provider = :provider,
                      notes = :notes,
                      receipt_file = :receipt_file
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':expense_date', $this->expense_date);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':invoice_number', $this->invoice_number);
        $stmt->bindParam(':provider', $this->provider);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':receipt_file', $this->receipt_file);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Delete expense
    public function delete() {
        // Delete receipt file if exists
        if ($this->receipt_file && file_exists('../uploads/receipts/' . $this->receipt_file)) {
            unlink('../uploads/receipts/' . $this->receipt_file);
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Get total expenses by period
    public static function getTotalByPeriod($db, $year, $month = null) {
        $query = "SELECT SUM(amount) as total FROM expenses 
                  WHERE YEAR(expense_date) = :year";
        
        if ($month) {
            $query .= " AND MONTH(expense_date) = :month";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':year', $year);
        
        if ($month) {
            $stmt->bindParam(':month', $month);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }
    
    // Get expenses by category
    public static function getByCategory($db, $year = null) {
        $query = "SELECT ec.name, ec.color, SUM(e.amount) as total, COUNT(e.id) as count
                  FROM expenses e
                  INNER JOIN expense_categories ec ON e.category_id = ec.id";
        
        if ($year) {
            $query .= " WHERE YEAR(e.expense_date) = :year";
        }
        
        $query .= " GROUP BY ec.id ORDER BY total DESC";
        
        $stmt = $db->prepare($query);
        
        if ($year) {
            $stmt->bindParam(':year', $year);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get monthly expenses for year
    public static function getMonthlyTotal($db, $year) {
        $query = "SELECT MONTH(expense_date) as month, SUM(amount) as total
                  FROM expenses
                  WHERE YEAR(expense_date) = :year
                  GROUP BY MONTH(expense_date)
                  ORDER BY month ASC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
