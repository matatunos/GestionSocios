<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=documents" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Subir Documento</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=documents&action=store" enctype="multipart/form-data">
        
        <div class="form-group mb-3">
            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" required maxlength="255" placeholder="Título descriptivo del documento">
        </div>

        <div class="form-group mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Descripción detallada del contenido"></textarea>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Visibilidad</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked onchange="togglePermissions()">
                <label class="form-check-label" for="is_public">Documento público (visible para todos)</label>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="file" class="form-label">Archivo <span class="text-danger">*</span></label>
            <input type="file" name="file" id="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.zip,.rar">
            <small class="text-muted">Formatos permitidos: PDF, Word, Excel, TXT, Imágenes, ZIP, RAR. Máximo 10MB</small>
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
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </button>
        </div>
    </form>
</div>

<script>
function togglePermissions() {
    const isPublic = document.getElementById('is_public').checked;
    const permissionsSection = document.getElementById('permissionsSection');
    permissionsSection.style.display = isPublic ? 'none' : 'block';
}

// Preview del archivo seleccionado (opcional, solo log por ahora)
document.getElementById('file').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const file = this.files[0];
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        console.log('Archivo seleccionado:', file.name, '-', fileSize, 'MB');
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>