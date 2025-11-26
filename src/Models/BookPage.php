<?php
// Modelo para gestionar las páginas del libro
class BookPage {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }


    public function getAllByVersion($version_id) {
        $stmt = $this->db->prepare("SELECT * FROM book_pages WHERE version_id = :version_id ORDER BY page_number ASC, position ASC");
        $stmt->bindParam(':version_id', $version_id);

        // Modelo para gestionar las páginas del libro
        class BookPage {
            private $db;
            public function __construct($db) {
                $this->db = $db;
            }

            public function getAllByVersion($version_id) {
                $stmt = $this->db->prepare("SELECT * FROM book_pages WHERE version_id = :version_id ORDER BY page_number ASC, position ASC");
                $stmt->bindParam(':version_id', $version_id);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            public function getAllByBook($book_id) {
                $stmt = $this->db->prepare("SELECT * FROM book_pages WHERE book_id = :book_id ORDER BY page_number ASC, position ASC");
                $stmt->bindParam(':book_id', $book_id);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            public function savePages($version_id, $pages) {
                $stmt = $this->db->prepare("DELETE FROM book_pages WHERE version_id = :version_id");
                $stmt->bindParam(':version_id', $version_id);
                $stmt->execute();
                $insert = $this->db->prepare("INSERT INTO book_pages (version_id, book_id, page_number, content, position) VALUES (:version_id, :book_id, :page_number, :content, :position)");
                foreach ($pages as $idx => $page) {
                    $insert->bindParam(':version_id', $version_id);
                    $insert->bindValue(':book_id', $page['book_id'] ?? 0);
                    $insert->bindValue(':page_number', $page['page_number'] ?? ($idx + 1));
                    $insert->bindValue(':content', $page['content']);
                    $insert->bindValue(':position', $page['position'] ?? 'full');
                    $insert->execute();
                }
            }
        }
            $insert->bindValue(':position', $page['position'] ?? 'full');
