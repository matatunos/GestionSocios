<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-plus"></i> Nueva Carpeta
        </h1>
    </div>
    <div class="page-actions">
        <a href="index.php?page=document_folders" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=document_folders&action=store">
        <?php require_once __DIR__ . '/../../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        
        <div class="form-group mb-3">
            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required maxlength="255" 
                   placeholder="Nombre de la carpeta">
        </div>

        <div class="form-group mb-3">
            <label for="parent_id" class="form-label">Carpeta Padre</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">Raíz (sin carpeta padre)</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?php echo $folder['id']; ?>">
                        <?php echo str_repeat('&nbsp;&nbsp;', substr_count($folder['path'], '/') - 1); ?>
                        <?php echo htmlspecialchars($folder['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Selecciona una carpeta padre para crear jerarquía</small>
        </div>

        <div class="form-group mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="3" 
                      placeholder="Descripción opcional de la carpeta"></textarea>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Carpeta
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layout.php';
?>
