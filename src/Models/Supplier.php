<?php

class Supplier {
    private $conn;
    private $table_name = "suppliers";

    public $id;
    public $name;
    public $cif_nif;
    public $email;
    public $phone;
    public $address;
    public $website;
    public $logo_path;
    public $notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    cif_nif = :cif_nif,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    website = :website,
                    logo_path = :logo_path,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->cif_nif = htmlspecialchars(strip_tags($this->cif_nif));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->logo_path = htmlspecialchars(strip_tags($this->logo_path));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cif_nif", $this->cif_nif);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":logo_path", $this->logo_path);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
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
            $this->name = $row['name'];
            $this->cif_nif = $row['cif_nif'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->website = $row['website'];
            $this->logo_path = $row['logo_path'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    cif_nif = :cif_nif,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    website = :website,
                    logo_path = :logo_path,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->cif_nif = htmlspecialchars(strip_tags($this->cif_nif));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->logo_path = htmlspecialchars(strip_tags($this->logo_path));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cif_nif", $this->cif_nif);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":logo_path", $this->logo_path);
        $stmt->bindParam(":notes", $this->notes);
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
}
?>
