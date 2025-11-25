<?php
// Vista para mostrar el QR de un vale de evento
require_once __DIR__ . '/../../Helpers/QrGenerator.php';
require_once __DIR__ . '/../../Models/EventVoucher.php';
require_once __DIR__ . '/../../Config/database.php';

$eventId = $_GET['event_id'] ?? null;
$memberId = $_GET['member_id'] ?? null;
if (!$eventId || !$memberId) {
    echo 'Faltan datos.';
    exit;
}
$db = (new Database())->getConnection();
$model = new EventVoucher($db);

// Crear el vale y obtener el código
$code = $model->createVoucher($eventId, $memberId);
// Generar el QR
$qrImage = QrGenerator::generate($code);

header('Content-Type: text/html; charset=utf-8');
?>
<h2>Vale QR para evento</h2>
<p>Escanea este código en el punto de recogida:</p>
<img src="data:image/png;base64,<?= base64_encode($qrImage) ?>" alt="QR Vale" style="width:300px;">
<p><b>Código:</b> <?= htmlspecialchars($code) ?></p>
