<?php

class DonationController {
    private $db;
    private $donation;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donation = new Donation($this->db);
        $this->member = new Member($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    // List donations per year
    public function index() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        $stmt = $this->donation->readAllByYear($year);
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donations/index.php';
    }

    // Show form to add donation
    public function create() {
        $this->checkAdmin();
        $membersStmt = $this->member->readAll();
        $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donations/create.php';
    }

    // Store new donation
    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!validate_csrf_token()) {
                $error = "Invalid security token. Please try again.";
                $membersStmt = $this->member->readAll();
                $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
                require __DIR__ . '/../Views/donations/create.php';
                return;
            }
            
            $this->donation->member_id = $_POST['member_id'];
            $this->donation->amount = $_POST['amount'];
            $this->donation->type = $_POST['type'];
            $this->donation->year = $_POST['year'];
            if ($this->donation->create()) {
                header('Location: index.php?page=donations&msg=created');
                exit;
            } else {
                $error = "Error creating donation.";
                $membersStmt = $this->member->readAll();
                $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
                require __DIR__ . '/../Views/donations/create.php';
            }
        }
    }
}
?>
