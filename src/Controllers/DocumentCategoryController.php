<?php
require_once __DIR__ . '/../Models/DocumentCategory.php';
class DocumentCategoryController {
    private $db;
    private $categoryModel;
    public function __construct($db) {
        $this->db = $db;
        $this->categoryModel = new DocumentCategory($db);
    }
    public function index() {
        $categories = $this->categoryModel->readAll();
        require_once __DIR__ . '/../Views/document_categories/index.php';
    }
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $color = $_POST['color'] ?? '#6366f1';
            if ($name) {
                $this->categoryModel->create($name, $description, $color);
            }
        }
        header('Location: index.php?page=document_categories');
        exit;
    }
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $stmt = $this->db->prepare('DELETE FROM document_categories WHERE id = ?');
            $stmt->execute([$id]);
        }
        header('Location: index.php?page=document_categories');
        exit;
    }
}
