<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Nuevo Evento</h1>
    <a href="index.php?page=events" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=events&action=store" method="POST">
        <?php echo csrf_input_field(); ?>
        <div class="form-group">
            <label class="form-label">Nombre del Evento</label>
            <input type="text" name="name" class="form-control" required placeholder="Ej: Cena de Navidad 2024">
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Precio Estándar (€)</label>
                <input type="number" step="0.01" name="price" class="form-control" value="0.00">
            </div>
        </div>

        <div class="form-group">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" checked>
                <span>Evento Activo (visible para pagos)</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Evento
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
