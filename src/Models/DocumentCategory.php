<?php
class DocumentCategory {
    private $conn;
    private $table = 'document_categories';

    public $id;
    public $name;
    public $description;
    public $color;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $description = '', $color = null) {
        $query = "INSERT INTO " . $this->table . " (name, description, color) VALUES (:name, :description, :color)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':color', $color);
        return $stmt->execute();
    }
}
