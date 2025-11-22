<?php ob_start(); ?>

<?php $title = 'Subir Documento'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-cloud-upload-alt"></i> Subir Documento
        </h1>
        <p class="page-subtitle">Añadir un nuevo documento a la biblioteca</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=documents" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?page=documents&action=store" enctype="multipart/form-data">
        <div class="form-grid">
            <!-- Título -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="title">Título <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-input" required maxlength="255" placeholder="Título descriptivo del documento">
            </div>
            
            <!-- Descripción -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="description">Descripción</label>
                <textarea name="description" id="description" class="form-input" rows="4" placeholder="Descripción detallada del contenido"></textarea>
            </div>
            
            <!-- Categoría -->
            <div class="form-group">
                <label for="category">Categoría <span class="required">*</span></label>
                <select name="category" id="category" class="form-select" required>
                    <option value="general">General</option>
                    <option value="actas">Actas</option>
                    <option value="estatutos">Estatutos</option>
                    <option value="facturas">Facturas</option>
                    <option value="otros">Otros</option>
                </select>
            </div>
            
            <!-- Visibilidad -->
            <div class="form-group">
                <label for="is_public">Visibilidad</label>
                <label class="toggle-switch" style="margin-top: 0.5rem;">
                    <input type="checkbox" name="is_public" id="is_public" checked onchange="togglePermissions()">
                    <span class="toggle-slider"></span>
                    <span class="toggle-label">
                        <span class="toggle-text">Documento público (visible para todos)</span>
                    </span>
                </label>
            </div>
            
            <!-- Archivo -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="file">Archivo <span class="required">*</span></label>
                <input type="file" name="file" id="file" class="form-input" required accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.zip,.rar">
                <small class="form-text">Formatos permitidos: PDF, Word, Excel, TXT, Imágenes, ZIP, RAR. Máximo 10MB</small>
            </div>
            
            <!-- Permisos (solo si es privado) -->
            <div class="form-group" id="permissionsSection" style="grid-column: 1 / -1; display: none;">
                <label>Socios con acceso</label>
                <div class="members-select-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.5rem; max-height: 300px; overflow-y: auto; padding: 1rem; background: var(--bg-glass); border: 1px solid var(--border-light); border-radius: var(--radius-md);">
                    <?php foreach ($members as $member): ?>
                        <label style="display: flex; align-items: center; padding: 0.5rem; border-radius: 0.5rem; cursor: pointer; transition: var(--transition);" class="member-checkbox-label">
                            <input type="checkbox" name="permitted_members[]" value="<?php echo $member['id']; ?>" class="member-checkbox" style="margin-right: 0.5rem;">
                            <span style="font-size: 0.875rem;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small class="form-text">Selecciona los socios que podrán ver y descargar este documento</small>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </button>
            <a href="index.php?page=documents" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function togglePermissions() {
    const isPublic = document.getElementById('is_public').checked;
    const permissionsSection = document.getElementById('permissionsSection');
    permissionsSection.style.display = isPublic ? 'none' : 'block';
}

// Preview del archivo seleccionado
document.getElementById('file').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const file = this.files[0];
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        console.log('Archivo seleccionado:', file.name, '-', fileSize, 'MB');
    }
});
</script>

<style>
.required {
    color: var(--danger-500);
}

.member-checkbox-label:hover {
    background: var(--primary-50);
}

[data-theme="dark"] .member-checkbox-label:hover {
    background: rgba(99, 102, 241, 0.1);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>