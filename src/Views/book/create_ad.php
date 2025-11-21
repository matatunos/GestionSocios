<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Nuevo Anuncio - Libro <?php echo $year; ?></h1>
    <a href="index.php?page=book&year=<?php echo $year; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=book&action=store">
        <input type="hidden" name="year" value="<?php echo $year; ?>">

        <div class="form-group">
            <label for="donor_id">Donante / Negocio</label>
            <select id="donor_id" name="donor_id" class="form-control" required>
                <option value="">Seleccionar Donante...</option>
                <?php foreach ($donors as $donor): ?>
                    <option value="<?php echo $donor['id']; ?>">
                        <?php echo htmlspecialchars($donor['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="text-sm text-muted mt-1">
                ¿No encuentras el donante? <a href="index.php?page=donors&action=create" target="_blank" class="text-primary">Crear nuevo donante</a>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="ad_type">Tipo de Anuncio</label>
                <select id="ad_type" name="ad_type" class="form-control" required onchange="updatePrice()">
                    <option value="media" data-price="50">Media Página</option>
                    <option value="full" data-price="100">Página Completa</option>
                    <option value="cover" data-price="200">Portada</option>
                    <option value="back_cover" data-price="150">Contraportada</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Precio (€)</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Estado del Pago</label>
            <select id="status" name="status" class="form-control">
                <option value="pending">Pendiente</option>
                <option value="paid">Pagado</option>
            </select>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Anuncio
            </button>
        </div>
    </form>
</div>

<script>
function updatePrice() {
    const select = document.getElementById('ad_type');
    const priceInput = document.getElementById('amount');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    
    // Only update if empty or matches a standard price (to allow custom overrides)
    // For simplicity, let's just suggest it if empty
    if (priceInput.value === '' || priceInput.value === '0') {
        priceInput.value = price;
    }
}
// Run on load
updatePrice();
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
