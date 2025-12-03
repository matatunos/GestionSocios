<?php

class SupplierInvoice {
    private $conn;
    private $table_name = "supplier_invoices";

    public $id;
    public $supplier_id;
    public $order_id;
    public $invoice_number;
    public $invoice_date;
    public $due_date;
    public $payment_date;
    public $subtotal;
    public $tax_amount;
    public $discount_amount;
    public $amount;
    public $status;
    public $payment_method;
    public $bank_reference;
    public $tipo_factura;
    public $file_path;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    supplier_id = :supplier_id,
                    invoice_number = :invoice_number,
                    invoice_date = :invoice_date,
                    amount = :amount,
                    status = :status,
                    file_path = :file_path,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->supplier_id = htmlspecialchars(strip_tags($this->supplier_id));
        $this->invoice_number = htmlspecialchars(strip_tags($this->invoice_number));
        $this->invoice_date = htmlspecialchars(strip_tags($this->invoice_date));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":supplier_id", $this->supplier_id);
        $stmt->bindParam(":invoice_number", $this->invoice_number);
        $stmt->bindParam(":invoice_date", $this->invoice_date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getBySupplierId($supplier_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE supplier_id = ? ORDER BY invoice_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $supplier_id);
        $stmt->execute();
        return $stmt;
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
    
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->supplier_id = $row['supplier_id'];
            $this->invoice_number = $row['invoice_number'];
            $this->invoice_date = $row['invoice_date'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->file_path = $row['file_path'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Statistics Methods

    public function getTotalAmount($year = null) {
        $query = "SELECT SUM(amount) as total FROM " . $this->table_name;
        if ($year) {
            $query .= " WHERE YEAR(invoice_date) = :year";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($year) {
            $stmt->bindParam(':year', $year);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getPendingAmount() {
        $query = "SELECT SUM(amount) as total FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getTopSuppliers($limit = 5, $year = null) {
        $query = "SELECT s.name, SUM(i.amount) as total_amount, COUNT(i.id) as invoice_count 
                  FROM " . $this->table_name . " i
                  JOIN suppliers s ON i.supplier_id = s.id";
        
        if ($year) {
            $query .= " WHERE YEAR(i.invoice_date) = :year";
        }
        
        $query .= " GROUP BY s.id ORDER BY total_amount DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        if ($year) {
            $stmt->bindParam(':year', $year);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyStats($year) {
        $query = "SELECT MONTH(invoice_date) as month, SUM(amount) as total 
                  FROM " . $this->table_name . " 
                  WHERE YEAR(invoice_date) = :year 
                  GROUP BY MONTH(invoice_date) 
                  ORDER BY month ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns [month => total]
        
        // Fill missing months with 0
        $stats = [];
        for ($i = 1; $i <= 12; $i++) {
            $stats[$i] = $results[$i] ?? 0;
        }
        return $stats;
    }

    public function getRecentInvoices($limit = 5) {
        $query = "SELECT i.*, s.name as supplier_name 
                  FROM " . $this->table_name . " i
                  JOIN suppliers s ON i.supplier_id = s.id
                  ORDER BY i.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
