<?php
require_once __DIR__ . '/../../Models/Event.php';
require_once __DIR__ . '/../../Models/EventAttendance.php';

$eventModel = new Event($db);
$stmt = $eventModel->readAll();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$attendanceModel = new EventAttendance($db);

?>
<div class="card" style="margin-bottom:2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
        <i class="fas fa-calendar-alt" style="margin-right: 0.5rem; color: var(--primary-600);"></i>
        Informe de Eventos y Asistentes
    </h2>
    <table class="table">
        <thead>
            <tr>
                <th>Evento</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Registrados</th>
                <th>Confirmados</th>
                <th>Asistió</th>
                <th>Cancelados</th>
                <th>Total</th>
                <th>% Ocupación</th>
                <th>Ingresos (€)</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event):
            $stats = $attendanceModel->getStatsByEvent($event['id']);
            $total = ($stats['registered'] ?? 0) + ($stats['confirmed'] ?? 0) + ($stats['attended'] ?? 0) + ($stats['cancelled'] ?? 0);
            $max = $event['max_attendees'] ?? 0;
            $ocupacion = $max ? round(($total / $max) * 100, 1) . '%' : '-';
            $ingresos = ($event['price'] ?? 0) * ($stats['confirmed'] ?? 0);
        ?>
            <tr>
                <td style="font-weight:500;">
                    <?php if ($event['is_active']): ?>
                        <i class="fas fa-circle" style="color:#22c55e;font-size:0.8rem;margin-right:0.3rem;"></i>
                    <?php else: ?>
                        <i class="fas fa-circle" style="color:#ef4444;font-size:0.8rem;margin-right:0.3rem;"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($event['name']) ?>
                </td>
                <td><?= htmlspecialchars($event['date']) ?></td>
                <td><span class="badge <?= $event['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $event['is_active'] ? 'Activo' : 'Inactivo' ?></span></td>
                <td><?= $stats['registered'] ?? 0 ?></td>
                <td><?= $stats['confirmed'] ?? 0 ?></td>
                <td><?= $stats['attended'] ?? 0 ?></td>
                <td><?= $stats['cancelled'] ?? 0 ?></td>
                <td><?= $total ?></td>
                <td><?= $ocupacion ?></td>
                <td><?= number_format($ingresos,2) ?></td>
                <td><a href="index.php?page=events&action=show&id=<?= $event['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-users"></i> Ver</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
