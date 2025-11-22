<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Editar Donante</h1>
    <a href="index.php?page=donors" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=donors&action=update&id=<?php echo $donor->id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nombre del Negocio / Donante</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($donor->name); ?>" required>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="contact_person">Persona de Contacto</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($donor->contact_person); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($donor->phone); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($donor->email); ?>">
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($donor->address); ?></textarea>
        </div>

        <div class="form-group">
            <label for="logo">Logo / Imagen (para Libro de Fiestas)</label>
            <?php if ($donor->logo_url): ?>
                <div style="margin-bottom: 1rem;">
                    <img src="<?php echo htmlspecialchars($donor->logo_url); ?>" 
                         alt="Logo actual" 
                         style="max-width: 200px; max-height: 150px; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.5rem;">
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem;">Logo actual</p>
                    <div class="flex gap-2 mt-2">
                        <a href="<?php echo htmlspecialchars($donor->logo_url); ?>" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="<?php echo htmlspecialchars($donor->logo_url); ?>" download class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <?php 
                        // Check if donor has images in history
                        require_once __DIR__ . '/../../Models/DonorImageHistory.php';
                        $database = new Database();
                        $db = $database->getConnection();
                        $imageHistory = new DonorImageHistory($db);
                        $historyCount = $imageHistory->countByDonor($donor->id);
                        if ($historyCount > 0):
                        ?>
                        <a href="index.php?page=donors&action=imageHistory&id=<?php echo $donor->id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-history"></i> Histórico (<?php echo $historyCount; ?>)
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" id="logo" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                Formatos aceptados: JPG, PNG, GIF, WebP.
                <?php if ($donor->logo_url): ?>
                    <br>Deja vacío para mantener el logo actual.
                <?php endif; ?>
            </small>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Donante
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
