<?php ob_start(); ?>
<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-file-alt"></i> Nueva Solicitud</h1>
        <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="grant-info" style="background: #e7f3ff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <strong>Subvención:</strong> <?php echo htmlspecialchars($grant['title']); ?><br>
        <strong>Organismo:</strong> <?php echo htmlspecialchars($grant['organization']); ?>
    </div>

    <form method="POST" action="index.php?page=grants&action=storeApplication" class="form-card">
        <input type="hidden" name="grant_id" value="<?php echo $grant['id']; ?>">
        
        <div class="form-grid">
            <div class="form-group">
                <label>Número de Registro</label>
                <input type="text" name="application_number" class="form-control">
            </div>
            <div class="form-group">
                <label>Fecha Solicitud <span class="required">*</span></label>
                <input type="date" name="application_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Importe Solicitado <span class="required">*</span></label>
                <input type="number" name="requested_amount" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Tipo de Pago</label>
                <select name="payment_type" class="form-control">
                    <option value="unico">Pago Único</option>
                    <option value="anticipo">Con Anticipo</option>
                    <option value="fraccionado">Fraccionado</option>
                </select>
            </div>
            <div class="form-group">
                <label>Plazo Justificación</label>
                <input type="date" name="justification_deadline" class="form-control">
            </div>
            <div class="form-group">
                <label>Responsable</label>
                <select name="responsible_user_id" class="form-control">
                    <option value="">Sin asignar</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full-width">
                <label>Notas</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear Solicitud</button>
            <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
