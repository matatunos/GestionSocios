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

        require __DIR__ . '/../Views/dashboard.php';
    }
}
