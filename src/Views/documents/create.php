<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=documents" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1><i class="fas fa-cloud-upload-alt"></i> Subir Documento(s)</h1>
    <p class="text-muted">Sube uno o varios documentos a la vez</p>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="uploadForm" method="POST" action="index.php?page=documents&action=store" enctype="multipart/form-data">
        <?php 
        require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; 
        // Asegurar que hay sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        echo CsrfHelper::getTokenField(); 
        ?>
        
        <div class="form-group mb-3">
            <label for="category_ids" class="form-label">Categorías</label>
            <select name="category_ids[]" id="category_ids" class="form-control" multiple>
                <?php if (isset($categories) && is_array($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" style="color:<?php echo htmlspecialchars($cat['color']); ?>;">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <small class="text-muted">Puedes seleccionar varias categorías usando Ctrl o Shift.</small>
        </div>
        
        <div class="form-group mb-3">
            <label for="folder_id" class="form-label">Carpeta</label>
            <select name="folder_id" id="folder_id" class="form-control">
                <option value="">Raíz (sin carpeta)</option>
                <?php
                // Obtener carpetas si existen
                require_once __DIR__ . '/../../Models/Document.php';
                $docModel = new Document($GLOBALS['db'] ?? null);
                $folders = $docModel->getFolders();
                foreach ($folders as $folder):
                ?>
                    <option value="<?php echo $folder['id']; ?>">
                        <?php echo str_repeat('&nbsp;&nbsp;', substr_count($folder['path'], '/') - 1); ?>
                        <?php echo htmlspecialchars($folder['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group mb-3">
            <label for="status" class="form-label">Estado</label>
            <select name="status" id="status" class="form-control">
                <option value="published">Publicado</option>
                <option value="draft">Borrador</option>
                <option value="archived">Archivado</option>
            </select>
        </div>
        
        <div class="form-group mb-3" id="singleFileMetadata" style="display:none;">
            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" maxlength="255" placeholder="Título descriptivo del documento">
            <small class="text-muted">Solo para un archivo. Si subes varios, se usará el nombre de cada archivo.</small>
        </div>

        <div class="form-group mb-3" id="singleFileDescription" style="display:none;">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Descripción detallada del contenido"></textarea>
            <small class="text-muted">Solo para un archivo.</small>
        </div>

        <div class="form-group mb-3">
            <label for="tags" class="form-label">Etiquetas</label>
            <div class="tags-selector">
                <?php if (isset($tags) && is_array($tags)): ?>
                    <?php foreach ($tags as $tag): ?>
                        <label class="tag-checkbox" style="display: inline-block; margin: 5px;">
                            <input type="checkbox" name="tag_ids[]" value="<?php echo $tag['id']; ?>" style="display: none;">
                            <span class="tag-badge" style="
                                display: inline-block;
                                padding: 6px 12px;
                                background: <?php echo htmlspecialchars($tag['color']); ?>22;
                                border: 2px solid <?php echo htmlspecialchars($tag['color']); ?>;
                                color: <?php echo htmlspecialchars($tag['color']); ?>;
                                border-radius: 20px;
                                font-size: 13px;
                                font-weight: 500;
                                cursor: pointer;
                                transition: all 0.2s;
                            " data-color="<?php echo htmlspecialchars($tag['color']); ?>">
                                <i class="fas fa-tag" style="margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No hay etiquetas disponibles. <a href="index.php?page=document_tags">Crear etiquetas</a></p>
                <?php endif; ?>
            </div>
            <small class="text-muted">Haz clic en las etiquetas para seleccionarlas (puedes seleccionar varias)</small>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Visibilidad</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked onchange="togglePermissions()">
                <label class="form-check-label" for="is_public">Documento público (visible para todos)</label>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="files" class="form-label">Archivo(s) <span class="text-danger">*</span></label>
            <input type="file" name="files[]" id="files" class="form-control" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.zip,.rar">
            <small class="text-muted">Puedes seleccionar uno o varios archivos. Formatos: PDF, Word, Excel, TXT, Imágenes, ZIP, RAR. Máximo 10MB por archivo</small>
        </div>

        <!-- Preview de archivos seleccionados -->
        <div id="filesPreview" style="display:none;">
            <h3>Archivos Seleccionados <span id="fileCount" class="badge badge-primary"></span></h3>
            <div id="filesList" class="files-list"></div>
        </div>

        <!-- Permisos (solo si es privado) -->
        <div id="permissionsSection" style="display: none;" class="mb-3">
            <div class="card bg-light border">
                <div class="card-body">
                    <h5 class="card-title mb-3">Socios con acceso</h5>
                    <div class="members-list" style="max-height: 200px; overflow-y: auto; padding-right: 10px;">
                        <?php if (isset($members) && is_array($members)): ?>
                            <?php foreach ($members as $member): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permitted_members[]" value="<?php echo $member['id']; ?>" id="member_<?php echo $member['id']; ?>">
                                    <label class="form-check-label" for="member_<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay socios disponibles.</p>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted mt-2 d-block">Selecciona los socios que podrán ver y descargar este documento.</small>
                </div>
            </div>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="fas fa-cloud-upload-alt"></i> Subir <span id="submitFileCount">Documento(s)</span>
            </button>
            <a href="index.php?page=documents" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

        <!-- Barra de progreso -->
        <div id="uploadProgress" style="display:none; margin-top: 2rem;">
            <h3>Progreso de Subida</h3>
            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
                <div class="progress-text" id="progressText">0%</div>
            </div>
            <div id="uploadStatus" class="upload-status"></div>
        </div>
    </form>
</div>

<style>
.files-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    background: #f8fafc;
    margin-top: 1rem;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}

.file-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
}

.file-info {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 1rem;
}

.file-icon {
    font-size: 24px;
}

.file-details {
    flex: 1;
}

.file-name {
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: #64748b;
}

.file-actions button {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 0.5rem;
    font-size: 18px;
    transition: color 0.2s;
}

.file-actions button:hover {
    color: #dc2626;
}

.progress-container {
    position: relative;
    width: 100%;
    height: 40px;
    background: #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    transition: width 0.3s;
    width: 0%;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 600;
    color: #1e293b;
    z-index: 1;
}

.upload-status {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    background: white;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
}

.status-item.success {
    background: #dcfce7;
    color: #166534;
}

.status-item.error {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<script>
function togglePermissions() {
    const isPublic = document.getElementById('is_public').checked;
    const permissionsSection = document.getElementById('permissionsSection');
    permissionsSection.style.display = isPublic ? 'none' : 'block';
}

// Selector de tags con efecto visual
document.querySelectorAll('.tag-checkbox').forEach(label => {
    const checkbox = label.querySelector('input[type="checkbox"]');
    const badge = label.querySelector('.tag-badge');
    const color = badge.getAttribute('data-color');
    
    badge.addEventListener('click', function() {
        checkbox.checked = !checkbox.checked;
        updateTagStyle();
    });
    
    function updateTagStyle() {
        if (checkbox.checked) {
            badge.style.background = color;
            badge.style.color = '#fff';
            badge.style.borderColor = color;
            badge.style.transform = 'scale(1.05)';
            badge.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        } else {
            badge.style.background = color + '22';
            badge.style.color = color;
            badge.style.borderColor = color;
            badge.style.transform = 'scale(1)';
            badge.style.boxShadow = 'none';
        }
    }
    
    updateTagStyle();
});

// Preview de archivos seleccionados
const filesInput = document.getElementById('files');
const filesPreview = document.getElementById('filesPreview');
const filesList = document.getElementById('filesList');
const fileCount = document.getElementById('fileCount');
const submitFileCount = document.getElementById('submitFileCount');
const singleFileMetadata = document.getElementById('singleFileMetadata');
const singleFileDescription = document.getElementById('singleFileDescription');
const titleInput = document.getElementById('title');

let selectedFiles = [];

filesInput.addEventListener('change', function(e) {
    selectedFiles = Array.from(e.target.files);
    updateFilesPreview();
});

function updateFilesPreview() {
    if (selectedFiles.length === 0) {
        filesPreview.style.display = 'none';
        singleFileMetadata.style.display = 'none';
        singleFileDescription.style.display = 'none';
        titleInput.required = false;
        submitFileCount.textContent = 'Documento(s)';
        return;
    }
    
    // Si es un solo archivo, mostrar campos de título y descripción
    if (selectedFiles.length === 1) {
        singleFileMetadata.style.display = 'block';
        singleFileDescription.style.display = 'block';
        titleInput.required = true;
        filesPreview.style.display = 'none';
        submitFileCount.textContent = '1 Documento';
    } else {
        singleFileMetadata.style.display = 'none';
        singleFileDescription.style.display = 'none';
        titleInput.required = false;
        filesPreview.style.display = 'block';
        fileCount.textContent = selectedFiles.length;
        submitFileCount.textContent = selectedFiles.length + ' Documentos';
        
        filesList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-item';
            
            const extension = file.name.split('.').pop().toLowerCase();
            let iconClass = 'fa-file';
            let iconColor = '#94a3b8';
            
            if (extension === 'pdf') {
                iconClass = 'fa-file-pdf';
                iconColor = '#ef4444';
            } else if (['doc', 'docx'].includes(extension)) {
                iconClass = 'fa-file-word';
                iconColor = '#3b82f6';
            } else if (['xls', 'xlsx'].includes(extension)) {
                iconClass = 'fa-file-excel';
                iconColor = '#10b981';
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                iconClass = 'fa-file-image';
                iconColor = '#f59e0b';
            } else if (['zip', 'rar', '7z'].includes(extension)) {
                iconClass = 'fa-file-archive';
                iconColor = '#8b5cf6';
            }
            
            item.innerHTML = `
                <div class="file-info">
                    <div class="file-icon">
                        <i class="fas ${iconClass}" style="color: ${iconColor};"></i>
                    </div>
                    <div class="file-details">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <div class="file-actions">
                    <button type="button" onclick="removeFile(${index})" title="Eliminar">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            `;
            
            filesList.appendChild(item);
        });
    }
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    filesInput.files = dt.files;
    
    updateFilesPreview();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Interceptar envío del formulario para subida masiva
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    if (selectedFiles.length > 1) {
        e.preventDefault();
        await handleBulkUpload();
    }
    // Si es un solo archivo, deja que el formulario se envíe normalmente
});

