<?php

class Payment {
    private $conn;
    private $table_name = "payments";

    public $id;
    public $member_id;
    public $amount;
    public $payment_date;
    public $concept;
    public $status;
    public $fee_year;
    public $payment_type;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, m.first_name, m.last_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN members m ON p.member_id = m.id
                  ORDER BY p.payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET member_id=:member_id, amount=:amount, payment_date=:payment_date, concept=:concept, status=:status, fee_year=:fee_year, payment_type=:payment_type";
        
        $stmt = $this->conn->prepare($query);

        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_date = htmlspecialchars(strip_tags($this->payment_date));
        $this->concept = htmlspecialchars(strip_tags($this->concept));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->fee_year = !empty($this->fee_year) ? htmlspecialchars(strip_tags($this->fee_year)) : null;
        $this->payment_type = !empty($this->payment_type) ? htmlspecialchars(strip_tags($this->payment_type)) : 'fee';

        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_date", $this->payment_date);
        $stmt->bindParam(":concept", $this->concept);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":fee_year", $this->fee_year);
        $stmt->bindParam(":payment_type", $this->payment_type);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getMonthlyIncome() {
        $query = "SELECT SUM(amount) as total FROM " . $this->table_name . " 
                  WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getPendingCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
    public function readOne() {
        $query = "SELECT p.*, m.first_name, m.last_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN members m ON p.member_id = m.id
                  WHERE p.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->member_id = $row['member_id'];
            $this->amount = $row['amount'];
            $this->payment_date = $row['payment_date'];
            $this->concept = $row['concept'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET member_id = :member_id,
                      amount = :amount,
                      payment_date = :payment_date,
                      concept = :concept,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_date = htmlspecialchars(strip_tags($this->payment_date));
        $this->concept = htmlspecialchars(strip_tags($this->concept));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':member_id', $this->member_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_date', $this->payment_date);
        $stmt->bindParam(':concept', $this->concept);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

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
}
