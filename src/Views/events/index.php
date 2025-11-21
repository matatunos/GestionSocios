<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Gestión de Eventos</h1>
    <a href="index.php?page=events&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Evento
    </a>
</div>

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
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> €</td>
                        <td>
                            <span class="badge <?php echo $row['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $row['is_active'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=events&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
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
