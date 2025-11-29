<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=payments" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Pago</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=payments&action=update&id=<?php echo $payment->id; ?>" method="POST">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div class="form-group">
            <label class="form-label">Socio</label>
            <select name="member_id" class="form-control" required>
                <option value="">Seleccione un socio...</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?php echo $member['id']; ?>" <?php echo ($member['id'] == $payment->member_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Importe (€)</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($payment->amount); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Fecha de Pago</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars($payment->payment_date); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Concepto</label>
            <input type="text" name="concept" class="form-control" value="<?php echo htmlspecialchars($payment->concept); ?>" placeholder="Ej: Cuota Enero 2024" required>
        </div>

        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control">
                <option value="paid" <?php echo ($payment->status == 'paid') ? 'selected' : ''; ?>>Pagado</option>
                <option value="pending" <?php echo ($payment->status == 'pending') ? 'selected' : ''; ?>>Pendiente</option>
            </select>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Pago
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
