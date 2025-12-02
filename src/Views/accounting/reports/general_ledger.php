<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-book-open"></i> Libro Mayor</h1>
        <div class="header-actions">
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

.text-success {
    color: #10b981;
}

.text-danger {
    color: #ef4444;
}
</style>
