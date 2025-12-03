<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=announcements" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Nuevo Anuncio</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=announcements&action=store" method="POST">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        
        <div class="form-group mb-3">
            <label class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" required maxlength="255">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Contenido <span class="text-danger">*</span></label>
            <textarea class="form-control" name="content" rows="4" required placeholder="Escribe el contenido del anuncio..."></textarea>
            <small class="text-muted">El contenido se mostrará tal cual en la página de login</small>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select class="form-control" name="type">
                        <option value="info">Info (Azul)</option>
                        <option value="success">Éxito (Verde)</option>
                        <option value="warning">Advertencia (Naranja)</option>
                        <option value="danger">Urgente (Rojo)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Prioridad</label>
                    <input type="number" class="form-control" name="priority" value="0" min="0" max="100">
                    <small class="text-muted">Mayor número = más arriba</small>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Fecha de Expiración (Opcional)</label>
            <input type="datetime-local" class="form-control" name="expires_at">
            <small class="text-muted">Dejar vacío para que no expire</small>
        </div>

        <div class="form-group mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                <label class="form-check-label" for="is_active">
                    Activar anuncio inmediatamente
                </label>
            </div>
        </div>

        <div class="form-actions mt-4 text-right">
            <a href="index.php?page=announcements" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Anuncio
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
