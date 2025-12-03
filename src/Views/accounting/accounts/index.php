<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-book"></i> Plan de Cuentas</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php?page=accounting&action=createAccount" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cuenta
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="accounts">
            
            <div class="filter-group">
                <label for="account_type">Tipo de Cuenta</label>
                <select name="account_type" id="account_type" class="form-control">
                    <option value="">Todos</option>
                    <option value="asset" <?php echo ($filters['account_type'] ?? '') === 'asset' ? 'selected' : ''; ?>>Activo</option>
                    <option value="liability" <?php echo ($filters['account_type'] ?? '') === 'liability' ? 'selected' : ''; ?>>Pasivo</option>
                    <option value="equity" <?php echo ($filters['account_type'] ?? '') === 'equity' ? 'selected' : ''; ?>>Patrimonio</option>
                    <option value="income" <?php echo ($filters['account_type'] ?? '') === 'income' ? 'selected' : ''; ?>>Ingresos</option>
                    <option value="expense" <?php echo ($filters['account_type'] ?? '') === 'expense' ? 'selected' : ''; ?>>Gastos</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="is_active">Estado</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="">Todos</option>
                    <option value="1" <?php echo ($filters['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Activas</option>
                    <option value="0" <?php echo ($filters['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactivas</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?page=accounting&action=accounts" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Accounts Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tipo de Saldo</th>
                        <th>Nivel</th>
                        <th>Cuenta Padre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay cuentas registradas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'asset' => 'Activo',
                                        'liability' => 'Pasivo',
                                        'equity' => 'Patrimonio',
                                        'income' => 'Ingresos',
                                        'expense' => 'Gastos'
                                    ];
                                    echo $typeLabels[$account['account_type']] ?? $account['account_type'];
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $account['balance_type'] === 'debit' ? 'info' : 'warning'; ?>">
                                        <?php echo ucfirst($account['balance_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo $account['level']; ?></td>
                                <td><?php echo htmlspecialchars($account['parent_name'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($account['is_active']): ?>
                                        <span class="badge badge-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="index.php?page=accounting&action=editAccount&id=<?php echo $account['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=accounting&action=generalLedger&account_id=<?php echo $account['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Ver Libro Mayor">
                                            <i class="fas fa-book-open"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
