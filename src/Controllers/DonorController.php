<?php

class DonorController {
    private $db;
    private $donor;
    private $imageHistory;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->donor = new Donor($this->db);
        $this->imageHistory = new DonorImageHistory($this->db);
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
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                $fileType = $_FILES['logo']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $fileName = 'donor_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $logoUrl = 'uploads/donors/' . $fileName;
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
            
            // Debug to file
            $debugLog = __DIR__ . '/../../public/donor_update_debug.txt';
            $debugContent = "=== DONOR UPDATE DEBUG - " . date('Y-m-d H:i:s') . " ===\n";
            $debugContent .= "Donor ID: " . $id . "\n";
            $debugContent .= "Current Logo: " . ($currentLogo ?? 'NULL') . "\n";
            $debugContent .= "Current Logo is truthy: " . ($currentLogo ? 'YES' : 'NO') . "\n";
            $debugContent .= "Has uploaded file: " . (isset($_FILES['logo']) ? 'YES' : 'NO') . "\n";
            if (isset($_FILES['logo'])) {
                $debugContent .= "Upload error code: " . $_FILES['logo']['error'] . " (0 = OK)\n";
                $debugContent .= "File type: " . ($_FILES['logo']['type'] ?? 'UNKNOWN') . "\n";
                $debugContent .= "File name: " . ($_FILES['logo']['name'] ?? 'UNKNOWN') . "\n";
                $debugContent .= "File size: " . ($_FILES['logo']['size'] ?? 0) . " bytes\n";
            }
            $debugContent .= "\nCondition checks:\n";
            $debugContent .= "isset(\$_FILES['logo']): " . (isset($_FILES['logo']) ? 'TRUE' : 'FALSE') . "\n";
            $debugContent .= "\$_FILES['logo']['error'] === UPLOAD_ERR_OK: " . (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK ? 'TRUE' : 'FALSE') . "\n";
            $debugContent .= "\$currentLogo truthy: " . ($currentLogo ? 'TRUE' : 'FALSE') . "\n";
            $debugContent .= "ALL CONDITIONS MET: " . (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $currentLogo ? 'YES - ENTERING COMPARISON' : 'NO - NOT ENTERING COMPARISON') . "\n";
            $debugContent .= "\n";
            file_put_contents($debugLog, $debugContent);
            
            // Check if a new logo is being uploaded and there's already a current logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $currentLogo) {
                $debugContent .= "✓ ENTERED COMPARISON FLOW\n";
                file_put_contents($debugLog, $debugContent);
                
