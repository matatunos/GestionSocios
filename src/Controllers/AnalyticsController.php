<?php

require_once __DIR__ . '/../Models/Analytics.php';
require_once __DIR__ . '/../Helpers/Auth.php';

class AnalyticsController {
    private $db;
    private $analyticsModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->analyticsModel = new Analytics($this->db);
    }
    
    /**
     * Main analytics page
     */
    public function index() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: index.php?page=login');
            exit;
        }
        
        // Check permissions (admin or treasurer can view analytics)
        if (!Auth::hasPermission('reports', 'view')) {
            $_SESSION['error'] = 'No tiene permisos para ver estadísticas';
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        $years = $_GET['years'] ?? 5;
        
        // Get all analytics data
        $data = [
            'summary' => $this->analyticsModel->getComparativeSummary(),
            'membersGrowth' => $this->analyticsModel->getMembersGrowthByYear($years),
            'totalMembers' => $this->analyticsModel->getTotalMembersByYear($years),
            'incomeByYear' => $this->analyticsModel->getIncomeByYear($years),
            'donationsByYear' => $this->analyticsModel->getDonationsByYear($years),
            'expensesByYear' => $this->analyticsModel->getExpensesByYear($years),
            'monthlyTrend' => $this->analyticsModel->getMonthlyTrend(),
            'retentionRate' => $this->analyticsModel->getRetentionRate($years),
            'categoriesDistribution' => $this->analyticsModel->getMemberCategoriesDistribution(),
            'paymentTypes' => $this->analyticsModel->getPaymentTypeDistribution(),
            'prediction' => $this->analyticsModel->predictNextYearIncome(),
            'selectedYears' => $years
        ];
        
        extract($data);
        require __DIR__ . '/../Views/analytics/index.php';
    }
    
    /**
     * Get data for AJAX requests
     */
    public function getData() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        
        $type = $_GET['type'] ?? '';
        $years = $_GET['years'] ?? 5;
        $year = $_GET['year'] ?? date('Y');
        
        $result = [];
        
        switch ($type) {
            case 'membersGrowth':
                $result = $this->analyticsModel->getMembersGrowthByYear($years);
                break;
            case 'income':
                $result = $this->analyticsModel->getIncomeByYear($years);
                break;
            case 'donations':
                $result = $this->analyticsModel->getDonationsByYear($years);
                break;
            case 'expenses':
                $result = $this->analyticsModel->getExpensesByYear($years);
                break;
            case 'monthlyTrend':
                $result = $this->analyticsModel->getMonthlyTrend($year);
                break;
            case 'retention':
                $result = $this->analyticsModel->getRetentionRate($years);
                break;
            case 'categories':
                $result = $this->analyticsModel->getMemberCategoriesDistribution();
                break;
            case 'paymentTypes':
                $result = $this->analyticsModel->getPaymentTypeDistribution($year);
                break;
            case 'prediction':
                $result = $this->analyticsModel->predictNextYearIncome();
                break;
            case 'summary':
                $result = $this->analyticsModel->getComparativeSummary();
                break;
            default:
                $result = ['error' => 'Tipo de datos no válido'];
        }
        
        echo json_encode($result);
        exit;
    }
}
