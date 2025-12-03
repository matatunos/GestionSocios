<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-plus"></i> Nuevo Tag
        </h1>
    </div>
    <div class="page-actions">
        <a href="index.php?page=document_tags" class="btn btn-secondary">
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

    <form method="POST" action="index.php?page=document_tags&action=store">
        <?php require_once __DIR__ . '/../../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        
        <div class="form-group mb-3">
            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required maxlength="100" 
                   placeholder="Nombre del tag">
        </div>

        <div class="form-group mb-3">
            <label for="color" class="form-label">Color</label>
            <div class="color-picker-wrapper">
                <input type="color" name="color" id="color" class="form-control" value="#6366f1" 
                       style="width: 100px; height: 40px;">
                <span class="color-preview" id="colorPreview" style="background: #6366f1;"></span>
            </div>
            <small class="text-muted">Selecciona un color para el tag</small>
        </div>

        <div class="form-group mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="3" 
                      placeholder="Descripción opcional del tag"></textarea>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Tag
            </button>
        </div>
    </form>
</div>

<style>
.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}
.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}
</style>

<script>
document.getElementById('color').addEventListener('input', function(e) {
    document.getElementById('colorPreview').style.background = e.target.value;
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layout.php';
?>
