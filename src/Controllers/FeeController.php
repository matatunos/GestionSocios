<?php

class FeeController {
    private $db;
    private $fee;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->fee = new Fee($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        $stmt = $this->fee->readAll();
        $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/fees/index.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!validate_csrf_token()) {
                $_SESSION['error'] = "Invalid security token. Please try again.";
                header('Location: index.php?page=fees');
                return;
            }
            
            $this->fee->year = $_POST['year'];
            $this->fee->amount = $_POST['amount'];

            if ($this->fee->create()) {
                header('Location: index.php?page=fees');
            } else {
                $error = "Error creating fee.";
                $this->index();
            }
        }
    }

    public function generatePayments($year) {
        $this->checkAdmin();
        
        // Get fee amount for the year
        $this->fee->year = $year;
        // Simple query to get amount (should add readOne to model, but this works for now)
        $stmt = $this->db->prepare("SELECT amount FROM annual_fees WHERE year = ?");
        $stmt->execute([$year]);
        $fee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fee) {
            header('Location: index.php?page=fees&error=no_fee_defined');
            exit;
        }

        $amount = $fee['amount'];
        $concept = "Cuota Anual " . $year;

        // Get all active members
        $memberModel = new Member($this->db);
        $stmt = $memberModel->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($members as $member) {
            if ($member['status'] !== 'active') continue;

            // Check if payment already exists for this member and year
            $check = $this->db->prepare("SELECT id FROM payments WHERE member_id = ? AND fee_year = ?");
            $check->execute([$member['id'], $year]);
            
            if (!$check->fetch()) {
                // Create pending payment
                $payment = new Payment($this->db);
                $payment->member_id = $member['id'];
                $payment->amount = $amount;
                $payment->payment_date = date('Y-m-d'); // Or maybe null? Let's say today.
                $payment->concept = $concept;
                $payment->status = 'pending';
                // We need to add fee_year and payment_type to Payment model create method first!
                // For now, let's do a direct insert to ensure it works with the new columns
                $query = "INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) 
                          VALUES (?, ?, ?, ?, 'pending', ?, 'fee')";
                $ins = $this->db->prepare($query);
                $ins->execute([$member['id'], $amount, date('Y-m-d'), $concept, $year]);
                $count++;
            }
        }

        header("Location: index.php?page=fees&success=generated&count=$count&year=$year");
    }
}
