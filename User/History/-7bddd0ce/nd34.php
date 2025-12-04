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
        // Build filters from GET parameters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'payment_status' => $_GET['payment_status'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search' => $_GET['search'] ?? '',
            'year_from' => $_GET['year_from'] ?? '',
            'year_to' => $_GET['year_to'] ?? ''
        ];
        
        // Pagination
        $limit = 20;
        $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $offset = ($page - 1) * $limit;
        
        // Legacy filter support
        $legacyFilter = $_GET['filter'] ?? 'all';
        if ($legacyFilter === 'current') {
            $filters['payment_status'] = 'current';
        } elseif ($legacyFilter === 'delinquent') {
            $filters['payment_status'] = 'delinquent';
        }
        
        $members = $this->member->readFiltered($filters, $limit, $offset);
        $totalRecords = $this->member->countFiltered($filters);
        $totalPages = ceil($totalRecords / $limit);
        
        // Get categories for filter dropdown
        $categoryModel = new MemberCategory($this->db);
        $categories = $categoryModel->readAll();
        
        require __DIR__ . '/../Views/members/list.php';
    }

    public function create() {
        $this->checkAdmin();
        
        // Get categories for dropdown
        $categoryModel = new MemberCategory($this->db);
        $categories = $categoryModel->readAll();
        
        require __DIR__ . '/../Views/members/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            $this->member->first_name = $_POST['first_name'];
            $this->member->last_name = $_POST['last_name'];
            $this->member->dni = $_POST['dni'] ?? null;
            $this->member->email = $_POST['email'];
            $this->member->phone = $_POST['phone'];
            $this->member->address = $_POST['address'];
            $this->member->status = $_POST['status'] ?? 'active';
            $this->member->category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            $this->member->photo_url = $this->handleUpload();

            if ($this->member->create()) {
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $lastId = $this->db->lastInsertId();
                $audit->create(
                    $_SESSION['user_id'],
                    'create',
                    'member',
                    $lastId,
                    'Alta de socio: ' . $this->member->first_name . ' ' . $this->member->last_name . ' (' . $this->member->email . ') por el usuario ' . ($_SESSION['username'] ?? '')
                );
                
                // Enviar notificación de bienvenida al nuevo socio
                try {
                    require_once __DIR__ . '/../Helpers/NotificationHelper.php';
                    $member_name = $this->member->first_name . ' ' . $this->member->last_name;
                    NotificationHelper::sendWelcomeNotification(
                        $this->db, 
                        $this->member->id, 
                        $member_name
                    );
                } catch (Exception $e) {
                    // No fallar si la notificación no se puede enviar
                    error_log("Error sending welcome notification: " . $e->getMessage());
                }
                
                header('Location: index.php?page=members');
            } else {
                $error = "Error creating member.";
                require __DIR__ . '/../Views/members/create.php';
            }
        }
    }

    private function handleUpload() {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Validate file size (5MB maximum)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['photo']['size'] > $maxSize) {
                throw new Exception('El archivo es demasiado grande. Máximo 5MB permitido.');
            }
            
            // Validate MIME type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            // finfo_close is deprecated and not needed in modern PHP
            
            if (!in_array($mimeType, $allowedMimeTypes)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPEG, PNG, GIF y WebP.');
            }
            
            // Validate file extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                throw new Exception('Extensión de archivo no permitida.');
            }
            
            $uploadDir = __DIR__ . '/../../public/uploads/members/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // More restrictive permissions
            }
            
            // Generate secure filename
            $fileName = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
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
            
            // Get categories for dropdown
            $categoryModel = new MemberCategory($this->db);
            $categories = $categoryModel->readAll();
            
            require __DIR__ . '/../Views/members/edit.php';
        } else {
            header('Location: index.php?page=members');
        }
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            
            $this->member->id = $id;
            // Leer datos originales antes de modificar
            $this->member->readOne();
            $original = [
                'first_name' => $this->member->first_name,
                'last_name' => $this->member->last_name,
                'dni' => $this->member->dni,
                'email' => $this->member->email,
                'phone' => $this->member->phone,
                'address' => $this->member->address,
                'latitude' => $this->member->latitude,
                'longitude' => $this->member->longitude,
                'status' => $this->member->status,
                'category_id' => $this->member->category_id,
                'photo_url' => $this->member->photo_url,
                'created_at' => $this->member->created_at
            ];
            $currentPhoto = $this->member->photo_url;
            // Asignar nuevos valores
            $this->member->first_name = $_POST['first_name'];
            $this->member->last_name = $_POST['last_name'];
            $this->member->dni = $_POST['dni'] ?? null;
            $this->member->email = $_POST['email'];
            $this->member->phone = $_POST['phone'];
            $this->member->address = $_POST['address'];
            $this->member->latitude = $_POST['latitude'] ?? null;
            $this->member->longitude = $_POST['longitude'] ?? null;
            $this->member->status = $_POST['status'];
            $this->member->category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            if (!empty($_POST['created_at'])) {
                $this->member->created_at = $_POST['created_at'];
            }
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

            // Detectar campos modificados antes del update
            $changedFields = [];
            if ($original['first_name'] !== $_POST['first_name']) $changedFields[] = 'nombre';
            if ($original['last_name'] !== $_POST['last_name']) $changedFields[] = 'apellidos';
            if ($original['dni'] !== ($_POST['dni'] ?? null)) $changedFields[] = 'dni';
            if ($original['email'] !== $_POST['email']) $changedFields[] = 'email';
            if ($original['phone'] !== $_POST['phone']) $changedFields[] = 'teléfono';
            if ($original['address'] !== $_POST['address']) $changedFields[] = 'dirección';
            if ($original['latitude'] != ($_POST['latitude'] ?? null)) $changedFields[] = 'latitud';
            if ($original['longitude'] != ($_POST['longitude'] ?? null)) $changedFields[] = 'longitud';
            if ($original['status'] !== $_POST['status']) $changedFields[] = 'estado';
            if ($original['category_id'] != (!empty($_POST['category_id']) ? $_POST['category_id'] : null)) $changedFields[] = 'categoría';
            if ($photoUrl !== $original['photo_url']) $changedFields[] = 'imagen';
            if (isset($_POST['created_at']) && $original['created_at'] !== $_POST['created_at']) $changedFields[] = 'fecha alta';
            $detalle = 'Modificación de socio: ' . $original['first_name'] . ' ' . $original['last_name'] . ' (' . $original['email'] . ') por el usuario ' . ($_SESSION['username'] ?? '');
            if ($changedFields) {
                $detalle .= ' [Campos modificados: ' . implode(', ', $changedFields) . ']';
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create(
                    $_SESSION['user_id'],
                    'update',
                    'member',
                    $id,
                    $detalle
                );
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
            $this->member->deactivated_at = date('Y-m-d H:i:s');
            
            if ($this->member->update()) {
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'deactivate', 'member', $id, 'Baja de socio por el usuario ' . ($_SESSION['username'] ?? ''));
                
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
            // Registrar en audit_log
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'delete', 'member', $id, 'Borrado de socio por el usuario ' . ($_SESSION['username'] ?? ''));
            
            header('Location: index.php?page=members&msg=deleted');
            exit;
        }
        header('Location: index.php?page=members&error=1');
        exit;
    }

    public function markPaid($id) {
        $this->checkAdmin();
        $currentYear = date('Y');
        
        // Obtener la categoría del socio
        $memberStmt = $this->db->prepare("SELECT category_id, first_name, last_name FROM members WHERE id = ?");
        $memberStmt->execute([$id]);
        $member = $memberStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            $_SESSION['error'] = "Socio no encontrado.";
            header('Location: index.php?page=members');
            exit;
        }
        
        // DEBUG: Log para ver qué categoría tiene el socio
        error_log("DEBUG markPaid - Socio ID: $id, Categoría: " . ($member['category_id'] ?? 'NULL'));
        
        // Obtener la cuota correcta según la categoría del socio
        $paymentAmount = null;
        if (!empty($member['category_id'])) {
            // Intentar obtener cuota específica de la categoría
            $categoryFeeStmt = $this->db->prepare("SELECT fee_amount FROM category_fee_history WHERE category_id = ? AND year = ?");
            $categoryFeeStmt->execute([$member['category_id'], $currentYear]);
            $categoryFee = $categoryFeeStmt->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG markPaid - Consulta categoría: category_id={$member['category_id']}, year=$currentYear, resultado: " . json_encode($categoryFee));
            if ($categoryFee && isset($categoryFee['fee_amount'])) {
                $paymentAmount = floatval($categoryFee['fee_amount']);
                error_log("DEBUG markPaid - Cuota de categoría encontrada: $paymentAmount");
            }
        }
        
        // Si no hay cuota de categoría, usar cuota por defecto
        if ($paymentAmount === null || $paymentAmount <= 0) {
            $feeStmt = $this->db->prepare("SELECT amount FROM annual_fees WHERE year = ?");
            $feeStmt->execute([$currentYear]);
            $fee = $feeStmt->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG markPaid - Consulta annual_fees: year=$currentYear, resultado: " . json_encode($fee));
            if ($fee && isset($fee['amount'])) {
                $paymentAmount = floatval($fee['amount']);
                error_log("DEBUG markPaid - Cuota por defecto encontrada: $paymentAmount");
            }
        }
        
        error_log("DEBUG markPaid - Cuota final: " . ($paymentAmount ?? 'NULL'));
        
        if (!$paymentAmount || $paymentAmount <= 0) {
            $_SESSION['error'] = "No hay cuota definida para el año $currentYear. Por favor, define la cuota en Configuración > Categorías.";
            
            // Preserve current filters
            $redirectParams = [];
            if (isset($_GET['filter'])) $redirectParams['filter'] = $_GET['filter'];
            if (isset($_GET['category'])) $redirectParams['category'] = $_GET['category'];
            if (isset($_GET['payment_status'])) $redirectParams['payment_status'] = $_GET['payment_status'];
            if (isset($_GET['search'])) $redirectParams['search'] = $_GET['search'];
            if (isset($_GET['year_from'])) $redirectParams['year_from'] = $_GET['year_from'];
            if (isset($_GET['year_to'])) $redirectParams['year_to'] = $_GET['year_to'];
            if (isset($_GET['p'])) $redirectParams['p'] = $_GET['p'];
            
            $queryString = !empty($redirectParams) ? '&' . http_build_query($redirectParams) : '';
            header('Location: index.php?page=members' . $queryString);
            exit;
        }
        
        try {
            // Check if payment already exists
            $checkStmt = $this->db->prepare("SELECT id, status FROM payments WHERE member_id = ? AND fee_year = ? AND payment_type = 'fee'");
            $checkStmt->execute([$id, $currentYear]);
            $existingPayment = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            $paymentId = null;
            $paymentDate = date('Y-m-d');
            $paymentConcept = "Cuota Anual " . $currentYear;
            
            if ($existingPayment) {
                // Update existing payment to paid
                $updateStmt = $this->db->prepare("UPDATE payments SET status = 'paid', payment_date = ? WHERE id = ?");
                $success = $updateStmt->execute([$paymentDate, $existingPayment['id']]);
                if (!$success) {
                    throw new Exception("Error al actualizar el pago existente");
                }
                $paymentId = $existingPayment['id'];
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'markPaid', 'member_payment', $paymentId, 'Pago marcado como realizado por el usuario ' . ($_SESSION['username'] ?? ''));
            } else {
                // Create new payment as paid
                $insertStmt = $this->db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) VALUES (?, ?, ?, ?, 'paid', ?, 'fee')");
                $success = $insertStmt->execute([
                    $id,
                    $paymentAmount,
                    $paymentDate,
                    $paymentConcept,
                    $currentYear
                ]);
                if (!$success) {
                    throw new Exception("Error al crear el nuevo pago");
                }
                $paymentId = $this->db->lastInsertId();
                // Registrar en audit_log
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'markPaid', 'member_payment', $paymentId, 'Pago creado y marcado como realizado por el usuario ' . ($_SESSION['username'] ?? ''));
            }
            
            // Crear asiento contable automático
            require_once __DIR__ . '/../Helpers/AccountingHelper.php';
            $accountingCreated = AccountingHelper::createEntryFromPayment(
                $this->db,
                $paymentId,
                $paymentAmount,
                $paymentConcept,
                $paymentDate,
                'transfer', // Método de pago por defecto
                'fee'
            );
            
            if (!$accountingCreated) {
                error_log("No se pudo crear el asiento contable para el pago de cuota #$paymentId");
            }
            
            $_SESSION['success'] = 'Pago marcado correctamente' . ($accountingCreated ? ' y registrado en contabilidad' : '');        } catch (Exception $e) {
            error_log("Error in markPaid: " . $e->getMessage());
            $_SESSION['error'] = 'Error al marcar el pago: ' . $e->getMessage();
        }
        
        // Preserve current filters
        $redirectParams = [];
        if (isset($_GET['filter'])) $redirectParams['filter'] = $_GET['filter'];
        if (isset($_GET['category'])) $redirectParams['category'] = $_GET['category'];
        if (isset($_GET['payment_status'])) $redirectParams['payment_status'] = $_GET['payment_status'];
        if (isset($_GET['search'])) $redirectParams['search'] = $_GET['search'];
        if (isset($_GET['year_from'])) $redirectParams['year_from'] = $_GET['year_from'];
        if (isset($_GET['year_to'])) $redirectParams['year_to'] = $_GET['year_to'];
        if (isset($_GET['p'])) $redirectParams['p'] = $_GET['p'];
        
        $queryString = !empty($redirectParams) ? '&' . http_build_query($redirectParams) : '';
        header('Location: index.php?page=members' . $queryString);
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
                if ($comparisonData['old_image'] && !$this->imageHistory->imageExists($id, $comparisonData['old_image'])) {
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
    
    // ... Funcionalidad de mapa y GPS eliminada ...
}

