<?php ob_start(); ?>

<div class="card" style="max-width: 800px; margin: 2rem auto;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">
            <i class="fas fa-edit"></i> Editar Categoría
        </h1>
        <p style="color: var(--text-muted);">Modifica los datos de la categoría</p>
    </div>

    <form action="index.php?page=member_categories&action=update" method="POST">
        <input type="hidden" name="id" value="<?php echo $category->id; ?>">
        
        <div class="form-group">
            <label class="form-label">Nombre de la Categoría *</label>
            <input type="text" name="name" class="form-control" required 
                   value="<?php echo htmlspecialchars($category->name); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($category->description); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Cuota Predeterminada (€) *</label>
            <input type="number" name="default_fee" class="form-control" step="0.01" min="0" required
                   value="<?php echo htmlspecialchars($category->default_fee); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Color Identificativo</label>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="color" name="color" value="<?php echo htmlspecialchars($category->color); ?>" 
                       style="width: 60px; height: 40px; border: none; border-radius: var(--radius-md); cursor: pointer;">
                <small style="color: var(--text-muted);">Color para identificar visualmente esta categoría</small>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Orden de Visualización</label>
            <input type="number" name="display_order" class="form-control" min="0"
                   value="<?php echo htmlspecialchars($category->display_order); ?>">
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" <?php echo $category->is_active ? 'checked' : ''; ?> 
                       style="width: 18px; height: 18px;">
                <span class="form-label" style="margin: 0;">Categoría activa</span>
            </label>
        </div>

        <?php
        $memberCount = $category->countMembers();
        if ($memberCount > 0):
        ?>
        <div class="alert" style="background: #eff6ff; border: 1px solid #3b82f6; color: #1e40af; margin-bottom: 1.5rem;">
            <i class="fas fa-info-circle"></i> 
            <strong>Información:</strong> Esta categoría tiene <?php echo $memberCount; ?> socio(s) asignado(s).
        </div>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="index.php?page=member_categories" class="btn btn-secondary" style="flex: 1;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
