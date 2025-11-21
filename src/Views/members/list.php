<?php ob_start(); ?>

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
