<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-balance-scale"></i> Balance de Sumas y Saldos</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=exportReport&type=trial_balance&start_date=<?php echo urlencode($startDate ?? date('Y-01-01')); ?>&end_date=<?php echo urlencode($endDate ?? date('Y-12-31')); ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="trialBalance">
            
            <div class="filter-group">
                <label for="start_date">Fecha Desde</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       value="<?php echo htmlspecialchars($startDate ?? date('Y-01-01')); ?>">
            </div>

            <div class="filter-group">
                <label for="end_date">Fecha Hasta</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="<?php echo htmlspecialchars($endDate ?? date('Y-12-31')); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Consultar
                </button>
            </div>
        </form>
    </div>

    <div class="report-card">
        <div class="report-header">
            <h2>Balance de Sumas y Saldos</h2>
            <p>
                Período: 
                <?php 
                $startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
                $endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);
                echo ($startDateTime ? $startDateTime->format('d/m/Y') : 'Fecha inválida') . ' - ' . 
                     ($endDateTime ? $endDateTime->format('d/m/Y') : 'Fecha inválida');
                ?>
            </p>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2">Código</th>
                        <th rowspan="2">Cuenta</th>
                        <th rowspan="2">Tipo</th>
                        <th colspan="2" class="text-center">Sumas</th>
                        <th colspan="2" class="text-center">Saldos</th>
                    </tr>
                    <tr>
                        <th class="text-right">Debe</th>
                        <th class="text-right">Haber</th>
                        <th class="text-right">Deudor</th>
                        <th class="text-right">Acreedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalDebit = 0;
                    $totalCredit = 0;
                    $totalDebtorBalance = 0;
                    $totalCreditorBalance = 0;

                    // Group by account type
                    $groupedBalances = [];
                    foreach ($balances as $balance) {
                        $groupedBalances[$balance['account_type']][] = $balance;
                    }

                    $typeLabels = [
                        'asset' => 'ACTIVO',
                        'liability' => 'PASIVO',
                        'equity' => 'PATRIMONIO',
                        'income' => 'INGRESOS',
                        'expense' => 'GASTOS'
                    ];

                    foreach ($groupedBalances as $type => $items):
                    ?>
                        <tr class="group-header">
                            <td colspan="7"><strong><?php echo $typeLabels[$type] ?? $type; ?></strong></td>
                        </tr>
                        <?php foreach ($items as $balance):
                            $debtorBalance = max(0, $balance['total_debit'] - $balance['total_credit']);
                            $creditorBalance = max(0, $balance['total_credit'] - $balance['total_debit']);
                            
                            $totalDebit += $balance['total_debit'];
                            $totalCredit += $balance['total_credit'];
                            $totalDebtorBalance += $debtorBalance;
                            $totalCreditorBalance += $creditorBalance;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($balance['code']); ?></td>
                                <td><?php echo htmlspecialchars($balance['name']); ?></td>
                                <td>
                                    <?php
                                    $typeLabel = [
                                        'asset' => 'Activo',
                                        'liability' => 'Pasivo',
                                        'equity' => 'Patrimonio',
                                        'income' => 'Ingresos',
                                        'expense' => 'Gastos'
                                    ];
                                    echo $typeLabel[$balance['account_type']] ?? $balance['account_type'];
                                    ?>
                                </td>
                                <td class="text-right"><?php echo number_format($balance['total_debit'], 2); ?> €</td>
                                <td class="text-right"><?php echo number_format($balance['total_credit'], 2); ?> €</td>
                                <td class="text-right">
                                    <?php echo $debtorBalance > 0 ? number_format($debtorBalance, 2) . ' €' : '-'; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $creditorBalance > 0 ? number_format($creditorBalance, 2) . ' €' : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">TOTALES:</th>
                        <th class="text-right"><?php echo number_format($totalDebit, 2); ?> €</th>
                        <th class="text-right"><?php echo number_format($totalCredit, 2); ?> €</th>
                        <th class="text-right"><?php echo number_format($totalDebtorBalance, 2); ?> €</th>
                        <th class="text-right"><?php echo number_format($totalCreditorBalance, 2); ?> €</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Verification Summary -->
        <div class="verification-summary">
            <div class="verification-item">
                <div class="verification-label">Verificación de Sumas:</div>
                <div class="verification-value <?php echo abs($totalDebit - $totalCredit) < 0.01 ? 'status-success' : 'status-error'; ?>">
                    <?php if (abs($totalDebit - $totalCredit) < 0.01): ?>
                        <i class="fas fa-check-circle"></i> Cuadrado
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i> Descuadrado (Diferencia: <?php echo number_format(abs($totalDebit - $totalCredit), 2); ?> €)
                    <?php endif; ?>
                </div>
            </div>
            <div class="verification-item">
                <div class="verification-label">Verificación de Saldos:</div>
                <div class="verification-value <?php echo abs($totalDebtorBalance - $totalCreditorBalance) < 0.01 ? 'status-success' : 'status-error'; ?>">
                    <?php if (abs($totalDebtorBalance - $totalCreditorBalance) < 0.01): ?>
                        <i class="fas fa-check-circle"></i> Cuadrado
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i> Descuadrado (Diferencia: <?php echo number_format(abs($totalDebtorBalance - $totalCreditorBalance), 2); ?> €)
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Report Card Container */
.report-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    margin-top: 2rem;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.report-header {
    padding: 2rem;
    background: white;
    border-bottom: 2px solid #f3f4f6;
}

