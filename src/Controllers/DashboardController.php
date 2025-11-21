<?php

class DashboardController {
    private $db;
    private $member;
    private $payment;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->member = new Member($this->db);
        $this->payment = new Payment($this->db);
    }

    public function index() {
        // Get stats
        $monthlyIncome = $this->payment->getMonthlyIncome();
        $pendingPayments = $this->payment->getPendingCount();
        
        // Get active members count
        $query = "SELECT COUNT(*) as total FROM members WHERE status = 'active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $activeMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        require __DIR__ . '/../Views/dashboard.php';
    }
}
