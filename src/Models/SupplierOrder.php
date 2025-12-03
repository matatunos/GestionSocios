<?php

class SupplierOrder {
    private $conn;
    private $table_name = "supplier_orders";

    public $id;
    public $supplier_id;
    public $order_number;
    public $order_date;
    public $expected_delivery_date;
    public $status;
    public $subtotal;
    public $tax_amount;
    public $discount_amount;
    public $total_amount;
    public $notes;
    public $approved_by;
    public $approved_at;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    supplier_id = :supplier_id,
                    order_number = :order_number,
                    order_date = :order_date,
                    expected_delivery_date = :expected_delivery_date,
                    status = :status,
                    subtotal = :subtotal,
                    tax_amount = :tax_amount,
                    discount_amount = :discount_amount,
                    total_amount = :total_amount,
                    notes = :notes,
                    approved_by = :approved_by,
                    approved_at = :approved_at,
                    created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->supplier_id = intval($this->supplier_id);
        $this->order_number = htmlspecialchars(strip_tags($this->order_number ?? ''));
        $this->order_date = $this->order_date ?? date('Y-m-d');
        $this->expected_delivery_date = !empty($this->expected_delivery_date) ? $this->expected_delivery_date : null;
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'draft'));
        $this->subtotal = floatval($this->subtotal ?? 0.00);
        $this->tax_amount = floatval($this->tax_amount ?? 0.00);
        $this->discount_amount = floatval($this->discount_amount ?? 0.00);
        $this->total_amount = floatval($this->total_amount ?? 0.00);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->approved_by = !empty($this->approved_by) ? intval($this->approved_by) : null;
        $this->approved_at = !empty($this->approved_at) ? $this->approved_at : null;
        $this->created_by = !empty($this->created_by) ? intval($this->created_by) : null;

        // Bind values
        $stmt->bindParam(":supplier_id", $this->supplier_id);
        $stmt->bindParam(":order_number", $this->order_number);
        $stmt->bindParam(":order_date", $this->order_date);
        $stmt->bindParam(":expected_delivery_date", $this->expected_delivery_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":tax_amount", $this->tax_amount);
        $stmt->bindParam(":discount_amount", $this->discount_amount);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":approved_by", $this->approved_by);
        $stmt->bindParam(":approved_at", $this->approved_at);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readBySupplierId($supplier_id) {
        $query = "SELECT so.*, s.name as supplier_name, u.username as creator_name,
                         a.username as approver_name
                 FROM " . $this->table_name . " so
                 JOIN suppliers s ON so.supplier_id = s.id
                 LEFT JOIN users u ON so.created_by = u.id
                 LEFT JOIN users a ON so.approved_by = a.id
                 WHERE so.supplier_id = :supplier_id 
                 ORDER BY so.order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':supplier_id', $supplier_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function readAll($filters = []) {
        $query = "SELECT so.*, s.name as supplier_name, u.username as creator_name
                 FROM " . $this->table_name . " so
                 JOIN suppliers s ON so.supplier_id = s.id
                 LEFT JOIN users u ON so.created_by = u.id
                 WHERE 1=1";
        
        // Filtros
        if (!empty($filters['status'])) {
            $query .= " AND so.status = :status";
        }
        if (!empty($filters['supplier_id'])) {
            $query .= " AND so.supplier_id = :supplier_id";
        }
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(so.order_date) = :year";
        }
        
        $query .= " ORDER BY so.order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['supplier_id'])) {
            $stmt->bindParam(':supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['year'])) {
            $stmt->bindParam(':year', $filters['year']);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT so.*, s.name as supplier_name, s.email as supplier_email,
                         s.phone as supplier_phone, u.username as creator_name
                 FROM " . $this->table_name . " so
                 JOIN suppliers s ON so.supplier_id = s.id
                 LEFT JOIN users u ON so.created_by = u.id
                 WHERE so.id = ? 
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->supplier_id = $row['supplier_id'];
            $this->order_number = $row['order_number'];
            $this->order_date = $row['order_date'];
            $this->expected_delivery_date = $row['expected_delivery_date'];
            $this->status = $row['status'];
            $this->subtotal = $row['subtotal'];
            $this->tax_amount = $row['tax_amount'];
            $this->discount_amount = $row['discount_amount'];
            $this->total_amount = $row['total_amount'];
            $this->notes = $row['notes'];
            $this->approved_by = $row['approved_by'];
            $this->approved_at = $row['approved_at'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    expected_delivery_date = :expected_delivery_date,
                    status = :status,
                    subtotal = :subtotal,
                    tax_amount = :tax_amount,
                    discount_amount = :discount_amount,
                    total_amount = :total_amount,
                    notes = :notes,
                    approved_by = :approved_by,
                    approved_at = :approved_at
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->expected_delivery_date = !empty($this->expected_delivery_date) ? $this->expected_delivery_date : null;
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'draft'));
        $this->subtotal = floatval($this->subtotal ?? 0.00);
        $this->tax_amount = floatval($this->tax_amount ?? 0.00);
        $this->discount_amount = floatval($this->discount_amount ?? 0.00);
        $this->total_amount = floatval($this->total_amount ?? 0.00);
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->approved_by = !empty($this->approved_by) ? intval($this->approved_by) : null;
        $this->approved_at = !empty($this->approved_at) ? $this->approved_at : null;
        $this->id = intval($this->id);

        // Bind values
        $stmt->bindParam(":expected_delivery_date", $this->expected_delivery_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":tax_amount", $this->tax_amount);
        $stmt->bindParam(":discount_amount", $this->discount_amount);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":approved_by", $this->approved_by);
        $stmt->bindParam(":approved_at", $this->approved_at);
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

    public function approve($user_id) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    status = 'sent',
                    approved_by = :approved_by,
                    approved_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":approved_by", $user_id);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getTotalAmount($year = null) {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total
                 FROM " . $this->table_name . "
                 WHERE status IN ('confirmed', 'received')";
        
        if ($year) {
            $query .= " AND YEAR(order_date) = :year";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($year) {
            $stmt->bindParam(':year', $year);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    public function getPendingOrders() {
        $query = "SELECT COUNT(*) as count
                 FROM " . $this->table_name . "
                 WHERE status IN ('sent', 'confirmed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'];
    }
}
?>
