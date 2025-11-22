<?php

class BookAdController {
    private $db;
    private $bookAd;
    private $donor;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->bookAd = new BookAd($this->db);
        $this->donor = new Donor($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=book');
            exit;
        }
    }

    public function index() {
        $year = $_GET['year'] ?? date('Y');
        $stmt = $this->bookAd->readAllByYear($year);
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals
        $totalRevenue = 0;
        foreach ($ads as $ad) {
            $totalRevenue += $ad['amount'];
        }

        require __DIR__ . '/../Views/book/index.php';
    }

    public function create() {
        $this->checkAdmin();
        
        $year = $_GET['year'] ?? date('Y');

        // Get donors for the dropdown
        require_once __DIR__ . '/../Models/Donor.php';
        $donorModel = new Donor($this->db);
        $donors = $donorModel->readAll();

        // Get ad prices for current year
        require_once __DIR__ . '/../Models/AdPrice.php';
        $adPriceModel = new AdPrice($this->db);
        $adPrices = $adPriceModel->getPricesByYear($year);
        
        require __DIR__ . '/../Views/book/create_ad.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bookAd->donor_id = $_POST['donor_id'];
            $this->bookAd->year = !empty($_POST['year']) ? $_POST['year'] : date('Y');
            $this->bookAd->ad_type = $_POST['ad_type'];
            $this->bookAd->amount = $_POST['amount'];
            $this->bookAd->status = $_POST['status'];
            $this->bookAd->image_url = ''; // Handle upload if needed later

            if ($this->bookAd->create()) {
                header('Location: index.php?page=book&year=' . $_POST['year']);
            } else {
                $error = "Error creating ad.";
                $year = $_POST['year'];
                // Re-fetch donors for the view
                require_once __DIR__ . '/../Models/Donor.php';
                $donorModel = new Donor($this->db);
                $donors = $donorModel->readAll();
                require __DIR__ . '/../Views/book/create_ad.php';
            }
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->bookAd->id = $id;
        if ($this->bookAd->readOne()) {
            $year = $this->bookAd->year;
            if ($this->bookAd->delete()) {
                header('Location: index.php?page=book&year=' . $year . '&msg=deleted');
                exit;
            }
        }
        header('Location: index.php?page=book&error=1');
    }
}
?>
