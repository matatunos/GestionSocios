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
        $page = $_GET['page_num'] ?? 1;
        $perPage = 25;
        $result = $this->model->getAuditLog($filters, $page, $perPage);
        require __DIR__ . '/../Views/audit_log/index.php';
    }

    public function export_excel() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        $this->model->exportExcel($filters);
    }

    public function export_pdf() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity' => $_GET['entity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        $this->model->exportPDF($filters);
    }
}
