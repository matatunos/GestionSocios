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
        
        // Load Members
        $stmt = $this->member->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load Active Events
        $eventModel = new Event($this->db);
        $stmtEvents = $eventModel->readActive();
        $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/payments/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->payment->member_id = $_POST['member_id'];
            $this->payment->amount = $_POST['amount'];
            $this->payment->payment_date = $_POST['payment_date'];
            $this->payment->concept = $_POST['concept'];
            $this->payment->status = $_POST['status'];
            $this->payment->payment_type = $_POST['payment_type'] ?? 'fee';
            $this->payment->event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
            
            if ($this->payment->payment_type === 'fee') {
                $this->payment->fee_year = date('Y', strtotime($this->payment->payment_date));
            }

            if ($this->payment->create()) {
                header('Location: index.php?page=payments');
            } else {
                $error = "Error creating payment.";
                $stmt = $this->member->readAll();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Reload events if error
                $eventModel = new Event($this->db);
                $stmtEvents = $eventModel->readActive();
                $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
                require __DIR__ . '/../Views/payments/create.php';
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
            $this->payment->id = $id;
            $this->payment->member_id = $_POST['member_id'];
            $this->payment->amount = $_POST['amount'];
            $this->payment->payment_date = $_POST['payment_date'];
            $this->payment->concept = $_POST['concept'];
            $this->payment->status = $_POST['status'];

            if ($this->payment->update()) {
                header('Location: index.php?page=payments');
            } else {
                $error = "Error updating payment.";
                $stmt = $this->member->readAll();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $payment = $this->payment;
                require __DIR__ . '/../Views/payments/edit.php';
            }
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->payment->id = $id;
        if ($this->payment->delete()) {
            header('Location: index.php?page=payments');
        } else {
            header('Location: index.php?page=payments&error=delete_failed');
        }
    }
}
