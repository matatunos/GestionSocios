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
    public $photo_url;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY last_name ASC, first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
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
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->status = $row['status'];
            $this->photo_url = $row['photo_url'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, email=:email, phone=:phone, address=:address, status=:status, photo_url=:photo_url";
        
        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->photo_url = htmlspecialchars(strip_tags($this->photo_url));

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":photo_url", $this->photo_url);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, email=:email, phone=:phone, address=:address, status=:status, photo_url=:photo_url
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->photo_url = htmlspecialchars(strip_tags($this->photo_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":photo_url", $this->photo_url);
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
}
