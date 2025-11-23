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

        // 1. Recent Payments
        $paymentsStmt = $this->db->prepare(
            "SELECT p.amount, p.created_at as activity_date, 'payment' as type, p.payment_type as subtype, 
                    m.first_name, m.last_name, p.concept as description
             FROM payments p
             JOIN members m ON p.member_id = m.id
             ORDER BY p.created_at DESC LIMIT 10"
        );
        $paymentsStmt->execute();
        $recentPayments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Recent Donations
        $donationsStmt = $this->db->prepare(
            "SELECT d.amount, d.created_at as activity_date, 'donation' as type, d.type as subtype,
                    do.name as first_name, '' as last_name, 'Donación' as description
             FROM donations d
             JOIN donors do ON d.donor_id = do.id
             ORDER BY d.created_at DESC LIMIT 10"
        );
        $donationsStmt->execute();
        $recentDonations = $donationsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Recent Book Ads
        $adsStmt = $this->db->prepare(
            "SELECT b.amount, b.created_at as activity_date, 'book_ad' as type, b.ad_type as subtype,
                    do.name as first_name, '' as last_name, 'Anuncio Libro' as description
             FROM book_ads b
             JOIN donors do ON b.donor_id = do.id
             ORDER BY b.created_at DESC LIMIT 10"
        );
        $adsStmt->execute();
        $recentAds = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Recent Member Deactivations
        $deactivationsStmt = $this->db->prepare(
            "SELECT 0 as amount, deactivated_at as activity_date, 'deactivation' as type, '' as subtype,
                    first_name, last_name, 'Baja de Socio' as description
             FROM members
             WHERE status = 'inactive' AND deactivated_at IS NOT NULL
             ORDER BY deactivated_at DESC LIMIT 10"
        );
        $deactivationsStmt->execute();
        $recentDeactivations = $deactivationsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Merge and Sort
        $recentActivity = array_merge($recentPayments, $recentDonations, $recentAds, $recentDeactivations);
        
        usort($recentActivity, function($a, $b) {
            return strtotime($b['activity_date']) - strtotime($a['activity_date']);
        });

        // Limit to top 10
        $recentActivity = array_slice($recentActivity, 0, 10);

        // Get recent notifications
        $recentNotifications = [];
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../Models/Notification.php';
            $notificationModel = new Notification($this->db);
            $recentNotifications = $notificationModel->readByMember($_SESSION['user_id'], 5);
        }
        
        // Monthly income data for chart
        $monthlyIncomeStmt = $this->db->prepare(
            "SELECT MONTH(payment_date) as month, SUM(amount) as total
             FROM payments
             WHERE YEAR(payment_date) = YEAR(CURDATE()) AND status = 'paid'
             GROUP BY MONTH(payment_date)
             ORDER BY month"
        );
        $monthlyIncomeStmt->execute();
        $monthlyIncome = array_fill(1, 12, 0);
        while ($row = $monthlyIncomeStmt->fetch(PDO::FETCH_ASSOC)) {
            $monthlyIncome[(int)$row['month']] = (float)$row['total'];
        }
        
        // Member growth data
        $memberGrowthStmt = $this->db->prepare(
            "SELECT MONTH(created_at) as month, COUNT(*) as total
             FROM members
             WHERE YEAR(created_at) = YEAR(CURDATE())
             GROUP BY MONTH(created_at)
             ORDER BY month"
        );
        $memberGrowthStmt->execute();
        $memberGrowth = array_fill(1, 12, 0);
        while ($row = $memberGrowthStmt->fetch(PDO::FETCH_ASSOC)) {
            $memberGrowth[(int)$row['month']] = (int)$row['total'];
        }
        
        // Payment status distribution
        $paymentStatusStmt = $this->db->prepare(
            "SELECT status, COUNT(*) as count
             FROM payments
             WHERE YEAR(payment_date) = YEAR(CURDATE())
             GROUP BY status"
        );
        $paymentStatusStmt->execute();
        $paymentStatus = ['paid' => 0, 'pending' => 0];
        while ($row = $paymentStatusStmt->fetch(PDO::FETCH_ASSOC)) {
            $paymentStatus[$row['status']] = (int)$row['count'];
        }
        
        // Category distribution
        $categoryDistStmt = $this->db->prepare(
            "SELECT mc.name, mc.color, COUNT(m.id) as count
             FROM member_categories mc
             LEFT JOIN members m ON mc.id = m.category_id AND m.status = 'active'
             GROUP BY mc.id
             HAVING count > 0
             ORDER BY count DESC"
        );
        $categoryDistStmt->execute();
        $categoryDistribution = $categoryDistStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/dashboard.php';
    }
}
