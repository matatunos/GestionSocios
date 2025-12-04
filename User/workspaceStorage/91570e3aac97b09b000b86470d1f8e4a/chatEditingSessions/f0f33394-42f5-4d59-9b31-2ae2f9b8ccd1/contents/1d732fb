<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesar Conciliación</title>
</head>
<body>
    <div class="container">
        <h1>Conciliación Bancaria - Selección de Transacciones</h1>
        
        <div class="stats-row">
            <div class="stat-box">
                <strong>Saldo Extracto:</strong> <?= number_format($stats['statement_balance'], 2) ?> €
            </div>
            <div class="stat-box">
                <strong>Saldo Sistema:</strong> <?= number_format($stats['system_balance'], 2) ?> €
            </div>
            <div class="stat-box">
                <strong>Diferencia:</strong> 
                <span class="<?= abs($stats['difference']) > 0.01 ? 'amount-egreso' : 'amount-ingreso' ?>">
                    <?= number_format(abs($stats['difference']), 2) ?> €
                </span>
            </div>
        </div>
        
        <form method="POST" class="form-card">
            <p><strong>Selecciona las transacciones que aparecen en el extracto bancario:</strong></p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Importe</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="transactions[]" value="<?= $tx['id'] ?>" 
                                       <?= $tx['is_reconciled'] ? 'checked disabled' : '' ?>>
                            </td>
                            <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                            <td><?= htmlspecialchars($tx['description']) ?></td>
                            <td><?= ucfirst($tx['type']) ?></td>
                            <td class="amount-<?= $tx['type'] ?>"><?= ($tx['type'] === 'ingreso' ? '+' : '-') . number_format(abs($tx['amount']), 2) ?> €</td>
                            <td><?= $tx['is_reconciled'] ? '<span class="badge badge-success">Conciliada</span>' : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Marcar como Conciliadas</button>
                <a href="index.php?page=bank&subpage=reconciliation" class="btn-secondary">Cancelar</a>
            </div>
        </form>
        
        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="transactions[]"]:not(:disabled)');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        </script>
    </div>
</body>
</html>
