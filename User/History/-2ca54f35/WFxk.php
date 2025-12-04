<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas Bancarias</title>
    <link rel="stylesheet" href="css/bank.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>💳 Cuentas Bancarias</h1>
            <a href="index.php?page=bank&subpage=accounts&action=create" class="btn-primary">➕ Nueva Cuenta</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($accounts)): ?>
            <div class="empty-state">
                <p>No hay cuentas registradas</p>
                <a href="index.php?page=bank&subpage=accounts&action=create" class="btn-primary">Crear Primera Cuenta</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Banco</th>
                        <th>IBAN</th>
                        <th>Tipo</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($acc['bank_name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($acc['iban']) ?></code></td>
                            <td><?= ucfirst($acc['account_type']) ?></td>
                            <td><strong><?= number_format($acc['current_balance'], 2) ?> €</strong></td>
                            <td>
                                <?php if ($acc['is_default']): ?>
                                    <span class="badge badge-success">Predeterminada</span>
                                <?php endif; ?>
                                <?php if ($acc['is_active']): ?>
                                    <span class="badge badge-primary">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=bank&subpage=accounts&action=view&id=<?= $acc['id'] ?>" class="btn-sm">Ver</a>
                                <a href="index.php?page=bank&subpage=accounts&action=edit&id=<?= $acc['id'] ?>" class="btn-sm">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
