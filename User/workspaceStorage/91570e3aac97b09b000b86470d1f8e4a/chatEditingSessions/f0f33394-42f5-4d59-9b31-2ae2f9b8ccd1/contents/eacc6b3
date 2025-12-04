<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Conciliaciones Bancarias</title>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>✓ Conciliaciones Bancarias</h1>
            <a href="index.php?page=bank&subpage=reconciliation&action=start" class="btn-primary">Iniciar Conciliación</a>
        </div>
        
        <?php if (empty($reconciliations)): ?>
            <p>No hay conciliaciones registradas</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Período</th>
                        <th>Saldo Extracto</th>
                        <th>Saldo Sistema</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reconciliations as $rec): ?>
                        <tr>
                            <td><?= htmlspecialchars($rec['bank_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($rec['period_start'])) ?> - <?= date('d/m/Y', strtotime($rec['period_end'])) ?></td>
                            <td><?= number_format($rec['statement_balance'], 2) ?> €</td>
                            <td><?= number_format($rec['system_balance'], 2) ?> €</td>
                            <td class="<?= abs($rec['statement_balance'] - $rec['system_balance']) > 0.01 ? 'amount-egreso' : 'amount-ingreso' ?>">
                                <?= number_format(abs($rec['statement_balance'] - $rec['system_balance']), 2) ?> €
                            </td>
                            <td>
                                <?php if ($rec['completed_at']): ?>
                                    <span class="badge badge-success">Completada</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">En proceso</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$rec['completed_at']): ?>
                                    <a href="index.php?page=bank&subpage=reconciliation&action=process&id=<?= $rec['id'] ?>" class="btn-sm">Continuar</a>
                                <?php else: ?>
                                    <a href="index.php?page=bank&subpage=reconciliation&action=view&id=<?= $rec['id'] ?>" class="btn-sm">Ver</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
