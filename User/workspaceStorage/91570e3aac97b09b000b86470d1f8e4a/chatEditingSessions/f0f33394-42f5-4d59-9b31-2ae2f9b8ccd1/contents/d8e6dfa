<?php

require_once __DIR__ . '/../Models/IssuedInvoice.php';
require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/../Helpers/AccountingHelper.php';

class InvoiceController {
    private $db;
    private $invoice;
    
    public function __construct($db) {
        $this->db = $db;
        $this->invoice = new IssuedInvoice($db);
    }
    
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
    }
    
    /**
     * Listar facturas
     */
    public function index() {
        $this->checkAdmin();
        
        // Filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'series_id' => $_GET['series_id'] ?? '',
            'customer_type' => $_GET['customer_type'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Paginación
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Obtener facturas
        $invoicesStmt = $this->invoice->readAll($filters, $limit, $offset);
        $invoices = $invoicesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Total para paginación
        $totalInvoices = $this->invoice->count($filters);
        $totalPages = ceil($totalInvoices / $limit);
        
        // Obtener series para filtro
        $series = IssuedInvoice::getSeries($this->db);
        
        require __DIR__ . '/../Views/invoices/index.php';
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function create() {
        $this->checkAdmin();
        
        // Obtener series activas
        $series = IssuedInvoice::getSeries($this->db);
        
        // Obtener socios para autocompletar
        $memberModel = new Member($this->db);
        $membersStmt = $memberModel->readAll();
        $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/invoices/create.php';
    }
    
    /**
     * Guardar nueva factura
     */
    public function store() {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=invoices');
            exit;
        }
        
        try {
            // Datos de la factura
            $this->invoice->series_id = $_POST['series_id'];
            $this->invoice->issue_date = $_POST['issue_date'];
            $this->invoice->due_date = $_POST['due_date'] ?? null;
            
            // Cliente
            $this->invoice->customer_type = $_POST['customer_type'];
            $this->invoice->member_id = $_POST['customer_type'] === 'member' ? ($_POST['member_id'] ?? null) : null;
            $this->invoice->customer_name = $_POST['customer_name'];
            $this->invoice->customer_tax_id = $_POST['customer_tax_id'] ?? '';
            $this->invoice->customer_address = $_POST['customer_address'] ?? '';
            $this->invoice->customer_city = $_POST['customer_city'] ?? '';
            $this->invoice->customer_postal_code = $_POST['customer_postal_code'] ?? '';
            $this->invoice->customer_country = $_POST['customer_country'] ?? 'España';
            $this->invoice->customer_email = $_POST['customer_email'] ?? '';
            $this->invoice->customer_phone = $_POST['customer_phone'] ?? '';
            
            // Descripción
            $this->invoice->description = $_POST['description'];
            $this->invoice->notes = $_POST['notes'] ?? '';
            
            // Estado y pago
            $this->invoice->status = $_POST['status'] ?? 'draft';
            $this->invoice->payment_method = $_POST['payment_method'] ?? 'transfer';
            $this->invoice->reference = $_POST['reference'] ?? '';
            
            $this->invoice->created_by = $_SESSION['user_id'];
            
            // Líneas de factura
            $lines = [];
            if (isset($_POST['lines'])) {
                foreach ($_POST['lines'] as $line) {
                    if (!empty($line['concept'])) {
                        $lines[] = [
                            'concept' => $line['concept'],
                            'description' => $line['description'] ?? '',
                            'quantity' => floatval($line['quantity']),
                            'unit_price' => floatval($line['unit_price']),
                            'discount_rate' => floatval($line['discount_rate'] ?? 0),
                            'tax_rate' => floatval($line['tax_rate'])
                        ];
                    }
                }
            }
            
            if (empty($lines)) {
                throw new Exception("Debe agregar al menos una línea a la factura");
            }
            
            // Crear factura
            if ($this->invoice->create($lines)) {
                // Auditoría
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new AuditLog($this->db);
                $audit->create($_SESSION['user_id'], 'create', 'issued_invoice', $this->invoice->id, 
                    'Factura ' . $this->invoice->full_number . ' creada');
                
                $_SESSION['success'] = 'Factura creada correctamente: ' . $this->invoice->full_number;
                header('Location: index.php?page=invoices&action=view&id=' . $this->invoice->id);
                exit;
            } else {
                throw new Exception("Error al crear la factura");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear la factura: ' . $e->getMessage();
            header('Location: index.php?page=invoices&action=create');
            exit;
        }
    }
    
    /**
     * Ver detalle de factura
     */
    public function view() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        $this->invoice->id = $id;
        
        if (!$this->invoice->readOne()) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: index.php?page=invoices');
            exit;
        }
        
        // Obtener líneas
        $lines = $this->invoice->readLines();
        
        require __DIR__ . '/../Views/invoices/view.php';
    }
    
    /**
     * Emitir factura (cambiar de borrador a emitida)
     */
    public function issue() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        $this->invoice->id = $id;
        
        if (!$this->invoice->readOne()) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: index.php?page=invoices');
            exit;
        }
        
        if ($this->invoice->status !== 'draft') {
            $_SESSION['error'] = 'Solo se pueden emitir facturas en borrador';
            header('Location: index.php?page=invoices&action=view&id=' . $id);
            exit;
        }
        
        try {
            // Emitir factura
            if (!$this->invoice->issue($_SESSION['user_id'])) {
                throw new Exception("Error al emitir la factura");
            }
            
            // Crear asiento contable
            $accountingCreated = $this->createAccountingEntry();
            
            // Generar firma Verifactu (si está habilitado)
            $verifactuCreated = $this->createVerifactuSignature();
            
            // Auditoría
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'issue', 'issued_invoice', $id, 
                'Factura ' . $this->invoice->full_number . ' emitida');
            
            $message = 'Factura emitida correctamente';
            if ($accountingCreated) {
                $message .= ' y registrada en contabilidad';
            }
            if ($verifactuCreated) {
                $message .= '. Firma Verifactu generada';
            }
            
            $_SESSION['success'] = $message;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al emitir la factura: ' . $e->getMessage();
        }
        
        header('Location: index.php?page=invoices&action=view&id=' . $id);
        exit;
    }
    
    /**
     * Marcar factura como pagada
     */
    public function markPaid() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        $this->invoice->id = $id;
        
        if (!$this->invoice->readOne()) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: index.php?page=invoices');
            exit;
        }
        
        if ($this->invoice->status === 'paid') {
            $_SESSION['error'] = 'La factura ya está marcada como pagada';
            header('Location: index.php?page=invoices&action=view&id=' . $id);
            exit;
        }
        
        if ($this->invoice->status === 'cancelled') {
            $_SESSION['error'] = 'No se puede marcar como pagada una factura cancelada';
            header('Location: index.php?page=invoices&action=view&id=' . $id);
            exit;
        }
        
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? null;
        
        if ($this->invoice->markAsPaid($payment_date, $payment_method)) {
            // Auditoría
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'mark_paid', 'issued_invoice', $id, 
                'Factura ' . $this->invoice->full_number . ' marcada como pagada');
            
            $_SESSION['success'] = 'Factura marcada como pagada correctamente';
        } else {
            $_SESSION['error'] = 'Error al marcar la factura como pagada';
        }
        
        header('Location: index.php?page=invoices&action=view&id=' . $id);
        exit;
    }
    
    /**
     * Cancelar factura
     */
    public function cancel() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        $this->invoice->id = $id;
        
        if (!$this->invoice->readOne()) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: index.php?page=invoices');
            exit;
        }
        
        if ($this->invoice->status === 'cancelled') {
            $_SESSION['error'] = 'La factura ya está cancelada';
            header('Location: index.php?page=invoices&action=view&id=' . $id);
            exit;
        }
        
        if ($this->invoice->status === 'paid') {
            $_SESSION['error'] = 'No se puede cancelar una factura pagada. Debe emitir una factura rectificativa';
            header('Location: index.php?page=invoices&action=view&id=' . $id);
            exit;
        }
        
        $reason = $_POST['reason'] ?? 'Cancelada por el usuario';
        
        if ($this->invoice->cancel($_SESSION['user_id'], $reason)) {
            // Auditoría
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'cancel', 'issued_invoice', $id, 
                'Factura ' . $this->invoice->full_number . ' cancelada: ' . $reason);
            
            $_SESSION['success'] = 'Factura cancelada correctamente';
        } else {
            $_SESSION['error'] = 'Error al cancelar la factura';
        }
        
        header('Location: index.php?page=invoices&action=view&id=' . $id);
        exit;
    }
    
    /**
     * Generar PDF de la factura
     */
    public function generatePDF() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        $this->invoice->id = $id;
        
        if (!$this->invoice->readOne()) {
            $_SESSION['error'] = 'Factura no encontrada';
            header('Location: index.php?page=invoices');
            exit;
        }
        
        // Obtener líneas
        $lines = $this->invoice->readLines();
        
        // Generar PDF
        require_once __DIR__ . '/../Helpers/InvoicePDFHelper.php';
        $pdf = new InvoicePDFHelper();
        $pdfContent = $pdf->generate($this->invoice, $lines);
        
        // Enviar PDF al navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Factura_' . $this->invoice->full_number . '.pdf"');
        echo $pdfContent;
        exit;
    }
    
    /**
     * Crear asiento contable al emitir factura
     */
    private function createAccountingEntry() {
        try {
            // Verificar que hay período contable abierto
            require_once __DIR__ . '/../Models/AccountingPeriod.php';
            $period = AccountingPeriod::getOpenPeriodForDate($this->db, $this->invoice->issue_date);
            
            if (!$period) {
                error_log("No hay período contable abierto para la fecha: " . $this->invoice->issue_date);
                return false;
            }
            
            // Obtener cuentas contables
            require_once __DIR__ . '/../Models/AccountingAccount.php';
            
            // 430 - Clientes (DEBE)
            $clientsAccount = AccountingHelper::getAccountByCode($this->db, '430');
            // 705 - Prestaciones de servicios (HABER)
            $incomeAccount = AccountingHelper::getAccountByCode($this->db, '705');
            // 477 - IVA Repercutido (HABER) - Solo si hay IVA
            $taxAccount = $this->invoice->tax_amount > 0 ? 
                AccountingHelper::getAccountByCode($this->db, '477') : null;
            
            if (!$clientsAccount || !$incomeAccount) {
                error_log("No se encontraron las cuentas contables necesarias");
                return false;
            }
            
            // Crear asiento
            require_once __DIR__ . '/../Models/AccountingEntry.php';
            $entry = new AccountingEntry($this->db);
            $entry->entry_date = $this->invoice->issue_date;
            $entry->period_id = $period['id'];
            $entry->description = "Factura {$this->invoice->full_number} - {$this->invoice->customer_name}";
            $entry->reference = "INV-" . $this->invoice->id;
            $entry->entry_type = 'automatic';
            $entry->source_type = 'issued_invoice';
            $entry->source_id = $this->invoice->id;
            $entry->status = 'posted';
            $entry->created_by = $_SESSION['user_id'];
            $entry->posted_by = $_SESSION['user_id'];
            $entry->posted_at = date('Y-m-d H:i:s');
            
            // Líneas del asiento
            $lines = [
                // DEBE: Clientes
                [
                    'account_id' => $clientsAccount['id'],
                    'description' => "Factura {$this->invoice->full_number}",
                    'debit' => $this->invoice->total,
                    'credit' => 0,
                    'line_order' => 1
                ],
                // HABER: Ingresos (base imponible)
                [
                    'account_id' => $incomeAccount['id'],
                    'description' => $this->invoice->description,
                    'debit' => 0,
                    'credit' => $this->invoice->subtotal - $this->invoice->discount_amount,
                    'line_order' => 2
                ]
            ];
            
            // HABER: IVA Repercutido (si hay)
            if ($taxAccount && $this->invoice->tax_amount > 0) {
                $lines[] = [
                    'account_id' => $taxAccount['id'],
                    'description' => "IVA " . number_format($this->invoice->tax_rate, 2) . "%",
                    'debit' => 0,
                    'credit' => $this->invoice->tax_amount,
                    'line_order' => 3
                ];
            }
            
            if ($entry->create($lines)) {
                // Actualizar factura con ID del asiento
                $updateQuery = "UPDATE issued_invoices SET accounting_entry_id = ? WHERE id = ?";
                $stmt = $this->db->prepare($updateQuery);
                $stmt->execute([$entry->id, $this->invoice->id]);
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creando asiento contable para factura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear firma Verifactu para la factura
     */
    private function createVerifactuSignature() {
        try {
            // Verificar si Verifactu está habilitado
            $query = "SELECT setting_value FROM organization_settings 
                      WHERE category = 'verifactu' AND setting_key = 'enabled'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || $result['setting_value'] !== '1') {
                return false; // Verifactu no está habilitado
            }
            
            // Obtener NIF emisor
            $query = "SELECT setting_value FROM organization_settings 
                      WHERE category = 'verifactu' AND setting_key = 'nif_emisor'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $nif_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $nif_emisor = $nif_result['setting_value'] ?? '';
            
            if (empty($nif_emisor)) {
                error_log("Verifactu habilitado pero NIF emisor no configurado");
                return false;
            }
            
            // Preparar datos de la factura para el hash
            $invoice_data = [
                'nif_emisor' => $nif_emisor,
                'full_number' => $this->invoice->full_number,
                'issue_date' => $this->invoice->issue_date,
                'total' => $this->invoice->total,
                'customer_tax_id' => $this->invoice->customer_tax_id ?? ''
            ];
            
            // Crear instancia de Verifactu y generar firma
            require_once __DIR__ . '/../Models/VerifactuSignature.php';
            $verifactu = new VerifactuSignature($this->db);
            
            if ($verifactu->generateSignature($this->invoice->id, $invoice_data)) {
                // Si auto_send está habilitado, enviar a AEAT
                $query = "SELECT setting_value FROM organization_settings 
                          WHERE category = 'verifactu' AND setting_key = 'auto_send'";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $auto_send_result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($auto_send_result && $auto_send_result['setting_value'] === '1') {
                    $verifactu->sendToAEAT();
                }
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creando firma Verifactu: " . $e->getMessage());
            return false;
        }
    }
}
?>
