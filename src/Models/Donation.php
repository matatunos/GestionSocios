<?php

class Donation {
    private $conn;
    private $table_name = "donations";

    public $id;
    public $donor_id;
    public $amount;
    public $type; // media, full, cover
    public $year;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET donor_id=:donor_id, amount=:amount, type=:type, year=:year";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":donor_id", $this->donor_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":year", $this->year);
        return $stmt->execute();
    }

    public function readAllByYear($year) {
        $query = "SELECT d.*, don.name as donor_name FROM " . $this->table_name . " d JOIN donors don ON d.donor_id = don.id WHERE d.year = ? ORDER BY d.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$year]);
        return $stmt;
    }
}
?>
