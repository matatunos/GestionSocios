<?php

require_once __DIR__ . '/../Models/Supplier.php';
require_once __DIR__ . '/../Models/SupplierInvoice.php';
require_once __DIR__ . '/../Helpers/CsrfHelper.php';

class SupplierController {
    private $db;
    private $supplier;
    private $invoice;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->supplier = new Supplier($this->db);
        $this->invoice = new SupplierInvoice($this->db);
    }

    public function index() {
        $stmt = $this->supplier->readAll();
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/suppliers/index.php';
    }

    public function create() {
        require __DIR__ . '/../Views/suppliers/create.php';
    }

    public function store() {
        CsrfHelper::validateRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->supplier->name = $_POST['name'] ?? '';
            $this->supplier->cif_nif = $_POST['cif_nif'] ?? '';
            $this->supplier->email = $_POST['email'] ?? '';
            $this->supplier->phone = $_POST['phone'] ?? '';
            $this->supplier->address = $_POST['address'] ?? '';
            $this->supplier->website = $_POST['website'] ?? '';
            $this->supplier->notes = $_POST['notes'] ?? '';

            // Handle Logo Upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Validate file size (5MB maximum)
                    if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                        throw new Exception('El archivo es demasiado grande. Máximo 5MB permitido.');
                    }

                    $uploadDir = 'public/uploads/suppliers/logos/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileInfo = pathinfo($_FILES['logo']['name']);
                    $extension = strtolower($fileInfo['extension']);
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        throw new Exception('Formato de imagen no válido');
                    }

                    // Validate MIME type using finfo
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
                    finfo_close($finfo);
                    
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        throw new Exception('Tipo de archivo no permitido.');
                    }

                    $fileName = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $this->supplier->logo_path = $targetPath;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error al subir logo: " . $e->getMessage();
                }
            }

            if ($this->supplier->create()) {
                // Create specific folder for this supplier's documents
                $supplierDir = 'public/uploads/suppliers/' . $this->supplier->id . '/';
                if (!file_exists($supplierDir)) {
                    mkdir($supplierDir, 0755, true);
                }

                $_SESSION['success'] = "Proveedor creado correctamente.";
                header("Location: index.php?page=suppliers");
            } else {
                $_SESSION['error'] = "No se pudo crear el proveedor.";
                header("Location: index.php?page=suppliers&action=create");
            }
            exit;
        }
    }

    public function show() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : die('ERROR: ID no encontrado.');
        $this->supplier->id = $id;
        $this->supplier->readOne();

        $stmt = $this->invoice->getBySupplierId($id);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/suppliers/show.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : die('ERROR: ID no encontrado.');
        $this->supplier->id = $id;
        $this->supplier->readOne();
        require __DIR__ . '/../Views/suppliers/edit.php';
    }

    public function update() {
        CsrfHelper::validateRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->supplier->id = $_POST['id'];
            $this->supplier->name = $_POST['name'];
            $this->supplier->cif_nif = $_POST['cif_nif'];
            $this->supplier->email = $_POST['email'];
            $this->supplier->phone = $_POST['phone'];
            $this->supplier->address = $_POST['address'];
            $this->supplier->website = $_POST['website'];
            $this->supplier->notes = $_POST['notes'];

            // Handle Logo Update
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Validate file size (5MB maximum)
                    if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                        throw new Exception('El archivo es demasiado grande. Máximo 5MB permitido.');
                    }

                    $uploadDir = 'public/uploads/suppliers/logos/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        throw new Exception('Formato de imagen no válido');
                    }

                    // Validate MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
                    finfo_close($finfo);
                    
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        throw new Exception('Tipo de archivo no permitido.');
                    }

                    $fileName = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $this->supplier->logo_path = $targetPath;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error al subir logo: " . $e->getMessage();
                }
            } else {
                // Keep existing logo if not updated
                $currentSupplier = new Supplier($this->db);
                $currentSupplier->id = $this->supplier->id;
                $currentSupplier->readOne();
                $this->supplier->logo_path = $currentSupplier->logo_path;
            }

            if ($this->supplier->update()) {
                $_SESSION['success'] = "Proveedor actualizado.";
                header("Location: index.php?page=suppliers");
            } else {
                $_SESSION['error'] = "Error al actualizar.";
                header("Location: index.php?page=suppliers&action=edit&id=" . $this->supplier->id);
            }
            exit;
        }
    }

    public function delete() {
        CsrfHelper::validateRequest();
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "ID inválido.";
            header("Location: index.php?page=suppliers");
            exit;
        }
        
        $this->supplier->id = $id;
        if ($this->supplier->delete()) {
            $_SESSION['success'] = "Proveedor eliminado.";
        } else {
            $_SESSION['error'] = "No se pudo eliminar.";
        }
        header("Location: index.php?page=suppliers");
        exit;
    }

    public function uploadInvoice() {
        CsrfHelper::validateRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplierId = $_POST['supplier_id'];
            $invoiceNumber = $_POST['invoice_number'];
            
            // Basic validation
            if (empty($invoiceNumber) || empty($_FILES['invoice_file']['name'])) {
                $_SESSION['error'] = "Número de factura y archivo son obligatorios.";
                header("Location: index.php?page=suppliers&action=show&id=" . $supplierId);
                exit;
            }

            $this->invoice->supplier_id = $supplierId;
            $this->invoice->invoice_number = $invoiceNumber;
            $this->invoice->invoice_date = $_POST['invoice_date'];
            $this->invoice->amount = $_POST['amount'];
            $this->invoice->status = $_POST['status'];
            $this->invoice->notes = $_POST['notes'];

            // File Upload Logic
            if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Validate file size (10MB maximum for invoices)
                    if ($_FILES['invoice_file']['size'] > 10 * 1024 * 1024) {
                        throw new Exception('El archivo es demasiado grande. Máximo 10MB permitido.');
                    }

                    $supplierDir = 'public/uploads/suppliers/' . intval($supplierId) . '/';
                    if (!file_exists($supplierDir)) {
                        mkdir($supplierDir, 0755, true);
                    }

                    $fileInfo = pathinfo($_FILES['invoice_file']['name']);
                    $extension = strtolower($fileInfo['extension']);
                    
                    // Validate extension (allow pdf, images, maybe doc)
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                    if (!in_array($extension, $allowedExtensions)) {
                        throw new Exception('Tipo de archivo no permitido.');
                    }

                    // Validate MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $_FILES['invoice_file']['tmp_name']);
                    finfo_close($finfo);
                    
                    // Allow PDF and Images MIME types
                    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                     if (!in_array($mimeType, $allowedMimes)) {
                        throw new Exception('Tipo MIME no permitido: ' . $mimeType);
                    }

                    // Rename file using invoice number
                    // Sanitize invoice number for filename
                    $safeInvoiceNum = preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoiceNumber);
                    $fileName = $safeInvoiceNum . '.' . $extension;
                    $targetPath = $supplierDir . $fileName;

                    // Handle duplicate filenames? Overwrite or append?
                    // User asked: "los ficheros se guardarian con el numero de factura o recibo como nombre"
                    // Assuming overwrite or unique per invoice number.
                    
                    if (move_uploaded_file($_FILES['invoice_file']['tmp_name'], $targetPath)) {
                        $this->invoice->file_path = $targetPath;
                    } else {
                        throw new Exception('Error al mover el archivo subido.');
                    }

                    if ($this->invoice->create()) {
                        $invoiceId = $this->db->lastInsertId();
                        $_SESSION['success'] = "Factura subida correctamente.";
                        
                        // Crear asiento contable automático
                        require_once __DIR__ . '/../Helpers/AccountingHelper.php';
                        $paymentDate = ($this->invoice->status === 'paid') ? date('Y-m-d') : null;
                        $accountingCreated = AccountingHelper::createEntryFromSupplierInvoice(
                            $this->db,
                            $invoiceId,
                            $this->invoice->amount,
                            'Factura ' . $invoiceNumber . ' - Proveedor',
                            $this->invoice->invoice_date,
                            $paymentDate,
                            'transfer'
                        );
                        
                        if (!$accountingCreated) {
                            error_log("No se pudo crear el asiento contable para la factura #$invoiceId");
                        }
                    } else {
                        // If DB insert fails, maybe delete the file?
                        unlink($targetPath);
                        $_SESSION['error'] = "Error al guardar la factura en base de datos.";
                    }

                } catch (Exception $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
            }

            header("Location: index.php?page=suppliers&action=show&id=" . $supplierId);
            exit;
        }
    }
    
    public function deleteInvoice() {
        CsrfHelper::validateRequest();
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "ID inválido.";
            header("Location: index.php?page=suppliers");
            exit;
        }
        
        $this->invoice->id = $id;
        $this->invoice->readOne();
        
        $supplierId = $this->invoice->supplier_id;
        $filePath = $this->invoice->file_path;
        
        if ($this->invoice->delete()) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $_SESSION['success'] = "Factura eliminada.";
        } else {
            $_SESSION['error'] = "Error al eliminar factura.";
        }
        
        header("Location: index.php?page=suppliers&action=show&id=" . $supplierId);
        exit;
    }

    public function dashboard() {
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        
        // Fetch stats
        $totalAmount = $this->invoice->getTotalAmount($year);
        $pendingAmount = $this->invoice->getPendingAmount();
        $topSuppliers = $this->invoice->getTopSuppliers(5, $year);
        $monthlyStats = $this->invoice->getMonthlyStats($year);
        $recentInvoices = $this->invoice->getRecentInvoices(5);
        
        // Total suppliers count
        $stmt = $this->supplier->readAll();
        $totalSuppliers = $stmt->rowCount();
        
        require __DIR__ . '/../Views/suppliers/dashboard.php';
    }
}
?>
