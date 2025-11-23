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
    <tr><th>ID</th><th>Nombre</th><th>QR Vale Evento</th></tr>
    <?php foreach ($members as $member): ?>
    <tr>
        <td><?= $member['id'] ?></td>
        <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
        <td>
            <?php if ($eventId): ?>
            <a href="../vouchers/show.php?event_id=<?= $eventId ?>&member_id=<?= $member['id'] ?>" target="_blank">Ver QR</a>
            <?php else: ?>
            <em>Selecciona evento</em>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
