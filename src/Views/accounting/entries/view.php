<?php ob_start(); ?>

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

    <div class="entry-view-card">
        <!-- Entry Header Info -->
        <div class="entry-header">
            <div class="entry-header-main">
                <div class="entry-number">
                    <span class="entry-number-label">Asiento N°</span>
                    <span class="entry-number-value"><?php echo htmlspecialchars($entry['entry_number']); ?></span>
                </div>
                <div class="entry-status">
                    <?php
                    $statusLabels = [
                        'draft' => '<span class="badge badge-warning">Borrador</span>',
                        'posted' => '<span class="badge badge-success">Contabilizado</span>',
                        'cancelled' => '<span class="badge badge-danger">Cancelado</span>'
                    ];
                    echo $statusLabels[$entry['status']] ?? $entry['status'];
                    ?>
                </div>
            </div>
            <div class="entry-meta">
                <div class="entry-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?></span>
                </div>
                <div class="entry-meta-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo htmlspecialchars($entry['period_name'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="entry-description">
            <h3>Descripción</h3>
            <p><?php echo htmlspecialchars($entry['description']); ?></p>
            <?php if (!empty($entry['reference'])): ?>
                <div class="entry-reference">
                    <strong>Referencia:</strong> <?php echo htmlspecialchars($entry['reference']); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Entry Lines Table -->
        <div class="entry-lines">
            <h3>Movimientos Contables</h3>
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
                                    <div class="account-info">
                                        <strong class="account-code"><?php echo htmlspecialchars($line['account_code']); ?></strong>
                                        <span class="account-name"><?php echo htmlspecialchars($line['account_name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($line['description'] ?? ''); ?></td>
                                <td class="text-right amount-debit">
                                    <?php echo $line['debit'] > 0 ? number_format($line['debit'], 2) . ' €' : '-'; ?>
                                </td>
                                <td class="text-right amount-credit">
                                    <?php echo $line['credit'] > 0 ? number_format($line['credit'], 2) . ' €' : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">TOTALES:</th>
                            <th class="text-right total-debit"><?php echo number_format($totalDebit, 2); ?> €</th>
                            <th class="text-right total-credit"><?php echo number_format($totalCredit, 2); ?> €</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Entry Footer Info -->
        <div class="entry-footer">
            <div class="entry-footer-item">
                <i class="fas fa-user"></i>
                <span>Creado por <strong><?php echo htmlspecialchars($entry['created_by_name'] ?? 'N/A'); ?></strong></span>
            </div>
            <div class="entry-footer-item">
                <i class="fas fa-clock"></i>
                <span><?php echo date('d/m/Y H:i', strtotime($entry['created_at'])); ?></span>
            </div>
        </div>
    </div>
</div>

<style>
/* Entry View Card */
.entry-view-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

/* Entry Header */
.entry-header {
    padding: 2rem;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.entry-header-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.entry-number {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.entry-number-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 600;
}

.entry-number-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.entry-meta {
    display: flex;
    gap: 1.5rem;
    color: #6b7280;
    font-size: 0.9rem;
}

.entry-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.entry-meta-item i {
    color: #9ca3af;
}

/* Description Section */
.entry-description {
    padding: 2rem;
    border-bottom: 1px solid #f3f4f6;
}

.entry-description h3 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    font-size: 0.875rem;
}

.entry-description p {
    margin: 0;
    color: #111827;
    font-size: 1rem;
    line-height: 1.5;
}

.entry-reference {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #92400e;
}

/* Entry Lines */
.entry-lines {
    padding: 0;
}

.entry-lines h3 {
    padding: 1.5rem 2rem 1rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    font-size: 0.875rem;
}

.table-responsive {
    padding: 0;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.data-table thead {
    background: #f9fafb;
}

.data-table thead th {
    padding: 0.875rem 2rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
}

.data-table tbody td {
    padding: 1rem 2rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}

.data-table tbody tr {
    transition: background-color 0.15s ease;
}

.data-table tbody tr:hover {
    background-color: #fafbfc;
}

/* Account Info */
.account-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.account-code {
    font-size: 0.95rem;
    color: #111827;
}

.account-name {
    font-size: 0.85rem;
    color: #6b7280;
}

/* Amounts */
.text-right {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, 'SF Mono', 'Roboto Mono', monospace;
}

.amount-debit {
    color: #dc2626;
    font-weight: 500;
}

.amount-credit {
    color: #059669;
    font-weight: 500;
}

/* Footer Totals */
.data-table tfoot {
    background: #f9fafb;
    border-top: 2px solid #e5e7eb;
}

.data-table tfoot th {
    color: #111827;
    padding: 1rem 2rem;
    font-size: 0.9rem;
    font-weight: 700;
}

.total-debit {
    color: #dc2626;
}

.total-credit {
    color: #059669;
}

/* Status Badges */
.badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.025em;
    border: 1px solid;
}

.badge-warning {
    color: #d97706;
    background: #fef3c7;
    border-color: #fde68a;
}

.badge-success {
    color: #059669;
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.badge-danger {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}

/* Entry Footer */
.entry-footer {
    padding: 1.5rem 2rem;
    background: #fafbfc;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.entry-footer-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.entry-footer-item i {
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    .entry-header-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .entry-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .entry-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .data-table thead th,
    .data-table tbody td,
    .data-table tfoot th {
        padding: 0.75rem 1rem;
    }
}

/* Print Styles */
@media print {
    .entry-view-card {
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
    
    .data-table tbody tr:hover {
        background-color: transparent !important;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
