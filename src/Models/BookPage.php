<?php
// Modelo para gestionar las páginas del libro
class BookPage {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }


    public function getAllByBook($book_id) {
        $stmt = $this->db->prepare("SELECT * FROM book_pages WHERE book_id = :book_id ORDER BY page_number ASC, position ASC");
        $stmt->bindParam(':book_id', $book_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function savePages($book_id, $pages) {
        // Elimina las páginas actuales
        $stmt = $this->db->prepare("DELETE FROM book_pages WHERE book_id = :book_id");
        $stmt->bindParam(':book_id', $book_id);
        $stmt->execute();
        // Inserta las nuevas páginas
        $insert = $this->db->prepare("INSERT INTO book_pages (book_id, page_number, content, position) VALUES (:book_id, :page_number, :content, :position)");
        foreach ($pages as $idx => $page) {
            $insert->bindParam(':book_id', $book_id);
            $insert->bindValue(':page_number', $page['page_number'] ?? ($idx + 1));
            $insert->bindValue(':content', $page['content']);
            $insert->bindValue(':position', $page['position'] ?? 'full');
            $insert->execute();
        }
    }
}
