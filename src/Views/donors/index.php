<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'Donante eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-4">
    <h1>Listado de Donantes</h1>
    <a href="index.php?page=donors&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Donante
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donors)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay donantes registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donors as $row): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['contact_person']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['email']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if (!empty($row['logo_url'])): ?>
                                    <a href="/<?php echo htmlspecialchars($row['logo_url']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="Ver Logo">
                                        <i class="fas fa-image"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?page=donors&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="index.php?page=donors&action=delete&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este donante?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
