<?php

require_once __DIR__ . '/../Models/AccountingAccount.php';
require_once __DIR__ . '/../Models/AccountingEntry.php';
require_once __DIR__ . '/../Models/AccountingPeriod.php';

class AccountingController {
        // Default entry point for the module
        public function index() {
            // Redirect to dashboard or load a default view
            $this->dashboard();
        }
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    // Dashboard
    public function dashboard() {
        $currentYear = date('Y');
        
        // Get current period
        $currentPeriod = AccountingPeriod::getCurrentOpenPeriod($this->db);
        
        // Get statistics
        $stats = $this->getStats($currentYear);
        
        require_once __DIR__ . '/../Views/accounting/dashboard.php';
    }
    
    // Chart of Accounts - List
    public function accounts() {
        $filters = [
            'account_type' => $_GET['account_type'] ?? '',
            'is_active' => $_GET['is_active'] ?? ''
        ];
        
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll($filters);
        
        require_once __DIR__ . '/../Views/accounting/accounts/index.php';
    }
    
    // Chart of Accounts - Create
    public function createAccount() {
        $accountModel = new AccountingAccount($this->db);
        $parentAccounts = $accountModel->readAll(['is_active' => 1]);
        
        require_once __DIR__ . '/../Views/accounting/accounts/create.php';
    }
    
