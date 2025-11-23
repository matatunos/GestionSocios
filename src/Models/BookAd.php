<?php

class BookAd {
    private $conn;
    private $table_name = "book_ads";

    public $id;
    public $donor_id;
    public $year;
    public $ad_type;
    public $amount;
    public $status;
    public $image_url;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET donor_id=:donor_id, year=:year, ad_type=:ad_type, amount=:amount, status=:status, image_url=:image_url";
        $stmt = $this->conn->prepare($query);

        $this->donor_id = htmlspecialchars(strip_tags($this->donor_id));
        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->ad_type = htmlspecialchars(strip_tags($this->ad_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        $stmt->bindParam(":donor_id", $this->donor_id);
        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":ad_type", $this->ad_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":image_url", $this->image_url);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAllByYear($year) {
        $query = "SELECT b.*, d.name as donor_name, d.logo_url as donor_logo FROM " . $this->table_name . " b JOIN donors d ON b.donor_id = d.id WHERE b.year = ? ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$year]);
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->donor_id = $row['donor_id'];
            $this->year = $row['year'];
            $this->ad_type = $row['ad_type'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->image_url = $row['image_url'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET donor_id=:donor_id, year=:year, ad_type=:ad_type, amount=:amount, status=:status, image_url=:image_url WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->donor_id = htmlspecialchars(strip_tags($this->donor_id));
        $this->year = htmlspecialchars(strip_tags($this->year));
        $this->ad_type = htmlspecialchars(strip_tags($this->ad_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":donor_id", $this->donor_id);
        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":ad_type", $this->ad_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