.report-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
}

.report-header p {
    margin: 0;
    color: #6b7280;
    font-size: 0.95rem;
}

/* Table Styling */
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
    background: #fafafa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table thead th {
    padding: 0.875rem 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
}

.data-table thead tr:first-child th {
    background: #f9fafb;
    color: #111827;
    border-bottom: 1px solid #d1d5db;
    padding-bottom: 0.5rem;
}

.data-table thead tr:last-child th {
    background: #f3f4f6;
    color: #374151;
    padding-top: 0.5rem;
}

.data-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}

.data-table tbody tr {
    transition: background-color 0.15s ease;
}

.data-table tbody tr:hover:not(.group-header) {
    background-color: #fafbfc;
}

/* Group Headers - Diseño minimalista */
.group-header td {
    background: #f9fafb !important;
    color: #111827 !important;
    font-weight: 600;
    padding: 0.875rem 1rem !important;
    font-size: 0.85rem;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    border-top: 2px solid #e5e7eb !important;
    border-bottom: 1px solid #d1d5db !important;
}

/* Number Formatting */
.text-right {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, 'SF Mono', 'Roboto Mono', monospace;
    font-weight: 400;
}

.text-center {
    text-align: center;
}

/* Footer Totals - Más limpio */
.data-table tfoot {
    background: white;
    border-top: 2px solid #e5e7eb;
}

.data-table tfoot tr:first-child {
    background: #f9fafb;
}

.data-table tfoot tr:first-child th {
    color: #111827;
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.data-table tfoot tr:last-child {
    background: white;
    border-top: 1px solid #e5e7eb;
}

.data-table tfoot tr:last-child th {
    padding: 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
}

/* Verification Summary */
.verification-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem 2rem 2rem;
    background: #fafbfc;
    border-top: 1px solid #e5e7eb;
}

.verification-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.verification-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.verification-value {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 2px solid;
}

.verification-value i {
    font-size: 1.125rem;
}

.status-success {
    color: #059669;
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.status-error {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}

/* Responsive Design */
@media (max-width: 768px) {
    .report-header {
        padding: 1.5rem 1rem;
    }
    
    .report-header h2 {
        font-size: 1.25rem;
    }
    
    .data-table {
        font-size: 0.85rem;
    }
    
    .data-table thead th,
    .data-table tbody td,
    .data-table tfoot th {
        padding: 0.625rem 0.5rem;
    }
    
    .group-header td {
        font-size: 0.8rem;
    }
}

/* Print Styles */
@media print {
    .report-card {
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
    
    .data-table tbody tr:hover {
        background-color: transparent !important;
    }
    
    .group-header td {
        background: #f9fafb !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
