
ini_set('memory_limit', '256M');
<?php

require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/../Models/Donation.php';
require_once __DIR__ . '/../Models/Expense.php';
require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/Payment.php';

class ExportController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Exportar listado de socios a Excel (CSV)
     */
    public function exportMembersExcel() {
        $memberModel = new Member($this->db);
        $stmt = $memberModel->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Headers para descargar como archivo Excel (CSV)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="socios_' . date('Y-m-d') . '.csv"');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8 (para Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'ID',
            'Número de Socio',
            'Nombre',
            'Apellidos',
            'Email',
            'Teléfono',
            'Dirección',
            'Estado',
            'Categoría',
            'Activo',
            'Fecha de Alta'
        ], ';');
        
        // Datos
        foreach ($members as $member) {
            fputcsv($output, [
                $member['id'],
                $member['member_number'],
                $member['first_name'],
                $member['last_name'],
                $member['email'],
                $member['phone'],
                $member['address'],
                $member['status'],
                $member['category_name'] ?? 'Sin categoría',
                $member['status'] === 'active' ? 'Sí' : 'No',
                date('d/m/Y', strtotime($member['created_at']))
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar listado de socios a PDF
     */
    public function exportMembersPDF() {
        try {
            $memberModel = new Member($this->db);
            $stmt = $memberModel->readAll();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load Composer autoload
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                throw new Exception('Composer autoload no encontrado. Ejecute: composer install');
            }
            require_once $autoloadPath;
            
            if (!class_exists('TCPDF')) {
                throw new Exception('TCPDF no está instalado. Ejecute: composer require tecnickcom/tcpdf');
            }
            
            // Create PDF
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Sistema de Gestión');
            $pdf->SetAuthor('Asociación');
            $pdf->SetTitle('Listado de Socios - ' . date('d/m/Y'));
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);
            
            // Add page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('helvetica', '', 9);
            
            // Generate HTML
            $html = $this->generateMembersPDFHTML($members);
            
            // Write HTML
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Output PDF
            $pdf->Output('socios_' . date('Y-m-d') . '.pdf', 'D');
            exit;
            
        } catch (Exception $e) {
            error_log('Export PDF error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar PDF: ' . $e->getMessage();
            header('Location: index.php?page=members');
            exit;
        }
    }
    
    /**
     * Exportar donaciones a Excel (CSV)
     */
    public function exportDonationsExcel() {
        $donationModel = new Donation($this->db);
        
        $query = "SELECT d.*, m.first_name, m.last_name, m.id AS member_id 
              FROM donations d
              LEFT JOIN members m ON d.member_id = m.id
              ORDER BY d.date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="donaciones_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID',
            'Fecha',
            'Socio',
            'Número Socio',
            'Importe',
            'Concepto',
            'Método de Pago',
            'Estado'
        ], ';');
        
        foreach ($donations as $donation) {
            $donor_name = $donation['first_name'] 
                ? $donation['first_name'] . ' ' . $donation['last_name'] 
                : 'Anónimo';
            
            fputcsv($output, [
                $donation['id'],
                date('d/m/Y', strtotime($donation['date'])),
                $donor_name,
                $donation['member_id'] ?? '',
                number_format($donation['amount'], 2, ',', '.') . ' €',
                $donation['description'],
                $donation['payment_method'],
                $donation['status']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar gastos a Excel (CSV)
     */
    public function exportExpensesExcel() {
        $expenseModel = new Expense($this->db);
        
        $query = "SELECT e.*, c.name as category_name, c.color as category_color
                  FROM expenses e
                  LEFT JOIN expense_categories c ON e.category_id = c.id
                  ORDER BY e.expense_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="gastos_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID',
            'Fecha',
            'Descripción',
            'Categoría',
            'Importe',
            'Método de Pago',
            'Proveedor',
            'Número Factura',
            'Estado'
        ], ';');
        
        foreach ($expenses as $expense) {
            fputcsv($output, [
                $expense['id'],
                date('d/m/Y', strtotime($expense['date'])),
                $expense['description'],
                $expense['category_name'] ?? 'Sin categoría',
                number_format($expense['amount'], 2, ',', '.') . ' €',
                $expense['payment_method'],
                $expense['vendor'] ?? '',
                $expense['invoice_number'] ?? '',
                $expense['status']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar eventos a Excel (CSV)
     */
    public function exportEventsExcel() {
        $eventModel = new Event($this->db);
        
        $query = "SELECT * FROM events ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="eventos_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID',
            'Nombre',
            'Fecha',
            'Hora Inicio',
            'Hora Fin',
            'Ubicación',
            'Tipo',
            'Descripción',
            'Precio',
            'Máximo Asistentes',
            'Requiere Registro',
            'Activo'
        ], ';');
        
        foreach ($events as $event) {
            fputcsv($output, [
                $event['id'],
                $event['name'],
                date('d/m/Y', strtotime($event['date'])),
                $event['start_time'] ?? '',
                $event['end_time'] ?? '',
                $event['location'] ?? '',
                $event['event_type'] ?? '',
                strip_tags($event['description']),
                $event['price'] ? number_format($event['price'], 2, ',', '.') . ' €' : 'Gratis',
                $event['max_attendees'] ?? 'Sin límite',
                $event['requires_registration'] ? 'Sí' : 'No',
                $event['is_active'] ? 'Sí' : 'No'
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar pagos/cuotas a Excel (CSV)
     */
    public function exportPaymentsExcel() {
        $query = "SELECT p.*, m.first_name, m.last_name, m.member_number 
                  FROM payments p
                  JOIN members m ON p.member_id = m.id
                  ORDER BY p.due_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pagos_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID',
            'Socio',
            'Número Socio',
            'Descripción',
            'Importe',
            'Fecha Vencimiento',
            'Fecha Pago',
            'Método de Pago',
            'Estado'
        ], ';');
        
        foreach ($payments as $payment) {
            fputcsv($output, [
                $payment['id'],
                $payment['first_name'] . ' ' . $payment['last_name'],
                $payment['member_number'],
                $payment['description'],
                number_format($payment['amount'], 2, ',', '.') . ' €',
                date('d/m/Y', strtotime($payment['due_date'])),
                $payment['paid_date'] ? date('d/m/Y', strtotime($payment['paid_date'])) : '',
                $payment['payment_method'] ?? '',
                $payment['status']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Generar HTML para PDF de socios (TCPDF compatible)
     */
    private function generateMembersPDFHTML($members) {
        $html = '<style>
            h1 { text-align: center; color: #4f46e5; font-size: 18px; margin-bottom: 10px; }
            .header-info { text-align: center; color: #64748b; font-size: 10px; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; font-size: 8px; }
            th { background-color: #f1f5f9; padding: 6px 4px; text-align: left; border: 1px solid #e2e8f0; font-weight: bold; }
            td { padding: 5px 4px; border: 1px solid #e2e8f0; }
            tr:nth-child(even) { background-color: #f8fafc; }
            .status-active { color: #059669; font-weight: bold; }
            .status-inactive { color: #dc2626; }
        </style>';
        
        $html .= '<h1>Listado de Socios</h1>';
        $html .= '<div class="header-info">Generado el ' . date('d/m/Y H:i') . ' | Total: ' . count($members) . ' socios</div>';
        
        $html .= '<table>
            <thead>
                <tr>
                    <th style="width: 8%;">Nº</th>
                    <th style="width: 27%;">Nombre Completo</th>
                    <th style="width: 25%;">Email</th>
                    <th style="width: 15%;">Teléfono</th>
                    <th style="width: 15%;">Categoría</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($members as $member) {
            $statusClass = ($member['status'] === 'active') ? 'status-active' : 'status-inactive';
            $statusText = ($member['status'] === 'active') ? 'Activo' : 'Inactivo';
            
            $html .= '<tr>
                <td>' . htmlspecialchars($member['member_number']) . '</td>
                <td>' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</td>
                <td>' . htmlspecialchars($member['email']) . '</td>
                <td>' . htmlspecialchars($member['phone']) . '</td>
                <td>' . htmlspecialchars($member['category_name'] ?? 'Sin categoría') . '</td>
                <td class="' . $statusClass . '">' . $statusText . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }
}
