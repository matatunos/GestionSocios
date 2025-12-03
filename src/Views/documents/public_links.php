<?php 
require_once __DIR__ . '/../../Helpers/FileTypeHelper.php';
require_once __DIR__ . '/../../Helpers/DocumentViewHelper.php';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-share-alt"></i> Enlaces Públicos</h1>
        <p class="page-subtitle">Gestiona los documentos compartidos públicamente</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=documents" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Documentos
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (empty($documents)): ?>
    <div class="empty-state">
        <i class="fas fa-share-alt fa-4x"></i>
        <h3>No hay documentos públicos</h3>
        <p>Los documentos con enlaces públicos activos aparecerán aquí</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Enlace Público</th>
                    <th>Estado</th>
                    <th>Descargas</th>
                    <th>Expira</th>
                    <th>Creado por</th>
                    <th>Último acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION), 24); ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($doc['title']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($doc['file_name']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="text" 
                                       class="form-control public-url" 
                                       value="<?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/index.php?page=public_document&token=' . $doc['public_token']); ?>" 
                                       readonly>
                                <button class="btn btn-outline-secondary copy-url" type="button" title="Copiar enlace">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <?php if ($doc['status'] === 'active'): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php elseif ($doc['status'] === 'expired'): ?>
                                <span class="badge bg-warning">Expirado</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Límite alcanzado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($doc['download_stats']); ?></span>
                        </td>
                        <td>
                            <?php if ($doc['token_expires_at']): ?>
                                <?php 
                                $expiry = new DateTime($doc['token_expires_at']);
                                $now = new DateTime();
                                if ($expiry < $now) {
                                    echo '<span class="text-danger">' . $expiry->format('d/m/Y H:i') . '</span>';
                                } else {
                                    echo '<span>' . $expiry->format('d/m/Y H:i') . '</span>';
                                }
                                ?>
                            <?php else: ?>
                                <span class="text-muted">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                            <br><small class="text-muted"><?php echo DocumentViewHelper::timeAgo($doc['public_created_at']); ?></small>
                        </td>
                        <td>
                            <?php if ($doc['public_last_access']): ?>
                                <?php echo DocumentViewHelper::timeAgo($doc['public_last_access']); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin accesos</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=documents&action=public_stats&id=<?php echo $doc['id']; ?>" 
                                   class="btn btn-sm btn-info" title="Ver estadísticas">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger revoke-link" 
                                        data-id="<?php echo $doc['id']; ?>"
                                        title="Revocar enlace">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-state i {
        color: #ddd;
        margin-bottom: 20px;
    }
    .input-group {
        max-width: 400px;
    }
    .public-url {
        font-family: monospace;
        font-size: 0.85rem;
    }
    .d-flex {
        display: flex;
    }
    .align-items-center {
        align-items: center;
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>

<script>
// Copiar URL al portapapeles
document.querySelectorAll('.copy-url').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('.public-url');
        input.select();
        document.execCommand('copy');
        
        const icon = this.querySelector('i');
        icon.className = 'fas fa-check';
        setTimeout(() => {
            icon.className = 'fas fa-copy';
        }, 2000);
    });
});

// Revocar enlace
document.querySelectorAll('.revoke-link').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('¿Está seguro de revocar este enlace público? Ya no será accesible.')) {
            return;
        }
        
        const docId = this.getAttribute('data-id');
        const formData = new FormData();
        formData.append('id', docId);
        
        try {
            const response = await fetch('index.php?page=documents&action=revoke_public', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert('Error al revocar enlace: ' + (data.error || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
