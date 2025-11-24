<?php

class Member {
    private $conn;
    private $table_name = "members";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $status;
    public $category_id;
    public $photo_url;
    public $created_at;
    public $deactivated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT m.*, mc.name as category_name, mc.color as category_color 
                  FROM " . $this->table_name . " m
                  LEFT JOIN member_categories mc ON m.category_id = mc.id
                  ORDER BY m.last_name ASC, m.first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readFiltered($filters = [], $limit = null, $offset = null) {
        $currentYear = date('Y');
        $query = "SELECT m.*, 
                  mc.name as category_name, 
                  mc.color as category_color,
                  EXISTS (
                      SELECT 1 FROM payments p 
                      WHERE p.member_id = m.id 
                      AND p.fee_year = $currentYear 
                      AND p.status = 'paid'
                  ) as has_paid_current_year
                  FROM " . $this->table_name . " m
                  LEFT JOIN member_categories mc ON m.category_id = mc.id
                  WHERE 1=1";
        
        $params = [];
        
        // Filter by status (active/inactive)
        if (!empty($filters['status'])) {
            $query .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Filter by category
        if (!empty($filters['category_id'])) {
            $query .= " AND m.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        // Search by name, email, phone
        if (!empty($filters['search'])) {
            $query .= " AND (CONCAT(m.first_name, ' ', m.last_name) LIKE :search 
                          OR m.email LIKE :search 
                          OR m.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Filter by registration year
        if (!empty($filters['year_from'])) {
            $query .= " AND YEAR(m.created_at) >= :year_from";
            $params[':year_from'] = $filters['year_from'];
        }
        
        if (!empty($filters['year_to'])) {
            $query .= " AND YEAR(m.created_at) <= :year_to";
            $params[':year_to'] = $filters['year_to'];
        }
        
        // Filter by payment status (requires checking payments table)
        if (!empty($filters['payment_status'])) {
            $currentYear = date('Y');
            
            if ($filters['payment_status'] === 'current') {
                $query .= " AND EXISTS (
                    SELECT 1 FROM payments p 
                    WHERE p.member_id = m.id 
                    AND p.fee_year = $currentYear
                )";
            } elseif ($filters['payment_status'] === 'delinquent') {
                $query .= " AND NOT EXISTS (
                    SELECT 1 FROM payments p 
                    WHERE p.member_id = m.id 
                    AND p.fee_year = $currentYear
                ) AND m.status = 'active'";
            }
        }
        
        $query .= " ORDER BY m.last_name ASC, m.first_name ASC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :offset, :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered($filters = []) {
        $query = "SELECT COUNT(DISTINCT m.id) as total
                  FROM " . $this->table_name . " m
                  LEFT JOIN member_categories mc ON m.category_id = mc.id
                  WHERE 1=1";
        
        $params = [];
        
        // Filter by status (active/inactive)
        if (!empty($filters['status'])) {
            $query .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Filter by category
        if (!empty($filters['category_id'])) {
            $query .= " AND m.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        // Search by name, email, phone
        if (!empty($filters['search'])) {
            $query .= " AND (CONCAT(m.first_name, ' ', m.last_name) LIKE :search 
                          OR m.email LIKE :search 
                          OR m.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Filter by registration year
        if (!empty($filters['year_from'])) {
            $query .= " AND YEAR(m.created_at) >= :year_from";
            $params[':year_from'] = $filters['year_from'];
        }
        
        if (!empty($filters['year_to'])) {
            $query .= " AND YEAR(m.created_at) <= :year_to";
            $params[':year_to'] = $filters['year_to'];
        }
        
        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            $currentYear = date('Y');
            
            if ($filters['payment_status'] === 'current') {
                $query .= " AND EXISTS (
                    SELECT 1 FROM payments p 
                    WHERE p.member_id = m.id 
                    AND p.fee_year = $currentYear
                )";
            } elseif ($filters['payment_status'] === 'delinquent') {
                $query .= " AND NOT EXISTS (
                    SELECT 1 FROM payments p 
                    WHERE p.member_id = m.id 
                    AND p.fee_year = $currentYear
                ) AND m.status = 'active'";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->dni = $row['dni'] ?? null;
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->latitude = $row['latitude'] ?? null;
            $this->longitude = $row['longitude'] ?? null;
            $this->status = $row['status'];
            $this->category_id = $row['category_id'] ?? null;
            $this->photo_url = $row['photo_url'];
            $this->created_at = $row['created_at'];
            $this->deactivated_at = $row['deactivated_at'] ?? null;
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, dni=:dni, email=:email, phone=:phone, address=:address, latitude=:latitude, longitude=:longitude, status=:status, category_id=:category_id, photo_url=:photo_url";
        
        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->dni = !empty($this->dni) ? htmlspecialchars(strip_tags($this->dni)) : null;
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->latitude = ($this->latitude !== null && $this->latitude !== '') ? floatval($this->latitude) : null;
        $this->longitude = ($this->longitude !== null && $this->longitude !== '') ? floatval($this->longitude) : null;
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->photo_url = htmlspecialchars(strip_tags($this->photo_url));

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":dni", $this->dni);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(":photo_url", $this->photo_url);

        if ($stmt->execute()) {
            // Get the inserted ID
            $newId = $this->conn->lastInsertId();
            
            // Update member_number to be the same as ID
            $updateQuery = "UPDATE " . $this->table_name . " SET member_number = :id WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":id", $newId);
            $updateStmt->execute();
            
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, dni=:dni, email=:email, phone=:phone, address=:address, latitude=:latitude, longitude=:longitude, status=:status, category_id=:category_id, photo_url=:photo_url, deactivated_at=:deactivated_at
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->dni = !empty($this->dni) ? htmlspecialchars(strip_tags($this->dni)) : null;
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->latitude = ($this->latitude !== null && $this->latitude !== '') ? floatval($this->latitude) : null;
        $this->longitude = ($this->longitude !== null && $this->longitude !== '') ? floatval($this->longitude) : null;
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->photo_url = htmlspecialchars(strip_tags($this->photo_url));
        $this->deactivated_at = !empty($this->deactivated_at) ? htmlspecialchars(strip_tags($this->deactivated_at)) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":dni", $this->dni);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(":photo_url", $this->photo_url);
        $stmt->bindParam(":deactivated_at", $this->deactivated_at);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getPaymentStatus() {
        // Verificar si tiene pagada la cuota del año actual
        $currentYear = date('Y');
        $query = "SELECT COUNT(*) as count FROM payments 
                  WHERE member_id = ? 
                  AND payment_type = 'fee' 
                  AND fee_year = ? 
                  AND status = 'paid'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $currentYear);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0 ? 'current' : 'delinquent';
    }

    public function readByPaymentStatus($paymentStatus) {
        // Retorna socios activos filtrados por estado de pago
        $currentYear = date('Y');
        
        if ($paymentStatus === 'current') {
            $query = "SELECT DISTINCT m.* FROM " . $this->table_name . " m
                      INNER JOIN payments p ON m.id = p.member_id
                      WHERE m.status = 'active'
                      AND p.payment_type = 'fee'
                      AND p.fee_year = ?
                      AND p.status = 'paid'
                      ORDER BY m.last_name, m.first_name";
        } else {
            $query = "SELECT m.* FROM " . $this->table_name . " m
                      WHERE m.status = 'active'
                      AND m.id NOT IN (
                          SELECT member_id FROM payments
                          WHERE payment_type = 'fee'
                          AND fee_year = ?
                          AND status = 'paid'
                      )
                      ORDER BY m.last_name, m.first_name";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $currentYear);
        $stmt->execute();
        return $stmt;
    }
    public function getActiveCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = 'active' 
                  ORDER BY last_name ASC, first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
