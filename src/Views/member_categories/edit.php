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

<!-- Fee History Section -->
<div class="card" style="max-width: 800px; margin: 2rem auto;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">
        <i class="fas fa-history"></i> Histórico de Cuotas
    </h2>
    
    <!-- Add/Edit Fee Form -->
    <form action="index.php?page=member_categories&action=updateFee" method="POST" style="background: var(--bg-body); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
        <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Año</label>
                <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required min="2000" max="2100">
            </div>
            
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Cuota (€)</label>
                <input type="number" name="fee_amount" class="form-control" step="0.01" min="0" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir/Actualizar
            </button>
        </div>
    </form>
    
    <!-- Fee History Table -->
    <?php if (empty($feeHistory)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No hay cuotas registradas para esta categoría.
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Cuota</th>
                    <th>Actualizado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feeHistory as $fee): ?>
                    <tr>
                        <td style="font-weight: 600;">
                            <?php echo $fee['year']; ?>
                            <?php if ($fee['year'] == date('Y')): ?>
                                <span class="badge" style="background: var(--primary-600); color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: var(--radius-full); margin-left: 0.5rem;">Actual</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($fee['fee_amount'], 2); ?> €</td>
                        <td style="color: var(--text-muted); font-size: 0.875rem;">
                            <?php echo date('d/m/Y H:i', strtotime($fee['updated_at'])); ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="index.php?page=member_categories&action=deleteFee&id=<?php echo $fee['id']; ?>&category_id=<?php echo $category->id; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar la cuota del año <?php echo $fee['year']; ?>?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
