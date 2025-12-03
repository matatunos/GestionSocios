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
                    <tr>
                        <th colspan="3" class="text-right">VERIFICACIÓN:</th>
                        <th colspan="2" class="text-center <?php echo abs($totalDebit - $totalCredit) < 0.01 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo abs($totalDebit - $totalCredit) < 0.01 ? '✓ Cuadrado' : '✗ Descuadrado'; ?>
                        </th>
                        <th colspan="2" class="text-center <?php echo abs($totalDebtorBalance - $totalCreditorBalance) < 0.01 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo abs($totalDebtorBalance - $totalCreditorBalance) < 0.01 ? '✓ Cuadrado' : '✗ Descuadrado'; ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
/* Report Card Container */
.report-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
    overflow: hidden;
}

.report-header {
    padding: 2rem 2rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}

.report-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
}

.report-header p {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    font-weight: 500;
}

/* Table Styling */
.table-responsive {
    padding: 0;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.data-table thead {
    background: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table thead th {
    padding: 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e5e7eb;
}

.data-table thead tr:first-child th {
    background: #1f2937;
    color: white;
    border-bottom: 1px solid #374151;
}

.data-table thead tr:last-child th {
    background: #374151;
    color: white;
}

.data-table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #1f2937;
}

.data-table tbody tr {
    transition: background-color 0.2s ease;
}

.data-table tbody tr:hover:not(.group-header) {
    background-color: #f9fafb;
}

.data-table tbody tr:nth-child(even):not(.group-header) {
    background-color: #fafbfc;
}

/* Group Headers */
.group-header td {
    background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%) !important;
    color: white !important;
    font-weight: 700;
    padding: 1rem 1rem !important;
    font-size: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border-bottom: 2px solid #1e40af !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Account Type Colors */
.data-table tbody tr:not(.group-header):hover {
    box-shadow: inset 4px 0 0 #3b82f6;
}

/* Number Formatting */
.text-right {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.text-center {
    text-align: center;
}

/* Footer Totals */
.data-table tfoot {
    background: #f8f9fa;
    border-top: 3px solid #3b82f6;
}

.data-table tfoot tr:first-child {
    background: linear-gradient(90deg, #1f2937 0%, #374151 100%);
}

.data-table tfoot tr:first-child th {
    color: white;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.data-table tfoot tr:last-child {
    background: #e5e7eb;
}

.data-table tfoot tr:last-child th {
    padding: 1.25rem 1rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
}

/* Status Badges */
.text-success {
    color: #059669 !important;
    font-weight: 700;
    background: #d1fae5;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.text-danger {
    color: #dc2626 !important;
    font-weight: 700;
    background: #fee2e2;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
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
        padding: 0.75rem 0.5rem;
    }
    
    .group-header td {
        font-size: 0.9rem;
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
        background: #3b82f6 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
