<?php 
require_once __DIR__ . '/../../Helpers/FileTypeHelper.php';
require_once __DIR__ . '/../../Helpers/FileUploadHelper.php';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-trash"></i> Papelera de Documentos</h1>
        <p class="page-subtitle">Documentos eliminados que pueden ser restaurados</p>
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
        <i class="fas fa-trash fa-4x"></i>
        <h3>No hay documentos en la papelera</h3>
        <p>Los documentos eliminados aparecerán aquí</p>
    </div>
<?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Eliminado por</th>
                            <th>Fecha de eliminación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td class="text-center">
                                    <?php echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION)); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($doc['title']); ?></strong>
                                    <?php if ($doc['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($doc['description'], 0, 100)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                                <td>
                                    <?php echo FileUploadHelper::formatBytes($doc['file_size']); ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($doc['deleted_by_first_name'])) {
                                        echo htmlspecialchars($doc['deleted_by_first_name'] . ' ' . $doc['deleted_by_last_name']);
                                    } else {
                                        echo 'Desconocido';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $date = new DateTime($doc['deleted_at']);
                                    echo $date->format('d/m/Y H:i');
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form method="POST" action="index.php?page=documents&action=restore" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Restaurar">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="index.php?page=documents&action=permanent_delete" 
                                              onsubmit="return confirm('¿Está seguro de eliminar permanentemente este documento? Esta acción no se puede deshacer.');"
                                              style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar permanentemente">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
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
    .btn-group {
        display: flex;
        gap: 5px;
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
