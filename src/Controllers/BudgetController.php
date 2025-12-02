<?php

require_once __DIR__ . '/../Models/Budget.php';
require_once __DIR__ . '/../Models/AccountingAccount.php';

class BudgetController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    // List budgets
    public function index() {
        $filters = [
            'fiscal_year' => $_GET['fiscal_year'] ?? '',
            'status' => $_GET['status'] ?? '',
            'period_type' => $_GET['period_type'] ?? ''
        ];
        
        $budgetModel = new Budget($this->db);
        $budgets = $budgetModel->readAll($filters);
        
        require_once __DIR__ . '/../Views/accounting/budgets/index.php';
    }
    
    // Create budget
    public function create() {
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll(['is_active' => 1]);
        
        require_once __DIR__ . '/../Views/accounting/budgets/create.php';
    }
    
    // Store budget
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=budget');
            exit;
        }
        
        $budgetModel = new Budget($this->db);
        $budgetModel->name = $_POST['name'] ?? '';
        $budgetModel->description = $_POST['description'] ?? '';
        $budgetModel->fiscal_year = $_POST['fiscal_year'] ?? date('Y');
        $budgetModel->account_id = $_POST['account_id'] ?? null;
        $budgetModel->amount = $_POST['amount'] ?? 0;
        $budgetModel->period_type = $_POST['period_type'] ?? 'yearly';
        $budgetModel->period_number = !empty($_POST['period_number']) ? $_POST['period_number'] : null;
        $budgetModel->status = $_POST['status'] ?? 'draft';
        $budgetModel->created_by = $_SESSION['user_id'];
        
        if ($budgetModel->create()) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'budget', $this->db->lastInsertId(), 'Presupuesto creado');
            
            $_SESSION['success'] = 'Presupuesto creado correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear el presupuesto';
        }
        
        header('Location: index.php?page=budget');
        exit;
    }
    
    // Edit budget
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $budgetModel = new Budget($this->db);
        if (!$budgetModel->readOne($id)) {
            $_SESSION['error'] = 'Presupuesto no encontrado';
            header('Location: index.php?page=budget');
            exit;
        }
        
        // Convert model object to array for view
        $budget = [
            'id' => $budgetModel->id,
            'name' => $budgetModel->name,
            'description' => $budgetModel->description,
            'fiscal_year' => $budgetModel->fiscal_year,
            'account_id' => $budgetModel->account_id,
            'amount' => $budgetModel->amount,
            'period_type' => $budgetModel->period_type,
            'period_number' => $budgetModel->period_number,
            'status' => $budgetModel->status
        ];
        
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll(['is_active' => 1]);
        
        require_once __DIR__ . '/../Views/accounting/budgets/edit.php';
    }
    
    // Update budget
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=budget');
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        $budgetModel = new Budget($this->db);
        if (!$budgetModel->readOne($id)) {
            $_SESSION['error'] = 'Presupuesto no encontrado';
            header('Location: index.php?page=budget');
            exit;
        }
        
        $budgetModel->name = $_POST['name'] ?? '';
        $budgetModel->description = $_POST['description'] ?? '';
        $budgetModel->fiscal_year = $_POST['fiscal_year'] ?? date('Y');
        $budgetModel->account_id = $_POST['account_id'] ?? null;
        $budgetModel->amount = $_POST['amount'] ?? 0;
        $budgetModel->period_type = $_POST['period_type'] ?? 'yearly';
        $budgetModel->period_number = !empty($_POST['period_number']) ? $_POST['period_number'] : null;
        $budgetModel->status = $_POST['status'] ?? 'draft';
        
        if ($budgetModel->update()) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'budget', $id, 'Presupuesto actualizado');
            
            $_SESSION['success'] = 'Presupuesto actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el presupuesto';
        }
        
        header('Location: index.php?page=budget');
        exit;
    }
    
    // Delete budget
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        $budgetModel = new Budget($this->db);
        if ($budgetModel->delete($id)) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'delete', 'budget', $id, 'Presupuesto eliminado');
            
            $_SESSION['success'] = 'Presupuesto eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el presupuesto';
        }
        
        header('Location: index.php?page=budget');
        exit;
    }
    
    // Budget vs Actual Report
    public function report() {
        $selectedYear = $_GET['fiscal_year'] ?? date('Y');
        $selectedAccountId = $_GET['account_id'] ?? null;
        
        $budgetData = Budget::getBudgetVsActual($this->db, $selectedYear, $selectedAccountId);
        
        // Calculate totals
        $totalBudget = 0;
        $totalActual = 0;
        foreach ($budgetData as $row) {
            $totalBudget += $row['budget_amount'];
            $totalActual += $row['actual_amount'];
        }
        $totalVariance = $totalBudget - $totalActual;
        $totalVariancePercent = $totalBudget > 0 ? ($totalVariance / $totalBudget * 100) : 0;
        $totalExecutionPercent = $totalBudget > 0 ? ($totalActual / $totalBudget * 100) : 0;
        
        // Get accounts for filter
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll(['is_active' => 1]);
        
        require_once __DIR__ . '/../Views/accounting/budgets/report.php';
    }
}
