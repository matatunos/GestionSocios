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
                                
                                <div style="display: inline-block; position: relative;">
                                    <button onclick="toggleDonorCertMenu(<?php echo $row['id']; ?>)" class="btn btn-sm btn-info" style="background: #8b5cf6; border-color: #8b5cf6;">
                                        <i class="fas fa-certificate"></i> Certificados <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
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
