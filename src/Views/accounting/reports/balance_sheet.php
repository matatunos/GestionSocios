<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-balance-scale"></i> Balance de Situación</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=exportReport&type=balance_sheet&end_date=<?php echo urlencode($endDate ?? date('Y-12-31')); ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card no-print">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="balanceSheet">
            
            <div class="filter-group">
                <label for="period_id">Periodo Contable</label>
                <select name="period_id" id="period_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Seleccionar periodo...</option>
                    <?php foreach ($periods as $period): ?>
                        <option value="<?php echo $period['id']; ?>" 
                                <?php echo ($periodId ?? '') == $period['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($period['name']); ?> 
                            (<?php echo date('d/m/Y', strtotime($period['start_date'])); ?> - 
                             <?php echo date('d/m/Y', strtotime($period['end_date'])); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="end_date">O seleccione fecha de corte</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="<?php echo $endDate; ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-chart-line"></i> Generar
                </button>
            </div>
        </form>
    </div>

    <!-- Balance Sheet Header -->
    <div class="report-header">
        <h2>Balance de Situación</h2>
        <p>A fecha: <strong><?php echo date('d/m/Y', strtotime($endDate)); ?></strong></p>
    </div>

    <!-- Balance Sheet Content -->
    <div class="balance-sheet-container">
        <!-- ACTIVO (Assets) -->
        <div class="balance-section">
            <h3 class="section-title bg-primary">ACTIVO</h3>
            <table class="balance-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Importe €</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay cuentas de activo con saldo</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $account): ?>
                            <tr class="level-<?php echo $account['level']; ?>">
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td class="text-right"><?php echo number_format($account['balance'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL ACTIVO</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalAssets, 2, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- PASIVO Y PATRIMONIO NETO (Liabilities & Equity) -->
        <div class="balance-section">
            <h3 class="section-title bg-danger">PASIVO Y PATRIMONIO NETO</h3>
            
            <!-- Patrimonio Neto -->
            <h4 class="subsection-title">Patrimonio Neto</h4>
            <table class="balance-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Importe €</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equity)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay cuentas de patrimonio con saldo</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equity as $account): ?>
                            <tr class="level-<?php echo $account['level']; ?>">
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td class="text-right"><?php echo number_format($account['balance'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="2"><strong>Total Patrimonio Neto</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalEquity, 2, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Pasivo -->
            <h4 class="subsection-title">Pasivo</h4>
            <table class="balance-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Importe €</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liabilities)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay cuentas de pasivo con saldo</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($liabilities as $account): ?>
                            <tr class="level-<?php echo $account['level']; ?>">
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td class="text-right"><?php echo number_format($account['balance'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="2"><strong>Total Pasivo</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalLiabilities, 2, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Total Pasivo + Patrimonio -->
            <table class="balance-table">
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL PASIVO Y PATRIMONIO NETO</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalLiabilities + $totalEquity, 2, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Balance Check -->
    <?php
    $difference = $totalAssets - ($totalLiabilities + $totalEquity);
    $isBalanced = abs($difference) < 0.01; // Tolerance for rounding
    ?>
    <div class="balance-check <?php echo $isBalanced ? 'balanced' : 'unbalanced'; ?>">
        <?php if ($isBalanced): ?>
            <i class="fas fa-check-circle"></i> El balance está cuadrado
        <?php else: ?>
            <i class="fas fa-exclamation-triangle"></i> 
            Descuadre: <?php echo number_format($difference, 2, ',', '.'); ?> €
        <?php endif; ?>
    </div>
</div>

<style>
.report-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-header h2 {
    margin: 0 0 10px 0;
    color: #333;
}

.report-header p {
    margin: 0;
    color: #666;
}

.balance-sheet-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.balance-section {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title {
    color: white;
    padding: 15px;
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.section-title.bg-primary {
    background: #007bff;
}

.section-title.bg-danger {
    background: #dc3545;
}

.subsection-title {
    padding: 10px 15px;
    margin: 0;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-size: 16px;
}

.balance-table {
    width: 100%;
    border-collapse: collapse;
}

.balance-table thead th {
    background: #f8f9fa;
    padding: 10px;
    border-bottom: 2px solid #dee2e6;
    font-weight: bold;
    text-align: left;
}

.balance-table tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid #e9ecef;
}

.balance-table tr.level-0 td {
    font-weight: bold;
    padding-left: 10px;
}

.balance-table tr.level-1 td:nth-child(2) {
    padding-left: 25px;
}

.balance-table tr.level-2 td:nth-child(2) {
    padding-left: 40px;
}

.balance-table tr.level-3 td:nth-child(2) {
    padding-left: 55px;
}

.balance-table tfoot tr {
    background: #e9ecef;
    font-weight: bold;
}

.balance-table tfoot td {
    padding: 12px 10px;
    border-top: 2px solid #333;
}

.total-row {
    background: #dee2e6 !important;
}

.subtotal-row {
    background: #f8f9fa !important;
}

.balance-check {
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
}

.balance-check.balanced {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.balance-check.unbalanced {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.balance-check i {
    margin-right: 8px;
}

@media (max-width: 1024px) {
    .balance-sheet-container {
        grid-template-columns: 1fr;
    }
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .content-header .header-actions {
        display: none !important;
    }
    
    .balance-sheet-container {
        page-break-inside: avoid;
    }
}
</style>
