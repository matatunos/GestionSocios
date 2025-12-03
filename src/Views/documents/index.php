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
        <div class="btn-group" style="margin-right: 10px;">
            <button type="button" class="btn btn-secondary" id="viewGridBtn" onclick="setView('grid')" title="Vista en cuadrícula">
                <i class="fas fa-th"></i>
            </button>
            <button type="button" class="btn btn-secondary" id="viewListBtn" onclick="setView('list')" title="Vista en lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
        <?php if (Auth::hasPermission('documents_create')): ?>
            <a href="index.php?page=documents&action=create" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento(s)
            </a>
        <?php endif; ?>
        <a href="index.php?page=documents&action=favorites" class="btn btn-warning" style="margin-left:0.5em;">
            <i class="fas fa-star"></i> Favoritos
        </a>
        <a href="index.php?page=documents&action=trash" class="btn btn-secondary" style="margin-left:0.5em;">
            <i class="fas fa-trash"></i> Papelera
        </a>
        <a href="index.php?page=document_categories" class="btn btn-secondary" style="margin-left:0.5em;">
            <i class="fas fa-tags"></i> Categorías
        </a>
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
<div class="documents-grid" id="documentsGrid">
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
                        <span class="document-meta-item" title="Descargas totales">
                            <i class="fas fa-download"></i>
                            <?php echo $doc['downloads']; ?>
                        </span>
                        <?php if ($doc['public_enabled'] && $doc['public_token']): ?>
                        <span class="document-meta-item" title="Descargas públicas" style="color: #10b981;">
                            <i class="fas fa-globe"></i>
                            <?php 
                                echo $doc['public_downloads'];
                                if ($doc['public_download_limit'] !== null) {
                                    echo '/' . $doc['public_download_limit'];
                                } else {
                                    echo '/∞';
                                }
                            ?>
                        </span>
                        <?php endif; ?>
                        <span class="document-meta-item">
                            <i class="fas fa-hdd"></i>
                            <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                        </span>
                        <?php if (!$doc['is_public']): ?>
                            <span class="badge badge-warning">
                                <i class="fas fa-lock"></i> Privado
                            </span>
                        <?php endif; ?>
                        <?php if ($doc['public_enabled'] && $doc['public_token']): ?>
                            <span class="badge badge-success" title="Enlace público activo">
                                <i class="fas fa-link"></i> Público
                            </span>
                        <?php endif; ?>
                        <?php if (isset($doc['tags']) && !empty($doc['tags'])): ?>
                            <span class="document-tags" style="display: inline-flex; gap: 4px; align-items: center; margin-left: 8px;">
                                <?php foreach ($doc['tags'] as $tag): ?>
                                    <span class="tag-dot" 
                                          style="
                                              display: inline-block;
                                              width: 10px;
                                              height: 10px;
                                              border-radius: 50%;
                                              background-color: <?php echo htmlspecialchars($tag['color']); ?>;
                                              border: 2px solid <?php echo htmlspecialchars($tag['color']); ?>;
                                              cursor: help;
                                          "
                                          title="<?php echo htmlspecialchars($tag['name']); ?>: <?php echo htmlspecialchars($tag['description'] ?? ''); ?>">
                                    </span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="document-actions">
                    <a href="index.php?page=documents&action=download&id=<?php echo $doc['id']; ?>" 
                       class="btn btn-sm btn-primary" title="Descargar">
                        <i class="fas fa-download"></i>
                    </a>
                    
                    <a href="index.php?page=documents&action=preview&id=<?php echo $doc['id']; ?>" 
                       class="btn btn-sm btn-secondary" target="_blank" title="Vista previa">
                        <i class="fas fa-eye"></i>
                    </a>
                    
                    <button type="button" class="btn btn-sm btn-info favorite-btn" 
                            data-id="<?php echo $doc['id']; ?>" 
                            title="Favorito">
                        <i class="far fa-star"></i>
                    </button>
                    
                    <?php if ($doc['public_enabled'] && $doc['public_token']): ?>
                    <button type="button" class="btn btn-sm btn-success copy-public-link-btn" 
                            data-token="<?php echo $doc['public_token']; ?>" 
                            title="Copiar enlace público">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger revoke-public-link-btn" 
                            data-id="<?php echo $doc['id']; ?>" 
                            title="Cancelar enlace público">
                        <i class="fas fa-unlink"></i>
                    </button>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-sm btn-success share-public-btn" 
                            data-id="<?php echo $doc['id']; ?>" 
                            data-title="<?php echo htmlspecialchars($doc['title']); ?>"
                            data-has-public="<?php echo ($doc['public_enabled'] && $doc['public_token']) ? '1' : '0'; ?>"
                            title="Compartir públicamente">
                        <i class="fas fa-share-alt"></i>
                    </button>
                    
                    <a href="index.php?page=documents&action=versions&id=<?php echo $doc['id']; ?>" 
                       class="btn btn-sm btn-warning" title="Versiones">
                        <i class="fas fa-history"></i>
                    </a>
                    
                    <?php if ($doc['uploaded_by'] == $_SESSION['user_id'] || Auth::hasPermission('documents_edit')): ?>
                        <a href="index.php?page=documents&action=edit&id=<?php echo $doc['id']; ?>" 
                           class="btn btn-sm btn-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($doc['uploaded_by'] == $_SESSION['user_id'] || Auth::hasPermission('documents_delete')): ?>
                        <form method="POST" action="index.php?page=documents&action=delete" 
                              style="display: inline;" class="delete-document"
                              onsubmit="return confirm('¿Mover este documento a la papelera?');">
                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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

