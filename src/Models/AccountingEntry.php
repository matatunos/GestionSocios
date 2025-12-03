<?php

class AccountingEntry {
    private $conn;
    private $table = 'accounting_entries';
    
    public $id;
    public $entry_number;
    public $entry_date;
    public $period_id;
    public $description;
    public $reference;
    public $entry_type;
    public $source_type;
    public $source_id;
    public $status;
    public $created_by;
    public $posted_by;
    public $posted_at;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all entries with filters and pagination
    public function readAll($filters = [], $limit = null, $offset = null) {
        $query = "SELECT e.*, 
                         p.name as period_name,
                         u.name as created_by_name,
                         u2.name as posted_by_name,
                         (SELECT COALESCE(SUM(debit), 0) FROM accounting_entry_lines WHERE entry_id = e.id) as total_debit,
                         (SELECT COALESCE(SUM(credit), 0) FROM accounting_entry_lines WHERE entry_id = e.id) as total_credit
                  FROM " . $this->table . " e
                  LEFT JOIN accounting_periods p ON e.period_id = p.id
                  LEFT JOIN users u ON e.created_by = u.id
                  LEFT JOIN users u2 ON e.posted_by = u2.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['period_id']) && $filters['period_id'] !== '') {
            $query .= " AND e.period_id = :period_id";
            $params[':period_id'] = $filters['period_id'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (isset($filters['entry_type']) && $filters['entry_type'] !== '') {
            $query .= " AND e.entry_type = :entry_type";
            $params[':entry_type'] = $filters['entry_type'];
        }
        
        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query .= " AND e.entry_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query .= " AND e.entry_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $query .= " ORDER BY e.entry_date DESC, e.entry_number DESC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Count entries with filters
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " e WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['period_id']) && $filters['period_id'] !== '') {
            $query .= " AND e.period_id = :period_id";
            $params[':period_id'] = $filters['period_id'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (isset($filters['entry_type']) && $filters['entry_type'] !== '') {
            $query .= " AND e.entry_type = :entry_type";
            $params[':entry_type'] = $filters['entry_type'];
        }
        
        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query .= " AND e.entry_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query .= " AND e.entry_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }
    
    // Get entry by ID with lines
    public function readOne($id) {
        $query = "SELECT e.*, 
                         p.name as period_name,
                         u.name as created_by_name
                  FROM " . $this->table . " e
                  LEFT JOIN accounting_periods p ON e.period_id = p.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->entry_number = $row['entry_number'];
            $this->entry_date = $row['entry_date'];
            $this->period_id = $row['period_id'];
            $this->description = $row['description'];
            $this->reference = $row['reference'];
            $this->entry_type = $row['entry_type'];
            $this->source_type = $row['source_type'];
            $this->source_id = $row['source_id'];
            $this->status = $row['status'];
            $this->created_by = $row['created_by'];
            $this->posted_by = $row['posted_by'];
            $this->posted_at = $row['posted_at'];
            
            return $row;
        }
        
        return false;
    }
    
    // Get entry lines
    public function getLines($entryId) {
        $query = "SELECT el.*, a.code as account_code, a.name as account_name
                  FROM accounting_entry_lines el
                  INNER JOIN accounting_accounts a ON el.account_id = a.id
                  WHERE el.entry_id = :entry_id
                  ORDER BY el.line_order ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':entry_id', $entryId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create entry with lines
    public function create($lines = []) {
        // Validate that debits equal credits
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($lines as $line) {
            $totalDebit += floatval($line['debit']);
            $totalCredit += floatval($line['credit']);
        }
        
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return false; // Debits must equal credits
        }
        
        $this->conn->beginTransaction();
        
        try {
            // Generate entry number if not set
            if (empty($this->entry_number)) {
                $this->entry_number = $this->generateEntryNumber();
            }
            
            // Insert entry
            $query = "INSERT INTO " . $this->table . "
                      (entry_number, entry_date, period_id, description, reference, 
                       entry_type, source_type, source_id, status, created_by)
                      VALUES (:entry_number, :entry_date, :period_id, :description, :reference,
                              :entry_type, :source_type, :source_id, :status, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':entry_number', $this->entry_number);
            $stmt->bindParam(':entry_date', $this->entry_date);
            $stmt->bindParam(':period_id', $this->period_id);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':reference', $this->reference);
            $stmt->bindParam(':entry_type', $this->entry_type);
            $stmt->bindParam(':source_type', $this->source_type);
            $stmt->bindParam(':source_id', $this->source_id);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':created_by', $this->created_by);
            
            $stmt->execute();
            $entryId = $this->conn->lastInsertId();
            
            // Insert lines
            $lineQuery = "INSERT INTO accounting_entry_lines
                          (entry_id, account_id, description, debit, credit, line_order)
                          VALUES (:entry_id, :account_id, :description, :debit, :credit, :line_order)";
            
            $lineStmt = $this->conn->prepare($lineQuery);
            
            $lineOrder = 0;
            foreach ($lines as $line) {
                $lineOrder++;
                $lineStmt->bindParam(':entry_id', $entryId);
                $lineStmt->bindParam(':account_id', $line['account_id']);
                $lineStmt->bindParam(':description', $line['description']);
                $lineStmt->bindParam(':debit', $line['debit']);
                $lineStmt->bindParam(':credit', $line['credit']);
                $lineStmt->bindParam(':line_order', $lineOrder);
                $lineStmt->execute();
            }
            
            $this->conn->commit();
            $this->id = $entryId;
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    // Post entry (make it final)
    public function post($userId) {
        $query = "UPDATE " . $this->table . "
                  SET status = 'posted',
                      posted_by = :posted_by,
                      posted_at = NOW()
                  WHERE id = :id AND status = 'draft'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':posted_by', $userId);
        
        return $stmt->execute();
    }
    
    // Cancel entry
    public function cancel() {
        $query = "UPDATE " . $this->table . "
                  SET status = 'cancelled'
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Generate unique entry number
    private function generateEntryNumber() {
        $year = date('Y', strtotime($this->entry_date));
        
        $query = "SELECT MAX(CAST(SUBSTRING(entry_number, -6) AS UNSIGNED)) as max_num
                  FROM " . $this->table . "
                  WHERE entry_number LIKE :prefix";
        
        $prefix = "AS-" . $year . "%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prefix', $prefix);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextNum = ($row['max_num'] ?? 0) + 1;
        
        return "AS-" . $year . "-" . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }
}
