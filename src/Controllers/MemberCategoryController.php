<?php

require_once __DIR__ . '/../Models/MemberCategory.php';
require_once __DIR__ . '/../Models/CategoryFeeHistory.php';

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
            // Save initial fee to history for current year
            $feeHistory = new CategoryFeeHistory($this->db);
            $feeHistory->category_id = $this->db->lastInsertId();
            $feeHistory->year = date('Y');
            $feeHistory->fee_amount = $categoryModel->default_fee;
            $feeHistory->createOrUpdate();
            
            $_SESSION['success'] = 'Categoría creada correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear la categoría';
        }
        
        header('Location: index.php?page=settings#members');
        exit;
    }
    
    // Show edit form
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $categoryModel = new MemberCategory($this->db);
        $categoryModel->id = $id;
        
        if (!$categoryModel->readOne()) {
            $_SESSION['error'] = 'Categoría no encontrada';
            header('Location: index.php?page=settings#members');
            exit;
        }
        
        // Get fee history for this category
        $feeHistoryModel = new CategoryFeeHistory($this->db);
        $feeHistoryStmt = $feeHistoryModel->readByCategory($id);
        $feeHistory = $feeHistoryStmt->fetchAll(PDO::FETCH_ASSOC);
        
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
            // Update current year fee in history
            $feeHistory = new CategoryFeeHistory($this->db);
            $feeHistory->category_id = $categoryModel->id;
            $feeHistory->year = date('Y');
            $feeHistory->fee_amount = $categoryModel->default_fee;
            $feeHistory->createOrUpdate();
            
            $_SESSION['success'] = 'Categoría actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la categoría';
        }
        
        header('Location: index.php?page=settings#members');
        exit;
    }
    
    // Add or update fee for a specific year
    public function updateFee() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=settings#members');
            exit;
        }
        
        $categoryId = $_POST['category_id'] ?? 0;
        $year = $_POST['year'] ?? date('Y');
        $feeAmount = $_POST['fee_amount'] ?? 0;
        
        $feeHistory = new CategoryFeeHistory($this->db);
        $feeHistory->category_id = $categoryId;
        $feeHistory->year = $year;
        $feeHistory->fee_amount = $feeAmount;
        
        if ($feeHistory->createOrUpdate()) {
            // If updating current year, also update default_fee
            if ($year == date('Y')) {
                $categoryModel = new MemberCategory($this->db);
                $categoryModel->id = $categoryId;
                if ($categoryModel->readOne()) {
                    $categoryModel->default_fee = $feeAmount;
                    $categoryModel->update();
                }
            }
            $_SESSION['success'] = "Cuota para el año $year actualizada correctamente";
        } else {
            $_SESSION['error'] = 'Error al actualizar la cuota';
        }
        
        header("Location: index.php?page=member_categories&action=edit&id=$categoryId");
        exit;
    }
    
    // Delete fee for a specific year
    public function deleteFee() {
        $id = $_GET['id'] ?? 0;
        $categoryId = $_GET['category_id'] ?? 0;
        
        $feeHistory = new CategoryFeeHistory($this->db);
        $feeHistory->id = $id;
        
        if ($feeHistory->delete()) {
            $_SESSION['success'] = 'Cuota eliminada correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la cuota';
        }
        
        header("Location: index.php?page=member_categories&action=edit&id=$categoryId");
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
        
        header('Location: index.php?page=settings#members');
        exit;
    }
}
