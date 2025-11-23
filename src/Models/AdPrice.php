<?php

class AdPrice {
    private $conn;
    private $table_name = "ad_prices";

    public $id;
    public $year;
    public $type;
    public $amount;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create or Update price for a specific year and type
    public function save() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET year=:year, type=:type, amount=:amount 
                  ON DUPLICATE KEY UPDATE amount=:amount";
        
        $stmt = $this->conn->prepare($query);

        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->amount = floatval($this->amount);

        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":amount", $this->amount);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all prices for a specific year
    public function getPricesByYear($year) {
        $query = "SELECT type, amount FROM " . $this->table_name . " WHERE year = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$year]);
        
        $prices = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prices[$row['type']] = $row['amount'];
        }
        return $prices;
    }
}
?>
