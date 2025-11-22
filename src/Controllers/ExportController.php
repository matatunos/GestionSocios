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
        $members = $memberModel->read();
        
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
                $member['active'] ? 'Sí' : 'No',
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
        $memberModel = new Member($this->db);
        $members = $memberModel->read();
        
        // Generar HTML para PDF
        $html = $this->generateMembersPDFHTML($members);
        
        // Configurar headers para PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="socios_' . date('Y-m-d') . '.pdf"');
        
        // Usar wkhtmltopdf o generar HTML que se puede imprimir a PDF desde el navegador
        // Por ahora, generamos un HTML imprimible
        echo $html;
        exit;
    }
    
    /**
     * Exportar donaciones a Excel (CSV)
     */
    public function exportDonationsExcel() {
        $donationModel = new Donation($this->db);
        
        $query = "SELECT d.*, m.first_name, m.last_name, m.member_number 
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
                $donation['member_number'] ?? '',
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
                  ORDER BY e.date DESC";
        
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
     * Generar HTML para PDF de socios (imprimible)
     */
    private function generateMembersPDFHTML($members) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Listado de Socios - <?php echo date('d/m/Y'); ?></title>
            <style>
                @page { margin: 1.5cm; }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 10pt;
                    color: #333;
                }
                h1 {
                    text-align: center;
                    color: #6366f1;
                    margin-bottom: 0.5cm;
                }
                .header-info {
                    text-align: center;
                    color: #64748b;
                    margin-bottom: 1cm;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 0.5cm;
                }
                thead {
                    background: #f1f5f9;
                }
                th {
                    padding: 8px;
                    text-align: left;
                    border-bottom: 2px solid #e2e8f0;
                    font-weight: 700;
                }
                td {
                    padding: 6px 8px;
                    border-bottom: 1px solid #e2e8f0;
                }
                tr:nth-child(even) {
                    background: #f8fafc;
                }
                .status-active {
                    color: #059669;
                    font-weight: 600;
                }
                .status-inactive {
                    color: #dc2626;
                }
                .footer {
                    position: fixed;
                    bottom: 0;
                    width: 100%;
                    text-align: center;
                    font-size: 8pt;
                    color: #94a3b8;
                    margin-top: 1cm;
                }
                @media print {
                    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                    .page-break { page-break-before: always; }
                }
            </style>
        </head>
        <body>
            <h1>Listado de Socios</h1>
            <div class="header-info">
                Generado el <?php echo date('d/m/Y H:i'); ?> | Total: <?php echo count($members); ?> socios
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['member_number']); ?></td>
                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                        <td><?php echo htmlspecialchars($member['category_name'] ?? 'Sin categoría'); ?></td>
                        <td class="<?php echo $member['active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $member['active'] ? 'Activo' : 'Inactivo'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="footer">
                Sistema de Gestión de Asociaciones - Página <span class="page-number"></span>
            </div>
            
            <script>
                // Add page numbers
                window.onload = function() {
                    var pageNumbers = document.querySelectorAll('.page-number');
                    pageNumbers.forEach(function(el) {
                        el.textContent = '1';
                    });
                };
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
