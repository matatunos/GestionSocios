<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=members" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Socio</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=members&action=update&id=<?php echo $member->id; ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input_field(); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nombre</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($member->first_name); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Apellidos</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($member->last_name); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member->email); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Teléfono</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member->phone); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección</label>
            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($member->address); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Foto de Perfil</label>
            <?php if (!empty($member->photo_url)): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($member->photo_url); ?>" alt="Foto actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                </div>
            <?php endif; ?>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <small style="color: var(--text-muted);">Dejar en blanco para mantener la foto actual.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control">
                <option value="active" <?php echo $member->status === 'active' ? 'selected' : ''; ?>>Activo</option>
                <option value="inactive" <?php echo $member->status === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Socio
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
