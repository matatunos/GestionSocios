<?php

class SupplierOrderLine {
    private $conn;
    private $table_name = "supplier_order_lines";

    public $id;
    public $order_id;
    public $line_number;
    public $description;
    public $quantity;
    public $unit_price;
    public $tax_rate;
    public $discount_rate;
    public $line_total;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    order_id = :order_id,
                    line_number = :line_number,
                    description = :description,
                    quantity = :quantity,
                    unit_price = :unit_price,
                    tax_rate = :tax_rate,
                    discount_rate = :discount_rate,
                    line_total = :line_total,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->order_id = intval($this->order_id);
        $this->line_number = intval($this->line_number);
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->quantity = floatval($this->quantity ?? 1.00);
        $this->unit_price = floatval($this->unit_price ?? 0.00);
        $this->tax_rate = floatval($this->tax_rate ?? 21.00);
        $this->discount_rate = floatval($this->discount_rate ?? 0.00);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        
        // Calcular total de la línea
        $base = $this->quantity * $this->unit_price;
        $discounted = $base - ($base * $this->discount_rate / 100);
        $this->line_total = $discounted + ($discounted * $this->tax_rate / 100);

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":line_number", $this->line_number);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":tax_rate", $this->tax_rate);
        $stmt->bindParam(":discount_rate", $this->discount_rate);
        $stmt->bindParam(":line_total", $this->line_total);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readByOrderId($order_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE order_id = :order_id 
                 ORDER BY line_number ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
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
            $this->order_id = $row['order_id'];
            $this->line_number = $row['line_number'];
            $this->description = $row['description'];
            $this->quantity = $row['quantity'];
            $this->unit_price = $row['unit_price'];
            $this->tax_rate = $row['tax_rate'];
            $this->discount_rate = $row['discount_rate'];
            $this->line_total = $row['line_total'];
            $this->notes = $row['notes'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    description = :description,
                    quantity = :quantity,
                    unit_price = :unit_price,
                    tax_rate = :tax_rate,
                    discount_rate = :discount_rate,
                    line_total = :line_total,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->quantity = floatval($this->quantity ?? 1.00);
        $this->unit_price = floatval($this->unit_price ?? 0.00);
        $this->tax_rate = floatval($this->tax_rate ?? 21.00);
        $this->discount_rate = floatval($this->discount_rate ?? 0.00);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        
        // Calcular total de la línea
        $base = $this->quantity * $this->unit_price;
        $discounted = $base - ($base * $this->discount_rate / 100);
        $this->line_total = $discounted + ($discounted * $this->tax_rate / 100);
        
        $this->id = intval($this->id);

        // Bind values
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":tax_rate", $this->tax_rate);
        $stmt->bindParam(":discount_rate", $this->discount_rate);
        $stmt->bindParam(":line_total", $this->line_total);
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
        $this->id = intval($this->id);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function deleteByOrderId($order_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $order_id = intval($order_id);
        $stmt->bindParam(1, $order_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
