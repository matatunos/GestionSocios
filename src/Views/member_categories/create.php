<?php ob_start(); ?>

<div class="card" style="max-width: 800px; margin: 2rem auto;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">
            <i class="fas fa-plus-circle"></i> Nueva Categoría de Socio
        </h1>
        <p style="color: var(--text-muted);">Define una nueva categoría de membresía</p>
    </div>

    <form action="index.php?page=member_categories&action=store" method="POST">
        <div class="form-group">
            <label class="form-label">Nombre de la Categoría *</label>
            <input type="text" name="name" class="form-control" required placeholder="ej: Juvenil, Senior...">
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Breve descripción de la categoría"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Cuota Predeterminada (€) *</label>
            <input type="number" name="default_fee" class="form-control" step="0.01" min="0" value="0.00" required>
            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                Esta será la cuota sugerida para socios de esta categoría
            </small>
        </div>

        <div class="form-group">
            <label class="form-label">Color Identificativo</label>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="color" name="color" value="#6366f1" style="width: 60px; height: 40px; border: none; border-radius: var(--radius-md); cursor: pointer;">
                <small style="color: var(--text-muted);">Selecciona un color para identificar visualmente esta categoría</small>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Orden de Visualización</label>
            <input type="number" name="display_order" class="form-control" value="0" min="0">
            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                Las categorías se ordenarán de menor a mayor número
            </small>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" checked style="width: 18px; height: 18px;">
                <span class="form-label" style="margin: 0;">Categoría activa</span>
            </label>
            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem; margin-left: 1.5rem;">
                Solo las categorías activas aparecerán disponibles al crear/editar socios
            </small>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i> Crear Categoría
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
