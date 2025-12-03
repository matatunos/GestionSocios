<?php ob_start(); ?>

<div class="card" style="max-width: 900px; margin: 2rem auto;">
    <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem;">
        <i class="fas fa-edit"></i> Editar Gasto
    </h1>

    <form action="index.php?page=expenses&action=update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $expense->id; ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Descripción *</label>
                <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($expense->description); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Importe (€) *</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($expense->amount); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Categoría *</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($expense->category_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Fecha del Gasto *</label>
                <input type="date" name="expense_date" class="form-control" value="<?php echo htmlspecialchars($expense->expense_date); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Método de Pago</label>
                <select name="payment_method" class="form-control">
                    <option value="transfer" <?php echo ($expense->payment_method == 'transfer') ? 'selected' : ''; ?>>Transferencia</option>
                    <option value="cash" <?php echo ($expense->payment_method == 'cash') ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="card" <?php echo ($expense->payment_method == 'card') ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="check" <?php echo ($expense->payment_method == 'check') ? 'selected' : ''; ?>>Cheque</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nº Factura/Recibo</label>
                <input type="text" name="invoice_number" class="form-control" value="<?php echo htmlspecialchars($expense->invoice_number); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Proveedor</label>
            <input type="text" name="provider" class="form-control" value="<?php echo htmlspecialchars($expense->provider); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($expense->notes); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Adjuntar Justificante</label>
            <?php if ($expense->receipt_file): ?>
                <div style="margin-bottom: 0.5rem;">
                    <a href="/uploads/receipts/<?php echo htmlspecialchars($expense->receipt_file); ?>" target="_blank" class="btn btn-sm btn-secondary">
                        <i class="fas fa-file"></i> Ver archivo actual
                    </a>
                </div>
            <?php endif; ?>
            <input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                Dejar vacío para mantener el archivo actual
            </small>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="index.php?page=expenses" class="btn btn-secondary" style="flex: 1;">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
