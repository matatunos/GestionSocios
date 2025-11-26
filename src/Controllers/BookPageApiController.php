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
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $book_id = $data['book_id'] ?? null;
        $pages = $data['pages'] ?? null;
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