    // Chart of Accounts - Store
    public function storeAccount() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=accounting&action=accounts');
            exit;
        }
        
        $accountModel = new AccountingAccount($this->db);
        $accountModel->code = $_POST['code'] ?? '';
        $accountModel->name = $_POST['name'] ?? '';
        $accountModel->description = $_POST['description'] ?? '';
        $accountModel->account_type = $_POST['account_type'] ?? '';
        $accountModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $accountModel->level = $_POST['level'] ?? 0;
        $accountModel->balance_type = $_POST['balance_type'] ?? '';
        $accountModel->is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($accountModel->create()) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'accounting_account', $this->db->lastInsertId(), 'Cuenta contable creada');
            
            $_SESSION['success'] = 'Cuenta creada correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear la cuenta';
        }
        
        header('Location: index.php?page=accounting&action=accounts');
        exit;
    }
    
    // Chart of Accounts - Edit
    public function editAccount() {
        $id = $_GET['id'] ?? 0;
        
        $accountModel = new AccountingAccount($this->db);
        if (!$accountModel->readOne($id)) {
            $_SESSION['error'] = 'Cuenta no encontrada';
            header('Location: index.php?page=accounting&action=accounts');
            exit;
        }
        
        $parentAccounts = $accountModel->readAll(['is_active' => 1]);
        
        require_once __DIR__ . '/../Views/accounting/accounts/edit.php';
    }
    
    // Chart of Accounts - Update
    public function updateAccount() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=accounting&action=accounts');
            exit;
        }
        
        $id = $_GET['id'] ?? 0;
        
        $accountModel = new AccountingAccount($this->db);
        if (!$accountModel->readOne($id)) {
            $_SESSION['error'] = 'Cuenta no encontrada';
            header('Location: index.php?page=accounting&action=accounts');
            exit;
        }
        
        $accountModel->code = $_POST['code'] ?? '';
        $accountModel->name = $_POST['name'] ?? '';
        $accountModel->description = $_POST['description'] ?? '';
        $accountModel->account_type = $_POST['account_type'] ?? '';
        $accountModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $accountModel->level = $_POST['level'] ?? 0;
        $accountModel->balance_type = $_POST['balance_type'] ?? '';
        $accountModel->is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($accountModel->update()) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'accounting_account', $id, 'Cuenta contable actualizada');
            
            $_SESSION['success'] = 'Cuenta actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la cuenta';
        }
        
        header('Location: index.php?page=accounting&action=accounts');
        exit;
    }
    
    // Journal Entries - List
    public function entries() {
        $filters = [
            'period_id' => $_GET['period_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'entry_type' => $_GET['entry_type'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $entryModel = new AccountingEntry($this->db);
        $entries = $entryModel->readAll($filters, $limit, $offset);
        $totalRecords = $entryModel->countAll($filters);
        $totalPages = ceil($totalRecords / $limit);
        
        // Get periods for filter
        $periodModel = new AccountingPeriod($this->db);
        $periods = $periodModel->readAll();
        
        require_once __DIR__ . '/../Views/accounting/entries/index.php';
    }
    
    // Journal Entry - View
    public function viewEntry() {
        $id = $_GET['id'] ?? 0;
        
        $entryModel = new AccountingEntry($this->db);
        $entry = $entryModel->readOne($id);
        
        if (!$entry) {
            $_SESSION['error'] = 'Asiento no encontrado';
            header('Location: index.php?page=accounting&action=entries');
            exit;
        }
        
        $lines = $entryModel->getLines($id);
        
        require_once __DIR__ . '/../Views/accounting/entries/view.php';
    }
    
    // Journal Entry - Create
    public function createEntry() {
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll(['is_active' => 1]);
        
        $periodModel = new AccountingPeriod($this->db);
        $periods = $periodModel->readAll(['status' => 'open']);
        
        require_once __DIR__ . '/../Views/accounting/entries/create.php';
    }
    
    // Journal Entry - Store
    public function storeEntry() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=accounting&action=entries');
            exit;
        }
        
        // Get lines from POST
        $lines = [];
        if (isset($_POST['account_id']) && is_array($_POST['account_id'])) {
            for ($i = 0; $i < count($_POST['account_id']); $i++) {
                if (!empty($_POST['account_id'][$i])) {
                    $lines[] = [
                        'account_id' => $_POST['account_id'][$i],
                        'description' => $_POST['line_description'][$i] ?? '',
                        'debit' => floatval($_POST['debit'][$i] ?? 0),
                        'credit' => floatval($_POST['credit'][$i] ?? 0)
                    ];
                }
            }
        }
        
        if (count($lines) < 2) {
            $_SESSION['error'] = 'Debe agregar al menos dos líneas al asiento';
            header('Location: index.php?page=accounting&action=createEntry');
            exit;
        }
        
        $entryModel = new AccountingEntry($this->db);
        $entryModel->entry_date = $_POST['entry_date'] ?? date('Y-m-d');
        $entryModel->period_id = $_POST['period_id'] ?? null;
        $entryModel->description = $_POST['description'] ?? '';
        $entryModel->reference = $_POST['reference'] ?? null;
        $entryModel->entry_type = 'manual';
        $entryModel->source_type = 'manual';
        $entryModel->status = 'draft';
        $entryModel->created_by = $_SESSION['user_id'];
        
        if ($entryModel->create($lines)) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'accounting_entry', $entryModel->id, 'Asiento contable creado');
            
            $_SESSION['success'] = 'Asiento creado correctamente';
            header('Location: index.php?page=accounting&action=viewEntry&id=' . $entryModel->id);
        } else {
            $_SESSION['error'] = 'Error al crear el asiento. Verifique que los débitos y créditos estén balanceados';
            header('Location: index.php?page=accounting&action=createEntry');
        }
        exit;
    }
    
    // Post entry
    public function postEntry() {
        $id = $_GET['id'] ?? 0;
        
        $entryModel = new AccountingEntry($this->db);
        if (!$entryModel->readOne($id)) {
            $_SESSION['error'] = 'Asiento no encontrado';
            header('Location: index.php?page=accounting&action=entries');
            exit;
        }
        
        if ($entryModel->post($_SESSION['user_id'])) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'accounting_entry', $id, 'Asiento contable contabilizado');
            
            $_SESSION['success'] = 'Asiento contabilizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al contabilizar el asiento';
        }
        
        header('Location: index.php?page=accounting&action=viewEntry&id=' . $id);
        exit;
    }
    
    // General Ledger Report
    public function generalLedger() {
        $accountId = $_GET['account_id'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-01-01');
        $endDate = $_GET['end_date'] ?? date('Y-12-31');
        
        $accountModel = new AccountingAccount($this->db);
        $accounts = $accountModel->readAll(['is_active' => 1]);
        
        $ledgerData = [];
        if (!empty($accountId)) {
            $accountModel->readOne($accountId);
            
            // Get transactions
            $query = "SELECT 
                          e.entry_date,
                          e.entry_number,
                          e.description,
                          el.description as line_description,
                          el.debit,
                          el.credit
                      FROM accounting_entry_lines el
                      INNER JOIN accounting_entries e ON el.entry_id = e.id
                      WHERE el.account_id = :account_id
                        AND e.status = 'posted'
                        AND e.entry_date BETWEEN :start_date AND :end_date
                      ORDER BY e.entry_date ASC, e.entry_number ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':account_id', $accountId);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $ledgerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/../Views/accounting/reports/general_ledger.php';
    }
    
    // Trial Balance Report
    public function trialBalance() {
        $startDate = $_GET['start_date'] ?? date('Y-01-01');
        $endDate = $_GET['end_date'] ?? date('Y-12-31');
        
        $query = "SELECT 
                      a.id,
                      a.code,
                      a.name,
                      a.account_type,
                      COALESCE(SUM(el.debit), 0) as total_debit,
                      COALESCE(SUM(el.credit), 0) as total_credit
                  FROM accounting_accounts a
                  LEFT JOIN accounting_entry_lines el ON a.id = el.account_id
                  LEFT JOIN accounting_entries e ON el.entry_id = e.id
                  WHERE a.is_active = 1
                    AND (e.id IS NULL OR (e.status = 'posted' AND e.entry_date BETWEEN :start_date AND :end_date))
                  GROUP BY a.id, a.code, a.name, a.account_type
                  HAVING total_debit > 0 OR total_credit > 0
                  ORDER BY a.code ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/accounting/reports/trial_balance.php';
    }
    
    // Get statistics
    private function getStats($year) {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        
        $stats = [];
        
        // Total entries
        $query = "SELECT COUNT(*) as total FROM accounting_entries 
                  WHERE YEAR(entry_date) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $stats['total_entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Posted entries
        $query = "SELECT COUNT(*) as total FROM accounting_entries 
                  WHERE YEAR(entry_date) = :year AND status = 'posted'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $stats['posted_entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total accounts
        $query = "SELECT COUNT(*) as total FROM accounting_accounts WHERE is_active = 1";
        $stmt = $this->db->query($query);
        $stats['total_accounts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
    }
}
