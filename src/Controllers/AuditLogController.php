<?php
require_once __DIR__ . '/../Models/AuditLog.php';

class AuditLogController {
    private $db;
    private $model;

    public function __construct($db = null) {
        if ($db === null) {
            $db = (new Database())->getConnection();
        }
        $this->db = $db;
        $this->model = new AuditLog($db);
    }

    public function index() {
        // Filtros
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        $page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $logs = $this->model->readFiltered($filters, $perPage, $offset);
        $total = $this->model->countFiltered($filters);
        $totalPages = max(1, ceil($total / $perPage));
        // Output buffering para layout
        ob_start();
        require __DIR__ . '/../Views/audit_log/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layout.php';
    }

    public function export_excel() {
        // Implementar exportación a Excel si el modelo lo soporta
        // $this->model->exportExcel($filters);
    }

    public function export_pdf() {
        // Implementar exportación a PDF si el modelo lo soporta
        // $this->model->exportPDF($filters);
    }
}
