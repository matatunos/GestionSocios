<?php
// Controlador para validación y escaneo de vales QR de eventos
require_once __DIR__ . '/../Models/EventVoucher.php';
require_once __DIR__ . '/../Config/database.php';

class EventVoucherController {
    private $db;
    private $model;
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->model = new EventVoucher($this->db);
    }

    // Endpoint para validar QR y marcar como recogido
    public function validateVoucher($code) {
        $status = $this->model->getVoucherStatus($code);
        if ($status === 'valid') {
            $this->model->markAsCollected($code);
        }
        return [ 'status' => $status ];
    }
}

// Ejemplo de uso para endpoint AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $controller = new EventVoucherController();
    $result = $controller->validateVoucher($_POST['code']);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
