<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Gestión de Eventos</h1>
    <a href="index.php?page=events&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Evento
    </a>
</div>

<form method="GET" style="margin-bottom: 1rem;">
    <input type="hidden" name="page" value="events">
    <label style="font-weight: 500; margin-right: 1rem;">
        <input type="checkbox" name="show_discarded" value="1" <?php if (!empty($_GET['show_discarded'])) echo 'checked'; ?>> Mostrar eventos descartados
    </label>
    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
</form>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay eventos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($events as $row): ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <?php if (!empty($row['discarded']) && $row['discarded']): ?>
                                <span class="badge badge-danger" style="margin-right: 0.5rem;">Descartado</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                        <td><?php echo isset($row['price']) ? number_format($row['price'], 2) . ' €' : '-'; ?></td>
                        <td>
                            <span class="badge <?php echo (!isset($row['discarded']) || !$row['discarded']) ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo (!isset($row['discarded']) || !$row['discarded']) ? 'Activo' : 'Descartado'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=events&action=show&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-users"></i> Participantes
                            </a>
                            <a href="index.php?page=events&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <?php if (empty($row['discarded']) || !$row['discarded']): ?>
                                <a href="index.php?page=events&action=discard&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Descartar este evento?')">
                                    <i class="fas fa-trash"></i> Descartar
                                </a>
                            <?php else: ?>
                                <a href="index.php?page=events&action=restore&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('¿Restaurar este evento?')">
                                    <i class="fas fa-undo"></i> Restaurar
                                </a>
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
