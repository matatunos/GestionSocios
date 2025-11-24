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
<h2>Socios activos</h2>
<style>
    .member-actions {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }
    .member-actions .btn {
        white-space: nowrap;
        padding: 0.25em 0.5em;
        font-size: 1em;
        min-width: 32px;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
</style>
<form method="get" style="margin-bottom:20px;">
    <label for="event_id">Selecciona evento:</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()">
        <option value="">-- Elige --</option>
        <?php foreach ($events as $ev): ?>
        <option value="<?= $ev['id'] ?>" <?= ($eventId == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['name']) ?> (<?= $ev['date'] ?>)</option>
        <?php endforeach; ?>
    </select>
</form>
<table border="1" cellpadding="6">
    <tr><th>ID</th><th>Nombre</th><th>QR Vale Evento</th><th>Acciones</th></tr>
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
        </td>
    </tr>
    <?php endforeach; ?>
</table>
