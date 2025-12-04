<?php

class IssuedInvoice {
    private $conn;
    private $table = 'issued_invoices';
    
    public $id;
    public $series_id;
    public $invoice_number;
    public $full_number;
    public $issue_date;
    public $due_date;
    
    public $customer_type;
    public $member_id;
    public $customer_name;
    public $customer_tax_id;
    public $customer_address;
    public $customer_city;
    public $customer_postal_code;
    public $customer_country;
    public $customer_email;
    public $customer_phone;
    
    public $description;
    public $notes;
    
    public $subtotal;
    public $tax_rate;
    public $tax_amount;
    public $discount_rate;
    public $discount_amount;
    public $total;
    
    public $status;
    public $payment_method;
    public $payment_date;
    
    public $reference;
    public $accounting_entry_id;
    public $pdf_path;
    
    public $created_by;
    public $created_at;
    public $issued_by;
    public $issued_at;
    public $cancelled_by;
    public $cancelled_at;
    public $cancellation_reason;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Generar siguiente número de factura para una serie
     */
    public function generateInvoiceNumber($series_id) {
        try {
            // Obtener serie
            $query = "SELECT prefix, next_number FROM invoice_series WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$series_id]);
            $series = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$series) {
                return false;
            }
            
            $prefix = $series['prefix'];
            $number = $series['next_number'];
            $year = date('Y');
            
            // Formato: F2025/0001
            $invoice_number = sprintf("%04d", $number);
            $full_number = "{$prefix}{$year}/{$invoice_number}";
            
            // Incrementar contador de la serie
            $updateQuery = "UPDATE invoice_series SET next_number = next_number + 1 WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$series_id]);
            
            return [
                'invoice_number' => $invoice_number,
                'full_number' => $full_number
            ];
            
        } catch (PDOException $e) {
            error_log("Error generando número de factura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear factura con líneas
     */
    public function create($lines = []) {
        try {
            $this->conn->beginTransaction();
            
            // Generar número de factura
            $numberData = $this->generateInvoiceNumber($this->series_id);
            if (!$numberData) {
                throw new Exception("No se pudo generar el número de factura");
            }
            
            $this->invoice_number = $numberData['invoice_number'];
            $this->full_number = $numberData['full_number'];
            
            // Calcular totales si no están definidos
            if (empty($this->subtotal) && !empty($lines)) {
                $this->calculateTotals($lines);
            }
            
            $query = "INSERT INTO " . $this->table . " 
                (series_id, invoice_number, full_number, issue_date, due_date,
                 customer_type, member_id, customer_name, customer_tax_id, 
                 customer_address, customer_city, customer_postal_code, customer_country,
                 customer_email, customer_phone, description, notes,
                 subtotal, tax_rate, tax_amount, discount_rate, discount_amount, total,
                 status, payment_method, reference, created_by)
                VALUES 
                (:series_id, :invoice_number, :full_number, :issue_date, :due_date,
                 :customer_type, :member_id, :customer_name, :customer_tax_id,
                 :customer_address, :customer_city, :customer_postal_code, :customer_country,
                 :customer_email, :customer_phone, :description, :notes,
                 :subtotal, :tax_rate, :tax_amount, :discount_rate, :discount_amount, :total,
                 :status, :payment_method, :reference, :created_by)";
                 
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':series_id', $this->series_id);
            $stmt->bindParam(':invoice_number', $this->invoice_number);
            $stmt->bindParam(':full_number', $this->full_number);
            $stmt->bindParam(':issue_date', $this->issue_date);
            $stmt->bindParam(':due_date', $this->due_date);
            $stmt->bindParam(':customer_type', $this->customer_type);
            $stmt->bindParam(':member_id', $this->member_id);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':customer_tax_id', $this->customer_tax_id);
            $stmt->bindParam(':customer_address', $this->customer_address);
            $stmt->bindParam(':customer_city', $this->customer_city);
            $stmt->bindParam(':customer_postal_code', $this->customer_postal_code);
            $stmt->bindParam(':customer_country', $this->customer_country);
            $stmt->bindParam(':customer_email', $this->customer_email);
            $stmt->bindParam(':customer_phone', $this->customer_phone);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':notes', $this->notes);
            $stmt->bindParam(':subtotal', $this->subtotal);
            $stmt->bindParam(':tax_rate', $this->tax_rate);
            $stmt->bindParam(':tax_amount', $this->tax_amount);
            $stmt->bindParam(':discount_rate', $this->discount_rate);
            $stmt->bindParam(':discount_amount', $this->discount_amount);
            $stmt->bindParam(':total', $this->total);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':payment_method', $this->payment_method);
            $stmt->bindParam(':reference', $this->reference);
            $stmt->bindParam(':created_by', $this->created_by);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al crear la factura");
            }
            
            $this->id = $this->conn->lastInsertId();
            
            // Insertar líneas de factura
            if (!empty($lines)) {
                $this->createLines($lines);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en IssuedInvoice::create(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear líneas de factura
     */
    private function createLines($lines) {
        $query = "INSERT INTO issued_invoice_lines 
            (invoice_id, line_order, concept, description, quantity, unit_price, 
             discount_rate, tax_rate, subtotal, discount_amount, tax_amount, total)
            VALUES 
            (:invoice_id, :line_order, :concept, :description, :quantity, :unit_price,
             :discount_rate, :tax_rate, :subtotal, :discount_amount, :tax_amount, :total)";
             
        $stmt = $this->conn->prepare($query);
        
        foreach ($lines as $index => $line) {
            // Calcular totales de línea
            $quantity = floatval($line['quantity']);
            $unit_price = floatval($line['unit_price']);
            $discount_rate = floatval($line['discount_rate'] ?? 0);
            $tax_rate = floatval($line['tax_rate']);
            
            $subtotal = $quantity * $unit_price;
            $discount_amount = $subtotal * ($discount_rate / 100);
            $base = $subtotal - $discount_amount;
            $tax_amount = $base * ($tax_rate / 100);
            $total = $base + $tax_amount;
            
            $stmt->execute([
                ':invoice_id' => $this->id,
                ':line_order' => $index + 1,
                ':concept' => $line['concept'],
                ':description' => $line['description'] ?? '',
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':discount_rate' => $discount_rate,
                ':tax_rate' => $tax_rate,
                ':subtotal' => $subtotal,
                ':discount_amount' => $discount_amount,
                ':tax_amount' => $tax_amount,
                ':total' => $total
            ]);
        }
    }
    
    /**
     * Calcular totales desde las líneas
     */
    private function calculateTotals($lines) {
        $this->subtotal = 0;
        $this->discount_amount = 0;
        $this->tax_amount = 0;
        $this->total = 0;
        
        foreach ($lines as $line) {
            $quantity = floatval($line['quantity']);
            $unit_price = floatval($line['unit_price']);
            $discount_rate = floatval($line['discount_rate'] ?? 0);
            $tax_rate = floatval($line['tax_rate']);
            
            $line_subtotal = $quantity * $unit_price;
            $line_discount = $line_subtotal * ($discount_rate / 100);
            $line_base = $line_subtotal - $line_discount;
            $line_tax = $line_base * ($tax_rate / 100);
            
            $this->subtotal += $line_subtotal;
            $this->discount_amount += $line_discount;
            $this->tax_amount += $line_tax;
        }
        
        $this->total = $this->subtotal - $this->discount_amount + $this->tax_amount;
        
        // Calcular tasa de impuesto promedio
        $base = $this->subtotal - $this->discount_amount;
        $this->tax_rate = $base > 0 ? ($this->tax_amount / $base) * 100 : 0;
    }
    
    /**
     * Leer una factura
     */
    public function readOne() {
        $query = "SELECT i.*, s.name as series_name, s.prefix as series_prefix,
                         m.first_name, m.last_name,
                         u1.username as created_by_name,
                         u2.username as issued_by_name
                  FROM " . $this->table . " i
                  LEFT JOIN invoice_series s ON i.series_id = s.id
                  LEFT JOIN members m ON i.member_id = m.id
                  LEFT JOIN users u1 ON i.created_by = u1.id
                  LEFT JOIN users u2 ON i.issued_by = u2.id
                  WHERE i.id = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            foreach ($row as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Leer líneas de factura
     */
    public function readLines() {
        $query = "SELECT * FROM issued_invoice_lines 
                  WHERE invoice_id = ? 
                  ORDER BY line_order";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar facturas con filtros
     */
    public function readAll($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT i.*, s.name as series_name, s.prefix as series_prefix,
                         m.first_name, m.last_name
                  FROM " . $this->table . " i
                  LEFT JOIN invoice_series s ON i.series_id = s.id
                  LEFT JOIN members m ON i.member_id = m.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['series_id'])) {
            $query .= " AND i.series_id = ?";
            $params[] = $filters['series_id'];
        }
        
        if (!empty($filters['customer_type'])) {
            $query .= " AND i.customer_type = ?";
            $params[] = $filters['customer_type'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND i.issue_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND i.issue_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (i.full_number LIKE ? OR i.customer_name LIKE ? OR i.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY i.issue_date DESC, i.id DESC";
        
        // Añadir LIMIT/OFFSET como integers directos (PDO no acepta placeholders para esto)
        $limit = (int)$limit;
        $offset = (int)$offset;
        $query .= " LIMIT $limit OFFSET $offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Contar facturas con filtros
     */
    public function count($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['series_id'])) {
            $query .= " AND series_id = ?";
            $params[] = $filters['series_id'];
        }
        
        if (!empty($filters['customer_type'])) {
            $query .= " AND customer_type = ?";
            $params[] = $filters['customer_type'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND issue_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND issue_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (full_number LIKE ? OR customer_name LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    /**
     * Emitir factura (cambiar estado a issued)
     */
    public function issue($user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'issued', 
                      issued_by = ?, 
                      issued_at = NOW()
                  WHERE id = ? AND status = 'draft'";
                  
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $this->id]);
    }
    
    /**
     * Marcar como pagada
     */
    public function markAsPaid($payment_date, $payment_method = null) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'paid', 
                      payment_date = ?,
                      payment_method = COALESCE(?, payment_method)
                  WHERE id = ?";
                  
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$payment_date, $payment_method, $this->id]);
    }
    
    /**
     * Cancelar factura
     */
    public function cancel($user_id, $reason) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'cancelled', 
                      cancelled_by = ?, 
                      cancelled_at = NOW(),
                      cancellation_reason = ?
                  WHERE id = ?";
                  
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $reason, $this->id]);
    }
    
    /**
     * Obtener series de facturación activas
     */
    public static function getSeries($db, $active_only = true) {
        $query = "SELECT * FROM invoice_series";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY is_default DESC, code ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener serie por defecto
     */
    public static function getDefaultSeries($db) {
        $query = "SELECT * FROM invoice_series WHERE is_default = 1 LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
