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
        // Pagination
        $limit = 20;
        $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->donor->readAll($limit, $offset);
        $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalRecords = $this->donor->countAll();
        $totalPages = ceil($totalRecords / $limit);
        
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
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $lastId = $this->db->lastInsertId();
                $audit->create(
                    $_SESSION['user_id'],
                    'create',
                    'donor',
                    $lastId,
                    'Alta de donante: ' . $this->donor->name . ' (' . $this->donor->email . ') por el usuario ' . ($_SESSION['username'] ?? '')
                );
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
            // Leer datos originales antes de modificar
            $this->donor->readOne();
            $original = [
                'name' => $this->donor->name,
                'contact_person' => $this->donor->contact_person,
                'phone' => $this->donor->phone,
                'email' => $this->donor->email,
                'address' => $this->donor->address,
                'latitude' => $this->donor->latitude,
                'longitude' => $this->donor->longitude,
                'logo_url' => $this->donor->logo_url
            ];
            $currentLogo = $this->donor->logo_url;
            // Asignar nuevos valores
            $this->donor->name = $_POST['name'];
            $this->donor->contact_person = $_POST['contact_person'];
            $this->donor->phone = $_POST['phone'];
            $this->donor->email = $_POST['email'];
            $this->donor->address = $_POST['address'];
            $this->donor->latitude = $_POST['latitude'] ?? null;
            $this->donor->longitude = $_POST['longitude'] ?? null;
            // Handle logo upload
            $logoUrl = $currentLogo; // Keep current logo by default
            
            // Check for upload errors first
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_OK && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande. Tamaño máximo permitido: ' . ini_get('upload_max_filesize'),
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
                ];
                
                $error = $uploadErrors[$_FILES['logo']['error']] ?? 'Error desconocido al subir el archivo';
                $donor = $this->donor;
                require __DIR__ . '/../Views/donors/edit.php';
                return;
            }
            
            // Check if a new logo is being uploaded and there's already a current logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $currentLogo) {
                
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
                    }
                }
            } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
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
                // Detectar campos modificados
                $changedFields = [];
                if ((string)$original['name'] !== (string)$_POST['name']) $changedFields[] = 'nombre';
                if ((string)$original['contact_person'] !== (string)$_POST['contact_person']) $changedFields[] = 'contacto';
                if ((string)$original['phone'] !== (string)$_POST['phone']) $changedFields[] = 'teléfono';
                if ((string)$original['email'] !== (string)$_POST['email']) $changedFields[] = 'email';
                if ((string)$original['address'] !== (string)$_POST['address']) $changedFields[] = 'dirección';
                if ((string)$original['latitude'] !== (string)($_POST['latitude'] ?? '')) $changedFields[] = 'latitud';
                if ((string)$original['longitude'] !== (string)($_POST['longitude'] ?? '')) $changedFields[] = 'longitud';
                if ((string)$logoUrl !== (string)$original['logo_url']) $changedFields[] = 'imagen';
                if ($changedFields) {
                    $detalle = 'Modificación de donante: ' . $original['name'] . ' (' . $original['email'] . ') por el usuario ' . ($_SESSION['username'] ?? '');
                    $detalle .= ' [Campos modificados: ' . implode(', ', $changedFields) . ']';
                    require_once __DIR__ . '/../Models/AuditLog.php';
                    $audit = new AuditLog($this->db);
                    $audit->create(
                        $_SESSION['user_id'],
                        'update',
                        'donor',
                        $id,
                        $detalle
                    );
                }
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
            // Registrar en audit_log
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'delete', 'donor', $id, 'Borrado de donante por el usuario ' . ($_SESSION['username'] ?? ''));
            header('Location: index.php?page=donors&msg=deleted');
        } else {
            header('Location: index.php?page=donors&error=1');
        }
    }

    public function gallery() {
        header('Location: index.php?page=gallery');
        exit;
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
                $this->imageHistory->markAllAsNotCurrent($id);
                if ($comparison['old_image'] && !$this->imageHistory->imageExists($id, $comparison['old_image'])) {
                    $this->imageHistory->donor_id = $id;
                    $this->imageHistory->image_url = $comparison['old_image'];
                    $this->imageHistory->is_current = false;
                    $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                    $this->imageHistory->replaced_at = date('Y-m-d H:i:s');
                    $this->imageHistory->create();
                }
                $this->imageHistory->donor_id = $id;
                $this->imageHistory->image_url = $newLogoUrl;
                $this->imageHistory->is_current = true;
                $this->imageHistory->uploaded_at = date('Y-m-d H:i:s');
                $this->imageHistory->replaced_at = null;
                $this->imageHistory->create();
                $this->donor->logo_url = $newLogoUrl;
            }
        } else {
            $tempPath = __DIR__ . '/../../public/' . $comparison['new_image_temp'];
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            $this->donor->logo_url = $comparison['old_image'];
        }
        $this->donor->update();
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
