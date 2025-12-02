<?php
require_once __DIR__ . '/../../layout.php';
?>

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
.report-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.report-header {
    padding: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.report-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.report-header p {
    margin: 0;
    color: var(--text-secondary);
}

.group-header td {
    background: var(--hover-bg);
    font-weight: 600;
    padding: 0.75rem 1rem;
}

.text-success {
    color: #10b981;
    font-weight: 600;
}

.text-danger {
    color: #ef4444;
    font-weight: 600;
}
</style>
