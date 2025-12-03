<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Editar Anuncio - Libro <?php echo $bookAd->year; ?></h1>
    <a href="index.php?page=book&year=<?php echo $bookAd->year; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=book&action=update&id=<?php echo $bookAd->id; ?>">
        <input type="hidden" name="year" value="<?php echo $bookAd->year; ?>">

        <div class="form-group">
            <label for="donor_id">Donante / Negocio</label>
            <select id="donor_id" name="donor_id" class="form-control" required>
                <option value="">Seleccionar Donante...</option>
                <?php foreach ($donors as $donor): ?>
                    <option value="<?php echo $donor['id']; ?>" <?php echo ($donor['id'] == $bookAd->donor_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($donor['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="ad_type">Tipo de Anuncio</label>
                <select id="ad_type" name="ad_type" class="form-control" onchange="updatePrice()" required>
                    <option value="">Seleccionar Tipo...</option>
                    <option value="media" <?php echo ($bookAd->ad_type == 'media') ? 'selected' : ''; ?>>Media Página</option>
                    <option value="full" <?php echo ($bookAd->ad_type == 'full') ? 'selected' : ''; ?>>Página Completa</option>
                    <option value="cover" <?php echo ($bookAd->ad_type == 'cover') ? 'selected' : ''; ?>>Portada</option>
                    <option value="back_cover" <?php echo ($bookAd->ad_type == 'back_cover') ? 'selected' : ''; ?>>Contraportada</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Precio (€)</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" value="<?php echo $bookAd->amount; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Estado del Pago</label>
            <select id="status" name="status" class="form-control">
                <option value="pending" <?php echo ($bookAd->status == 'pending') ? 'selected' : ''; ?>>Pendiente</option>
                <option value="paid" <?php echo ($bookAd->status == 'paid') ? 'selected' : ''; ?>>Pagado</option>
            </select>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <a href="index.php?page=book&year=<?php echo $bookAd->year; ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Anuncio
            </button>
        </div>
    </form>
</div>

<script>
// Ad Prices from Database
const adPrices = <?php echo json_encode($adPrices ?? []); ?>;

function updatePrice() {
    const select = document.getElementById('ad_type');
    const priceInput = document.getElementById('amount');
    const type = select.value;
    
    if (type && adPrices[type]) {
        priceInput.value = adPrices[type];
    }
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
