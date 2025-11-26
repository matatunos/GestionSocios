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
                        // Volcado de depuración en la respuesta JSON
                        echo json_encode([
                            'debug_book_id' => $book_id,
                            'debug_pages' => $pages
                        ]);
                        // Salir antes de guardar para depuración
                        exit;
                // Depuración: mostrar el book_id final y el de cada página
                error_log('BookPageApiController::savePages - book_id final: ' . $book_id);
                foreach ($pages as $idx => $page) {
                    error_log('Página ' . $idx . ' book_id antes: ' . ($page['book_id'] ?? 'null'));
                    $pages[$idx]['book_id'] = $book_id;
                    error_log('Página ' . $idx . ' book_id después: ' . $pages[$idx]['book_id']);
                }
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $book_id = $data['book_id'] ?? null;
        $pages = $data['pages'] ?? null;
        // Si no hay book_id, intenta crear el libro automáticamente
        require_once __DIR__ . '/../Models/Book.php';
        $bookModel = new Book($this->db);
        if (!$book_id || !$bookModel->exists($book_id)) {
            // Crear libro si no existe o si no se envió book_id
            $book_id = $bookModel->create([
                'year' => $data['year'] ?? date('Y'),
                'name' => $data['book_name'] ?? 'Libro ' . ($data['year'] ?? date('Y')),
                'created_by' => $_SESSION['user_id'] ?? 1
            ]);
        }
        if (!$pages || !is_array($pages)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
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
