<?php

/**
 * BankController
 * 
 * Gestión completa del sistema bancario:
 * - Dashboard con saldos y alertas
 * - CRUD de cuentas bancarias
 * - Listado y gestión de movimientos
 * - Importación de extractos CSV/OFX
 * - Conciliación bancaria
 * - Matching automático y manual
 */

require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/BankTransaction.php';
require_once __DIR__ . '/../Models/BankReconciliation.php';
require_once __DIR__ . '/../Models/TransactionMatch.php';
require_once __DIR__ . '/../Models/AuditLog.php';

class BankController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Dashboard bancario principal
     */
    public function dashboard() {
        // Obtener todas las cuentas con sus saldos
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        // Calcular totales
        $totalBalance = 0;
        foreach ($accounts as $account) {
            $totalBalance += $account['current_balance'];
        }
        
        // Transacciones recientes (últimas 20)
        $transactionModel = new BankTransaction($this->db);
        $recentTransactions = $transactionModel->readRecent(20);
        
        // Alertas
        $alerts = [
            'unmatched' => $transactionModel->countUnmatched(),
            'unreconciled' => $transactionModel->countUnreconciled(),
            'pending_import' => 0 // Placeholder
        ];
        
        require_once __DIR__ . '/../Views/bank/dashboard.php';
    }

    /**
     * Listar cuentas bancarias
     */
    public function accounts() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                $this->listAccounts();
                break;
            case 'create':
                $this->createAccount();
                break;
            case 'edit':
                $this->editAccount();
                break;
            case 'view':
                $this->viewAccount();
                break;
            case 'delete':
                $this->deleteAccount();
                break;
            case 'set_default':
                $this->setDefaultAccount();
                break;
            default:
                $this->listAccounts();
        }
    }
    
    /**
     * Listar cuentas
     */
    private function listAccounts() {
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        require_once __DIR__ . '/../Views/bank/accounts/index.php';
    }
    
    /**
     * Crear nueva cuenta
     */
    private function createAccount() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountModel = new BankAccount($this->db);
            
            $accountModel->bank_name = $_POST['bank_name'];
            $accountModel->account_holder = $_POST['account_holder'];
            $accountModel->iban = $_POST['iban'];
            $accountModel->swift_bic = $_POST['swift_bic'] ?? null;
            $accountModel->account_type = $_POST['account_type'];
            $accountModel->currency = $_POST['currency'] ?? 'EUR';
            $accountModel->initial_balance = !empty($_POST['initial_balance']) ? (float)$_POST['initial_balance'] : 0.00;
            $accountModel->current_balance = $accountModel->initial_balance;
            $accountModel->is_active = isset($_POST['is_active']) ? 1 : 0;
            $accountModel->notes = $_POST['notes'] ?? null;
            $accountModel->created_by = $_SESSION['user_id'] ?? 1;
            
            if ($accountModel->create()) {
                $_SESSION['message'] = "Cuenta bancaria creada exitosamente";
                
                AuditLog::create($this->db, [
                    'entity_type' => 'bank_account',
                    'entity_id' => $accountModel->id,
                    'action' => 'create',
                    'user_id' => $_SESSION['user_id'] ?? 1,
                    'details' => json_encode(['iban' => $accountModel->iban])
                ]);
                
                header('Location: index.php?page=bank&subpage=accounts');
                exit;
            } else {
                $error = "Error al crear la cuenta bancaria";
            }
        }
        
        require_once __DIR__ . '/../Views/bank/accounts/create.php';
    }
    
    /**
     * Ver detalle de cuenta
     */
    private function viewAccount() {
        $accountId = $_GET['id'] ?? null;
        
        if (!$accountId) {
            $_SESSION['error'] = "ID de cuenta no especificado";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        $accountModel = new BankAccount($this->db);
        $accountModel->id = $accountId;
        $accountModel->readOne();
        
        if (!$accountModel->bank_name) {
            $_SESSION['error'] = "Cuenta no encontrada";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        // Obtener estadísticas
        $stats = $accountModel->getStats();
        
        // Obtener transacciones recientes
        $transactions = $accountModel->getTransactions(50, 0);
        
        require_once __DIR__ . '/../Views/bank/accounts/view.php';
    }
    
    /**
     * Editar cuenta
     */
    private function editAccount() {
        $accountId = $_GET['id'] ?? null;
        
        if (!$accountId) {
            $_SESSION['error'] = "ID de cuenta no especificado";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        $accountModel = new BankAccount($this->db);
        $accountModel->id = $accountId;
        $accountModel->readOne();
        
        if (!$accountModel->bank_name) {
            $_SESSION['error'] = "Cuenta no encontrada";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountModel->bank_name = $_POST['bank_name'];
            $accountModel->account_holder = $_POST['account_holder'];
            $accountModel->iban = $_POST['iban'];
            $accountModel->swift_bic = $_POST['swift_bic'] ?? null;
            $accountModel->account_type = $_POST['account_type'];
            $accountModel->currency = $_POST['currency'] ?? 'EUR';
            $accountModel->is_active = isset($_POST['is_active']) ? 1 : 0;
            $accountModel->notes = $_POST['notes'] ?? null;
            
            if ($accountModel->update()) {
                $_SESSION['message'] = "Cuenta actualizada exitosamente";
                
                AuditLog::create($this->db, [
                    'entity_type' => 'bank_account',
                    'entity_id' => $accountModel->id,
                    'action' => 'update',
                    'user_id' => $_SESSION['user_id'] ?? 1
                ]);
                
                header('Location: index.php?page=bank&subpage=accounts&action=view&id=' . $accountModel->id);
                exit;
            } else {
                $error = "Error al actualizar la cuenta";
            }
        }
        
        require_once __DIR__ . '/../Views/bank/accounts/edit.php';
    }
    
    /**
     * Eliminar cuenta
     */
    private function deleteAccount() {
        $accountId = $_GET['id'] ?? null;
        
        if (!$accountId) {
            $_SESSION['error'] = "ID de cuenta no especificado";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        $accountModel = new BankAccount($this->db);
        $accountModel->id = $accountId;
        
        // Verificar si tiene transacciones
        $transactionModel = new BankTransaction($this->db);
        $count = $transactionModel->countByAccount($accountId);
        
        if ($count > 0) {
            $_SESSION['error'] = "No se puede eliminar la cuenta porque tiene $count transacciones asociadas";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        if ($accountModel->delete()) {
            $_SESSION['message'] = "Cuenta eliminada exitosamente";
            
            AuditLog::create($this->db, [
                'entity_type' => 'bank_account',
                'entity_id' => $accountId,
                'action' => 'delete',
                'user_id' => $_SESSION['user_id'] ?? 1
            ]);
        } else {
            $_SESSION['error'] = "Error al eliminar la cuenta";
        }
        
        header('Location: index.php?page=bank&subpage=accounts');
        exit;
    }
    
    /**
     * Establecer cuenta por defecto
     */
    private function setDefaultAccount() {
        $accountId = $_GET['id'] ?? null;
        
        if (!$accountId) {
            $_SESSION['error'] = "ID de cuenta no especificado";
            header('Location: index.php?page=bank&subpage=accounts');
            exit;
        }
        
        $accountModel = new BankAccount($this->db);
        $accountModel->id = $accountId;
        
        if ($accountModel->setAsDefault()) {
            $_SESSION['message'] = "Cuenta establecida como predeterminada";
        } else {
            $_SESSION['error'] = "Error al establecer cuenta predeterminada";
        }
        
        header('Location: index.php?page=bank&subpage=accounts');
        exit;
    }

    /**
     * Gestionar transacciones
     */
    public function transactions() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                $this->listTransactions();
                break;
            case 'create':
                $this->createTransaction();
                break;
            case 'view':
                $this->viewTransaction();
                break;
            case 'edit':
                $this->editTransaction();
                break;
            case 'delete':
                $this->deleteTransaction();
                break;
            default:
                $this->listTransactions();
        }
    }
    
    /**
     * Listar transacciones con filtros
     */
    private function listTransactions() {
        $filters = [
            'account_id' => $_GET['account_id'] ?? '',
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $transactionModel = new BankTransaction($this->db);
        $transactions = $transactionModel->readAll($filters, $perPage, $offset);
        $totalCount = $transactionModel->count($filters);
        $totalPages = ceil($totalCount / $perPage);
        
        // Obtener cuentas para filtro
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        require_once __DIR__ . '/../Views/bank/transactions/index.php';
    }
    
    /**
     * Crear transacción manual
     */
    private function createTransaction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionModel = new BankTransaction($this->db);
            
            $transactionModel->account_id = $_POST['account_id'];
            $transactionModel->transaction_date = $_POST['transaction_date'];
            $transactionModel->value_date = $_POST['value_date'] ?? $_POST['transaction_date'];
            $transactionModel->type = $_POST['type'];
            $transactionModel->amount = (float)$_POST['amount'];
            $transactionModel->description = $_POST['description'];
            $transactionModel->reference = $_POST['reference'] ?? null;
            $transactionModel->category = $_POST['category'] ?? null;
            $transactionModel->counterparty = $_POST['counterparty'] ?? null;
            $transactionModel->notes = $_POST['notes'] ?? null;
            $transactionModel->created_by = $_SESSION['user_id'] ?? 1;
            
            if ($transactionModel->create()) {
                // Recalcular saldo de la cuenta
                $accountModel = new BankAccount($this->db);
                $accountModel->id = $transactionModel->account_id;
                $accountModel->recalculateBalance();
                
                $_SESSION['message'] = "Transacción creada exitosamente";
                
                AuditLog::create($this->db, [
                    'entity_type' => 'bank_transaction',
                    'entity_id' => $transactionModel->id,
                    'action' => 'create',
                    'user_id' => $_SESSION['user_id'] ?? 1,
                    'details' => json_encode(['amount' => $transactionModel->amount, 'type' => $transactionModel->type])
                ]);
                
                header('Location: index.php?page=bank&subpage=transactions');
                exit;
            } else {
                $error = "Error al crear la transacción";
            }
        }
        
        // Obtener cuentas para el select
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        require_once __DIR__ . '/../Views/bank/transactions/create.php';
    }
    
    /**
     * Ver detalle de transacción
     */
    private function viewTransaction() {
        $transactionId = $_GET['id'] ?? null;
        
        if (!$transactionId) {
            $_SESSION['error'] = "ID de transacción no especificado";
            header('Location: index.php?page=bank&subpage=transactions');
            exit;
        }
        
        $transactionModel = new BankTransaction($this->db);
        $transactionModel->id = $transactionId;
        $transactionModel->readOne();
        
        if (!$transactionModel->account_id) {
            $_SESSION['error'] = "Transacción no encontrada";
            header('Location: index.php?page=bank&subpage=transactions');
            exit;
        }
        
        // Obtener matches si existen
        $matchModel = new TransactionMatch($this->db);
        $matches = $matchModel->readByTransaction($transactionId);
        
        // Obtener sugerencias de matching si no está emparejada
        if (empty($matches)) {
            $suggestions = $matchModel->suggestMatches($transactionId);
        } else {
            $suggestions = [];
        }
        
        require_once __DIR__ . '/../Views/bank/transactions/view.php';
    }

    /**
     * Importación de extractos
     */
    public function import() {
        $action = $_GET['action'] ?? 'form';
        
        switch ($action) {
            case 'form':
                $this->importForm();
                break;
            case 'process':
                $this->processImport();
                break;
            default:
                $this->importForm();
        }
    }
    
    /**
     * Formulario de importación
     */
    private function importForm() {
        // Obtener cuentas
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        require_once __DIR__ . '/../Views/bank/import/form.php';
    }
    
    /**
     * Procesar archivo de importación
     */
    private function processImport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['import_file'])) {
            $_SESSION['error'] = "Archivo no proporcionado";
            header('Location: index.php?page=bank&subpage=import');
            exit;
        }
        
        $accountId = $_POST['account_id'] ?? null;
        $format = $_POST['format'] ?? 'csv';
        
        if (!$accountId) {
            $_SESSION['error'] = "Cuenta bancaria no especificada";
            header('Location: index.php?page=bank&subpage=import');
            exit;
        }
        
        $file = $_FILES['import_file'];
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Error al subir archivo";
            header('Location: index.php?page=bank&subpage=import');
            exit;
        }
        
        try {
            // Leer archivo
            $content = file_get_contents($file['tmp_name']);
            
            // Parsear según formato
            $transactions = [];
            if ($format === 'csv') {
                $transactions = $this->parseCSV($content);
            } else {
                $_SESSION['error'] = "Formato no soportado aún: $format";
                header('Location: index.php?page=bank&subpage=import');
                exit;
            }
            
            // Importar transacciones
            $transactionModel = new BankTransaction($this->db);
            $result = $transactionModel->importFromArray($accountId, $transactions, $_SESSION['user_id'] ?? 1);
            
            // Recalcular saldo
            $accountModel = new BankAccount($this->db);
            $accountModel->id = $accountId;
            $accountModel->recalculateBalance();
            
            $_SESSION['message'] = "Importación completada: {$result['inserted']} nuevas, {$result['duplicates']} duplicadas";
            
            AuditLog::create($this->db, [
                'entity_type' => 'bank_import',
                'entity_id' => $accountId,
                'action' => 'import',
                'user_id' => $_SESSION['user_id'] ?? 1,
                'details' => json_encode($result)
            ]);
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Error en importación: " . $e->getMessage();
        }
        
        header('Location: index.php?page=bank&subpage=transactions&account_id=' . $accountId);
        exit;
    }
    
    /**
     * Parsear CSV simple
     */
    private function parseCSV($content) {
        $lines = explode("\n", $content);
        $transactions = [];
        
        // Asumiendo formato: fecha, descripción, importe, saldo
        // Saltar header
        array_shift($lines);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $fields = str_getcsv($line, ';');
            
            if (count($fields) >= 3) {
                $amount = (float)str_replace(',', '.', str_replace('.', '', $fields[2]));
                
                $transactions[] = [
                    'transaction_date' => $this->parseDate($fields[0]),
                    'description' => $fields[1],
                    'amount' => $amount,
                    'type' => $amount >= 0 ? 'ingreso' : 'egreso',
                    'reference' => $fields[3] ?? null
                ];
            }
        }
        
        return $transactions;
    }
    
    /**
     * Parsear fecha en diferentes formatos
     */
    private function parseDate($dateStr) {
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, trim($dateStr));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        return date('Y-m-d');
    }

    /**
     * Conciliación bancaria
     */
    public function reconciliation() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                $this->listReconciliations();
                break;
            case 'start':
                $this->startReconciliation();
                break;
            case 'process':
                $this->processReconciliation();
                break;
            case 'view':
                $this->viewReconciliation();
                break;
            case 'complete':
                $this->completeReconciliation();
                break;
            default:
                $this->listReconciliations();
        }
    }
    
    /**
     * Listar conciliaciones
     */
    private function listReconciliations() {
        $query = "SELECT r.*, a.bank_name, a.iban 
                  FROM bank_reconciliations r
                  JOIN bank_accounts a ON r.account_id = a.id
                  ORDER BY r.period_start DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $reconciliations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/bank/reconciliation/index.php';
    }
    
    /**
     * Iniciar nueva conciliación
     */
    private function startReconciliation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountId = $_POST['account_id'];
            $periodStart = $_POST['period_start'];
            $periodEnd = $_POST['period_end'];
            $statementBalance = (float)$_POST['statement_balance'];
            
            $reconciliationModel = new BankReconciliation($this->db);
            $reconciliationId = $reconciliationModel->startReconciliation(
                $accountId,
                $periodStart,
                $periodEnd,
                $statementBalance,
                $_SESSION['user_id'] ?? 1
            );
            
            if ($reconciliationId) {
                $_SESSION['message'] = "Conciliación iniciada";
                header('Location: index.php?page=bank&subpage=reconciliation&action=process&id=' . $reconciliationId);
                exit;
            } else {
                $error = "Error al iniciar conciliación";
            }
        }
        
        // Obtener cuentas
        $accountModel = new BankAccount($this->db);
        $accounts = $accountModel->readAll();
        
        require_once __DIR__ . '/../Views/bank/reconciliation/start.php';
    }
    
    /**
     * Procesar conciliación (seleccionar transacciones)
     */
    private function processReconciliation() {
        $reconciliationId = $_GET['id'] ?? null;
        
        if (!$reconciliationId) {
            $_SESSION['error'] = "ID de conciliación no especificado";
            header('Location: index.php?page=bank&subpage=reconciliation');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Guardar transacciones seleccionadas
            $selectedTransactions = $_POST['transactions'] ?? [];
            
            // Actualizar estado de transacciones
            if (!empty($selectedTransactions)) {
                $transactionModel = new BankTransaction($this->db);
                foreach ($selectedTransactions as $txId) {
                    $transactionModel->id = $txId;
                    $transactionModel->reconcile($reconciliationId);
                }
            }
            
            $_SESSION['message'] = count($selectedTransactions) . " transacciones marcadas como conciliadas";
            header('Location: index.php?page=bank&subpage=reconciliation&action=view&id=' . $reconciliationId);
            exit;
        }
        
        $reconciliationModel = new BankReconciliation($this->db);
        $reconciliationModel->id = $reconciliationId;
        $reconciliation = $reconciliationModel->readOne();
        
        if (!$reconciliation) {
            $_SESSION['error'] = "Conciliación no encontrada";
            header('Location: index.php?page=bank&subpage=reconciliation');
            exit;
        }
        
        // Obtener transacciones del período
        $transactions = $reconciliationModel->getTransactions($reconciliationId);
        $stats = $reconciliationModel->getStats($reconciliationId);
        
        require_once __DIR__ . '/../Views/bank/reconciliation/process.php';
    }

    /**
     * Matching de transacciones
     */
    public function matching() {
        $action = $_GET['action'] ?? 'auto';
        
        switch ($action) {
            case 'auto':
                $this->autoMatching();
                break;
            case 'manual':
                $this->manualMatching();
                break;
            case 'unmatch':
                $this->unmatch();
                break;
            default:
                $this->autoMatching();
        }
    }
    
    /**
     * Ejecutar matching automático
     */
    private function autoMatching() {
        $accountId = $_GET['account_id'] ?? null;
        
        $transactionModel = new BankTransaction($this->db);
        $result = $transactionModel->autoMatch($accountId, 85); // 85% threshold
        
        $_SESSION['message'] = "Matching automático: {$result['matched']} emparejadas, {$result['suggested']} sugerencias";
        
        header('Location: index.php?page=bank&subpage=transactions' . ($accountId ? '&account_id=' . $accountId : ''));
        exit;
    }
    
    /**
     * Matching manual
     */
    private function manualMatching() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'];
            $matchType = $_POST['match_type'];
            $matchId = $_POST['match_id'];
            
            $transactionModel = new BankTransaction($this->db);
            $transactionModel->id = $transactionId;
            
            if ($transactionModel->match($matchType, $matchId, $_SESSION['user_id'] ?? 1)) {
                $_SESSION['message'] = "Transacción emparejada exitosamente";
            } else {
                $_SESSION['error'] = "Error al emparejar transacción";
            }
            
            header('Location: index.php?page=bank&subpage=transactions&action=view&id=' . $transactionId);
            exit;
        }
    }
    
    /**
     * Desemparejar transacción
     */
    private function unmatch() {
        $transactionId = $_GET['transaction_id'] ?? null;
        
        if (!$transactionId) {
            $_SESSION['error'] = "ID de transacción no especificado";
            header('Location: index.php?page=bank&subpage=transactions');
            exit;
        }
        
        $transactionModel = new BankTransaction($this->db);
        $transactionModel->id = $transactionId;
        
        if ($transactionModel->unmatch()) {
            $_SESSION['message'] = "Emparejamiento eliminado";
        } else {
            $_SESSION['error'] = "Error al eliminar emparejamiento";
        }
        
        header('Location: index.php?page=bank&subpage=transactions&action=view&id=' . $transactionId);
        exit;
    }
}
