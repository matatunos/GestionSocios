<?php
// Encapsular la vista en el layout principal
ob_start();

// Helper para formatear acciones
function getActionBadge($action) {
    $colors = [
        'create' => 'success',
        'insert' => 'success',
        'update' => 'warning',
        'edit' => 'warning',
        'delete' => 'danger',
        'remove' => 'danger',
        'login' => 'info',
        'logout' => 'secondary',
        'export' => 'primary',
        'import' => 'primary'
    ];
    
    $icons = [
        'create' => 'fa-plus',
        'insert' => 'fa-plus',
        'update' => 'fa-edit',
        'edit' => 'fa-edit',
        'delete' => 'fa-trash',
        'remove' => 'fa-trash',
        'login' => 'fa-sign-in-alt',
        'logout' => 'fa-sign-out-alt',
        'export' => 'fa-file-export',
        'import' => 'fa-file-import'
    ];
    
    $key = strtolower($action);
    $color = $colors[$key] ?? 'secondary';
    $icon = $icons[$key] ?? 'fa-circle';
    
    return "<span class='badge badge-{$color}'><i class='fas {$icon}'></i> " . ucfirst($action) . "</span>";
}

// Helper para formatear detalles
function formatDetails($details) {
    if (empty($details)) return '<span class="text-muted">-</span>';
    
    // Intentar decodificar si es JSON
    $json = json_decode($details, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return '<button type="button" class="btn btn-sm btn-info" onclick=\'showDetails(' . json_encode($details) . ')\'><i class="fas fa-eye"></i> Ver detalles</button>';
    }
    
    // Si es texto largo, truncar
    if (strlen($details) > 50) {
        return '<span title="' . htmlspecialchars($details) . '">' . htmlspecialchars(substr($details, 0, 50)) . '...</span>';
    }
    
    return htmlspecialchars($details);
}
?>

<!-- Advanced Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="index.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
        <input type="hidden" name="page" value="audit_log">
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Usuario</label>
            <input type="text" name="user_id" class="form-control" placeholder="ID o Nombre" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Acción</label>
            <select name="action" class="form-control">
                <option value="">Todas</option>
                <option value="create" <?= ($_GET['action'] ?? '') === 'create' ? 'selected' : '' ?>>Crear</option>
                <option value="update" <?= ($_GET['action'] ?? '') === 'update' ? 'selected' : '' ?>>Actualizar</option>
                <option value="delete" <?= ($_GET['action'] ?? '') === 'delete' ? 'selected' : '' ?>>Eliminar</option>
                <option value="login" <?= ($_GET['action'] ?? '') === 'login' ? 'selected' : '' ?>>Login</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Entidad</label>
            <input type="text" name="entity" class="form-control" placeholder="Ej: members, books..." value="<?= htmlspecialchars($_GET['entity'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Desde</label>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.875rem;">Hasta</label>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </div>
        
        <div style="display: flex; gap: 0.5rem; align-items: end;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="index.php?page=audit_log" class="btn btn-secondary">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Results Summary & Header -->
<div class="flex justify-between items-center mb-4">
    <div>
        <h1 style="margin:0;">Auditoría</h1>
        <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
            <i class="fas fa-list-alt"></i> 
            <strong><?= $total ?></strong> registros encontrados
        </div>
    </div>
    <div class="btn-group">
        <a href="index.php?page=audit_log&action=export_excel" class="btn btn-secondary">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </a>
        <a href="index.php?page=audit_log&action=export_pdf" class="btn btn-secondary">
            <i class="fas fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Entidad / Ref.</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 2rem; color:var(--text-muted);">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        No hay registros de actividad que coincidan con los filtros.
                    </td>
                </tr>
                <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td style="white-space: nowrap;">
                        <i class="far fa-clock text-muted"></i> 
                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 24px; height: 24px; background: var(--primary-100); color: var(--primary-700); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <?= htmlspecialchars($log['username']) ?>
                        </div>
                    </td>
                    <td><?= getActionBadge($log['action']) ?></td>
                    <td>
                        <div style="font-weight: 500;"><?= htmlspecialchars(isset($log['entity']) ? $log['entity'] : '') ?></div>
                        <?php if (!empty($log['entity_id'])): ?>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Ref: #<?= htmlspecialchars($log['entity_id']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= formatDetails($log['details']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
    <div style="font-size: 0.875rem; color: var(--text-muted);">
        Mostrando <?= ($offset + 1) ?> - <?= min($offset + $limit, $total) ?> de <?= $total ?> registros
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <?php 
        // Construir query string base para los enlaces de paginación
        $queryParams = $_GET;
        unset($queryParams['page_num']);
        $queryString = http_build_query($queryParams);
        ?>
        
        <?php if ($page > 1): ?>
            <a href="index.php?<?= $queryString ?>&page_num=<?= $page - 1 ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
        <?php endif; ?>
        
        <div style="display: flex; gap: 0.25rem;">
            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1) {
                echo '<a href="index.php?' . $queryString . '&page_num=1" class="btn btn-sm btn-secondary">1</a>';
                if ($startPage > 2) echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
            }
            
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="index.php?<?= $queryString ?>&page_num=<?= $i ?>" 
                   class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>"
                   style="<?= $i == $page ? '' : 'background: white;' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; 
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                echo '<a href="index.php?' . $queryString . '&page_num=' . $totalPages . '" class="btn btn-sm btn-secondary">' . $totalPages . '</a>';
            }
            ?>
        </div>

        <?php if ($page < $totalPages): ?>
            <a href="index.php?<?= $queryString ?>&page_num=<?= $page + 1 ?>" class="btn btn-sm btn-secondary">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Detalles -->
<div id="detailsModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: var(--bg-card); padding: 2rem; border-radius: 0.5rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <span class="close" onclick="closeModal()" style="position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer;">&times;</span>
        <h3 class="mb-3">Detalles de la Actividad</h3>
        <pre id="jsonContent" style="background: var(--bg-body); padding: 1rem; border-radius: 0.25rem; overflow-x: auto; white-space: pre-wrap; font-size: 0.85rem;"></pre>
        <div class="mt-4 text-right">
            <button class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
function showDetails(details) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('jsonContent');
    
    try {
        // Intentar parsear JSON para formatearlo bonito
        const obj = JSON.parse(details);
        content.textContent = JSON.stringify(obj, null, 2);
    } catch (e) {
        // Si no es JSON, mostrar tal cual
        content.textContent = details;
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
