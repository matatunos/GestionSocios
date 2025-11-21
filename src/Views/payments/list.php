<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Control de Pagos</h1>
    <a href="index.php?page=payments&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Registrar Pago
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Socio</th>
                    <th>Concepto</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay pagos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $row): ?>
                        <tr>
                            <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['payment_date']); ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['concept']); ?>
                                <?php if ($row['fee_year']): ?>
                                    <span class="badge badge-secondary" style="font-size: 0.7rem; margin-left: 0.5rem;"><?php echo $row['fee_year']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 600; font-family: monospace;"><?php echo number_format($row['amount'], 2); ?> €</td>
                            <td>
                                <span class="badge <?php echo $row['status'] === 'paid' ? 'badge-paid' : 'badge-pending'; ?>">
                                    <?php echo $row['status'] === 'paid' ? 'Pagado' : 'Pendiente'; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="index.php?page=payments&action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" style="margin-right: 0.5rem;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="index.php?page=payments&action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este pago?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
