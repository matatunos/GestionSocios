<?php
// API para gestionar el orden, añadir y borrar páginas del libro
class BookPageApiController {
<<<<<<< HEAD
        public function createVersion() {
            if (($_SESSION['role'] ?? '') !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                exit;
            }
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $book_id = $data['book_id'] ?? null;
            $name = $data['name'] ?? null;
            $created_by = $_SESSION['user_id'] ?? null;
            if (!$book_id || !$name || !$created_by) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }
            require_once __DIR__ . '/../Models/BookVersion.php';
            $bookVersionModel = new BookVersion($this->db);
            $version_id = $bookVersionModel->create($book_id, $name, $created_by);
            echo json_encode(['success' => true, 'version_id' => $version_id]);
        }
=======
>>>>>>> 080e6c6929499a75caee3baefc568c3193113258
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
        require_once __DIR__ . '/../Models/Book.php';
        $bookModel = new Book($this->db);
        if (!$bookModel->exists($book_id)) {
            // Crear libro si no existe
            $book_id = $bookModel->create([
            'year' => $data['year'] ?? date('Y'),
            'name' => $data['book_name'] ?? 'Libro ' . ($data['year'] ?? date('Y')),
            'created_by' => $_SESSION['user_id'] ?? 1
            ]);
        }
        $version_id = $data['version_id'] ?? null;
        if (!$version_id) {
            require_once __DIR__ . '/../Models/BookVersion.php';
            $bookVersionModel = new BookVersion($this->db);
            $version_id = $bookVersionModel->create($book_id, $data['version_name'] ?? 'Versión automática', $_SESSION['user_id'] ?? 1);
        }
        // Actualizar el book_id en cada página
        foreach ($pages as $idx => $page) {
            $pages[$idx]['book_id'] = $book_id;
        }
        $bookPageModel = new BookPage($this->db);
        $bookPageModel->savePages($version_id, $pages);
        echo json_encode(['success' => true]);
    }
}
