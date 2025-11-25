<?php
=======
require_once __DIR__ . '/../Models/AuditLog.php';
require_once __DIR__ . '/../Config/database.php';

class AuditLogController {
    private $db;
    private $auditLog;

    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $db = $database->getConnection();
        }
        $this->db = $db;
        $this->auditLog = new AuditLog($this->db);
    }

    public function index() {
        // Filtros desde GET
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        $logs = $this->auditLog->readFiltered($filters, $limit, $offset);
        $total = $this->auditLog->countFiltered($filters);
        $totalPages = ceil($total / $limit);
        // Si se requiere layout, usar output buffering
        if (defined('USE_LAYOUT') && USE_LAYOUT) {
            ob_start();
            require __DIR__ . '/../Views/audit_log/index.php';
            $content = ob_get_clean();
            require __DIR__ . '/../Views/layout.php';
        } else {
            require __DIR__ . '/../Views/audit_log/index.php';
        }
    }

    public function export_excel() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $logs = $this->auditLog->readFiltered($filters, 1000, 0); // Exporta hasta 1000 registros
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Fecha', 'Usuario', 'Acción', 'Entidad', 'ID', 'Detalles'], ';');
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['username'],
                $log['action'],
                $log['entity'],
                $log['entity_id'],
                $log['details']
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function export_pdf() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $logs = $this->auditLog->readFiltered($filters, 1000, 0);
        require_once __DIR__ . '/../../vendor/autoload.php';
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema de Gestión');
        $pdf->SetAuthor('Asociación');
        $pdf->SetTitle('Registro de Actividad - ' . date('d/m/Y'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);
        $html = '<h1>Registro de Actividad</h1>';
        $html .= '<table border="1" cellpadding="4"><thead><tr>';
        $html .= '<th>Fecha</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>ID</th><th>Detalles</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($logs as $log) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($log['created_at']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['username']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['action']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['entity']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['entity_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['details']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('audit_log_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
}
    }
}
