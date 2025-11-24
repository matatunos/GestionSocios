<?php
require_once __DIR__ . '/../Config/Database.php';
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
            // Fetch active members with coordinates
            $stmtMembers = $this->db->prepare("SELECT id, first_name, last_name, latitude, longitude, address FROM members WHERE status = 'active' AND latitude IS NOT NULL AND longitude IS NOT NULL");
            $stmtMembers->execute();
            $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

            // Fetch donors with coordinates
            $stmtDonors = $this->db->prepare("SELECT id, name, latitude, longitude, address FROM donors WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
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
                    'address' => $member['address']
                ];
            }

            foreach ($donors as $donor) {
                $locations[] = [
                    'type' => 'donor',
                    'id' => $donor['id'],
                    'name' => $donor['name'],
                    'lat' => $donor['latitude'],
                    'lng' => $donor['longitude'],
                    'address' => $donor['address']
                ];
            }

            echo json_encode($locations);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
