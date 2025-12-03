<?php

class Fee {
    private $conn;
    private $table_name = "annual_fees";

    public $year;
    public $amount;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY year DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET year=:year, amount=:amount";
        $stmt = $this->conn->prepare($query);

        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->amount = htmlspecialchars(strip_tags($this->amount));

        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":amount", $this->amount);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Create or update if year already exists
    public function createOrUpdate() {
        $query = "INSERT INTO " . $this->table_name . " (year, amount) 
                  VALUES (:year, :amount) 
                  ON DUPLICATE KEY UPDATE amount = :amount";
        $stmt = $this->conn->prepare($query);

        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->amount = htmlspecialchars(strip_tags($this->amount));

        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":amount", $this->amount);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if a fee exists for a given year
    public function exists($year) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE year = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
