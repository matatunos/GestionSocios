<?php

class DonorController {
    private $db;
    private $donor;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donor = new Donor($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=donors');
            exit;
        }
    }

    public function index() {
        $stmt = $this->donor->readAll();
        $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donors/index.php';
    }

    public function create() {
        $this->checkAdmin();
        require __DIR__ . '/../Views/donors/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->donor->name = $_POST['name'];
            $this->donor->contact_person = $_POST['contact_person'];
            $this->donor->phone = $_POST['phone'];
            $this->donor->email = $_POST['email'];
            $this->donor->address = $_POST['address'];

            // Handle logo upload
            $logoUrl = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/donors/';
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                $fileType = $_FILES['logo']['type'];
                $fileSize = $_FILES['logo']['size'];

                if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                    $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $fileName = 'donor_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $logoUrl = '/uploads/donors/' . $fileName;
                    }
                }
            }

            $this->donor->logo_url = $logoUrl;

            if ($this->donor->create()) {
                header('Location: index.php?page=donors&success=created');
            } else {
                $error = "Error creating donor.";
                require __DIR__ . '/../Views/donors/create.php';
            }
        }
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->donor->id = $id;
        if ($this->donor->readOne()) {
            $donor = $this->donor;
            require __DIR__ . '/../Views/donors/edit.php';
        } else {
            header('Location: index.php?page=donors');
        }
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->donor->id = $id;
            
            // Read current donor data to preserve logo if not updating
            $this->donor->readOne();
            $currentLogo = $this->donor->logo_url;
            
            $this->donor->name = $_POST['name'];
            $this->donor->contact_person = $_POST['contact_person'];
            $this->donor->phone = $_POST['phone'];
            $this->donor->email = $_POST['email'];
            $this->donor->address = $_POST['address'];

            // Handle logo upload
            $logoUrl = $currentLogo; // Keep current logo by default
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/donors/';
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                $fileType = $_FILES['logo']['type'];
                $fileSize = $_FILES['logo']['size'];

                if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                    $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $fileName = 'donor_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        // Delete old logo if exists
                        if ($currentLogo && file_exists(__DIR__ . '/../../public' . $currentLogo)) {
                            unlink(__DIR__ . '/../../public' . $currentLogo);
                        }
                        $logoUrl = '/uploads/donors/' . $fileName;
                    }
                }
            }

            $this->donor->logo_url = $logoUrl;

            if ($this->donor->update()) {
                header('Location: index.php?page=donors&success=updated');
            } else {
                $error = "Error updating donor.";
                $donor = $this->donor;
                require __DIR__ . '/../Views/donors/edit.php';
            }
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->donor->id = $id;
        if ($this->donor->delete()) {
            header('Location: index.php?page=donors&msg=deleted');
        } else {
            header('Location: index.php?page=donors&error=1');
        }
    }
}
?>