.favorite-btn.favorited i {
    color: #fbbf24;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-help {
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.result-box {
    background: #f1f5f9;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.result-url {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.result-url input {
    flex: 1;
    font-family: monospace;
    font-size: 0.875rem;
}
</style>

<!-- Modal para generar enlace público -->
<div id="sharePublicModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-share-alt"></i> Compartir Públicamente</h2>
            <button class="modal-close" onclick="closeShareModal()">&times;</button>
        </div>
        
        <form id="sharePublicForm">
            <input type="hidden" id="share_doc_id" name="id">
            
            <div class="form-group">
                <label>Documento:</label>
                <div id="share_doc_title" style="font-weight:normal; color:#64748b;"></div>
            </div>
            
            <div class="form-group">
                <label for="expires_days">Expiración (días)</label>
                <select class="form-control" id="expires_days" name="expires_days">
                    <option value="">Sin expiración</option>
                    <option value="1">1 día</option>
                    <option value="7" selected>7 días</option>
                    <option value="30">30 días</option>
                    <option value="90">90 días</option>
                </select>
                <div class="form-help">Tiempo hasta que el enlace deje de funcionar</div>
            </div>
            
            <div class="form-group">
                <label for="download_limit">Límite de descargas</label>
                <input type="number" class="form-control" id="download_limit" name="download_limit" min="1" placeholder="Ilimitado">
                <div class="form-help">Número máximo de descargas permitidas (dejar vacío para ilimitado)</div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-link"></i> Generar Enlace Público
                </button>
            </div>
            
            <div id="shareResult" style="display:none;">
                <div class="result-box">
                    <strong><i class="fas fa-check-circle" style="color:#10b981;"></i> Enlace generado correctamente</strong>
                    <div class="result-url">
                        <input type="text" id="public_url" class="form-control" readonly>
                        <button type="button" class="btn btn-secondary" onclick="copyPublicUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="form-help" style="margin-top:0.5rem;">
                        Comparte este enlace con las personas que necesiten acceder al documento
                    </div>
                    <div style="margin-top:1rem;">
                        <button type="button" class="btn btn-success" onclick="closeShareModal()" style="width:100%;">
                            <i class="fas fa-check"></i> Listo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="js/documents.js"></script>
<script>
// Copiar enlace público directamente
document.querySelectorAll('.copy-public-link-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const token = this.getAttribute('data-token');
        const protocol = window.location.protocol;
        const host = window.location.host;
        const publicUrl = `${protocol}//${host}/public/index.php?page=public_document&token=${token}`;
        
        // Copiar al portapapeles
        navigator.clipboard.writeText(publicUrl).then(() => {
            const icon = this.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-check';
            this.style.backgroundColor = '#10b981';
            
            setTimeout(() => {
                icon.className = originalClass;
                this.style.backgroundColor = '';
            }, 2000);
        }).catch(err => {
            console.error('Error al copiar:', err);
            alert('No se pudo copiar el enlace');
        });
    });
});

// Revocar enlace público
document.querySelectorAll('.revoke-public-link-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('¿Estás seguro de que deseas cancelar el enlace público? El enlace actual dejará de funcionar.')) {
            return;
        }
        
        const docId = this.getAttribute('data-id');
        const icon = this.querySelector('i');
        const originalClass = icon.className;
        
        icon.className = 'fas fa-spinner fa-spin';
        this.disabled = true;
        
        try {
            const response = await fetch('index.php?page=documents&action=revoke_public', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${docId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Recargar la página para actualizar la vista
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Error desconocido'));
                icon.className = originalClass;
                this.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al revocar el enlace');
            icon.className = originalClass;
            this.disabled = false;
        }
    });
});

