<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-book-open"></i> Libro Mayor</h1>
        <div class="header-actions">
            <?php if (isset($accountId) && $accountId): ?>
            <a href="index.php?page=accounting&action=exportReport&type=general_ledger&account_id=<?php echo $accountId; ?>&start_date=<?php echo urlencode($startDate ?? date('Y-01-01')); ?>&end_date=<?php echo urlencode($endDate ?? date('Y-12-31')); ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <?php endif; ?>
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="generalLedger">
            
            <div class="filter-group">
                <label for="account_id">Cuenta <span class="required">*</span></label>
                <select name="account_id" id="account_id" class="form-control" required>
                    <option value="">Seleccione una cuenta...</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>" 
                                <?php echo ($accountId ?? '') == $account['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

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

    <?php if (!empty($accountId) && !empty($ledgerData)): ?>
        <div class="report-card">
            <div class="report-header">
                <h2>
                    Libro Mayor - <?php echo htmlspecialchars($accountModel->code . ' - ' . $accountModel->name); ?>
                </h2>
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
                            <th>Fecha</th>
                            <th>Número Asiento</th>
                            <th>Descripción Asiento</th>
                            <th>Descripción Línea</th>
                            <th class="text-right">Débito</th>
                            <th class="text-right">Crédito</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $balance = 0;
                        foreach ($ledgerData as $row):
                            $balance += ($row['debit'] - $row['credit']);
                        ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['entry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['entry_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['line_description'] ?? ''); ?></td>
                                <td class="text-right">
                                    <?php echo $row['debit'] > 0 ? number_format($row['debit'], 2) . ' €' : '-'; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row['credit'] > 0 ? number_format($row['credit'], 2) . ' €' : '-'; ?>
                                </td>
                                <td class="text-right <?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($balance, 2); ?> €
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">SALDO FINAL:</th>
                            <th class="text-right <?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo number_format($balance, 2); ?> €
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php elseif (!empty($accountId) && empty($ledgerData)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No hay movimientos para la cuenta seleccionada en el período indicado.
        </div>
    <?php endif; ?>
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
}

.data-table thead th {
    padding: 0.875rem 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.data-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}

.data-table tbody tr {
    transition: background-color 0.15s ease;
}

.data-table tbody tr:hover {
    background-color: #fafbfc;
}

/* Number Formatting */
.text-right {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, 'SF Mono', 'Roboto Mono', monospace;
    font-weight: 400;
}

/* Footer Totals */
.data-table tfoot {
    background: #f9fafb;
    border-top: 2px solid #e5e7eb;
}

.data-table tfoot th {
    color: #111827;
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
}

/* Balance Colors */
.text-success {
    color: #059669;
    font-weight: 500;
}

.text-danger {
    color: #dc2626;
    font-weight: 500;
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
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
