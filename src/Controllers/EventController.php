<?php

class EventController {
    private $db;
    private $event;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->event = new Event($this->db);
        $this->member = new Member($this->db);
    }
    public function show($id) {
        $this->checkAdmin();
        // Load event data
        $event = $this->event->readOne($id);
        if (!$event) {
            header('Location: index.php?page=events&error=notfound');
            exit;
        }
        // Get all active members
        $stmt = $this->member->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Build participants array with payment info
        $participants = [];
        foreach ($members as $m) {
            $payStmt = $this->db->prepare("SELECT id, status FROM payments WHERE member_id = ? AND event_id = ? AND payment_type = 'event'");
            $payStmt->execute([$m['id'], $id]);
            $pay = $payStmt->fetch(PDO::FETCH_ASSOC);
            $participants[] = ['member' => $m, 'payment' => $pay];
        }
        require __DIR__ . '/../Views/events/show.php';
    }

    // Mark participant as paid for an event
    public function markPaid($eventId, $memberId) {
        $this->checkAdmin();
        // Ensure event exists
        $event = $this->event->readOne($eventId);
        if (!$event) {
            header('Location: index.php?page=events&error=notfound');
            exit;
        }
        // Check existing payment record
        $checkStmt = $this->db->prepare("SELECT id FROM payments WHERE member_id = ? AND event_id = ? AND payment_type = 'event'");
        $checkStmt->execute([$memberId, $eventId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $upd = $this->db->prepare("UPDATE payments SET status = 'paid', payment_date = ? WHERE id = ?");
            $upd->execute([date('Y-m-d'), $existing['id']]);
        } else {
            $ins = $this->db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type, event_id) VALUES (?, ?, ?, ?, 'paid', NULL, 'event', ?)");
            $ins->execute([
                $memberId,
                $event['price'],
                date('Y-m-d'),
                'Evento: ' . $event['name'],
                $eventId
            ]);
        }
        header("Location: index.php?page=events&action=show&id=$eventId&msg=paid");
        exit;
    }

    public function index() {
        $this->checkAdmin();
        $stmt = $this->event->readAll();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/events/index.php';
    }

    public function create() {
        $this->checkAdmin();
        require __DIR__ . '/../Views/events/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->event->name = $_POST['name'];
            $this->event->description = $_POST['description'];
            $this->event->date = $_POST['date'];
            $this->event->price = $_POST['price'];
            $this->event->is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($this->event->create()) {
                header('Location: index.php?page=events');
            } else {
                $error = "Error creating event.";
                require __DIR__ . '/../Views/events/create.php';
            }
        }
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
            $this->event->id = $id;
            $this->event->name = $_POST['name'];
            $this->event->description = $_POST['description'];
            $this->event->date = $_POST['date'];
            $this->event->price = $_POST['price'];
            $this->event->is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($this->event->update()) {
                header('Location: index.php?page=events');
            } else {
                $error = "Error updating event.";
                $event = $this->event;
                require __DIR__ . '/../Views/events/edit.php';
            }
        }
    }
}
