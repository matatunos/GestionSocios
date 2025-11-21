<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Añadir Donación</h1>
    <a href="index.php?page=donations" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="post" action="index.php?page=donations&action=store">
        <div class="form-group">
            <label for="member_id">Socio</label>
            <select name="member_id" id="member_id" class="form-control" required>
                <option value="">Seleccionar socio...</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="amount">Importe (€)</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="type">Tipo</label>
            <select name="type" id="type" class="form-control" required>
                <option value="media">Media página</option>
                <option value="full">Página completa</option>
                <option value="cover">Portada/Trasera</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="year">Año</label>
            <input type="number" name="year" id="year" class="form-control" value="<?= date('Y') ?>" required>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            <a href="index.php?page=donations" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
