<?php

class ReportController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function executiveReport() {
        $this->checkAdmin();
        
        // Get income data by year and type
        $incomeQuery = "
            SELECT 
                YEAR(payment_date) as year,
                payment_type,
                COUNT(*) as count,
                SUM(amount) as total
            FROM payments
            WHERE status = 'paid'
            GROUP BY YEAR(payment_date), payment_type
            ORDER BY year DESC, payment_type
        ";
        $incomeStmt = $this->db->prepare($incomeQuery);
        $incomeStmt->execute();
        $incomeData = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total income per year
        $yearlyQuery = "
            SELECT 
                YEAR(payment_date) as year,
                SUM(amount) as total,
                COUNT(*) as payment_count
            FROM payments
            WHERE status = 'paid'
            GROUP BY YEAR(payment_date)
            ORDER BY year DESC
        ";
        $yearlyStmt = $this->db->prepare($yearlyQuery);
        $yearlyStmt->execute();
        $yearlyTotals = $yearlyStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get member count per year (members created each year)
        $membersQuery = "
            SELECT 
                YEAR(created_at) as year,
                COUNT(*) as member_count
            FROM members
            GROUP BY YEAR(created_at)
            ORDER BY year DESC
        ";
        $membersStmt = $this->db->prepare($membersQuery);
        $membersStmt->execute();
        $membersByYear = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize data by year
        $reportData = [];
        foreach ($incomeData as $row) {
            $year = $row['year'];
            if (!isset($reportData[$year])) {
                $reportData[$year] = [
                    'year' => $year,
                    'fee' => 0,
                    'event' => 0,
                    'donation' => 0,
                    'total' => 0
                ];
            }
            $reportData[$year][$row['payment_type']] = (float)$row['total'];
            $reportData[$year]['total'] += (float)$row['total'];
        }

        require __DIR__ . '/../Views/reports/executive.php';
    }
}
?>
