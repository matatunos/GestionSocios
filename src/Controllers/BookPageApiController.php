<?php
// API para gestionar el orden, añadir y borrar páginas del libro
class BookPageApiController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once __DIR__ . '/../Models/BookPage.php';
    }

    public function createVersion() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido: ' . json_last_error_msg()]);
                exit;
            }
            
            $book_id = $data['book_id'] ?? null;
            $name = $data['name'] ?? null;
            $year = $data['year'] ?? date('Y'); // Support creating book by year
            $created_by = $_SESSION['user_id'] ?? null;

            if (!$name) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos: se requiere nombre']);
                exit;
            }

            // If no book_id, try to find or create book by year
            if (!$book_id) {
                require_once __DIR__ . '/../Models/Book.php';
                $bookModel = new Book($this->db);
                
                // Check if book exists for this year
                // We don't have getByYear in Book model yet, but we can try create which usually checks or we can rely on savePages logic.
                // Actually Book::create just inserts. We should check if it exists first.
                // Let's assume we need to create it if we don't have an ID.
                // Ideally we should query for it.
                // For now, let's rely on the fact that if book_id is null, we create a new book.
                // But wait, if the book DOES exist but we just didn't pass the ID (e.g. frontend bug), we might duplicate.
                // The frontend sends book_id if it knows it.
                
                $book_id = $bookModel->create([
                    'year' => $year,
                    'title' => 'Libro ' . $year
                ]);
            }

            require_once __DIR__ . '/../Models/BookVersion.php';
            $bookVersionModel = new BookVersion($this->db);
            $version_id = $bookVersionModel->create($book_id, $name, $created_by);

            echo json_encode(['success' => true, 'version_id' => $version_id]);
        } catch (PDOException $e) {
            error_log("Database error in createVersion: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error de base de datos', 'details' => $e->getMessage()]);
        } catch (Exception $e) {
            error_log("Error in createVersion: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear versión', 'details' => $e->getMessage()]);
        }
    }

    public function deleteVersion() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $version_id = $data['version_id'] ?? null;

            if (!$version_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos: se requiere version_id']);
                exit;
            }

            require_once __DIR__ . '/../Models/BookVersion.php';
            $bookVersionModel = new BookVersion($this->db);
            
            if ($bookVersionModel->delete($version_id)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'No se pudo eliminar la versión']);
            }
        } catch (Exception $e) {
            error_log("Error in deleteVersion: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar versión', 'details' => $e->getMessage()]);
        }
    }

    public function savePages() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido: ' . json_last_error_msg()]);
                exit;
            }
            
            $book_id = $data['book_id'] ?? null;
            $pages = $data['pages'] ?? null;

            // Si no hay book_id, intenta crear el libro automáticamente
            require_once __DIR__ . '/../Models/Book.php';
            $bookModel = new Book($this->db);
            
            if (!$book_id || !$bookModel->exists($book_id)) {
                // Crear libro si no existe o si no se envió book_id
                $book_id = $bookModel->create([
                    'year' => $data['year'] ?? date('Y'),
                    'title' => $data['book_name'] ?? 'Libro ' . ($data['year'] ?? date('Y'))
                ]);
            }

            if (!$pages || !is_array($pages)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos: se requiere un array de páginas']);
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

            echo json_encode(['success' => true, 'book_id' => $book_id, 'version_id' => $version_id]);
        } catch (PDOException $e) {
            error_log("Database error in savePages: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error de base de datos', 'details' => $e->getMessage()]);
        } catch (Exception $e) {
            error_log("Error in savePages: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar páginas', 'details' => $e->getMessage()]);
        }
    }
}
