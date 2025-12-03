<?php
// Modelo para gestionar versiones del libro
class BookVersion {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllByBook($book_id) {
        $stmt = $this->db->prepare("SELECT * FROM book_versions WHERE book_id = :book_id ORDER BY created_at DESC");
        $stmt->bindParam(':book_id', $book_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM book_versions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($book_id, $name, $created_by) {
        $stmt = $this->db->prepare("INSERT INTO book_versions (book_id, name, created_by) VALUES (:book_id, :name, :created_by)");
        $stmt->bindParam(':book_id', $book_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':created_by', $created_by);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM book_versions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
