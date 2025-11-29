<?php
$pageTitle = 'Editar Proveedor';
ob_start();
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Editar Proveedor</h1>
        <p class="text-muted"><?php echo htmlspecialchars($this->supplier->name); ?></p>
    </div>
    <div class="header-actions">
        <a href="index.php?page=suppliers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <form action="index.php?page=suppliers&action=update" method="POST" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <input type="hidden" name="id" value="<?php echo $this->supplier->id; ?>">
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($this->supplier->name); ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="cif_nif" class="form-label">CIF / NIF</label>
                            <input type="text" class="form-control" id="cif_nif" name="cif_nif" value="<?php echo htmlspecialchars($this->supplier->cif_nif); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($this->supplier->phone); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($this->supplier->email); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="website" class="form-label">Sitio Web</label>
                            <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($this->supplier->website); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($this->supplier->address); ?></textarea>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="logo" class="form-label">Logo / Imagen</label>
                    <div class="image-preview-container" style="border: 2px dashed var(--border-light); padding: 1rem; text-align: center; border-radius: 8px; background: var(--bg-light);">
                        <?php if (!empty($this->supplier->logo_path) && file_exists($this->supplier->logo_path)): ?>
                            <img src="<?php echo htmlspecialchars($this->supplier->logo_path); ?>" alt="Logo actual" style="max-width: 100%; max-height: 150px; margin-bottom: 1rem; border-radius: 4px;">
                            <p class="text-sm text-muted">Logo actual</p>
                        <?php else: ?>
                            <i class="fas fa-image text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-sm text-muted mb-2">Sin logo</p>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted d-block mt-1">Subir para reemplazar</small>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="notes" class="form-label">Notas Internas</label>
                    <textarea class="form-control" id="notes" name="notes" rows="5"><?php echo htmlspecialchars($this->supplier->notes); ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions mt-4 text-right">
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
