<?php

class FeeController {
    private $db;
    private $fee;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->fee = new Fee($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        // Redirect to settings as fees are now managed there
        header('Location: index.php?page=settings&tab=fees');
        exit;
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            
            try {
                $year = $_POST['year'];
                $this->fee->year = $year;
                $this->fee->amount = $_POST['amount'];

                // Check if fee already exists to provide appropriate message
                $exists = $this->fee->exists($year);

                if ($this->fee->createOrUpdate()) {
                    // Auditoría de alta/modificación de tarifa
                    require_once __DIR__ . '/../Models/AuditLog.php';
                    $audit = new AuditLog($this->db);
                    $action = $exists ? 'update' : 'create';
                    $audit->create($_SESSION['user_id'], $action, 'fee', $year, 'Tarifa de socio ' . ($exists ? 'modificada' : 'creada') . ' por el usuario ' . ($_SESSION['username'] ?? ''));
                    header("Location: index.php?page=settings&tab=fees&success=$action&year=$year");
                    exit;
                } else {
                    // In case of error, we might want to redirect back with error param
                    header("Location: index.php?page=settings&tab=fees&error=save_failed");
                    exit;
                }
            } catch (Exception $e) {
                header("Location: index.php?page=settings&tab=fees&error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    public function generatePayments($year) {
        $this->checkAdmin();
        
        // Load CategoryFeeHistory model
        require_once __DIR__ . '/../Models/CategoryFeeHistory.php';
        $feeHistory = new CategoryFeeHistory($this->db);
        
        // Get all category fees for the year
        $stmt = $feeHistory->readByYear($year);
        $categoryFees = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categoryFees[$row['category_id']] = $row['fee_amount'];
        }
        
        // Get all active members with their categories
        $memberModel = new Member($this->db);
        $stmtMembers = $this->db->prepare("
            SELECT m.id, m.category_id, mc.name as category_name, mc.default_fee
            FROM members m
            LEFT JOIN member_categories mc ON m.category_id = mc.id
            WHERE m.status = 'active'
        ");
        $stmtMembers->execute();
        $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        $skipped = 0;
        $categoriesWithoutFee = [];
        
        foreach ($members as $member) {
            // Check if payment already exists for this member and year
            $check = $this->db->prepare("SELECT id FROM payments WHERE member_id = ? AND fee_year = ? AND payment_type = 'fee'");
            $check->execute([$member['id'], $year]);
            
            if ($check->fetch()) {
                continue; // Payment already exists
            }
            
            // Determine the fee amount for this member
            $amount = null;
            $concept = "Cuota Anual " . $year;
            
            if ($member['category_id'] && isset($categoryFees[$member['category_id']])) {
                // Use category fee for the specific year
                $amount = $categoryFees[$member['category_id']];
                $concept .= " - " . $member['category_name'];
            } elseif ($member['category_id'] && $member['default_fee'] !== null) {
                // Fallback to default_fee if no fee defined for the year
                $amount = $member['default_fee'];
                $concept .= " - " . $member['category_name'];
            } else {
                // No category or no fee defined - skip this member
                $skipped++;
                if ($member['category_name'] && !in_array($member['category_name'], $categoriesWithoutFee)) {
                    $categoriesWithoutFee[] = $member['category_name'];
                }
                continue;
            }
            
            // Create pending payment
            $query = "INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) 
                      VALUES (?, ?, ?, ?, 'pending', ?, 'fee')";
            $ins = $this->db->prepare($query);
            $ins->execute([$member['id'], $amount, date('Y-m-d'), $concept, $year]);
            $count++;
        }
        
        // Build redirect with results
        $redirect = "Location: index.php?page=settings&tab=members&success=generated&count=$count&year=$year";
        if ($skipped > 0) {
            $redirect .= "&skipped=$skipped";
            if (!empty($categoriesWithoutFee)) {
                $redirect .= "&categories=" . urlencode(implode(',', $categoriesWithoutFee));
            }
        }
        header($redirect);
    }
}
