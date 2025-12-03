<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=documents" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Documento</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=documents&action=update">
        <?php 
        require_once __DIR__ . '/../../Helpers/CsrfHelper.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        echo CsrfHelper::getTokenField();
        ?>
        <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
        
        <div class="form-group mb-3">
            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" required maxlength="255" value="<?php echo htmlspecialchars($document['title']); ?>">
        </div>

        <div class="form-group mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?php echo htmlspecialchars($document['description']); ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="tags" class="form-label">Etiquetas</label>
            <div class="tags-selector">
                <?php if (isset($tags) && is_array($tags)): ?>
                    <?php foreach ($tags as $tag): ?>
                        <?php 
                        $isChecked = in_array((int)$tag['id'], $currentTags ?? [], true);
                        ?>
                        <label class="tag-checkbox" style="display: inline-block; margin: 5px;">
                            <input type="checkbox" name="tag_ids[]" value="<?php echo $tag['id']; ?>" 
                                <?php echo $isChecked ? 'checked' : ''; ?>
                                style="display: none;">
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
            <small class="text-muted">Haz clic en las etiquetas para seleccionarlas o deseleccionarlas</small>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Visibilidad</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" <?php echo $document['is_public'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_public">Documento público (visible para todos)</label>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="category_ids" class="form-label">Categorías</label>
            <select name="category_ids[]" id="category_ids" class="form-control" multiple>
                <?php if (isset($categories) && is_array($categories)): ?>
                    <?php $selected = isset($document['category_ids']) ? $document['category_ids'] : []; ?>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" style="color:<?php echo htmlspecialchars($cat['color']); ?>;" <?php echo in_array($cat['id'], $selected) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <small class="text-muted">Puedes seleccionar varias categorías usando Ctrl o Shift.</small>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Para reemplazar el archivo, por favor elimine este documento y suba uno nuevo.
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Documento
            </button>
        </div>
    </form>
</div>

<script>
// Debug: Ver qué tags se envían al hacer submit
document.querySelector('form').addEventListener('submit', function(e) {
    const checkedTags = [];
    document.querySelectorAll('input[name="tag_ids[]"]:checked').forEach(cb => {
        checkedTags.push(cb.value);
    });
    console.log('Tags seleccionados al enviar:', checkedTags);
});
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
<script>
// Selector de tags con efecto visual
document.querySelectorAll('.tag-checkbox').forEach(label => {
    const checkbox = label.querySelector('input[type="checkbox"]');
    const badge = label.querySelector('.tag-badge');
    const color = badge.getAttribute('data-color');
    
    badge.addEventListener('click', function(e) {
        e.preventDefault();
        checkbox.checked = !checkbox.checked;
        console.log('Tag clicked:', badge.textContent.trim(), 'Checked:', checkbox.checked, 'Value:', checkbox.value);
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
    
    // Estado inicial
    updateTagStyle();
});
</script>
