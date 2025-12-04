<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Cuenta</title>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><?= htmlspecialchars($accountModel->bank_name) ?></h1>
            <div>
                <a href="index.php?page=bank&subpage=accounts&action=edit&id=<?= $accountModel->id ?>" class="btn-primary">Editar</a>
                <a href="index.php?page=bank&subpage=transactions&account_id=<?= $accountModel->id ?>" class="btn-secondary">Ver Movimientos</a>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>Saldo Actual</h3>
                <p class="amount"><?= number_format($accountModel->current_balance, 2) ?> €</p>
            </div>
            
            <div class="info-card">
                <h3>Total Ingresos</h3>
                <p class="amount-ingreso">+<?= number_format($stats['total_ingresos'], 2) ?> €</p>
            </div>
            
            <div class="info-card">
                <h3>Total Egresos</h3>
                <p class="amount-egreso">-<?= number_format($stats['total_egresos'], 2) ?> €</p>
            </div>
            
            <div class="info-card">
                <h3>Transacciones</h3>
                <p><?= $stats['total_transactions'] ?></p>
            </div>
        </div>
        
        <div class="details-section">
            <h2>Información de la Cuenta</h2>
            <dl>
                <dt>Titular:</dt><dd><?= htmlspecialchars($accountModel->account_holder) ?></dd>
                <dt>IBAN:</dt><dd><code><?= htmlspecialchars($accountModel->iban) ?></code></dd>
                <dt>SWIFT/BIC:</dt><dd><?= htmlspecialchars($accountModel->swift_bic ?: '-') ?></dd>
                <dt>Tipo:</dt><dd><?= ucfirst($accountModel->account_type) ?></dd>
                <dt>Moneda:</dt><dd><?= $accountModel->currency ?></dd>
                <dt>Estado:</dt><dd><?= $accountModel->is_active ? 'Activa' : 'Inactiva' ?></dd>
                <?php if ($accountModel->notes): ?>
                    <dt>Notas:</dt><dd><?= htmlspecialchars($accountModel->notes) ?></dd>
                <?php endif; ?>
            </dl>
        </div>
        
        <div class="transactions-section">
            <h2>Movimientos Recientes</h2>
            <?php if (empty($transactions)): ?>
                <p>No hay movimientos registrados</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Importe</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                                <td><a href="index.php?page=bank&subpage=transactions&action=view&id=<?= $tx['id'] ?>"><?= htmlspecialchars($tx['description']) ?></a></td>
                                <td><?= ucfirst($tx['type']) ?></td>
                                <td class="amount-<?= $tx['type'] ?>"><?= ($tx['type'] === 'ingreso' ? '+' : '-') . number_format(abs($tx['amount']), 2) ?> €</td>
                                <td><?= number_format($tx['balance_after'], 2) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="index.php?page=bank&subpage=transactions&account_id=<?= $accountModel->id ?>">Ver todos los movimientos →</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
