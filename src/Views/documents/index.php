<?php 
ob_start(); 
require_once __DIR__ . '/../../Helpers/Auth.php';
$title = 'Gestión de Documentos'; 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-folder-open"></i> Gestión de Documentos
        </h1>
        <p class="page-subtitle">Biblioteca de documentos compartidos</p>
    </div>
    <div class="page-actions">
        <?php if (Auth::hasPermission('documents_create')): ?>
            <a href="index.php?page=documents&action=create" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </a>
        <?php endif; ?>
    </div>
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

<!-- Estadísticas -->
<?php if (!empty($stats)): ?>
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <?php 
    $total_docs = 0;
    $total_size = 0;
    $total_downloads = 0;
    if (isset($stats[0])) {
        $total_docs = $stats[0]['total_documents'];
        $total_size = $stats[0]['total_size'];
        $total_downloads = $stats[0]['total_downloads'];
    }
    ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-file"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $total_docs; ?></div>
            <div class="stat-label">Documentos</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-hdd"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($total_size / (1024 * 1024), 1); ?> MB</div>
            <div class="stat-label">Almacenamiento</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-download"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $total_downloads; ?></div>
            <div class="stat-label">Descargas</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card filter-card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="documents">
        <div class="filter-row">
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" class="form-input" placeholder="Título o descripción..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label>Categoría</label>
                <select name="category_id" class="form-control">
                    <option value="">Todas</option>
                    <?php if (isset($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?> style="color:<?php echo htmlspecialchars($cat['color']); ?>;">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="index.php?page=documents" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista de documentos -->
<div class="documents-grid">
    <?php if (empty($documents)): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No hay documentos</h3>
                <p>Cuando se suban documentos, aparecerán aquí.</p>
                <?php if (Auth::hasPermission('documents_create')): ?>
                    <a href="index.php?page=documents&action=create" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-cloud-upload-alt"></i> Subir Primer Documento
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($documents as $doc): ?>
            <div class="document-card">
                <div class="document-icon">
                    <?php
                    $extension = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                    $icon_class = 'fa-file';
                    $icon_color = '#94a3b8';
                    
                    if (in_array($extension, ['pdf'])) {
                        $icon_class = 'fa-file-pdf';
                        $icon_color = '#ef4444';
                    } elseif (in_array($extension, ['doc', 'docx'])) {
                        $icon_class = 'fa-file-word';
                        $icon_color = '#3b82f6';
                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                        $icon_class = 'fa-file-excel';
                        $icon_color = '#10b981';
                    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $icon_class = 'fa-file-image';
                        $icon_color = '#f59e0b';
                    } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
                        $icon_class = 'fa-file-archive';
                        $icon_color = '#8b5cf6';
                    }
                    ?>
                    <i class="fas <?php echo $icon_class; ?>" style="color: <?php echo $icon_color; ?>;"></i>
                </div>
                
                <div class="document-content">
                    <h3 class="document-title"><?php echo htmlspecialchars($doc['title']); ?></h3>
                    <?php if (!empty($doc['category_name'])): ?>
                        <span class="badge" style="background:<?php echo htmlspecialchars($doc['category_color']); ?>;color:#fff;margin-bottom:0.5em;display:inline-block;">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($doc['category_name']); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($doc['description']): ?>
                        <p class="document-description"><?php echo htmlspecialchars($doc['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="document-meta">
                        <span class="document-meta-item">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                        </span>
                        <span class="document-meta-item">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($doc['created_at'])); ?>
                        </span>
                        <span class="document-meta-item">
                            <i class="fas fa-download"></i>
                            <?php echo $doc['downloads']; ?>
                        </span>
                        <span class="document-meta-item">
                            <i class="fas fa-hdd"></i>
                            <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                        </span>
                        <?php if (!$doc['is_public']): ?>
                            <span class="badge badge-warning">
                                <i class="fas fa-lock"></i> Privado
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="document-actions">
                    <a href="index.php?page=documents&action=download&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                    
                    <?php if ($doc['uploaded_by'] == $_SESSION['user_id'] || Auth::hasPermission('documents_edit')): ?>
                        <a href="index.php?page=documents&action=edit&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($doc['uploaded_by'] == $_SESSION['user_id'] || Auth::hasPermission('documents_delete')): ?>
                        <form method="POST" action="index.php?page=documents&action=delete" style="display: inline;" onsubmit="return confirm('¿Eliminar este documento permanentemente?');">
                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.document-card {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-500);
}

.document-icon {
    font-size: 3rem;
    text-align: center;
    padding: 1rem;
}

.document-content {
    flex: 1;
}

.document-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 0 0 0.5rem 0;
}

.document-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0 0 1rem 0;
    line-height: 1.5;
}

.document-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8125rem;
    color: var(--text-muted);
}

.document-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.document-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

[data-theme="dark"] .document-card {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(100, 116, 139, 0.3);
}

[data-theme="dark"] .document-card:hover {
    border-color: var(--primary-500);
    background: rgba(30, 41, 59, 0.7);
}

@media (max-width: 768px) {
    .documents-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>