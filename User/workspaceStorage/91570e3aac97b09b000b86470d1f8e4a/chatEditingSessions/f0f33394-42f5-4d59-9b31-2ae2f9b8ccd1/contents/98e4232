<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transacciones Bancarias</title>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>💸 Transacciones Bancarias</h1>
            <div>
                <a href="index.php?page=bank&subpage=transactions&action=create" class="btn-primary">➕ Nueva Transacción</a>
                <a href="index.php?page=bank&subpage=import" class="btn-secondary">📥 Importar</a>
            </div>
        </div>
        
        <form method="GET" class="filter-form">
            <input type="hidden" name="page" value="bank">
            <input type="hidden" name="subpage" value="transactions">
            <select name="account_id">
                <option value="">Todas las cuentas</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $filters['account_id'] == $acc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($acc['bank_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type">
                <option value="">Todos los tipos</option>
                <option value="ingreso" <?= $filters['type'] === 'ingreso' ? 'selected' : '' ?>>Ingresos</option>
                <option value="egreso" <?= $filters['type'] === 'egreso' ? 'selected' : '' ?>>Egresos</option>
            </select>
            <input type="date" name="date_from" value="<?= $filters['date_from'] ?>" placeholder="Desde">
            <input type="date" name="date_to" value="<?= $filters['date_to'] ?>" placeholder="Hasta">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Buscar...">
            <button type="submit" class="btn-primary">Filtrar</button>
        </form>
        
        <?php if (empty($transactions)): ?>
            <p>No hay transacciones que mostrar</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cuenta</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                            <td><?= htmlspecialchars(substr($tx['iban'], -4)) ?></td>
                            <td><a href="index.php?page=bank&subpage=transactions&action=view&id=<?= $tx['id'] ?>"><?= htmlspecialchars(substr($tx['description'], 0, 60)) ?></a></td>
                            <td><?= htmlspecialchars($tx['category'] ?: '-') ?></td>
                            <td><?= ucfirst($tx['type']) ?></td>
                            <td class="amount-<?= $tx['type'] ?>"><?= ($tx['type'] === 'ingreso' ? '+' : '-') . number_format(abs($tx['amount']), 2) ?> €</td>
                            <td>
                                <?php if ($tx['is_matched']): ?>
                                    <span class="badge badge-success">Emparejada</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Sin emparejar</span>
                                <?php endif; ?>
                                <?php if ($tx['is_reconciled']): ?>
                                    <span class="badge badge-info">Conciliada</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="index.php?page=bank&subpage=transactions&action=view&id=<?= $tx['id'] ?>" class="btn-sm">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=bank&subpage=transactions&page_num=<?= $i ?><?= http_build_query($filters) ?>" class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