                // Save the new image temporarily and redirect to comparison
                $uploadDir = __DIR__ . '/../../public/uploads/donors/temp/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['logo']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $tempFileName = 'temp_' . time() . '_' . uniqid() . '.' . $extension;
                    $tempPath = $uploadDir . $tempFileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $tempPath)) {
                        $debugContent .= "✓ Temp file created: " . $tempPath . "\n";
                        $debugContent .= "✓ Storing data in session\n";
                        $debugContent .= "✓ Redirecting to compareImages\n";
                        file_put_contents($debugLog, $debugContent);
                        
                        // Store data in session for comparison
                        $_SESSION['image_comparison'] = [
                            'donor_id' => $id,
                            'old_image' => $currentLogo,
                            'new_image_temp' => 'uploads/donors/temp/' . $tempFileName,
                            'donor_data' => [
                                'name' => $_POST['name'],
                                'contact_person' => $_POST['contact_person'],
                                'phone' => $_POST['phone'],
                                'email' => $_POST['email'],
                                'address' => $_POST['address']
                            ]
                        ];
                        
                        // Redirect to comparison view
                        header('Location: index.php?page=donors&action=compareImages');
                        exit;
                    } else {
                        $debugContent .= "✗ Failed to move uploaded file\n";
                        file_put_contents($debugLog, $debugContent);
                    }
                } else {
                    $debugContent .= "✗ File type not allowed: " . $fileType . "\n";
                    $debugContent .= "Allowed: " . implode(', ', $allowedTypes) . "\n";
                    file_put_contents($debugLog, $debugContent);
                }
            } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $debugContent .= "→ NORMAL UPLOAD FLOW (no existing logo)\n";
                file_put_contents($debugLog, $debugContent);
                $uploadDir = __DIR__ . '/../../public/uploads/donors/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                $fileType = $_FILES['logo']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $fileName = 'donor_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $logoUrl = 'uploads/donors/' . $fileName;
                        
                        // Add to history
                        $this->imageHistory->donor_id = $id;
                        $this->imageHistory->image_url = $logoUrl;
                        $this->imageHistory->is_current = true;
                        $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                        $this->imageHistory->replaced_at = null;
                        $this->imageHistory->create();
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

    public function gallery() {
        $stmt = $this->donor->readAll();
        $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/donors/gallery.php';
    }

    public function compareImages() {
        $this->checkAdmin();
        
        // Get comparison data from session
        if (!isset($_SESSION['image_comparison'])) {
            header('Location: index.php?page=donors');
            exit;
        }
        
        $comparison = $_SESSION['image_comparison'];
        
        // Get donor info
        $this->donor->id = $comparison['donor_id'];
        $this->donor->readOne();
        $donor = $this->donor;
        
        require __DIR__ . '/../Views/donors/compare_images.php';
    }

    public function selectImage($id) {
        $this->checkAdmin();
        
        if (!isset($_SESSION['image_comparison']) || !isset($_POST['choice'])) {
            header('Location: index.php?page=donors');
            exit;
        }
        
        $comparison = $_SESSION['image_comparison'];
        $choice = $_POST['choice']; // 'old' or 'new'
        
        $this->donor->id = $id;
        $this->donor->readOne();
        
        // Update donor data
        $this->donor->name = $comparison['donor_data']['name'];
        $this->donor->contact_person = $comparison['donor_data']['contact_person'];
        $this->donor->phone = $comparison['donor_data']['phone'];
        $this->donor->email = $comparison['donor_data']['email'];
        $this->donor->address = $comparison['donor_data']['address'];
        
        if ($choice === 'new') {
            // Move temp image to permanent location
            $tempPath = __DIR__ . '/../../public/' . $comparison['new_image_temp'];
            $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
            $newFileName = 'donor_' . time() . '_' . uniqid() . '.' . $extension;
            $permanentPath = __DIR__ . '/../../public/uploads/donors/' . $newFileName;
            
            if (rename($tempPath, $permanentPath)) {
                $newLogoUrl = 'uploads/donors/' . $newFileName;
                
                // Mark old image as replaced in history
                $this->imageHistory->markAllAsNotCurrent($id);
                
                // Add old image to history if not already there
                $currentInHistory = $this->imageHistory->getCurrentImage($id);
                if (!$currentInHistory && $comparison['old_image']) {
                    $this->imageHistory->donor_id = $id;
                    $this->imageHistory->image_url = $comparison['old_image'];
                    $this->imageHistory->is_current = false;
                    $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                    $this->imageHistory->replaced_at = date('Y-m-d H:i:s');
                    $this->imageHistory->create();
                }
                
                // Add new image to history as current
                $this->imageHistory->donor_id = $id;
                $this->imageHistory->image_url = $newLogoUrl;
                $this->imageHistory->is_current = true;
                $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                $this->imageHistory->replaced_at = null;
                $this->imageHistory->create();
                
                $this->donor->logo_url = $newLogoUrl;
            }
        } else {
            // Keep old image, delete temp
            $tempPath = __DIR__ . '/../../public/' . $comparison['new_image_temp'];
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            $this->donor->logo_url = $comparison['old_image'];
        }
        
        // Update donor
        $this->donor->update();
        
        // Clear session data
        unset($_SESSION['image_comparison']);
        
        header('Location: index.php?page=donors&success=updated');
        exit;
    }

    public function imageHistory($id) {
        $this->donor->id = $id;
        if (!$this->donor->readOne()) {
            header('Location: index.php?page=donors');
            exit;
        }
        
        $donor = $this->donor;
        $stmt = $this->imageHistory->getByDonor($id);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/donors/image_history.php';
    }

    public function restoreImage($donorId, $historyId) {
        $this->checkAdmin();
        
        // Get the history entry
        $this->imageHistory->id = $historyId;
        if (!$this->imageHistory->readOne()) {
            header('Location: index.php?page=donors&action=imageHistory&id=' . $donorId . '&error=not_found');
            exit;
        }
        
        // Make sure this image belongs to the specified donor
        if ($this->imageHistory->donor_id != $donorId) {
            header('Location: index.php?page=donors&action=imageHistory&id=' . $donorId . '&error=invalid');
            exit;
        }
        
        // Mark all images as not current
        $this->imageHistory->markAllAsNotCurrent($donorId);
        
        // Set this image as current
        $this->imageHistory->setAsCurrent($historyId, $donorId);
        
        // Update donor's logo_url
        $this->donor->id = $donorId;
        $this->donor->readOne();
        $this->donor->logo_url = $this->imageHistory->image_url;
        $this->donor->update();
        
        header('Location: index.php?page=donors&action=imageHistory&id=' . $donorId . '&success=restored');
        exit;
    }
}
?>
