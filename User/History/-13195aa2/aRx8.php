<?php ob_start(); ?>
<div class="main-content">
    <div class="content-header">
        <h1>Solicitud #<?php echo htmlspecialchars($application['application_number'] ?? $application['id']); ?></h1>
        <a href="index.php?page=grants&action=view&id=<?php echo $application['grant_id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Subvención
        </a>
    </div>

    <div class="view-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div>
            <div class="detail-card">
                <h3>Subvención</h3>
                <p><strong><?php echo htmlspecialchars($application['grant_title']); ?></strong></p>
            </div>

            <div class="detail-card">
                <h3>Datos de la Solicitud</h3>
                <dl class="detail-list">
                    <dt>Fecha Solicitud</dt>
                    <dd><?php echo date('d/m/Y', strtotime($application['application_date'])); ?></dd>
                    <dt>Importe Solicitado</dt>
                    <dd><strong><?php echo number_format($application['requested_amount'], 2); ?>€</strong></dd>
                    <?php if ($application['granted_amount']): ?>
                        <dt>Importe Concedido</dt>
                        <dd><strong style="color: #28a745;"><?php echo number_format($application['granted_amount'], 2); ?>€</strong></dd>
                    <?php endif; ?>
                    <dt>Estado</dt>
                    <dd><span class="badge badge-info"><?php echo ucfirst($application['status']); ?></span></dd>
                </dl>
            </div>

            <?php if (!empty($bankPayments)): ?>
                <div class="detail-card">
                    <h3>Pagos Bancarios Vinculados</h3>
                    <table class="table">
                        <thead>
                            <tr><th>Fecha</th><th>Descripción</th><th>Importe</th><th>Cuenta</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bankPayments as $payment): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($payment['transaction_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['description']); ?></td>
                                    <td><?php echo number_format($payment['amount'], 2); ?>€</td>
                                    <td><?php echo htmlspecialchars($payment['account_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="detail-card">
                <h3>Actualizar Estado</h3>
                <form method="POST" action="index.php?page=grants&action=updateApplicationStatus">
                    <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="borrador">Borrador</option>
                            <option value="presentada">Presentada</option>
                            <option value="en_evaluacion">En Evaluación</option>
                            <option value="concedida">Concedida</option>
                            <option value="denegada">Denegada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Importe Concedido</label>
                        <input type="number" name="granted_amount" class="form-control" step="0.01" value="<?php echo $application['granted_amount']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
