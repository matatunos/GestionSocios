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
                    <?php $selected = isset($document['id']) ? $documentModel->getCategoryIds($document['id']) : []; ?>
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

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
