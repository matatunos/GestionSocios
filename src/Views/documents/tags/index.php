<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-hashtag"></i> Gestión de Tags
        </h1>
        <p class="page-subtitle">Etiquetas para clasificar tus documentos</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=documents" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Documentos
        </a>
        <a href="index.php?page=document_tags&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Tag
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

<?php if (empty($tags)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-tags fa-4x"></i>
            <h3>No hay tags</h3>
            <p>Crea tu primer tag para clasificar tus documentos</p>
            <a href="index.php?page=document_tags&action=create" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Crear Primer Tag
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="tags-grid">
        <?php foreach ($tags as $tag): ?>
            <div class="tag-card">
                <div class="tag-header" style="background: <?php echo htmlspecialchars($tag['color']); ?>;">
                    <i class="fas fa-hashtag"></i>
                    <h3><?php echo htmlspecialchars($tag['name']); ?></h3>
                </div>
                <div class="tag-body">
                    <?php if ($tag['description']): ?>
                        <p class="tag-description"><?php echo htmlspecialchars($tag['description']); ?></p>
                    <?php endif; ?>
                    <div class="tag-stats">
                        <span class="badge badge-info">
                            <i class="fas fa-file"></i> <?php echo $tag['document_count']; ?> documentos
                        </span>
                        <span class="badge badge-secondary">
                            <i class="fas fa-chart-line"></i> <?php echo $tag['usage_count']; ?> usos
                        </span>
                    </div>
                </div>
                <div class="tag-actions">
                    <a href="index.php?page=documents&tag_id=<?php echo $tag['id']; ?>" 
                       class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Ver documentos
                    </a>
                    <a href="index.php?page=document_tags&action=edit&id=<?php echo $tag['id']; ?>" 
                       class="btn btn-sm btn-secondary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form method="POST" action="index.php?page=document_tags&action=delete" 
                          style="display:inline;" 
                          onsubmit="return confirm('¿Eliminar este tag? Se eliminará de todos los documentos.');">
                        <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
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
.tags-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.tag-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s;
}
.tag-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.tag-header {
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}
.tag-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}
.tag-body {
    padding: 1.5rem;
}
.tag-description {
    color: #64748b;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}
.tag-stats {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.tag-actions {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layout.php';
?>
