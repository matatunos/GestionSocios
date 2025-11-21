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
        $payment = new Payment($this->db);
        $member = new Member($this->db);

        $yearlyIncome = $payment->getYearlyIncome();
        $incomeByType = $payment->getIncomeByType();
        $pendingPayments = $payment->getPendingCount();
        $activeMembers = $member->getActiveCount();

        // Get recent activity (last 10 payments)
        $recentStmt = $this->db->prepare(
            "SELECT p.*, m.first_name, m.last_name, p.created_at as activity_date
             FROM payments p
             JOIN members m ON p.member_id = m.id
             ORDER BY p.created_at DESC
             LIMIT 10"
        );
        $recentStmt->execute();
        $recentActivity = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/dashboard.php';
    }
}
