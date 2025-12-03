<?php ob_start(); ?>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php?page=book_activities&year=<?php echo $year; ?>" class="btn btn-sm btn-secondary" style="margin-bottom: 1rem;">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Nueva Actividad <?php echo $year; ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=book_activities&action=store" enctype="multipart/form-data">
        <input type="hidden" name="year" value="<?php echo $year; ?>">

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">Título <span style="color: var(--danger-600);">*</span></label>
            <input type="text" name="title" class="form-control" required placeholder="Ej: Saluda del Presidente">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Texto descriptivo o contenido de la actividad..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Número de Página</label>
                <input type="number" name="page_number" class="form-control" placeholder="Opcional">
            </div>
            <div class="form-group">
                <label class="form-label">Orden de Visualización</label>
                <input type="number" name="display_order" class="form-control" value="0">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Menor número aparece primero</small>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">Imagen / Foto</label>
            <div style="margin-top: 0.5rem; display: flex; justify-content: center; padding: 2rem; border: 2px dashed var(--border-light); border-radius: 8px; background: var(--bg-glass);">
                <div style="text-align: center;">
                    <i class="fas fa-image" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <div style="font-size: 0.875rem; color: var(--text-main);">
                        <label for="image-upload" style="cursor: pointer; color: var(--primary-600); font-weight: 500;">
                            <span>Subir un archivo</span>
                            <input id="image-upload" name="image" type="file" style="display: none;" accept="image/*" onchange="previewImage(this)">
                        </label>
                        <span style="color: var(--text-muted);"> o arrastrar y soltar</span>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">PNG, JPG, GIF hasta 5MB</p>
                </div>
            </div>
            <div id="image-preview" style="margin-top: 1rem; display: none;">
                <p style="font-size: 0.875rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem;">Vista previa:</p>
                <img src="" alt="Preview" style="max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--border-light);">
            <a href="index.php?page=book_activities&year=<?php echo $year; ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Actividad
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layout.php'; ?>
