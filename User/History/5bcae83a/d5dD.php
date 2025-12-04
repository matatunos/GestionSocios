<?php

class PaymentController {
    private $db;
    private $payment;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->payment = new Payment($this->db);
        $this->member = new Member($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=payments');
            exit;
        }
    }

    public function index() {
        $stmt = $this->payment->readAll();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/payments/list.php';
    }

    public function create() {
        $this->checkAdmin();
        
        // Load Members with their category info
        $query = "SELECT m.id, m.first_name, m.last_name, m.category_id
                  FROM members m
                  WHERE m.is_active = 1
                  ORDER BY m.last_name, m.first_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load category fees by year (for current and previous years)
        $currentYear = date('Y');
        $queryFees = "SELECT cfh.category_id, cfh.year, cfh.fee_amount,
                             mc.name as category_name
                      FROM category_fee_history cfh
                      INNER JOIN member_categories mc ON cfh.category_id = mc.id
                      WHERE cfh.year >= :year_start
                      ORDER BY cfh.category_id, cfh.year DESC";
        $stmtFees = $this->db->prepare($queryFees);
        $stmtFees->bindValue(':year_start', $currentYear - 2, PDO::PARAM_INT);
        $stmtFees->execute();
        $categoryFees = $stmtFees->fetchAll(PDO::FETCH_ASSOC);
        
        // Load Active Events
        $eventModel = new Event($this->db);
        $stmtEvents = $eventModel->readActive();
        $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

        // Load Annual Fees for auto-fill (fallback)
        $feeModel = new Fee($this->db);
        $stmtFees = $feeModel->readAll();
        $fees = $stmtFees->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/payments/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            
            try {
                $member_id = $_POST['member_id'];
                $payment_type = $_POST['payment_type'] ?? 'fee';
                $payment_date = $_POST['payment_date'];
                // ...existing code...
                if ($payment_type === 'fee') {
                    $fee_year = date('Y', strtotime($payment_date));
                    $checkStmt = $this->db->prepare(
                        "SELECT id FROM payments 
                         WHERE member_id = ? AND fee_year = ? AND payment_type = 'fee'"
                    );
                    $checkStmt->execute([$member_id, $fee_year]);
                    if ($checkStmt->fetch()) {
                        header("Location: index.php?page=payments&action=create&error=duplicate_fee&year=$fee_year");
                        exit;
                    }
                    $this->payment->fee_year = $fee_year;
                }
                $this->payment->member_id = $member_id;
                $this->payment->amount = $_POST['amount'];
                $this->payment->payment_date = $payment_date;
                $this->payment->concept = $_POST['concept'];
                $this->payment->status = $_POST['status'];
                $this->payment->payment_type = $payment_type;
                $this->payment->event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;

                if ($this->payment->create()) {
                    $lastId = $this->db->lastInsertId();
                    
                    // Audit log registro alta pago
                    require_once __DIR__ . '/../Models/AuditLog.php';
                    $auditLog = new AuditLog($this->db);
                    $userId = $_SESSION['user_id'] ?? null;
                    $details = json_encode([
                        'member_id' => $member_id,
                        'amount' => $_POST['amount'],
                        'payment_type' => $payment_type,
                        'payment_date' => $payment_date
                    ]);
                    $auditLog->create($userId, 'create', 'payment', $lastId, $details);

                    // Crear asiento contable automático
                    require_once __DIR__ . '/../Helpers/AccountingHelper.php';
                    $accountingCreated = AccountingHelper::createEntryFromPayment(
                        $this->db,
                        $lastId,
                        $_POST['amount'],
                        $_POST['concept'],
                        $payment_date,
                        'transfer', // Por defecto, podría obtenerse del formulario
                        $payment_type
                    );
                    
                    if (!$accountingCreated) {
                        error_log("No se pudo crear el asiento contable para el pago #$lastId");
                    }

                    header('Location: index.php?page=payments&success=created');
                    exit;
                } else {
                    throw new Exception("Error al crear el pago.");
                }
            } catch (Exception $e) {
                // ...existing code...
            }
        }
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->payment->id = $id;
        if ($this->payment->readOne()) {
            $stmt = $this->member->readAll();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $payment = $this->payment;
            require __DIR__ . '/../Views/payments/edit.php';
        } else {
            header('Location: index.php?page=payments');
        }
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            
            // Get previous status to check if it changed to 'paid'
            $this->payment->id = $id;
            $this->payment->readOne();
            $previousStatus = $this->payment->status;
            
            $this->payment->member_id = $_POST['member_id'];
            $this->payment->amount = $_POST['amount'];
            $this->payment->payment_date = $_POST['payment_date'];
            $this->payment->concept = $_POST['concept'];
            $this->payment->status = $_POST['status'];

            if ($this->payment->update()) {
                // Audit log registro modificación pago
                require_once __DIR__ . '/../Models/AuditLog.php';
                $auditLog = new AuditLog($this->db);
                $userId = $_SESSION['user_id'] ?? null;
                $details = json_encode([
                    'payment_id' => $id,
                    'member_id' => $_POST['member_id'],
                    'amount' => $_POST['amount'],
                    'payment_date' => $_POST['payment_date']
                ]);
                $auditLog->create($userId, 'update', 'payment', $id, $details);
                
                // Si el pago cambió a 'paid', crear asiento contable
                if ($previousStatus !== 'paid' && $_POST['status'] === 'paid') {
                    require_once __DIR__ . '/../Helpers/AccountingHelper.php';
                    
                    // Determinar el tipo de pago
                    $stmt = $this->db->prepare("SELECT payment_type FROM payments WHERE id = ?");
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $paymentType = $row['payment_type'] ?? 'fee';
                    
                    $accountingCreated = AccountingHelper::createEntryFromPayment(
                        $this->db,
                        $id,
                        $_POST['amount'],
                        $_POST['concept'],
                        $_POST['payment_date'],
                        'transfer',
                        $paymentType
                    );
                    
                    if (!$accountingCreated) {
                        error_log("No se pudo crear el asiento contable para el pago #$id");
                    }
                }

                header('Location: index.php?page=payments');
            } else {
                // ...existing code...
            }
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        
        // Cancelar asiento contable asociado si existe
        require_once __DIR__ . '/../Models/AccountingEntry.php';
        $stmt = $this->db->prepare("SELECT id FROM accounting_entries WHERE source_type = 'payment' AND source_id = ?");
        $stmt->execute([$id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($entry) {
            $entryModel = new AccountingEntry($this->db);
            $entryModel->id = $entry['id'];
            $entryModel->cancel();
        }
        
        $this->payment->id = $id;
        if ($this->payment->delete()) {
            // Audit log registro eliminación pago
            require_once __DIR__ . '/../Models/AuditLog.php';
            $auditLog = new AuditLog($this->db);
            $userId = $_SESSION['user_id'] ?? null;
            $details = json_encode(['payment_id' => $id]);
            $auditLog->create($userId, 'delete', 'payment', $id, $details);

            header('Location: index.php?page=payments');
        } else {
            header('Location: index.php?page=payments&error=delete_failed');
        }
    }
}
