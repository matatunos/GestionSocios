<?php

class Analytics {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get members growth comparison by year
     */
    public function getMembersGrowthByYear($years = 5) {
        $currentYear = date('Y');
        $startYear = $currentYear - $years + 1;
        
        $query = "SELECT 
                    YEAR(join_date) as year,
                    COUNT(*) as new_members,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members
                  FROM members 
                  WHERE YEAR(join_date) >= :start_year
                  GROUP BY YEAR(join_date)
                  ORDER BY year";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_year', $startYear);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total active members by year (including previous years)
     */
    public function getTotalMembersByYear($years = 5) {
        $currentYear = date('Y');
        $result = [];
        
        for ($year = $currentYear - $years + 1; $year <= $currentYear; $year++) {
            $query = "SELECT COUNT(*) as total
                      FROM members 
                      WHERE YEAR(join_date) <= :year 
                      AND (status = 'active' OR (status = 'inactive' AND YEAR(COALESCE(updated_at, NOW())) > :year))";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $result[] = [
                'year' => $year,
                'total_members' => $row['total']
            ];
        }
        
        return $result;
    }
    
    /**
     * Get income comparison by year
     */
    public function getIncomeByYear($years = 5) {
        $currentYear = date('Y');
        $startYear = $currentYear - $years + 1;
        
        $query = "SELECT 
                    YEAR(payment_date) as year,
                    SUM(amount) as total_income,
                    COUNT(*) as payment_count,
                    AVG(amount) as avg_payment
                  FROM payments 
                  WHERE YEAR(payment_date) >= :start_year
                  GROUP BY YEAR(payment_date)
                  ORDER BY year";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_year', $startYear);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get donations comparison by year
     */
    public function getDonationsByYear($years = 5) {
        $currentYear = date('Y');
        $startYear = $currentYear - $years + 1;
        
        $query = "SELECT 
                    YEAR(donation_date) as year,
                    SUM(amount) as total_donations,
                    COUNT(*) as donation_count,
                    COUNT(DISTINCT donor_id) as unique_donors
                  FROM donations 
                  WHERE YEAR(donation_date) >= :start_year
                  GROUP BY YEAR(donation_date)
                  ORDER BY year";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_year', $startYear);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get expenses comparison by year
     */
    public function getExpensesByYear($years = 5) {
        $currentYear = date('Y');
        $startYear = $currentYear - $years + 1;
        
        $query = "SELECT 
                    YEAR(expense_date) as year,
                    SUM(amount) as total_expenses,
                    COUNT(*) as expense_count,
                    AVG(amount) as avg_expense
                  FROM expenses 
                  WHERE YEAR(expense_date) >= :start_year
                  GROUP BY YEAR(expense_date)
                  ORDER BY year";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_year', $startYear);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get monthly trend for current year
     */
    public function getMonthlyTrend($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $query = "SELECT 
                    MONTH(payment_date) as month,
                    SUM(amount) as income,
                    COUNT(*) as payment_count
                  FROM payments 
                  WHERE YEAR(payment_date) = :year
                  GROUP BY MONTH(payment_date)
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get expenses
        $query = "SELECT 
                    MONTH(expense_date) as month,
                    SUM(amount) as expenses,
                    COUNT(*) as expense_count
                  FROM expenses 
                  WHERE YEAR(expense_date) = :year
                  GROUP BY MONTH(expense_date)
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge data
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = 0;
            $expense = 0;
            
            foreach ($payments as $p) {
                if ($p['month'] == $m) {
                    $income = $p['income'];
                    break;
                }
            }
            
            foreach ($expenses as $e) {
                if ($e['month'] == $m) {
                    $expense = $e['expenses'];
                    break;
                }
            }
            
            $result[] = [
                'month' => $m,
                'month_name' => $this->getMonthName($m),
                'income' => $income,
                'expenses' => $expense,
                'balance' => $income - $expense
            ];
        }
        
        return $result;
    }
    
    /**
     * Get retention rate by year
     */
    public function getRetentionRate($years = 5) {
        $currentYear = date('Y');
        $result = [];
        
        for ($year = $currentYear - $years + 1; $year <= $currentYear; $year++) {
            // Members at start of year
            $query = "SELECT COUNT(*) as total
                      FROM members 
                      WHERE YEAR(join_date) < :year 
                      AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            $startCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Members still active at end of year
            $query = "SELECT COUNT(*) as total
                      FROM members 
                      WHERE YEAR(join_date) < :year 
                      AND status = 'active'
                      AND (updated_at IS NULL OR YEAR(updated_at) >= :year)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            $retained = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $rate = $startCount > 0 ? round(($retained / $startCount) * 100, 2) : 0;
            
            $result[] = [
                'year' => $year,
                'start_count' => $startCount,
                'retained' => $retained,
                'retention_rate' => $rate
            ];
        }
        
        return $result;
    }
    
    /**
     * Get member categories distribution
     */
    public function getMemberCategoriesDistribution() {
        $query = "SELECT 
                    mc.name as category,
                    mc.color,
                    COUNT(m.id) as member_count,
                    ROUND((COUNT(m.id) * 100.0 / (SELECT COUNT(*) FROM members WHERE status = 'active')), 2) as percentage
                  FROM member_categories mc
                  LEFT JOIN members m ON mc.id = m.category_id AND m.status = 'active'
                  GROUP BY mc.id, mc.name, mc.color
                  ORDER BY member_count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment type distribution
     */
    public function getPaymentTypeDistribution($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $query = "SELECT 
                    payment_type,
                    COUNT(*) as payment_count,
                    SUM(amount) as total_amount,
                    ROUND((SUM(amount) * 100.0 / (SELECT SUM(amount) FROM payments WHERE YEAR(payment_date) = :year2)), 2) as percentage
                  FROM payments 
                  WHERE YEAR(payment_date) = :year
                  GROUP BY payment_type
                  ORDER BY total_amount DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':year2', $year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Predict next year income based on historical data
     */
    public function predictNextYearIncome() {
        // Get last 3 years income
        $query = "SELECT 
                    YEAR(payment_date) as year,
                    SUM(amount) as total
                  FROM payments 
                  WHERE YEAR(payment_date) >= YEAR(NOW()) - 3
                  GROUP BY YEAR(payment_date)
                  ORDER BY year";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($data) < 2) {
            return null;
        }
        
        // Simple linear regression
        $n = count($data);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        
        foreach ($data as $i => $row) {
            $x = $i + 1; // Year index
            $y = $row['total'];
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Predict next year
        $nextYear = date('Y') + 1;
        $prediction = $intercept + $slope * ($n + 1);
        
        return [
            'year' => $nextYear,
            'predicted_income' => round($prediction, 2),
            'growth_rate' => count($data) > 1 ? round((($data[count($data)-1]['total'] - $data[0]['total']) / $data[0]['total']) * 100, 2) : 0,
            'confidence' => 'medium' // Could be improved with more sophisticated analysis
        ];
    }
    
    /**
     * Get comparative summary
     */
    public function getComparativeSummary() {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        // Members comparison
        $query = "SELECT 
                    SUM(CASE WHEN YEAR(join_date) = :current_year THEN 1 ELSE 0 END) as new_members_current,
                    SUM(CASE WHEN YEAR(join_date) = :last_year THEN 1 ELSE 0 END) as new_members_last,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as total_active
                  FROM members";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_year', $currentYear);
        $stmt->bindParam(':last_year', $lastYear);
        $stmt->execute();
        $members = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Income comparison
        $query = "SELECT 
                    SUM(CASE WHEN YEAR(payment_date) = :current_year THEN amount ELSE 0 END) as income_current,
                    SUM(CASE WHEN YEAR(payment_date) = :last_year THEN amount ELSE 0 END) as income_last
                  FROM payments";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_year', $currentYear);
        $stmt->bindParam(':last_year', $lastYear);
        $stmt->execute();
        $income = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Expenses comparison
        $query = "SELECT 
                    SUM(CASE WHEN YEAR(expense_date) = :current_year THEN amount ELSE 0 END) as expenses_current,
                    SUM(CASE WHEN YEAR(expense_date) = :last_year THEN amount ELSE 0 END) as expenses_last
                  FROM expenses";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_year', $currentYear);
        $stmt->bindParam(':last_year', $lastYear);
        $stmt->execute();
        $expenses = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Donations comparison
        $query = "SELECT 
                    SUM(CASE WHEN YEAR(donation_date) = :current_year THEN amount ELSE 0 END) as donations_current,
                    SUM(CASE WHEN YEAR(donation_date) = :last_year THEN amount ELSE 0 END) as donations_last
                  FROM donations";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_year', $currentYear);
        $stmt->bindParam(':last_year', $lastYear);
        $stmt->execute();
        $donations = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate percentages
        $membersChange = $members['new_members_last'] > 0 
            ? round((($members['new_members_current'] - $members['new_members_last']) / $members['new_members_last']) * 100, 2)
            : 0;
        
        $incomeChange = $income['income_last'] > 0 
            ? round((($income['income_current'] - $income['income_last']) / $income['income_last']) * 100, 2)
            : 0;
        
        $expensesChange = $expenses['expenses_last'] > 0 
            ? round((($expenses['expenses_current'] - $expenses['expenses_last']) / $expenses['expenses_last']) * 100, 2)
            : 0;
        
        $donationsChange = $donations['donations_last'] > 0 
            ? round((($donations['donations_current'] - $donations['donations_last']) / $donations['donations_last']) * 100, 2)
            : 0;
        
        return [
            'members' => [
                'current' => $members['new_members_current'],
                'last' => $members['new_members_last'],
                'change' => $membersChange,
                'total_active' => $members['total_active']
            ],
            'income' => [
                'current' => $income['income_current'],
                'last' => $income['income_last'],
                'change' => $incomeChange
            ],
            'expenses' => [
                'current' => $expenses['expenses_current'],
                'last' => $expenses['expenses_last'],
                'change' => $expensesChange
            ],
            'donations' => [
                'current' => $donations['donations_current'],
                'last' => $donations['donations_last'],
                'change' => $donationsChange
            ]
        ];
    }
    
    /**
     * Get month name in Spanish
     */
    private function getMonthName($month) {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $months[$month] ?? '';
    }
}
