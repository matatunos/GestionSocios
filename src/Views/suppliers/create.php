<?php
$pageTitle = 'Nuevo Proveedor';
ob_start();
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus"></i> Nuevo Proveedor</h1>
        <p class="text-muted">Registrar un nuevo proveedor en el sistema</p>
    </div>
    <div class="header-actions">
        <a href="index.php?page=suppliers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <form action="index.php?page=suppliers&action=store" method="POST" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="cif_nif" class="form-label">CIF / NIF</label>
                            <input type="text" class="form-control" id="cif_nif" name="cif_nif">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="website" class="form-label">Sitio Web</label>
                            <input type="url" class="form-control" id="website" name="website" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="logo" class="form-label">Logo / Imagen</label>
                    <div class="image-preview-container" style="border: 2px dashed var(--border-light); padding: 2rem; text-align: center; border-radius: 8px; background: var(--bg-light);">
                        <i class="fas fa-image text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-sm text-muted mb-2">Arrastra o selecciona una imagen</p>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="notes" class="form-label">Notas Internas</label>
                    <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Información adicional, cuenta bancaria, etc."></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions mt-4 text-right">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Proveedor
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
