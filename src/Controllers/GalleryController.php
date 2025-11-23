<?php

class GalleryController {
    private $db;
    private $donor;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donor = new Donor($this->db);
        $this->member = new Member($this->db);
    }

    public function index() {
        // Get all donors with logos
        $donorsStmt = $this->db->prepare("
            SELECT id, name, logo_url 
            FROM donors 
            WHERE logo_url IS NOT NULL AND logo_url != '' 
            ORDER BY name ASC
        ");
        $donorsStmt->execute();
        $donors = $donorsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all members with photos
        $membersStmt = $this->db->prepare("
            SELECT id, first_name, last_name, photo_url 
            FROM members 
            WHERE photo_url IS NOT NULL AND photo_url != '' 
            ORDER BY last_name ASC, first_name ASC
        ");
        $membersStmt->execute();
        $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/gallery/index.php';
    }
}
?>
