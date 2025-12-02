<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-file-invoice"></i> Asiento Contable #<?php echo htmlspecialchars($entry['entry_number']); ?></h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=entries" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <?php if ($entry['status'] === 'draft'): ?>
                <a href="index.php?page=accounting&action=postEntry&id=<?php echo $entry['id']; ?>" 
                   class="btn btn-success"
                   onclick="return confirm('¿Está seguro de contabilizar este asiento? Esta acción no se puede deshacer.');">
                    <i class="fas fa-check"></i> Contabilizar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-section">
            <h3>Información del Asiento</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Número:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars($entry['entry_number']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Período:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($entry['period_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value">
                        <?php
                        $statusLabels = [
                            'draft' => '<span class="badge badge-warning">Borrador</span>',
                            'posted' => '<span class="badge badge-success">Contabilizado</span>',
                            'cancelled' => '<span class="badge badge-danger">Cancelado</span>'
                        ];
                        echo $statusLabels[$entry['status']] ?? $entry['status'];
                        ?>
                    </span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label">Descripción:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($entry['description']); ?></span>
                </div>
                <?php if (!empty($entry['reference'])): ?>
                    <div class="detail-item full-width">
                        <span class="detail-label">Referencia:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($entry['reference']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">Creado por:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($entry['created_by_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fecha de creación:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($entry['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Líneas del Asiento</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Descripción</th>
                            <th class="text-right">Débito</th>
                            <th class="text-right">Crédito</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        foreach ($lines as $line):
                            $totalDebit += $line['debit'];
                            $totalCredit += $line['credit'];
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($line['account_code']); ?></strong>
                                    <?php echo htmlspecialchars($line['account_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($line['description'] ?? ''); ?></td>
                                <td class="text-right">
                                    <?php echo $line['debit'] > 0 ? number_format($line['debit'], 2) . ' €' : '-'; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $line['credit'] > 0 ? number_format($line['credit'], 2) . ' €' : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">TOTALES:</th>
                            <th class="text-right"><?php echo number_format($totalDebit, 2); ?> €</th>
                            <th class="text-right"><?php echo number_format($totalCredit, 2); ?> €</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.detail-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-section {
    padding: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.detail-section:last-child {
    border-bottom: none;
}

.detail-section h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.detail-value {
    font-size: 1rem;
    color: var(--text-color);
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
