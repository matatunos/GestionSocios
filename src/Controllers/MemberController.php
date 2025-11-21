<?php

class MemberController {
    private $db;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->member = new Member($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=members');
            exit;
        }
    }

    public function index() {
        $filter = $_GET['filter'] ?? 'all';
        
        if ($filter === 'current') {
            $stmt = $this->member->readByPaymentStatus('current');
        } elseif ($filter === 'delinquent') {
            $stmt = $this->member->readByPaymentStatus('delinquent');
        } else {
            $stmt = $this->member->readAll();
        }
        
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/members/list.php';
    }

    public function create() {
        $this->checkAdmin();
        require __DIR__ . '/../Views/members/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->member->first_name = $_POST['first_name'];
            $this->member->last_name = $_POST['last_name'];
            $this->member->email = $_POST['email'];
            $this->member->phone = $_POST['phone'];
            $this->member->address = $_POST['address'];
            $this->member->status = $_POST['status'];
            $this->member->photo_url = $this->handleUpload();

            if ($this->member->create()) {
                header('Location: index.php?page=members');
            } else {
                $error = "Error creating member.";
                require __DIR__ . '/../Views/members/create.php';
            }
        }
    }

    private function handleUpload() {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/members/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                return 'uploads/members/' . $fileName;
            }
        }
        return null;
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->member->id = $id;
        if ($this->member->readOne()) {
            $member = $this->member; // Pass object to view
            require __DIR__ . '/../Views/members/edit.php';
        } else {
            header('Location: index.php?page=members');
        }
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->member->id = $id;
            $this->member->first_name = $_POST['first_name'];
            $this->member->last_name = $_POST['last_name'];
            $this->member->email = $_POST['email'];
            $this->member->phone = $_POST['phone'];
            $this->member->address = $_POST['address'];
            $this->member->status = $_POST['status'];
            
            // Handle new photo or keep existing
            $newPhoto = $this->handleUpload();
            if ($newPhoto) {
                $this->member->photo_url = $newPhoto;
            } else {
                // We need to read the existing one if not provided, 
                // but since we are updating, we might overwrite with null if we don't load it first.
                // A better approach is to readOne first, but for now let's assume we pass it hidden or just don't update it if null.
                // Actually, the model update method updates ALL fields. So we MUST know the current photo if we don't want to lose it.
                // Let's read the current member state first.
                $currentMember = new Member($this->db);
                $currentMember->id = $id;
                $currentMember->readOne();
                $this->member->photo_url = $currentMember->photo_url;
            }

            if ($this->member->update()) {
                header('Location: index.php?page=members');
            } else {
                $error = "Error updating member.";
                // Reload member data to show form again
                $member = $this->member; 
                require __DIR__ . '/../Views/members/edit.php';
            }
        }
    }

    public function deactivate($id) {
        $this->checkAdmin();
        $this->member->id = $id;
        if ($this->member->readOne()) {
            $this->member->status = 'inactive';
            if ($this->member->update()) {
                header('Location: index.php?page=members&msg=deactivated');
                exit;
            }
        }
        header('Location: index.php?page=members&error=1');
        exit;
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->member->id = $id;
        if ($this->member->delete()) {
            header('Location: index.php?page=members&msg=deleted');
            exit;
        }
        header('Location: index.php?page=members&error=1');
        exit;
    }
}
