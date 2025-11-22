<?php

class MemberController {
    private $db;
    private $member;
    private $imageHistory;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->member = new Member($this->db);
        $this->imageHistory = new MemberImageHistory($this->db);
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
            
            // Read current member data  
            $this->member->readOne();
            $currentPhoto = $this->member->photo_url;
            
            $this->member->first_name = $_POST['first_name'];
            $this->member->last_name = $_POST['last_name'];
            $this->member->email = $_POST['email'];
            $this->member->phone = $_POST['phone'];
            $this->member->address = $_POST['address'];
            $this->member->status = $_POST['status'];
            
            // Handle photo upload
            $photoUrl = $currentPhoto; // Keep current photo by default
            
            // Check for upload errors first
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_OK && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande. Tamaño máximo permitido: ' . ini_get('upload_max_filesize'),
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
                ];
                
                $error = $uploadErrors[$_FILES['photo']['error']] ?? 'Error desconocido al subir el archivo';
                $member = $this->member;
                require __DIR__ . '/../Views/members/edit.php';
                return;
            }
            
            // Check if a new photo is being uploaded and there's already a current photo
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && $currentPhoto) {
                
                // Save the new image temporarily and redirect to comparison
                $uploadDir = __DIR__ . '/../../public/uploads/members/temp/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['photo']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $tempFileName = 'temp_' . time() . '_' . uniqid() . '.' . $extension;
                    $tempPath = $uploadDir . $tempFileName;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $tempPath)) {
                        // Store data in session for comparison
                        $_SESSION['image_comparison'] = [
                            'member_id' => $id,
                            'old_image' => $currentPhoto,
                            'new_image_temp' => 'uploads/members/temp/' . $tempFileName,
                            'member_data' => [
                                'first_name' => $_POST['first_name'],
                                'last_name' => $_POST['last_name'],
                                'email' => $_POST['email'],
                                'phone' => $_POST['phone'],
                                'address' => $_POST['address'],
                                'status' => $_POST['status']
                            ]
                        ];
                        
                        // Redirect to comparison view
                        header('Location: index.php?page=members&action=compareImages');
                        exit;
                    }
                }
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // New photo upload (no existing photo)
                $photoUrl = $this->handleUpload();
                
                // Add to history
                if ($photoUrl) {
                    $this->imageHistory->member_id = $id;
                    $this->imageHistory->image_url = $photoUrl;
                    $this->imageHistory->is_current = true;
                    $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                    $this->imageHistory->replaced_at = null;
                    $this->imageHistory->create();
                }
            }
            
            $this->member->photo_url = $photoUrl;

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
            $this->member->deactivated_at = date('Y-m-d H:i:s');
            
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

    public function markPaid($id) {
        $this->checkAdmin();
        $currentYear = date('Y');
        
        // Check if fee for current year exists
        $feeStmt = $this->db->prepare("SELECT amount FROM annual_fees WHERE year = ?");
        $feeStmt->execute([$currentYear]);
        $fee = $feeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fee) {
            header('Location: index.php?page=members&filter=delinquent&error=no_fee');
            exit;
        }
        
        // Check if payment already exists
        $checkStmt = $this->db->prepare("SELECT id, status FROM payments WHERE member_id = ? AND fee_year = ? AND payment_type = 'fee'");
        $checkStmt->execute([$id, $currentYear]);
        $existingPayment = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingPayment) {
            // Update existing payment to paid
            $updateStmt = $this->db->prepare("UPDATE payments SET status = 'paid', payment_date = ? WHERE id = ?");
            $updateStmt->execute([date('Y-m-d'), $existingPayment['id']]);
        } else {
            // Create new payment as paid
            $insertStmt = $this->db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) VALUES (?, ?, ?, ?, 'paid', ?, 'fee')");
            $insertStmt->execute([
                $id,
                $fee['amount'],
                date('Y-m-d'),
                "Cuota Anual " . $currentYear,
                $currentYear
            ]);
        }
        
        header('Location: index.php?page=members&filter=delinquent&msg=marked_paid');
        exit;
    }

    public function compareImages() {
        $this->checkAdmin();
        
        if (!isset($_SESSION['image_comparison'])) {
            header('Location: index.php?page=members');
            exit;
        }
        
        $comparisonData = $_SESSION['image_comparison'];
        
        // Load member data
        $this->member->id = $comparisonData['member_id'];
        $this->member->readOne();
        $member = $this->member;
        
        require __DIR__ . '/../Views/members/compare_images.php';
    }

    public function selectImage($id) {
        $this->checkAdmin();
        
        if (!isset($_SESSION['image_comparison']) || $_SESSION['image_comparison']['member_id'] != $id) {
            header('Location: index.php?page=members');
            exit;
        }
        
        $comparisonData = $_SESSION['image_comparison'];
        $choice = $_POST['choice'] ?? '';
        
        if ($choice === 'new') {
            // User chose the new image
            // Move temp file to permanent location
            $tempPath = __DIR__ . '/../../public/' . $comparisonData['new_image_temp'];
            $permanentFileName = 'member_' . time() . '_' . uniqid() . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);
            $permanentPath = __DIR__ . '/../../public/uploads/members/' . $permanentFileName;
            
            if (rename($tempPath, $permanentPath)) {
                $newPhotoUrl = 'uploads/members/' . $permanentFileName;
                
                // Mark old image as not current in history
                $this->imageHistory->markAllAsNotCurrent($id);
                
                // Add old image to history if not already there
                if ($comparisonData['old_image']) {
                    $this->imageHistory->member_id = $id;
                    $this->imageHistory->image_url = $comparisonData['old_image'];
                    $this->imageHistory->is_current = false;
                    $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                    $this->imageHistory->replaced_at = date('Y-m-d H:i:s');
                    $this->imageHistory->create();
                }
                
                // Add new image to history as current
                $this->imageHistory->member_id = $id;
                $this->imageHistory->image_url = $newPhotoUrl;
                $this->imageHistory->is_current = true;
                $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                $this->imageHistory->replaced_at = null;
                $this->imageHistory->create();
                
                // Update member with new photo
                $this->member->id = $id;
                $this->member->readOne();
                $this->member->photo_url = $newPhotoUrl;
            }
        } else {
            // User chose to keep the old image
            // Delete the temporary new image
            $tempPath = __DIR__ . '/../../public/' . $comparisonData['new_image_temp'];
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
        
        // Update member with other data
        $this->member->id = $id;
        $this->member->readOne();
        $this->member->first_name = $comparisonData['member_data']['first_name'];
        $this->member->last_name = $comparisonData['member_data']['last_name'];
        $this->member->email = $comparisonData['member_data']['email'];
        $this->member->phone = $comparisonData['member_data']['phone'];
        $this->member->address = $comparisonData['member_data']['address'];
        $this->member->status = $comparisonData['member_data']['status'];
        $this->member->update();
        
        // Clean up session
        unset($_SESSION['image_comparison']);
        
        header('Location: index.php?page=members&msg=updated');
        exit;
    }

    public function imageHistory($id) {
        $this->checkAdmin();
        
        // Load member data
        $this->member->id = $id;
        if (!$this->member->readOne()) {
            header('Location: index.php?page=members');
            exit;
        }
        $member = $this->member;
        
        // Get image history
        $historyStmt = $this->imageHistory->getByMember($id);
        $images = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/members/image_history.php';
    }

    public function restoreImage($memberId, $historyId) {
        $this->checkAdmin();
        
        // Verify history entry exists and belongs to this member
        $this->imageHistory->id = $historyId;
        if (!$this->imageHistory->readOne() || $this->imageHistory->member_id != $memberId) {
            header('Location: index.php?page=members&action=imageHistory&id=' . $memberId . '&error=invalid');
            exit;
        }
        
        // Set this image as current
        $this->imageHistory->setAsCurrent($historyId, $memberId);
        
        // Update member's photo_url
        $this->member->id = $memberId;
        $this->member->readOne();
        $this->member->photo_url = $this->imageHistory->image_url;
        $this->member->update();
        
        header('Location: index.php?page=members&action=imageHistory&id=' . $memberId . '&msg=restored');
        exit;
    }
}