// Abrir modal para compartir
document.querySelectorAll('.share-public-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const docId = this.getAttribute('data-id');
        const docTitle = this.getAttribute('data-title');
        const hasPublic = this.getAttribute('data-has-public') === '1';
        
        document.getElementById('share_doc_id').value = docId;
        document.getElementById('share_doc_title').textContent = docTitle;
        document.getElementById('sharePublicForm').reset();
        document.getElementById('share_doc_id').value = docId; // Restaurar después del reset
        document.getElementById('expires_days').value = '7'; // Valor por defecto
        
        // Si ya tiene enlace público, obtenerlo y mostrarlo
        const modalTitle = document.querySelector('#sharePublicModal .modal-header h2');
        const submitBtn = document.querySelector('#sharePublicForm button[type="submit"]');
        
        if (hasPublic) {
            // Intentar generar el enlace sin regenerate para obtener el existente
            const formData = new FormData();
            formData.append('id', docId);
            
            try {
                const response = await fetch('index.php?page=documents&action=generate_public', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.already_exists && data.existing_url) {
                    // Mostrar el enlace existente
                    document.getElementById('public_url').value = data.existing_url;
                    document.getElementById('shareResult').style.display = 'block';
                    if (modalTitle) {
                        modalTitle.innerHTML = '<i class="fas fa-link"></i> Enlace Público Existente';
                    }
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-redo"></i> Regenerar Enlace';
                    }
                } else {
                    // No hay enlace o hubo error
                    document.getElementById('shareResult').style.display = 'none';
                    if (modalTitle) {
                        modalTitle.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
                    }
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
                    }
                }
            } catch (error) {
                console.error('Error al verificar enlace:', error);
                document.getElementById('shareResult').style.display = 'none';
            }
        } else {
            // No tiene enlace público
            document.getElementById('shareResult').style.display = 'none';
            if (modalTitle) {
                modalTitle.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
            }
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
            }
        }
        
        document.getElementById('sharePublicModal').classList.add('show');
    });
});

function closeShareModal() {
    const modal = document.getElementById('sharePublicModal');
    const submitBtn = document.querySelector('#sharePublicForm button[type="submit"]');
    
    // Si se generó o regeneró un enlace, recargar la página
    if (submitBtn && (submitBtn.innerHTML.includes('Enlace Generado') || 
                      submitBtn.innerHTML.includes('Enlace Regenerado'))) {
        location.reload();
    } else {
        modal.classList.remove('show');
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('sharePublicModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeShareModal();
    }
});

// Generar enlace público
document.getElementById('sharePublicForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const isRegenerating = submitBtn.innerHTML.includes('Regenerar');
    
    // Si el botón dice "Regenerar", forzar regeneración
    if (isRegenerating) {
        formData.append('regenerate', 'true');
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (isRegenerating ? 'Regenerando...' : 'Generando...');
    
    try {
        const response = await fetch('index.php?page=documents&action=generate_public', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('public_url').value = data.url;
            document.getElementById('shareResult').style.display = 'block';
            submitBtn.innerHTML = '<i class="fas fa-check"></i> ' + (isRegenerating ? 'Enlace Regenerado' : 'Enlace Generado');
            submitBtn.disabled = false;
        } else if (data.already_exists && !isRegenerating) {
            // Ya existe un enlace público y no estábamos intentando regenerar
            // Mostrar el enlace existente directamente sin preguntar
            document.getElementById('public_url').value = data.existing_url;
            document.getElementById('shareResult').style.display = 'block';
            submitBtn.innerHTML = '<i class="fas fa-redo"></i> Regenerar Enlace';
            submitBtn.disabled = false;
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al generar el enlace');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-link"></i> Generar Enlace Público';
    }
});

function copyPublicUrl() {
    const input = document.getElementById('public_url');
    input.select();
    document.execCommand('copy');
    
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    icon.className = 'fas fa-check';
    setTimeout(() => {
        icon.className = 'fas fa-copy';
    }, 2000);
}

// Sistema de vistas (Grid/List)
function setView(viewType) {
    const grid = document.getElementById('documentsGrid');
    const gridBtn = document.getElementById('viewGridBtn');
    const listBtn = document.getElementById('viewListBtn');
    
    if (viewType === 'grid') {
        grid.classList.remove('documents-list');
        grid.classList.add('documents-grid');
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        localStorage.setItem('documentsView', 'grid');
    } else {
        grid.classList.remove('documents-grid');
        grid.classList.add('documents-list');
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
        localStorage.setItem('documentsView', 'list');
    }
}

// Restaurar vista guardada
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('documentsView') || 'grid';
    setView(savedView);
});
</script>

<style>
/* Vista en lista */
.documents-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.documents-list .document-card {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 15px 20px;
    margin-bottom: 0;
    border-radius: 0;
    border-bottom: 1px solid #e5e7eb;
}

.documents-list .document-card:first-child {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.documents-list .document-card:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    border-bottom: none;
}

.documents-list .document-icon {
    width: 50px;
    min-width: 50px;
    height: 50px;
    margin-right: 20px;
}

.documents-list .document-icon i {
    font-size: 24px;
}

.documents-list .document-content {
    flex: 1;
    min-width: 0;
}

.documents-list .document-title {
    font-size: 16px;
    margin-bottom: 8px;
}

.documents-list .document-description {
    font-size: 13px;
    max-height: none;
    -webkit-line-clamp: 2;
    margin-bottom: 10px;
}

.documents-list .document-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 13px;
}

.documents-list .document-actions {
    display: flex;
    flex-direction: row;
    gap: 8px;
    margin-left: 20px;
}

.btn-group {
    display: inline-flex;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.btn-group .btn {
    border-radius: 0;
    border-right: 1px solid rgba(255,255,255,0.2);
    margin: 0;
}

.btn-group .btn:last-child {
    border-right: none;
}

.btn-group .btn.active {
    background: var(--primary-600);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .documents-list .document-card {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .documents-list .document-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .documents-list .document-actions {
        margin-left: 0;
        margin-top: 15px;
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>