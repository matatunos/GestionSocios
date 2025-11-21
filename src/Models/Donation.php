<?php

class Donation {
    private $conn;
    private $table_name = "donations";

    public $id;
    public $member_id;
    public $amount;
    public $type; // media, full, cover
    public $year;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET member_id=:member_id, amount=:amount, type=:type, year=:year";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":year", $this->year);
        return $stmt->execute();
    }

    public function readAllByYear($year) {
        $query = "SELECT d.*, CONCAT(m.first_name, ' ', m.last_name) as member_name FROM " . $this->table_name . " d JOIN members m ON d.member_id = m.id WHERE d.year = ? ORDER BY d.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$year]);
        return $stmt;
    }
}
?>
