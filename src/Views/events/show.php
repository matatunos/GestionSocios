<?php ob_start(); ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'paid'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Pago registrado correctamente.
    </div>
<?php endif; ?>

<div class="mb-4">
    <a href="index.php?page=events" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver a eventos
    </a>
    <h1>Participantes: <?= htmlspecialchars($event->name) ?></h1>
</div>

<div class="card mb-4">
    <h3 style="margin-bottom: 1rem;">Información del Evento</h3>
    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($event->description)) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($event->date) ?></p>
    <p><strong>Precio:</strong> <?= number_format($event->price, 2) ?> €</p>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>Socio</th>
                <th>Estado de Pago</th>
                <th>Asistencia</th>
                <th style="text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($participants)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay participantes.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($participants as $p): 
                    $paid = $p['payment'] && $p['payment']['status'] === 'paid';
                ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <?= htmlspecialchars($p['member']['first_name'] . ' ' . $p['member']['last_name']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $paid ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $paid ? 'Pagado' : 'Pendiente' ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $attendanceStatus = $p['attendance']['status'] ?? 'pending';
                            ?>
                            <?php if ($attendanceStatus !== 'registered'): ?>
                                <form method="POST" action="index.php?page=events&action=updateAttendanceStatus&id=<?= $event->id ?>&member_id=<?= $p['member']['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="status" value="registered">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-user-check"></i> Marcar como registrado
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-active">Registrado</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if (!$paid): ?>
                                <a href="index.php?page=events&action=markPaid&id=<?= $event->id ?>&member_id=<?= $p['member']['id'] ?>" 
                                   class="btn btn-sm btn-primary"
                                   onclick="return confirm('¿Marcar como pagado para este socio?');">
                                    <i class="fas fa-check"></i> Marcar como Pagado
                                </a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.875rem;">
                                    <i class="fas fa-check-circle"></i> Pagado
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
