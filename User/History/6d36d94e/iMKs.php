<?php

class EventController {
        public function discard($id) {
            $this->checkAdmin();
            $eventModel = new Event($this->db);
            if ($eventModel->discard($id)) {
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'discard', 'event', $id, 'Evento descartado por el usuario ' . ($_SESSION['username'] ?? ''));
                $_SESSION['success'] = 'Evento descartado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al descartar el evento.';
            }
            header('Location: index.php?page=events');
            exit;
        }

        public function restore($id) {
            $this->checkAdmin();
            $eventModel = new Event($this->db);
            if ($eventModel->restore($id)) {
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'restore', 'event', $id, 'Evento restaurado por el usuario ' . ($_SESSION['username'] ?? ''));
                $_SESSION['success'] = 'Evento restaurado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al restaurar el evento.';
            }
            header('Location: index.php?page=events');
            exit;
        }
    private $db;
    private $event;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->event = new Event($this->db);
        $this->member = new Member($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=events');
            exit;
        }
    }
    public function show($id) {
        $this->checkAdmin();
        // Load event data
        $this->event->id = $id;
        if (!$this->event->readOne()) {
            header('Location: index.php?page=events&error=notfound');
            exit;
        }
        $event = $this->event;
        
        // Get all active members
        $stmt = $this->member->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bulk fetch payments for this event
        $payStmt = $this->db->prepare("SELECT member_id, id, status FROM payments WHERE event_id = ? AND payment_type = 'event'");
        $payStmt->execute([$id]);
        $payments = [];
        while ($row = $payStmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[$row['member_id']] = $row;
        }

        // Bulk fetch attendance for this event
        $attStmt = $this->db->prepare("SELECT member_id, status FROM event_attendance WHERE event_id = ?");
        $attStmt->execute([$id]);
        $attendances = [];
        while ($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            $attendances[$row['member_id']] = $row;
        }

        // Build participants array
        $participants = [];
        foreach ($members as $m) {
            $pay = $payments[$m['id']] ?? null;
            $attendance = $attendances[$m['id']] ?? null;
            $participants[] = ['member' => $m, 'payment' => $pay, 'attendance' => $attendance];
        }
        require __DIR__ . '/../Views/events/show.php';
    }

    // Mark participant as paid for an event
    public function markPaid($eventId, $memberId) {
        $this->checkAdmin();
        // Ensure event exists
        $this->event->id = $eventId;
        if (!$this->event->readOne()) {
            header('Location: index.php?page=events&error=notfound');
            exit;
        }
        
        $paymentId = null;
        $paymentAmount = $this->event->price;
        $paymentDate = date('Y-m-d');
        $paymentConcept = $this->event->name;
        
        // Check existing payment record
        $checkStmt = $this->db->prepare("SELECT id FROM payments WHERE member_id = ? AND event_id = ? AND payment_type = 'event'");
        $checkStmt->execute([$memberId, $eventId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $upd = $this->db->prepare("UPDATE payments SET status = 'paid', payment_date = ? WHERE id = ?");
            $upd->execute([$paymentDate, $existing['id']]);
            $paymentId = $existing['id'];
            // Registrar en audit_log
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'markPaid', 'event_payment', $paymentId, 'Pago de evento marcado como realizado por el usuario ' . ($_SESSION['username'] ?? ''));
        } else {
            $ins = $this->db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type, event_id) VALUES (?, ?, ?, ?, 'paid', NULL, 'event', ?)");
            $ins->execute([
                $memberId,
                $paymentAmount,
                $paymentDate,
                $paymentConcept,
                $eventId
            ]);
            $paymentId = $this->db->lastInsertId();
            // Registrar en audit_log
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'markPaid', 'event_payment', $paymentId, 'Pago de evento creado y marcado como realizado por el usuario ' . ($_SESSION['username'] ?? ''));
        }
        
        // Crear asiento contable automático
        require_once __DIR__ . '/../Helpers/AccountingHelper.php';
        $accountingCreated = AccountingHelper::createEntryFromPayment(
            $this->db,
            $paymentId,
            $paymentAmount,
            $paymentConcept,
            $paymentDate,
            'transfer',
            'event'
        );
        
        if (!$accountingCreated) {
            error_log("No se pudo crear el asiento contable para el pago de evento #$paymentId");
        }
        
        header("Location: index.php?page=events&action=show&id=$eventId&msg=paid");
        exit;
    }

    public function index() {
        $this->checkAdmin();
        $includeDiscarded = !empty($_GET['show_discarded']) && $_GET['show_discarded'] == '1';
        $stmt = $this->event->readAll($includeDiscarded);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/events/index.php';
    }

    public function create() {
        $this->checkAdmin();
        require __DIR__ . '/../Views/events/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=events');
            exit;
        }
        
        // Validate CSRF token
        require_once __DIR__ . '/../Helpers/CsrfHelper.php';
        CsrfHelper::validateRequest();

        $eventModel = new Event($this->db);
        $eventModel->name = $_POST['name'] ?? '';
        $eventModel->description = $_POST['description'] ?? '';
        $eventModel->date = $_POST['event_date'] ?? date('Y-m-d');
        $eventModel->location = $_POST['location'] ?? '';
        $eventModel->price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;

        if ($eventModel->create()) {
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $lastId = $this->db->lastInsertId();
            $audit->create($_SESSION['user_id'], 'create', 'event', $lastId, 'Alta de evento por el usuario ' . ($_SESSION['username'] ?? ''));
            // Notificación ntfy y Telegram
            require_once __DIR__ . '/../Notifications/NotificationManager.php';
            $notifier = new NotificationManager();
            $msg = 'Nuevo evento: ' . $eventModel->name . ' (' . $eventModel->date . ')';
            $notifier->sendNtfy($msg, 'Nuevo Evento');
            $notifier->sendTelegram($msg);
            $_SESSION['success'] = 'Evento registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el evento.';
        }
        header('Location: index.php?page=events');
        exit;
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->event->id = $id;
        $this->event->readOne();
        $event = $this->event;
        require __DIR__ . '/../Views/events/edit.php';
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            $this->event->id = $id;
            $this->event->name = $_POST['name'];
            $this->event->event_type = $_POST['event_type'] ?? 'other';
            $this->event->color = $_POST['color'] ?? '#6366f1';
            $this->event->description = $_POST['description'];
            $this->event->location = !empty($_POST['location']) ? $_POST['location'] : null;
            $this->event->date = $_POST['date'];
            $this->event->start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
            $this->event->end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
            $this->event->price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
            $this->event->max_attendees = !empty($_POST['max_attendees']) ? $_POST['max_attendees'] : null;
            $this->event->requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
            $this->event->registration_deadline = !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : null;
            $this->event->is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($this->event->update()) {
                // Auditoría de modificación de evento
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'update', 'event', $id, 'Modificación de evento por el usuario ' . ($_SESSION['username'] ?? ''));
                $_SESSION['success'] = "Evento actualizado correctamente";
                header('Location: index.php?page=calendar');
            } else {
                $error = "Error updating event.";
                $event = $this->event;
                require __DIR__ . '/../Views/events/edit.php';
            }
        }
    }

    public function updateAttendanceStatus() {
        $this->checkAdmin();
        $eventId = $_GET['id'] ?? null;
        $memberId = $_GET['member_id'] ?? null;
        $status = $_POST['status'] ?? null;
        if (!$eventId || !$memberId || !$status) {
            $_SESSION['error'] = "Datos incompletos.";
            header("Location: index.php?page=events&action=show&id=$eventId");
            exit;
        }
        // Check if attendance exists
        $checkStmt = $this->db->prepare("SELECT id FROM event_attendance WHERE event_id = :event_id AND member_id = :member_id");
        $checkStmt->bindParam(':event_id', $eventId);
        $checkStmt->bindParam(':member_id', $memberId);
        $checkStmt->execute();
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $stmt = $this->db->prepare("UPDATE event_attendance SET status = :status WHERE event_id = :event_id AND member_id = :member_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':member_id', $memberId);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("INSERT INTO event_attendance (event_id, member_id, status, registration_date) VALUES (:event_id, :member_id, :status, NOW())");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':member_id', $memberId);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
        }
        // Auditoría de cambio de estado de asistencia
        require_once __DIR__ . '/../Models/AuditLog.php';
        $audit = new AuditLog($this->db);
        $audit->create($_SESSION['user_id'], 'update', 'event_attendance', $eventId, 'Cambio de estado de asistencia para el evento ' . $eventId . ', miembro ' . $memberId . ' por el usuario ' . ($_SESSION['username'] ?? ''));
        $_SESSION['success'] = "Estado actualizado correctamente.";
        header("Location: index.php?page=events&action=show&id=$eventId");
        exit;
    }
}
