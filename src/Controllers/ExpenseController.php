<?php

require_once __DIR__ . '/../Models/Expense.php';
require_once __DIR__ . '/../Models/ExpenseCategory.php';

class ExpenseController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    // List all expenses
    public function index() {
        $filters = [
            'year' => $_GET['year'] ?? date('Y'),
            'month' => $_GET['month'] ?? null,
            'category_id' => $_GET['category_id'] ?? null
        ];
        
        $expenseModel = new Expense($this->db);
        $expenses = $expenseModel->readAll($filters);
        
        $categoryModel = new ExpenseCategory($this->db);
        $categories = $categoryModel->readAll();
        
        // Get statistics
        $yearTotal = Expense::getTotalByPeriod($this->db, $filters['year']);
        $monthTotal = $filters['month'] ? Expense::getTotalByPeriod($this->db, $filters['year'], $filters['month']) : 0;
        $byCategory = Expense::getByCategory($this->db, $filters['year']);
        
        require_once __DIR__ . '/../Views/expenses/index.php';
    }
    
    // Show create form
    public function create() {
        $categoryModel = new ExpenseCategory($this->db);
        $categories = $categoryModel->readAll();
        
        require_once __DIR__ . '/../Views/expenses/create.php';
    }
    
    // Store new expense
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=expenses');
            exit;
        }
        
        $expenseModel = new Expense($this->db);
        $expenseModel->category_id = $_POST['category_id'] ?? null;
        $expenseModel->description = $_POST['description'] ?? '';
        $expenseModel->amount = $_POST['amount'] ?? 0;
        $expenseModel->expense_date = $_POST['expense_date'] ?? date('Y-m-d');
        $expenseModel->payment_method = $_POST['payment_method'] ?? 'transfer';
        $expenseModel->invoice_number = $_POST['invoice_number'] ?? null;
        $expenseModel->provider = $_POST['provider'] ?? null;
        $expenseModel->notes = $_POST['notes'] ?? null;
        $expenseModel->created_by = $_SESSION['user_id'];
        
        // Handle file upload
        $expenseModel->receipt_file = null;
        if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === 0) {
            $uploadDir = __DIR__ . '/../../uploads/receipts/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $ext = pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION);
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $uploadDir . $filename)) {
                $expenseModel->receipt_file = $filename;
            }
        }
        
        if ($expenseModel->create()) {
            $_SESSION['success'] = 'Gasto registrado correctamente';
        } else {
            $_SESSION['error'] = 'Error al registrar el gasto';
        }
        
        header('Location: index.php?page=expenses');
        exit;
    }
    
    // Show edit form
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $expenseModel = new Expense($this->db);
        $expenseModel->id = $id;
        
        if (!$expenseModel->readOne()) {
            $_SESSION['error'] = 'Gasto no encontrado';
            header('Location: index.php?page=expenses');
            exit;
        }
        
        $categoryModel = new ExpenseCategory($this->db);
        $categories = $categoryModel->readAll();
        
        $expense = $expenseModel;
        require_once __DIR__ . '/../Views/expenses/edit.php';
    }
    
    // Update expense
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=expenses');
            exit;
        }
        
        $expenseModel = new Expense($this->db);
        $expenseModel->id = $_POST['id'] ?? 0;
        
        if (!$expenseModel->readOne()) {
            $_SESSION['error'] = 'Gasto no encontrado';
            header('Location: index.php?page=expenses');
            exit;
        }
        
        $expenseModel->category_id = $_POST['category_id'] ?? null;
        $expenseModel->description = $_POST['description'] ?? '';
        $expenseModel->amount = $_POST['amount'] ?? 0;
        $expenseModel->expense_date = $_POST['expense_date'] ?? date('Y-m-d');
        $expenseModel->payment_method = $_POST['payment_method'] ?? 'transfer';
        $expenseModel->invoice_number = $_POST['invoice_number'] ?? null;
        $expenseModel->provider = $_POST['provider'] ?? null;
        $expenseModel->notes = $_POST['notes'] ?? null;
        
        // Handle file upload
        if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === 0) {
            $uploadDir = __DIR__ . '/../../uploads/receipts/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Delete old file
            if ($expenseModel->receipt_file && file_exists($uploadDir . $expenseModel->receipt_file)) {
                unlink($uploadDir . $expenseModel->receipt_file);
            }
            
            $ext = pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION);
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $uploadDir . $filename)) {
                $expenseModel->receipt_file = $filename;
            }
        }
        
        if ($expenseModel->update()) {
            $_SESSION['success'] = 'Gasto actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el gasto';
        }
        
        header('Location: index.php?page=expenses');
        exit;
    }
    
    // Delete expense
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        $expenseModel = new Expense($this->db);
        $expenseModel->id = $id;
        
        if (!$expenseModel->readOne()) {
            $_SESSION['error'] = 'Gasto no encontrado';
        } else {
            if ($expenseModel->delete()) {
                $_SESSION['success'] = 'Gasto eliminado correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el gasto';
            }
        }
        
        header('Location: index.php?page=expenses');
        exit;
    }
}
