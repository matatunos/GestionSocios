<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=suppliers" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Proveedor</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=suppliers&action=update" method="POST" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <input type="hidden" name="id" value="<?php echo $this->supplier->id; ?>">
        
        <div class="form-group mb-3">
            <label class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($this->supplier->name); ?>" required>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">CIF / NIF</label>
                    <input type="text" class="form-control" name="cif_nif" value="<?php echo htmlspecialchars($this->supplier->cif_nif); ?>" placeholder="Ej: B12345678">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($this->supplier->phone); ?>" placeholder="Ej: 912345678">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($this->supplier->email); ?>" placeholder="contacto@proveedor.com">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Sitio Web</label>
                    <input type="url" class="form-control" name="website" value="<?php echo htmlspecialchars($this->supplier->website); ?>" placeholder="https://www.proveedor.com">
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Dirección</label>
            <textarea class="form-control" name="address" rows="3" placeholder="Dirección completa del proveedor"><?php echo htmlspecialchars($this->supplier->address); ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Logo / Imagen</label>
            <?php if (!empty($this->supplier->logo_path) && file_exists($this->supplier->logo_path)): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($this->supplier->logo_path); ?>" alt="Logo actual" style="max-width: 200px; max-height: 100px; border: 1px solid var(--border-light); border-radius: 4px; padding: 4px;">
                    <p class="text-sm text-muted mt-1">Logo actual</p>
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" name="logo" accept="image/*">
            <small class="text-muted">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5MB. Dejar vacío para mantener el logo actual.</small>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Notas Internas</label>
            <textarea class="form-control" name="notes" rows="4" placeholder="Información adicional, cuenta bancaria, condiciones de pago, etc."><?php echo htmlspecialchars($this->supplier->notes); ?></textarea>
        </div>

        <div class="form-actions mt-4 text-right">
            <a href="index.php?page=suppliers" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Proveedor
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
