<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-folder"></i> Gestión de Carpetas
        </h1>
        <p class="page-subtitle">Organiza tus documentos en carpetas jerárquicas</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=documents" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Documentos
        </a>
        <a href="index.php?page=document_folders&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Carpeta
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (empty($folders)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-folder-open fa-4x"></i>
            <h3>No hay carpetas</h3>
            <p>Crea tu primera carpeta para organizar tus documentos</p>
            <a href="index.php?page=document_folders&action=create" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Crear Primera Carpeta
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Ruta</th>
                        <th>Descripción</th>
                        <th>Documentos</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($folders as $folder): ?>
                        <tr>
                            <td>
                                <i class="fas fa-folder" style="color: #f59e0b; margin-right: 8px;"></i>
                                <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                            </td>
                            <td>
                                <code><?php echo htmlspecialchars($folder['path']); ?></code>
                            </td>
                            <td>
                                <?php if ($folder['description']): ?>
                                    <?php echo htmlspecialchars(substr($folder['description'], 0, 60)); ?>
                                    <?php if (strlen($folder['description']) > 60): ?>...<?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $folder['document_count']; ?> documentos
                                </span>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($folder['created_at']);
                                echo $date->format('d/m/Y');
                                ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="index.php?page=documents&folder_id=<?php echo $folder['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Ver documentos">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?page=document_folders&action=edit&id=<?php echo $folder['id']; ?>" 
                                       class="btn btn-sm btn-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($folder['document_count'] == 0): ?>
                                        <form method="POST" action="index.php?page=document_folders&action=delete" 
                                              style="display:inline;" 
                                              onsubmit="return confirm('¿Eliminar esta carpeta?');">
                                            <input type="hidden" name="id" value="<?php echo $folder['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #475569;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layout.php';
?>
