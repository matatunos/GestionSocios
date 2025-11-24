<?php ob_start(); ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

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
                <option value="0" <?php echo ($_GET['category_id'] ?? '') == '0' ? 'selected' : ''; ?>>Sin categoría</option>
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
                                    <span style="font-size: 0.85rem; color: var(--text-muted);">#<?php echo $row['id']; ?></span><br>
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
                                    <i class="fas fa-edit" title="Editar"></i>
                                </a>
                                
                                <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                <a href="index.php?page=map#member-<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Ver en mapa">
                                    <i class="fas fa-map-marker-alt"></i>
                                </a>
                                <?php endif; ?>
                                
                                <div style="display: inline-block; position: relative;">
                                    <button onclick="toggleCertMenu(<?php echo $row['id']; ?>)" class="btn btn-sm btn-info" style="background: #8b5cf6; border-color: #8b5cf6;">
                                        <i class="fas fa-certificate" title="Certificados"></i> <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                                    </button>
                                    <div id="certMenu<?php echo $row['id']; ?>" class="dropdown-menu" style="display: none; position: absolute; right: 0; background: white; border: 1px solid var(--border-light); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1000; min-width: 200px; margin-top: 0.25rem;">
                                        <a href="index.php?page=certificates&action=membership&id=<?php echo $row['id']; ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                            <i class="fas fa-id-card" style="width: 20px;"></i> Certificado de Socio
                                        </a>
                                        <a href="index.php?page=certificates&action=payments&id=<?php echo $row['id']; ?>&year=<?php echo date('Y'); ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                            <i class="fas fa-receipt" style="width: 20px;"></i> Certificado de Pagos <?php echo date('Y'); ?>
                                        </a>
                                        <a href="index.php?page=certificates&action=payments&id=<?php echo $row['id']; ?>&year=<?php echo date('Y') - 1; ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                            <i class="fas fa-receipt" style="width: 20px;"></i> Certificado de Pagos <?php echo date('Y') - 1; ?>
                                        </a>
                                    </div>
                                </div>
                                
                                <?php if (empty($row['has_paid_current_year'])): ?>
                                <?php
                                // Obtener la cuota anual y concepto para el año actual
                                $currentYear = date('Y');
                                $feeAmount = 0;
                                $feeConcept = 'Cuota Anual ' . $currentYear;
                                try {
                                    $feeStmt = $GLOBALS['db']->prepare("SELECT amount FROM annual_fees WHERE year = ?");
                                    $feeStmt->execute([$currentYear]);
                                    $feeRow = $feeStmt->fetch(PDO::FETCH_ASSOC);
                                    if ($feeRow) {
                                        $feeAmount = $feeRow['amount'];
                                    }
                                } catch (Exception $e) {}
                                ?>
                                <a href="index.php?page=members&action=markPaid&id=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-primary"
                                   onclick="return confirm('¿Marcar la cuota de <?php echo $currentYear; ?> como pagada para este socio?\nImporte: <?php echo number_format($feeAmount, 2); ?> €\nConcepto: <?php echo $feeConcept; ?>');">
                                    <i class="fas fa-check"></i> Marcar como Pagado
                                </a>
                                <?php else: ?>
                                <span class="badge" style="background: #10b981; color: white; padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-check-circle"></i> Al día <?php echo date('Y'); ?>
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($row['status'] === 'active'): ?>
                                    <a href="index.php?page=members&action=deactivate&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Dar de Baja"
                                       onclick="return confirm('¿Dar de baja a este socio?\n\nEl socio pasará a estado inactivo pero se conservarán todos sus datos.');">
                                        <i class="fas fa-user-slash"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="index.php?page=members&action=delete&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   title="Eliminar"
                                   onclick="return confirm('⚠️ ¿ELIMINAR PERMANENTEMENTE a este socio?\n\nEsta acción NO se puede deshacer.\nSe eliminará el socio pero se conservarán sus pagos asociados.');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
    <div style="font-size: 0.875rem; color: var(--text-muted);">
        Mostrando <?php echo ($offset + 1); ?> - <?php echo min($offset + $limit, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <?php if ($page > 1): ?>
            <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $page - 1])); ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
        <?php endif; ?>
        
        <div style="display: flex; gap: 0.25rem;">
            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1) {
                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
            }
            
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $i])); ?>" 
                   class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="<?php echo $i === $page ? '' : 'background: white;'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; 
            
            if ($endPage < $totalPages) {
                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
            }
            ?>
        </div>

        <?php if ($page < $totalPages): ?>
            <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $page + 1])); ?>" class="btn btn-sm btn-secondary">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
.dropdown-item:hover {
    background: var(--primary-50) !important;
    color: var(--primary-700) !important;
}
</style>

<script>
function toggleExportDropdown() {
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function toggleCertMenu(memberId) {
    const menu = document.getElementById('certMenu' + memberId);
    // Close all other cert menus first
    document.querySelectorAll('[id^="certMenu"]').forEach(m => {
        if (m.id !== 'certMenu' + memberId) {
            m.style.display = 'none';
        }
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdown');
    const menu = document.getElementById('exportMenu');
    
    if (menu && dropdown && !dropdown.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
    
    // Close certificate menus when clicking outside
    if (!e.target.closest('[onclick^="toggleCertMenu"]') && !e.target.closest('[id^="certMenu"]')) {
        document.querySelectorAll('[id^="certMenu"]').forEach(m => {
            m.style.display = 'none';
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
