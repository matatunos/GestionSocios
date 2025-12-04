<?php

class SupplierContact {
    private $conn;
    private $table_name = "supplier_contacts";

    public $id;
    public $supplier_id;
    public $name;
    public $position;
    public $email;
    public $phone;
    public $mobile;
    public $is_primary;
    public $notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    supplier_id = :supplier_id,
                    name = :name,
                    position = :position,
                    email = :email,
                    phone = :phone,
                    mobile = :mobile,
                    is_primary = :is_primary,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->supplier_id = intval($this->supplier_id);
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->position = htmlspecialchars(strip_tags($this->position ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile ?? ''));
        $this->is_primary = intval($this->is_primary ?? 0);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));

        // Bind values
        $stmt->bindParam(":supplier_id", $this->supplier_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":mobile", $this->mobile);
        $stmt->bindParam(":is_primary", $this->is_primary);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Si es contacto principal, quitar el flag de otros contactos
            if ($this->is_primary) {
                $updateQuery = "UPDATE " . $this->table_name . " 
                               SET is_primary = 0 
                               WHERE supplier_id = :supplier_id AND id != :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":supplier_id", $this->supplier_id);
                $updateStmt->bindParam(":id", $this->id);
                $updateStmt->execute();
            }
            
            return true;
        }

        return false;
    }

    public function readBySupplierId($supplier_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE supplier_id = :supplier_id 
                 ORDER BY is_primary DESC, name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':supplier_id', $supplier_id);
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
            $this->supplier_id = $row['supplier_id'];
            $this->name = $row['name'];
            $this->position = $row['position'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->mobile = $row['mobile'];
            $this->is_primary = $row['is_primary'];
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
                    position = :position,
                    email = :email,
                    phone = :phone,
                    mobile = :mobile,
                    is_primary = :is_primary,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->position = htmlspecialchars(strip_tags($this->position ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile ?? ''));
        $this->is_primary = intval($this->is_primary ?? 0);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->id = intval($this->id);

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":mobile", $this->mobile);
        $stmt->bindParam(":is_primary", $this->is_primary);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            // Si es contacto principal, quitar el flag de otros contactos
            if ($this->is_primary) {
                $updateQuery = "UPDATE " . $this->table_name . " 
                               SET is_primary = 0 
                               WHERE supplier_id = :supplier_id AND id != :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":supplier_id", $this->supplier_id);
                $updateStmt->bindParam(":id", $this->id);
                $updateStmt->execute();
            }
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = intval($this->id);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
