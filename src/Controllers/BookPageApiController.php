<?php
// API para gestionar el orden, añadir y borrar páginas del libro
class BookPageApiController {
    private $db;
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once __DIR__ . '/../Models/BookPage.php';
    }

    public function savePages() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        $book_id = $_POST['book_id'] ?? null;
        $pages = $_POST['pages'] ?? null;
        if (!$book_id || !$pages || !is_array($pages)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        $bookPageModel = new BookPage($this->db);
        $bookPageModel->savePages($book_id, $pages);
        echo json_encode(['success' => true]);
    }
}
