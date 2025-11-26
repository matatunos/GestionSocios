<?php

class BookDashboardController {
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

    public function index() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');

        // 1. Get Total Income from Book Ads
        // Only count PAID ads
        $incomeQuery = "SELECT 
                            COUNT(*) as total_ads,
                            SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_income,
                            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_income,
                            SUM(CASE WHEN ad_type = 'cover' THEN 1 ELSE 0 END) as cover_count,
                            SUM(CASE WHEN ad_type = 'back_cover' THEN 1 ELSE 0 END) as back_cover_count,
                            SUM(CASE WHEN ad_type = 'full' THEN 1 ELSE 0 END) as full_page_count,
                            SUM(CASE WHEN ad_type = 'media' THEN 1 ELSE 0 END) as half_page_count
                        FROM book_ads 
                        WHERE year = :year";
        
        $stmt = $this->db->prepare($incomeQuery);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $incomeStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Get Expenses for "Libro de Fiestas"
        // First, find or create the category
        $categoryId = $this->getOrCreateBookExpenseCategory();
        
        $expenseQuery = "SELECT 
                            COUNT(*) as total_expenses,
                            SUM(amount) as total_cost
                         FROM expenses 
                         WHERE category_id = :category_id 
                         AND YEAR(expense_date) = :year";
        
        $stmt = $this->db->prepare($expenseQuery);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $expenseStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Calculate Net Result
        $netResult = ($incomeStats['total_income'] ?? 0) - ($expenseStats['total_cost'] ?? 0);

        // 4. Get Recent Activity (Last 5 ads)
        $recentAdsQuery = "SELECT ba.*, d.name as donor_name 
                           FROM book_ads ba
                           JOIN donors d ON ba.donor_id = d.id
                           WHERE ba.year = :year
                           ORDER BY ba.created_at DESC
                           LIMIT 5";
        $stmt = $this->db->prepare($recentAdsQuery);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $recentAds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/book/dashboard.php';
    }

    private function getOrCreateBookExpenseCategory() {
        // Check if category exists
        $query = "SELECT id FROM expense_categories WHERE name = 'Libro de Fiestas' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id'];
        }

        // Create if not exists
        $insertQuery = "INSERT INTO expense_categories (name, description, color, is_active) 
                        VALUES ('Libro de Fiestas', 'Gastos relacionados con la impresión y maquetación del libro', '#4F46E5', 1)";
        $this->db->exec($insertQuery);
        return $this->db->lastInsertId();
    }
}
