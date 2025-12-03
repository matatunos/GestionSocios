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
                    order_id = :order_id,
                    invoice_number = :invoice_number,
                    invoice_date = :invoice_date,
                    due_date = :due_date,
                    payment_date = :payment_date,
                    subtotal = :subtotal,
                    tax_amount = :tax_amount,
                    discount_amount = :discount_amount,
                    amount = :amount,
                    status = :status,
                    payment_method = :payment_method,
                    bank_reference = :bank_reference,
                    tipo_factura = :tipo_factura,
                    file_path = :file_path,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->supplier_id = intval($this->supplier_id);
        $this->order_id = !empty($this->order_id) ? intval($this->order_id) : null;
        $this->invoice_number = htmlspecialchars(strip_tags($this->invoice_number ?? ''));
        $this->invoice_date = $this->invoice_date ?? date('Y-m-d');
        $this->due_date = !empty($this->due_date) ? $this->due_date : null;
        $this->payment_date = !empty($this->payment_date) ? $this->payment_date : null;
        $this->subtotal = floatval($this->subtotal ?? 0.00);
        $this->tax_amount = floatval($this->tax_amount ?? 0.00);
        $this->discount_amount = floatval($this->discount_amount ?? 0.00);
        $this->amount = floatval($this->amount ?? 0.00);
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'pending'));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method ?? 'transfer'));
        $this->bank_reference = htmlspecialchars(strip_tags($this->bank_reference ?? ''));
        $this->tipo_factura = htmlspecialchars(strip_tags($this->tipo_factura ?? 'normal'));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path ?? ''));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));

        // Bind values
        $stmt->bindParam(":supplier_id", $this->supplier_id);
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":invoice_number", $this->invoice_number);
        $stmt->bindParam(":invoice_date", $this->invoice_date);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":payment_date", $this->payment_date);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":tax_amount", $this->tax_amount);
        $stmt->bindParam(":discount_amount", $this->discount_amount);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":bank_reference", $this->bank_reference);
        $stmt->bindParam(":tipo_factura", $this->tipo_factura);
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
            $this->order_id = $row['order_id'];
            $this->invoice_number = $row['invoice_number'];
            $this->invoice_date = $row['invoice_date'];
            $this->due_date = $row['due_date'];
            $this->payment_date = $row['payment_date'];
            $this->subtotal = $row['subtotal'];
            $this->tax_amount = $row['tax_amount'];
            $this->discount_amount = $row['discount_amount'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->bank_reference = $row['bank_reference'];
            $this->tipo_factura = $row['tipo_factura'];
            $this->file_path = $row['file_path'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    public function getOverdueInvoices() {
        $query = "SELECT si.*, s.name as supplier_name 
                 FROM " . $this->table_name . " si
                 JOIN suppliers s ON si.supplier_id = s.id
                 WHERE si.status IN ('pending', 'overdue') 
                 AND si.due_date < CURDATE()
                 ORDER BY si.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
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
