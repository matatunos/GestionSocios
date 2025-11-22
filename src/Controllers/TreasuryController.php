<?php

require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Expense.php';
require_once __DIR__ . '/../Models/Member.php';

class TreasuryController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function dashboard() {
        // Get current year and month
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Get financial statistics
        $stats = $this->getFinancialStats($currentYear, $currentMonth);
        
        // Get pending payments (members without payment for current year)
        $pendingPayments = $this->getPendingPayments($currentYear);
        
        // Get pending book ad payments
        $pendingBookAds = $this->getPendingBookAds($currentYear);
        
        // Get monthly evolution for the year
        $monthlyEvolution = $this->getMonthlyEvolution($currentYear);
        
        // Get recent payments (last 10)
        $recentPayments = $this->getRecentPayments(10);
        
        // Get recent expenses (last 10)
        $recentExpenses = $this->getRecentExpenses(10);
        
        require_once __DIR__ . '/../Views/treasury/dashboard.php';
    }
    
    private function getFinancialStats($year, $month) {
        $stats = [];
        
        // Total income for the year
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM payments 
                  WHERE fee_year = :year AND status = 'paid'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $stats['year_income'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total expenses for the year
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM expenses 
                  WHERE YEAR(expense_date) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $stats['year_expenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Balance
        $stats['balance'] = $stats['year_income'] - $stats['year_expenses'];
        
        // Income for current month
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM payments 
                  WHERE fee_year = :year AND MONTH(payment_date) = :month AND status = 'paid'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        $stats['month_income'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Expenses for current month
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM expenses 
                  WHERE YEAR(expense_date) = :year AND MONTH(expense_date) = :month";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        $stats['month_expenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Month balance
        $stats['month_balance'] = $stats['month_income'] - $stats['month_expenses'];
        
        // Count pending payments
        $query = "SELECT COUNT(*) as total 
                  FROM members m
                  WHERE m.status = 'active'
                  AND NOT EXISTS (
                      SELECT 1 FROM payments p 
                      WHERE p.member_id = m.id AND p.fee_year = :year AND p.status = 'paid'
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $stats['pending_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Estimated pending amount (sum of default fees for pending members + book_ad pending)
        $query = "SELECT COALESCE(SUM(COALESCE(mc.default_fee, 0)), 0) as total
                  FROM members m
                  LEFT JOIN member_categories mc ON m.category_id = mc.id
                  WHERE m.status = 'active'
                  AND NOT EXISTS (
                      SELECT 1 FROM payments p 
                      WHERE p.member_id = m.id AND p.fee_year = :year AND p.status = 'paid'
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $membersPending = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Add book_ad pending payments
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                  FROM payments
                  WHERE payment_type = 'book_ad' AND status = 'pending' AND fee_year = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $bookAdPending = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stats['pending_amount'] = $membersPending + $bookAdPending;
        
        return $stats;
    }
    
    private function getPendingPayments($year) {
        $query = "SELECT m.id, m.first_name, m.last_name, m.email, m.phone,
                         mc.name as category_name, mc.color as category_color,
                         COALESCE(mc.default_fee, 0) as expected_amount
                  FROM members m
                  LEFT JOIN member_categories mc ON m.category_id = mc.id
                  WHERE m.status = 'active'
                  AND NOT EXISTS (
                      SELECT 1 FROM payments p 
                      WHERE p.member_id = m.id AND p.fee_year = :year AND p.status = 'paid'
                  )
                  ORDER BY m.last_name ASC, m.first_name ASC
                  LIMIT 50";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getMonthlyEvolution($year) {
        $evolution = [];
        
        // Initialize all months with zero
        for ($m = 1; $m <= 12; $m++) {
            $evolution[$m] = [
                'month' => $m,
                'income' => 0,
                'expenses' => 0
            ];
        }
        
        // Get income by month
        $query = "SELECT MONTH(payment_date) as month, SUM(amount) as total
                  FROM payments
                  WHERE fee_year = :year AND status = 'paid'
                  GROUP BY MONTH(payment_date)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $evolution[$row['month']]['income'] = $row['total'];
        }
        
        // Get expenses by month
        $query = "SELECT MONTH(expense_date) as month, SUM(amount) as total
                  FROM expenses
                  WHERE YEAR(expense_date) = :year
                  GROUP BY MONTH(expense_date)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $evolution[$row['month']]['expenses'] = $row['total'];
        }
        
        return array_values($evolution);
    }
    
    private function getPendingBookAds($year) {
        $query = "SELECT p.id, p.amount, p.concept, p.created_at,
                         ba.id as book_ad_id, ba.ad_type,
                         d.name as donor_name, d.email as donor_email, d.phone as donor_phone
                  FROM payments p
                  INNER JOIN book_ads ba ON p.book_ad_id = ba.id
                  INNER JOIN donors d ON ba.donor_id = d.id
                  WHERE p.payment_type = 'book_ad' 
                    AND p.status = 'pending'
                    AND p.fee_year = :year
                  ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecentPayments($limit) {
        $query = "SELECT p.*, 
                         m.first_name, m.last_name,
                         CASE 
                            WHEN p.payment_type = 'book_ad' THEN p.concept
                            ELSE CONCAT(m.first_name, ' ', m.last_name)
                         END as display_name
                  FROM payments p
                  LEFT JOIN members m ON p.member_id = m.id
                  WHERE p.status = 'paid'
                  ORDER BY p.payment_date DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecentExpenses($limit) {
        $query = "SELECT e.*, ec.name as category_name
                  FROM expenses e
                  LEFT JOIN expense_categories ec ON e.category_id = ec.id
                  ORDER BY e.expense_date DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
