<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'Donante eliminado correctamente.';
        ?>
    </div>
    <link rel="stylesheet" href="/css/listings.css?v=1">
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
                                <div class="listing-actions">
                                    <?php if (!empty($row['logo_url'])): ?>
                                        <a href="/<?php echo htmlspecialchars($row['logo_url']); ?>" target="_blank" class="btn btn-sm btn-warning" title="Ver Logo">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="index.php?page=donors&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit" title="Editar"></i>
                                    </a>
                                    
                                    <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                        <a href="index.php?page=map#donor-<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Ver en mapa">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div style="display: inline-block; position: relative;">
                                        <button onclick="toggleDonorCertMenu(<?php echo $row['id']; ?>)" class="btn btn-sm btn-info" style="background: #8b5cf6; border-color: #8b5cf6;">
                                            <i class="fas fa-award" title="Certificados"></i> <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                                        </button>
                                        <div id="donorCertMenu<?php echo $row['id']; ?>" class="dropdown-menu" style="display: none; position: absolute; right: 0; background: white; border: 1px solid var(--border-light); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1000; min-width: 220px; margin-top: 0.25rem;">
                                            <a href="index.php?page=certificates&action=donations&id=<?php echo $row['id']; ?>&year=<?php echo date('Y'); ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                                <i class="fas fa-hand-holding-heart" style="width: 20px;"></i> Certificado Donaciones <?php echo date('Y'); ?>
                                            </a>
                                            <a href="index.php?page=certificates&action=donations&id=<?php echo $row['id']; ?>&year=<?php echo date('Y') - 1; ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                                <i class="fas fa-hand-holding-heart" style="width: 20px;"></i> Certificado Donaciones <?php echo date('Y') - 1; ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <a href="index.php?page=donors&action=delete&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar este donante?');">
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
function toggleDonorCertMenu(donorId) {
    const menu = document.getElementById('donorCertMenu' + donorId);
    // Close all other cert menus first
    document.querySelectorAll('[id^="donorCertMenu"]').forEach(m => {
        if (m.id !== 'donorCertMenu' + donorId) {
            m.style.display = 'none';
        }
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close certificate menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick^="toggleDonorCertMenu"]') && !e.target.closest('[id^="donorCertMenu"]')) {
        document.querySelectorAll('[id^="donorCertMenu"]').forEach(m => {
            m.style.display = 'none';
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
