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
            'year' => isset($_GET['year']) && $_GET['year'] !== '' ? $_GET['year'] : date('Y'),
            'month' => $_GET['month'] ?? null,
            'category_id' => $_GET['category_id'] ?? null
        ];
        $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $expenseModel = new Expense($this->db);
        $expenses = $expenseModel->readAll($filters, $limit, $offset);
        $totalRecords = $expenseModel->countAll($filters);
        $totalPages = ceil($totalRecords / $limit);
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
            $uploadDir = __DIR__ . '/../../public/uploads/receipts/';
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
            $lastId = $this->db->lastInsertId();
            
            // Auditoría de alta de gasto
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'expense', $lastId, 'Alta de gasto por el usuario ' . ($_SESSION['username'] ?? ''));
            
            // Crear asiento contable automático
            require_once __DIR__ . '/../Helpers/AccountingHelper.php';
            $accountingCreated = AccountingHelper::createEntryFromExpense(
                $this->db,
                $lastId,
                $expenseModel->amount,
                $expenseModel->description,
                $expenseModel->expense_date,
                $expenseModel->payment_method,
                $expenseModel->category_id
            );
            
            if (!$accountingCreated) {
                error_log("No se pudo crear el asiento contable para el gasto #$lastId");
            }
            
            // Notificación ntfy y Telegram
            require_once __DIR__ . '/../Notifications/NotificationManager.php';
            $notifier = new NotificationManager();
            $msg = 'Nuevo gasto registrado: ' . $expenseModel->description . ' (' . number_format($expenseModel->amount, 2) . ' €)';
            $notifier->sendNtfy($msg, 'Nuevo Gasto');
            $notifier->sendTelegram($msg);
            $_SESSION['success'] = 'Gasto registrado correctamente' . ($accountingCreated ? ' y contabilizado' : '');
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
            $uploadDir = __DIR__ . '/../../public/uploads/receipts/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Delete old receipt file if exists
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
            // Auditoría de modificación de gasto
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'expense', $expenseModel->id, 'Modificación de gasto por el usuario ' . ($_SESSION['username'] ?? ''));
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
                // Auditoría de borrado de gasto
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'delete', 'expense', $expenseModel->id, 'Eliminación de gasto por el usuario ' . ($_SESSION['username'] ?? ''));
                $_SESSION['success'] = 'Gasto eliminado correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el gasto';
            }
        }
        
        header('Location: index.php?page=expenses');
        exit;
    }
}
