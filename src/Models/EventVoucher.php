<?php
// Modelo para la gestión de vales de eventos con QR
class EventVoucher {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    // Genera un código único seguro para el vale
    public function generateCode($eventId, $memberId) {
        return hash('sha256', uniqid($eventId . '-' . $memberId . '-' . random_bytes(8), true));
    }

    // Crea el registro y devuelve el código QR
    public function createVoucher($eventId, $memberId) {
        $code = $this->generateCode($eventId, $memberId);
        $stmt = $this->db->prepare("INSERT INTO event_vouchers (event_id, member_id, code, vendido) VALUES (?, ?, ?, 1)");
        $stmt->execute([$eventId, $memberId, $code]);
        return $code;
    }

    // Obtiene el estado del vale por código
    public function getVoucherStatus($code) {
        $stmt = $this->db->prepare("SELECT * FROM event_vouchers WHERE code = ?");
        $stmt->execute([$code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$voucher) return 'invalid';
        if ($voucher['recogido']) return 'used';
        return 'valid';
    }

    // Marca el vale como recogido
    public function markAsCollected($code) {
        $stmt = $this->db->prepare("UPDATE event_vouchers SET recogido = 1, fecha_recogida = NOW() WHERE code = ?");
        $stmt->execute([$code]);
    }
}
