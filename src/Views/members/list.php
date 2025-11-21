<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deactivated') echo 'Socio dado de baja correctamente.';
            else if ($_GET['msg'] === 'deleted') echo 'Socio eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        Error al procesar la operación.
    </div>
<?php endif; ?>

<div class="filter-tabs" style="margin-bottom: 1.5rem;">
    <a href="index.php?page=members&filter=all" 
       class="filter-tab <?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Todos
    </a>
    <a href="index.php?page=members&filter=current" 
       class="filter-tab <?php echo ($_GET['filter'] ?? '') === 'current' ? 'active' : ''; ?>">
        <i class="fas fa-check-circle"></i> Al Corriente
    </a>
    <a href="index.php?page=members&filter=delinquent" 
       class="filter-tab <?php echo ($_GET['filter'] ?? '') === 'delinquent' ? 'active' : ''; ?>">
        <i class="fas fa-exclamation-triangle"></i> Morosos
    </a>
</div>

<div class="flex justify-between items-center mb-4">
    <h1>Listado de Socios</h1>
    <a href="index.php?page=members&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Socio
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay socios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $row): ?>
                        <tr>
                            <td style="font-weight: 500; display: flex; align-items: center; gap: 1rem;">
                                <?php if (!empty($row['photo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['photo_url']); ?>" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: var(--primary-100); color: var(--primary-700); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.875rem;">
                                    <i class="fas fa-envelope" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($row['email']); ?>
                                </div>
                                <div style="font-size: 0.875rem; margin-top: 0.25rem;">
                                    <i class="fas fa-phone" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($row['phone']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $row['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $row['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="index.php?page=members&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <?php if ($row['status'] === 'active'): ?>
                                    <a href="index.php?page=members&action=deactivate&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('¿Dar de baja a este socio?\n\nEl socio pasará a estado inactivo pero se conservarán todos sus datos.');">
                                        <i class="fas fa-user-slash"></i> Dar de Baja
                                    </a>
                                <?php endif; ?>
                                
                                <a href="index.php?page=members&action=delete&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('⚠️ ¿ELIMINAR PERMANENTEMENTE a este socio?\n\nEsta acción NO se puede deshacer.\nSe eliminará el socio pero se conservarán sus pagos asociados.');">
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
