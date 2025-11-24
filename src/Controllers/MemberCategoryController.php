<?php

require_once __DIR__ . '/../Models/MemberCategory.php';

class MemberCategoryController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    // List all categories
    public function index() {
        $categoryModel = new MemberCategory($this->db);
        $categories = $categoryModel->readAllIncludingInactive();
        $statistics = MemberCategory::getStatistics($this->db);
        
        require_once __DIR__ . '/../Views/member_categories/index.php';
    }
    
    // Show create form
    public function create() {
        require_once __DIR__ . '/../Views/member_categories/create.php';
    }
    
    // Store new category
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=member_categories');
            exit;
        }
        
        $categoryModel = new MemberCategory($this->db);
        $categoryModel->name = $_POST['name'] ?? '';
        $categoryModel->description = $_POST['description'] ?? '';
        $categoryModel->default_fee = $_POST['default_fee'] ?? 0;
        $categoryModel->color = $_POST['color'] ?? '#6366f1';
        $categoryModel->is_active = isset($_POST['is_active']) ? 1 : 0;
        $categoryModel->display_order = $_POST['display_order'] ?? 0;
        
        if ($categoryModel->create()) {
            $_SESSION['success'] = 'Categoría creada correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear la categoría';
        }
        
        header('Location: index.php?page=settings');
        exit;
    }
    
    // Show edit form
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $categoryModel = new MemberCategory($this->db);
        $categoryModel->id = $id;
        
        if (!$categoryModel->readOne()) {
            $_SESSION['error'] = 'Categoría no encontrada';
            header('Location: index.php?page=settings');
            exit;
        }
        
        $category = $categoryModel;
        require_once __DIR__ . '/../Views/member_categories/edit.php';
    }
    
    // Update category
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=member_categories');
            exit;
        }
        
        $categoryModel = new MemberCategory($this->db);
        $categoryModel->id = $_POST['id'] ?? 0;
        $categoryModel->name = $_POST['name'] ?? '';
        $categoryModel->description = $_POST['description'] ?? '';
        $categoryModel->default_fee = $_POST['default_fee'] ?? 0;
        $categoryModel->color = $_POST['color'] ?? '#6366f1';
        $categoryModel->is_active = isset($_POST['is_active']) ? 1 : 0;
        $categoryModel->display_order = $_POST['display_order'] ?? 0;
        
        if ($categoryModel->update()) {
            $_SESSION['success'] = 'Categoría actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la categoría';
        }
        
        header('Location: index.php?page=settings');
        exit;
    }
    
    // Delete category
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        $categoryModel = new MemberCategory($this->db);
        $categoryModel->id = $id;
        
        // Check if category has members
        $memberCount = $categoryModel->countMembers();
        
        if ($memberCount > 0) {
            $_SESSION['error'] = "No se puede eliminar la categoría porque tiene $memberCount socio(s) asignado(s)";
        } else {
            if ($categoryModel->delete()) {
                $_SESSION['success'] = 'Categoría eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la categoría';
            }
        }
        
        header('Location: index.php?page=settings');
        exit;
    }
}
