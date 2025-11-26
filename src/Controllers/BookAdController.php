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

            // Validar que solo haya una portada y una contraportada por año
            if ($this->bookAd->ad_type === 'cover' || $this->bookAd->ad_type === 'back_cover') {
                $checkQuery = "SELECT COUNT(*) as count FROM book_ads WHERE year = :year AND ad_type = :ad_type";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bindParam(':year', $this->bookAd->year);
                $checkStmt->bindParam(':ad_type', $this->bookAd->ad_type);
                $checkStmt->execute();
                $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['count'] > 0) {
                    $typeName = $this->bookAd->ad_type === 'cover' ? 'portada' : 'contraportada';
                    $error = "Ya existe una {$typeName} para el año {$this->bookAd->year}. Solo puede haber una {$typeName} por año.";
                    $year = $_POST['year'];
                    require_once __DIR__ . '/../Models/Donor.php';
                    $donorModel = new Donor($this->db);
                    $donors = $donorModel->readAll();
                    require_once __DIR__ . '/../Models/AdPrice.php';
                    $adPriceModel = new AdPrice($this->db);
                    $adPrices = $adPriceModel->getPricesByYear($year);
                    require __DIR__ . '/../Views/book/create_ad.php';
                    return;
                }
            }

            if ($this->bookAd->create()) {
                // Get the last inserted ID
                $adId = $this->db->lastInsertId();
                
                // Create payment record immediately (pending or paid)
                $this->syncPayment($adId);
                
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

    public function edit($id) {
        $this->checkAdmin();
        $this->bookAd->id = $id;
        
        if ($this->bookAd->readOne()) {
            $bookAd = $this->bookAd;
            
            // Get donors for dropdown
            require_once __DIR__ . '/../Models/Donor.php';
            $donorModel = new Donor($this->db);
            $donors = $donorModel->readAll();
            
            // Get ad prices for current year
            require_once __DIR__ . '/../Models/AdPrice.php';
            $adPriceModel = new AdPrice($this->db);
            $adPrices = $adPriceModel->getPricesByYear($bookAd->year);
            
            require __DIR__ . '/../Views/book/edit_ad.php';
        } else {
            header('Location: index.php?page=book&error=1');
        }
    }
    
    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bookAd->id = $id;
            $this->bookAd->donor_id = $_POST['donor_id'];
            $this->bookAd->year = $_POST['year'];
            $this->bookAd->ad_type = $_POST['ad_type'];
            $this->bookAd->amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
            $this->bookAd->status = $_POST['status'];
            $this->bookAd->image_url = ''; // Keep existing
            
            // Validar que solo haya una portada y una contraportada por año
            if ($this->bookAd->ad_type === 'cover' || $this->bookAd->ad_type === 'back_cover') {
                $checkQuery = "SELECT COUNT(*) as count FROM book_ads WHERE year = :year AND ad_type = :ad_type AND id != :id";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bindParam(':year', $this->bookAd->year);
                $checkStmt->bindParam(':ad_type', $this->bookAd->ad_type);
                $checkStmt->bindParam(':id', $id);
                $checkStmt->execute();
                $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['count'] > 0) {
                    $typeName = $this->bookAd->ad_type === 'cover' ? 'portada' : 'contraportada';
                    $error = "Ya existe una {$typeName} para el año {$this->bookAd->year}. Solo puede haber una {$typeName} por año.";
                    $bookAd = $this->bookAd;
                    require_once __DIR__ . '/../Models/Donor.php';
                    $donorModel = new Donor($this->db);
                    $donors = $donorModel->readAll();
                    require_once __DIR__ . '/../Models/AdPrice.php';
                    $adPriceModel = new AdPrice($this->db);
                    $adPrices = $adPriceModel->getPricesByYear($_POST['year']);
                    require __DIR__ . '/../Views/book/edit_ad.php';
                    return;
                }
            }
            
            if ($this->bookAd->update()) {
                // If marked as paid, create/update payment record
                if ($_POST['status'] === 'paid') {
                    $this->syncPayment($id);
                }
                header('Location: index.php?page=book&year=' . $_POST['year'] . '&msg=updated');
                exit;
            } else {
                $error = "Error actualizando anuncio.";
                $bookAd = $this->bookAd;
                
                // Re-fetch data for view
                require_once __DIR__ . '/../Models/Donor.php';
                $donorModel = new Donor($this->db);
                $donors = $donorModel->readAll();
                
                require_once __DIR__ . '/../Models/AdPrice.php';
                $adPriceModel = new AdPrice($this->db);
                $adPrices = $adPriceModel->getPricesByYear($_POST['year']);
                
                require __DIR__ . '/../Views/book/edit_ad.php';
            }
        }
    }
    
    public function markPaid($id) {
        $this->checkAdmin();
        $this->bookAd->id = $id;
        
        if ($this->bookAd->readOne()) {
            $year = $this->bookAd->year;
            $this->bookAd->status = 'paid';
            
            if ($this->bookAd->update()) {
                // Create payment record
                $this->syncPayment($id);
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'markPaid', 'bookad_payment', $id, 'Pago de anuncio de libro marcado como realizado por el usuario ' . ($_SESSION['username'] ?? ''));
                header('Location: index.php?page=book&year=' . $year . '&msg=marked_paid');
                exit;
            }
        }
        header('Location: index.php?page=book&error=1');
    }
    
    private function syncPayment($adId) {
        // Sync book ad payment with payments table
        $this->bookAd->id = $adId;
        $this->bookAd->readOne();
        
        // Get donor info
        require_once __DIR__ . '/../Models/Donor.php';
        $donorModel = new Donor($this->db);
        $donorModel->id = $this->bookAd->donor_id;
        $donorModel->readOne();
        
        // Check if payment already exists
        $checkStmt = $this->db->prepare("SELECT id FROM payments WHERE payment_type = 'book_ad' AND book_ad_id = ?");
        $checkStmt->execute([$adId]);
        $existingPayment = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingPayment) {
            // Update existing payment
            $paymentStatus = $this->bookAd->status === 'paid' ? 'paid' : 'pending';
            $paymentDate = $this->bookAd->status === 'paid' ? date('Y-m-d') : NULL;
            $updateStmt = $this->db->prepare("UPDATE payments SET amount = ?, payment_date = ?, status = ? WHERE id = ?");
            $updateStmt->execute([
                $this->bookAd->amount,
                $paymentDate,
                $paymentStatus,
                $existingPayment['id']
            ]);
        } else {
            // Create new payment record (pending or paid)
            $paymentStatus = $this->bookAd->status === 'paid' ? 'paid' : 'pending';
            $paymentDate = $this->bookAd->status === 'paid' ? date('Y-m-d') : NULL;
            $insertStmt = $this->db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type, book_ad_id) VALUES (?, ?, ?, ?, ?, ?, 'book_ad', ?)");
            $insertStmt->execute([
                $this->bookAd->donor_id,
                $this->bookAd->amount,
                $paymentDate,
                'Anuncio Libro Fiestas ' . $this->bookAd->year . ' - ' . $donorModel->name,
                $paymentStatus,
                $this->bookAd->year,
                $adId
            ]);
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->bookAd->id = $id;
        if ($this->bookAd->readOne()) {
            $year = $this->bookAd->year;
            
            // Delete associated payment if exists
            $deletePaymentStmt = $this->db->prepare("DELETE FROM payments WHERE payment_type = 'book_ad' AND book_ad_id = ?");
            $deletePaymentStmt->execute([$id]);
            
            if ($this->bookAd->delete()) {
                header('Location: index.php?page=book&year=' . $year . '&msg=deleted');
                exit;
            }
        }
        header('Location: index.php?page=book&error=1');
    }
}
?>
