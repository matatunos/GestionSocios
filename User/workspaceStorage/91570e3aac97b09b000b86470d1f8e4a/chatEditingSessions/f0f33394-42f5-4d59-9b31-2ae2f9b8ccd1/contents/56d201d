<?php

class SupplierDocument {
    private $conn;
    private $table_name = "supplier_documents";

    public $id;
    public $supplier_id;
    public $document_id; // Referencia al gestor documental si existe
    public $document_type;
    public $name;
    public $file_path;
    public $description;
    public $upload_date;
    public $expiry_date;
    public $status;
    public $tags;
    public $uploaded_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    supplier_id = :supplier_id,
                    document_id = :document_id,
                    document_type = :document_type,
                    name = :name,
                    file_path = :file_path,
                    description = :description,
                    upload_date = :upload_date,
                    expiry_date = :expiry_date,
                    status = :status,
                    tags = :tags,
                    uploaded_by = :uploaded_by";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->supplier_id = intval($this->supplier_id);
        $this->document_id = !empty($this->document_id) ? intval($this->document_id) : null;
        $this->document_type = htmlspecialchars(strip_tags($this->document_type ?? 'otro'));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->upload_date = $this->upload_date ?? date('Y-m-d');
        $this->expiry_date = !empty($this->expiry_date) ? $this->expiry_date : null;
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'vigente'));
        $this->tags = htmlspecialchars(strip_tags($this->tags ?? ''));
        $this->uploaded_by = !empty($this->uploaded_by) ? intval($this->uploaded_by) : null;

        // Bind values
        $stmt->bindParam(":supplier_id", $this->supplier_id);
        $stmt->bindParam(":document_id", $this->document_id);
        $stmt->bindParam(":document_type", $this->document_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":upload_date", $this->upload_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":uploaded_by", $this->uploaded_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readBySupplierId($supplier_id, $filters = []) {
        $query = "SELECT sd.*, u.username as uploader_name 
                 FROM " . $this->table_name . " sd
                 LEFT JOIN users u ON sd.uploaded_by = u.id
                 WHERE sd.supplier_id = :supplier_id";
        
        // Filtros adicionales
        if (!empty($filters['document_type'])) {
            $query .= " AND sd.document_type = :document_type";
        }
        if (!empty($filters['status'])) {
            $query .= " AND sd.status = :status";
        }
        
        $query .= " ORDER BY sd.upload_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':supplier_id', $supplier_id);
        
        if (!empty($filters['document_type'])) {
            $stmt->bindParam(':document_type', $filters['document_type']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        
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
            $this->document_id = $row['document_id'];
            $this->document_type = $row['document_type'];
            $this->name = $row['name'];
            $this->file_path = $row['file_path'];
            $this->description = $row['description'];
            $this->upload_date = $row['upload_date'];
            $this->expiry_date = $row['expiry_date'];
            $this->status = $row['status'];
            $this->tags = $row['tags'];
            $this->uploaded_by = $row['uploaded_by'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    document_type = :document_type,
                    name = :name,
                    description = :description,
                    expiry_date = :expiry_date,
                    status = :status,
                    tags = :tags
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->document_type = htmlspecialchars(strip_tags($this->document_type ?? 'otro'));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->expiry_date = !empty($this->expiry_date) ? $this->expiry_date : null;
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'vigente'));
        $this->tags = htmlspecialchars(strip_tags($this->tags ?? ''));
        $this->id = intval($this->id);

        // Bind values
        $stmt->bindParam(":document_type", $this->document_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":tags", $this->tags);
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

    public function getExpiringDocuments($days = 30) {
        $query = "SELECT sd.*, s.name as supplier_name 
                 FROM " . $this->table_name . " sd
                 JOIN suppliers s ON sd.supplier_id = s.id
                 WHERE sd.status = 'vigente' 
                 AND sd.expiry_date IS NOT NULL
                 AND sd.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                 ORDER BY sd.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        
        return $stmt;
    }

    public function getExpiredDocuments() {
        $query = "SELECT sd.*, s.name as supplier_name 
                 FROM " . $this->table_name . " sd
                 JOIN suppliers s ON sd.supplier_id = s.id
                 WHERE sd.status = 'vigente' 
                 AND sd.expiry_date IS NOT NULL
                 AND sd.expiry_date < CURDATE()
                 ORDER BY sd.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
