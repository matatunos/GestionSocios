<?php
$pageTitle = 'Proveedores';
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="80">Logo</th>
                    <th>Nombre / Razón Social</th>
                    <th>CIF/NIF</th>
                    <th>Contacto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-truck text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p class="text-muted">No hay proveedores registrados</p>
                                <a href="index.php?page=suppliers&action=create" class="btn btn-sm btn-primary mt-2">
                                    Crear primer proveedor
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td>
                                <?php if (!empty($supplier['logo_path']) && file_exists($supplier['logo_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($supplier['logo_path']); ?>" 
                                         alt="Logo" 
                                         style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid var(--border-light);">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--bg-light); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                        <i class="fas fa-building"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($supplier['name']); ?></div>
                                <?php if (!empty($supplier['website'])): ?>
                                    <a href="<?php echo htmlspecialchars($supplier['website']); ?>" target="_blank" class="text-sm text-primary">
                                        <i class="fas fa-external-link-alt"></i> Web
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($supplier['cif_nif']); ?></td>
                            <td>
                                <?php if (!empty($supplier['email'])): ?>
                                    <div><i class="fas fa-envelope text-muted mr-1"></i> <a href="mailto:<?php echo htmlspecialchars($supplier['email']); ?>"><?php echo htmlspecialchars($supplier['email']); ?></a></div>
                                <?php endif; ?>
                                <?php if (!empty($supplier['phone'])): ?>
                                    <div><i class="fas fa-phone text-muted mr-1"></i> <?php echo htmlspecialchars($supplier['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="index.php?page=suppliers&action=show&id=<?php echo $supplier['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Ver detalles y facturas">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?page=suppliers&action=edit&id=<?php echo $supplier['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?page=suppliers&action=delete&id=<?php echo $supplier['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Estás seguro? Se eliminarán también todas las facturas asociadas.')"
                                       title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
