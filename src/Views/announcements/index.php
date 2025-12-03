<?php ob_start(); ?>

<div class="mb-4">
    <h1>Anuncios Públicos</h1>
    <p class="text-muted">Gestiona los anuncios que se muestran en la página de login</p>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header p-3">
        <a href="index.php?page=announcements&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Anuncio
        </a>
    </div>

    <?php if (empty($announcements)): ?>
        <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <p>No hay anuncios registrados</p>
            <a href="index.php?page=announcements&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear primer anuncio
            </a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Expira</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                    <th>Compartir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $ann): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo substr(htmlspecialchars($ann['content']), 0, 100); ?>...</small>
                        </td>
                        <td>
                            <?php
                            $badges = [
                                'info' => '<span class="badge badge-info"><i class="fas fa-info-circle"></i> Info</span>',
                                'warning' => '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Advertencia</span>',
                                'success' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Éxito</span>',
                                'danger' => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Urgente</span>'
                            ];
                            echo $badges[$ann['type']] ?? $badges['info'];
                            ?>
                        </td>
                        <td><?php echo $ann['priority']; ?></td>
                        <td>
                            <?php if ($ann['is_active']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ann['expires_at']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($ann['expires_at'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin expiración</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($ann['created_at'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=announcements&action=toggleActive&id=<?php echo $ann['id']; ?>" 
                                   class="btn btn-sm <?php echo $ann['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                   title="<?php echo $ann['is_active'] ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="fas fa-<?php echo $ann['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                </a>
                                <a href="index.php?page=announcements&action=edit&id=<?php echo $ann['id']; ?>" 
                                   class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="index.php?page=announcements&action=delete&id=<?php echo $ann['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este anuncio?');">
                                    <?php echo CsrfHelper::getTokenField(); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td>
                            <?php if ($ann['is_active']): ?>
                                <button class="btn btn-sm btn-info" 
                                        onclick="shareAnnouncement(<?php echo $ann['id']; ?>, '<?php echo addslashes($ann['title']); ?>')"
                                        title="Compartir">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="js/social-share.js"></script>
<script>
function shareAnnouncement(id, title) {
    const shareUrl = window.location.origin + window.location.pathname + '?page=announcements&id=' + id;
    const share = new SocialMediaShare({
        url: shareUrl,
        title: 'Anuncio: ' + title,
        description: 'Revisa este importante anuncio de nuestra asociación'
    });
    
    // Create modal for share buttons
    const modal = document.createElement('div');
    modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;';
    modal.innerHTML = '<div style="background:white;padding:2rem;border-radius:0.5rem;max-width:500px;width:90%;"><h3 style="margin-bottom:1rem;"><i class="fas fa-share-alt"></i> Compartir Anuncio</h3><div id="modal-share-buttons"></div><button onclick="this.parentElement.parentElement.remove()" style="margin-top:1rem;padding:0.5rem 1rem;background:#6c757d;color:white;border:none;border-radius:0.25rem;cursor:pointer;">Cerrar</button></div>';
    document.body.appendChild(modal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    share.initButtons('#modal-share-buttons', {
        platforms: ['facebook', 'twitter', 'whatsapp', 'telegram', 'copy'],
        size: 'medium',
        showLabels: true
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
