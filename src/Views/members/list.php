<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deactivated') echo 'Socio dado de baja correctamente.';
            else if ($_GET['msg'] === 'deleted') echo 'Socio eliminado correctamente.';
            else if ($_GET['msg'] === 'marked_paid') echo 'Cuota marcada como pagada correctamente.';
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php
            if ($_GET['error'] === 'no_fee') echo 'No hay cuota definida para el año actual. Por favor, defínela primero en la sección de Cuotas.';
            else echo 'Error al procesar la operación.';
        ?>
    </div>
<?php endif; ?>

<!-- Advanced Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="index.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
        <input type="hidden" name="page" value="members">
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Buscar</label>
            <input type="text" name="search" class="form-control" placeholder="Nombre, email, teléfono..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Estado</label>
            <select name="status" class="form-control">
                <option value="">Todos</option>
                <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activos</option>
                <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Categoría</label>
            <select name="category_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Estado de Pago</label>
            <select name="payment_status" class="form-control">
                <option value="">Todos</option>
                <option value="current" <?php echo ($_GET['payment_status'] ?? '') === 'current' ? 'selected' : ''; ?>>Al Corriente</option>
                <option value="delinquent" <?php echo ($_GET['payment_status'] ?? '') === 'delinquent' ? 'selected' : ''; ?>>Pendientes</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Año Alta (desde)</label>
            <input type="number" name="year_from" class="form-control" min="2000" max="<?php echo date('Y'); ?>" placeholder="Ej: 2020" value="<?php echo htmlspecialchars($_GET['year_from'] ?? ''); ?>">
        </div>
        
        <div style="display: flex; gap: 0.5rem; align-items: end;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="index.php?page=members" class="btn btn-secondary">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Results Summary -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-body); border-radius: var(--radius-md);">
    <div style="font-size: 0.875rem; color: var(--text-muted);">
        <i class="fas fa-users"></i> 
        <strong><?php echo count($members); ?></strong> socio(s) encontrado(s)
        <?php if (!empty(array_filter($_GET, fn($v, $k) => $k !== 'page' && !empty($v), ARRAY_FILTER_USE_BOTH))): ?>
            <span style="color: var(--primary-600);">(filtrado)</span>
        <?php endif; ?>
    </div>
    <a href="index.php?page=members&action=create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo Socio
    </a>
</div>

<div class="flex justify-between items-center mb-4">
    <h1>Listado de Socios</h1>
    <div class="btn-group">
        <div class="dropdown" style="display: inline-block; position: relative; margin-right: 0.5rem;">
            <button class="btn btn-secondary dropdown-toggle" id="exportDropdown" onclick="toggleExportDropdown()">
                <i class="fas fa-download"></i> Exportar
            </button>
            <div class="dropdown-menu" id="exportMenu" style="display: none;">
                <a href="index.php?page=export&action=members_excel" class="dropdown-item">
                    <i class="fas fa-file-excel"></i> Excel (CSV)
                </a>
                <a href="index.php?page=export&action=members_pdf" class="dropdown-item" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF (Imprimir)
                </a>
            </div>
        </div>
        <a href="index.php?page=members&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Socio
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>Contacto</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay socios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $row): ?>
                        <tr>
                            <td style="font-weight: 500; display: flex; align-items: center; gap: 1rem;">
                                <?php if (!empty($row['photo_url'])): ?>
                                    <img src="/<?php echo htmlspecialchars($row['photo_url']); ?>" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
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
                                <?php if (!empty($row['category_name'])): ?>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($row['category_color'] ?? '#6b7280'); ?>; color: white;">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.875rem;">Sin categoría</span>
                                <?php endif; ?>
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
                                
                                <a href="index.php?page=members&action=markPaid&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary"
                                   onclick="return confirm('¿Marcar la cuota de <?php echo date('Y'); ?> como pagada para este socio?');">
                                    <i class="fas fa-check"></i> Marcar como Pagado
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

<script>
function toggleExportDropdown() {
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdown');
    const menu = document.getElementById('exportMenu');
    
    if (menu && dropdown && !dropdown.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
