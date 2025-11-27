<?php
// Modelo para gestionar libros
class Book {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function exists($book_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM books WHERE id = :id");
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO books (year, title) VALUES (:year, :title)");
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
}
