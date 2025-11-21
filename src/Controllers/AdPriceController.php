<?php
require_once __DIR__ . '/../Models/AdPrice.php';

class AdPriceController {
    private $db;
    private $adPrice;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->adPrice = new AdPrice($this->db);
        $this->checkAdmin();
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?page=login");
            exit;
        }
    }

    public function index() {
        // This might be called via AJAX or included in settings
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $prices = $this->adPrice->getPricesByYear($year);
        
        // Return JSON if requested via AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($prices);
            exit;
        }
        
        // Otherwise render view (if we had a standalone page, but we'll integrate into settings)
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $year = $_POST['year'];
            $prices = [
                'media' => $_POST['price_media'] ?? 0,
                'full' => $_POST['price_full'] ?? 0,
                'cover' => $_POST['price_cover'] ?? 0,
                'back_cover' => $_POST['price_back_cover'] ?? 0
            ];

            $success = true;
            foreach ($prices as $type => $amount) {
                $this->adPrice->year = $year;
                $this->adPrice->type = $type;
                $this->adPrice->amount = $amount;
                if (!$this->adPrice->save()) {
                    $success = false;
                }
            }

            if ($success) {
                $_SESSION['message'] = "Precios de anuncios actualizados correctamente para el año $year.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error al actualizar algunos precios.";
                $_SESSION['message_type'] = "danger";
            }

            header("Location: index.php?page=settings"); // Redirect back to settings
            exit;
        }
    }
    
    // Helper to get prices for a view
    public function getPrices($year) {
        return $this->adPrice->getPricesByYear($year);
    }
}
?>
