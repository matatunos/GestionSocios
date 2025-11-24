<?php
// Vista básica de socios con enlace a QR de vales
require_once __DIR__ . '/../../Config/Database.php';
$db = (new Database())->getConnection();
$members = $db->query('SELECT id, first_name, last_name FROM members WHERE status = "active"')->fetchAll(PDO::FETCH_ASSOC);

$eventId = $_GET['event_id'] ?? null;

// Obtener eventos activos para el selector
require_once __DIR__ . '/../../Models/Event.php';
$eventModel = new Event($db);
$events = $eventModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-4">
    <h1>Listado de Socios</h1>
    <form method="get" style="margin-bottom:0;">
        <label for="event_id">Selecciona evento:</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()">
            <option value="">-- Elige --</option>
            <?php foreach ($events as $ev): ?>
            <option value="<?= $ev['id'] ?>" <?= ($eventId == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['name']) ?> (<?= $ev['date'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<link rel="stylesheet" href="/css/listings.css">
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>QR Vale Evento</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($members as $member): ?>
    <tr>
        <td><?= $member['id'] ?></td>
        <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
        <td>
            <?php if ($eventId): ?>
            <a href="../vouchers/show.php?event_id=<?= $eventId ?>&member_id=<?= $member['id'] ?>" target="_blank" class="btn btn-sm btn-warning">
                <i class="fas fa-qrcode" title="Ver QR"></i>
            </a>
            <?php else: ?>
            <em>Selecciona evento</em>
            <?php endif; ?>
        </td>
                    <td style="text-align: right;">
                        <div class="listing-actions">
                            <?php if (!empty($member['logo_url'])): ?>
                                <a href="/<?= htmlspecialchars($member['logo_url']) ?>" target="_blank" class="btn btn-sm btn-warning" title="Ver Logo">
                                    <i class="fas fa-image"></i>
                                </a>
                            <?php endif; ?>
                            <a href="index.php?page=members&action=edit&id=<?= $member['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit" title="Editar"></i>
                            </a>
                            <?php if (!empty($member['latitude']) && !empty($member['longitude'])): ?>
                                <a href="index.php?page=map#member-<?= $member['id'] ?>" class="btn btn-sm btn-warning" title="Ver en mapa">
                                    <i class="fas fa-map-marker-alt"></i>
                                </a>
                            <?php endif; ?>
                            <a href="index.php?page=members&action=delete&id=<?= $member['id'] ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Seguro que quieres eliminar este socio?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
