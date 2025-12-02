<?php

class DonationController {
    private $db;
    private $donation;
    private $donor;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donation = new Donation($this->db);
        $this->donor = new Donor($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    // List donations per year
    public function index() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        $stmt = $this->donation->readAllByYear($year);
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donations/index.php';
    }

    // Show form to add donation
    public function create() {
        $this->checkAdmin();
        $donorsStmt = $this->donor->readAll();
        $donors = $donorsStmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donations/create.php';
    }

    // Store new donation
    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->donation->donor_id = $_POST['donor_id'];
            $this->donation->amount = $_POST['amount'];
            $this->donation->type = $_POST['type'];
            $this->donation->year = $_POST['year'];
            if ($this->donation->create()) {
                $lastId = $this->db->lastInsertId();
                
                // Auditoría de alta de donación
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'create', 'donation', $lastId, 'Alta de donación por el usuario ' . ($_SESSION['username'] ?? ''));
                
                // Crear asiento contable automático
                require_once __DIR__ . '/../Helpers/AccountingHelper.php';
                $donationType = $_POST['type'] ?? 'monetary';
                $description = "Donación " . $donationType . " (" . $_POST['year'] . ")";
                $donationDate = date('Y-m-d'); // Usar fecha actual o del POST si existe
                
                $accountingCreated = AccountingHelper::createEntryFromDonation(
                    $this->db,
                    $lastId,
                    $_POST['amount'],
                    $description,
                    $donationDate,
                    'transfer'
                );
                
                if (!$accountingCreated) {
                    error_log("No se pudo crear el asiento contable para la donación #$lastId");
                }
                
                header('Location: index.php?page=donations&msg=created');
                exit;
            } else {
                $error = "Error creating donation.";
                $donorsStmt = $this->donor->readAll();
                $donors = $donorsStmt->fetchAll(PDO::FETCH_ASSOC);
                require __DIR__ . '/../Views/donations/create.php';
            }
        }
    }
}
?>

