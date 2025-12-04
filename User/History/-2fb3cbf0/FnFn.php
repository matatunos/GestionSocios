<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/../Models/Donor.php';

class MapController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // Set page title for layout
        $pageTitle = 'Mapa Global';
        // Content will be loaded in layout
        require __DIR__ . '/../Views/map/index.php';
    }

    public function getLocations() {
        header('Content-Type: application/json');
        
        try {
            $currentYear = date('Y');
            
            // Fetch active members with coordinates and payment status
            $stmtMembers = $this->db->prepare("
                SELECT 
                    m.id, 
                    m.first_name, 
                    m.last_name, 
                    m.latitude, 
                    m.longitude, 
                    m.address,
                    (SELECT COUNT(*) FROM payments p 
                     WHERE p.member_id = m.id 
                     AND p.payment_type = 'fee' 
                     AND p.fee_year = :year 
                     AND p.status = 'paid') as has_paid
                FROM members m
                WHERE m.status = 'active' 
                AND m.latitude IS NOT NULL 
                AND m.longitude IS NOT NULL
            ");
            $stmtMembers->bindParam(':year', $currentYear);
            $stmtMembers->execute();
            $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

            // Fetch donors with coordinates and donation status for current year
            $stmtDonors = $this->db->prepare("
                SELECT 
                    d.id, 
                    d.name, 
                    d.latitude, 
                    d.longitude, 
                    d.address,
                    (SELECT COUNT(*) FROM donations dn 
                     WHERE dn.donor_id = d.id 
                     AND dn.year = :year) as has_donated
                FROM donors d
                WHERE d.latitude IS NOT NULL 
                AND d.longitude IS NOT NULL
            ");
            $stmtDonors->bindParam(':year', $currentYear);
            $stmtDonors->execute();
            $donors = $stmtDonors->fetchAll(PDO::FETCH_ASSOC);

            $locations = [];

            foreach ($members as $member) {
                $locations[] = [
                    'type' => 'member',
                    'id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'lat' => $member['latitude'],
                    'lng' => $member['longitude'],
                    'address' => $member['address'],
                    'paid' => (int)$member['has_paid'] > 0
                ];
            }

            foreach ($donors as $donor) {
                $locations[] = [
                    'type' => 'donor',
                    'id' => $donor['id'],
                    'name' => $donor['name'],
                    'lat' => $donor['latitude'],
                    'lng' => $donor['longitude'],
                    'address' => $donor['address'],
                    'paid' => (int)$donor['has_donated'] > 0
                ];
            }

            echo json_encode($locations);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