async function handleBulkUpload() {
    const submitBtn = document.getElementById('submitBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const uploadStatus = document.getElementById('uploadStatus');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
    uploadProgress.style.display = 'block';
    uploadStatus.innerHTML = '';
    
    const formData = new FormData(document.getElementById('uploadForm'));
    let successCount = 0;
    let errorCount = 0;
    
    try {
        const response = await fetch('index.php?page=documents&action=bulk_store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.results) {
            data.results.forEach((result, index) => {
                const statusItem = document.createElement('div');
                statusItem.className = `status-item ${result.success ? 'success' : 'error'}`;
                
                const icon = result.success ? 
                    '<i class="fas fa-check-circle"></i>' : 
                    '<i class="fas fa-times-circle"></i>';
                
                statusItem.innerHTML = `
                    ${icon}
                    <span><strong>${result.filename}</strong>: ${result.message}</span>
                `;
                
                uploadStatus.appendChild(statusItem);
                
                if (result.success) successCount++;
                else errorCount++;
                
                const progress = ((index + 1) / data.results.length) * 100;
                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
            });
            
            const finalMessage = document.createElement('div');
            finalMessage.className = 'alert ' + (errorCount === 0 ? 'alert-success' : 'alert-warning');
            finalMessage.style.marginTop = '1rem';
            finalMessage.innerHTML = `
                <strong>Subida completada:</strong><br>
                ✓ ${successCount} archivo(s) subido(s) correctamente<br>
                ${errorCount > 0 ? `✗ ${errorCount} archivo(s) con errores` : ''}
            `;
            uploadStatus.appendChild(finalMessage);
            
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Completado';
            
            if (errorCount === 0) {
                setTimeout(() => {
                    window.location.href = 'index.php?page=documents';
                }, 3000);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-redo"></i> Reintentar';
            }
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error:', error);
        uploadStatus.innerHTML = `
            <div class="status-item error">
                <i class="fas fa-times-circle"></i>
                <span>Error al subir archivos: ${error.message}</span>
            </div>
        `;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Subir Documentos';
    }
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
<style>
select[multiple] {
    padding: 0.5rem;
}
select[multiple] option {
    padding: 0.5rem;
    border-radius: var(--radius-md);
    margin-bottom: 0.25rem;
}
select[multiple] option:hover {
    background: var(--primary-100);
}
</style>