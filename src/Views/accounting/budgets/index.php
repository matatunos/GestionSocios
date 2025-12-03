<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-chart-line"></i> Presupuestos</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php?page=budget&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Presupuesto
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="budget">
            
            <div class="filter-group">
                <label for="fiscal_year">Año Fiscal</label>
                <select name="fiscal_year" id="fiscal_year" class="form-control">
                    <option value="">Todos</option>
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear - 2; $year <= $currentYear + 2; $year++) {
                        $selected = ($filters['fiscal_year'] ?? '') == $year ? 'selected' : '';
                        echo "<option value=\"$year\" $selected>$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Estado</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="approved" <?php echo ($filters['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                    <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Cerrado</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="period_type">Tipo de Periodo</label>
                <select name="period_type" id="period_type" class="form-control">
                    <option value="">Todos</option>
                    <option value="yearly" <?php echo ($filters['period_type'] ?? '') === 'yearly' ? 'selected' : ''; ?>>Anual</option>
                    <option value="monthly" <?php echo ($filters['period_type'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                    <option value="quarterly" <?php echo ($filters['period_type'] ?? '') === 'quarterly' ? 'selected' : ''; ?>>Trimestral</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?page=budget" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Budgets Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Año Fiscal</th>
                        <th>Cuenta</th>
                        <th>Tipo de Periodo</th>
                        <th>Periodo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($budgets)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay presupuestos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($budgets as $budget): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($budget['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($budget['fiscal_year']); ?></td>
                                <td>
                                    <?php if (!empty($budget['account_code'])): ?>
                                        <?php echo htmlspecialchars($budget['account_code']); ?> - 
                                        <?php echo htmlspecialchars($budget['account_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $periodTypes = [
                                        'yearly' => 'Anual',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral'
                                    ];
                                    echo $periodTypes[$budget['period_type']] ?? $budget['period_type'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($budget['period_type'] === 'yearly') {
                                        echo 'Todo el año';
                                    } elseif ($budget['period_type'] === 'monthly') {
                                        $months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        echo $months[$budget['period_number']] ?? $budget['period_number'];
                                    } elseif ($budget['period_type'] === 'quarterly') {
                                        echo 'T' . $budget['period_number'];
                                    }
                                    ?>
                                </td>
                                <td class="text-right">
                                    <strong><?php echo number_format($budget['amount'], 2, ',', '.'); ?> €</strong>
                                </td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'draft' => '<span class="badge badge-secondary">Borrador</span>',
                                        'approved' => '<span class="badge badge-info">Aprobado</span>',
                                        'active' => '<span class="badge badge-success">Activo</span>',
                                        'closed' => '<span class="badge badge-dark">Cerrado</span>'
                                    ];
                                    echo $statusBadges[$budget['status']] ?? $budget['status'];
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=budget&action=edit&id=<?php echo $budget['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=budget&action=delete&id=<?php echo $budget['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro de eliminar este presupuesto?')" 
                                           title="Eliminar">
                                            <i class="fas fa-trash"></i>
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

    <?php if (!empty($budgets)): ?>
        <!-- Summary -->
        <div class="summary-card" style="margin-top: 20px;">
            <h3>Resumen</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?php echo count($budgets); ?></h4>
                            <p>Total Presupuestos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?php echo count(array_filter($budgets, fn($b) => $b['status'] === 'active')); ?></h4>
                            <p>Presupuestos Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?php echo number_format(array_sum(array_column($budgets, 'amount')), 2, ',', '.'); ?> €</h4>
                            <p>Monto Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.summary-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-box {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 24px;
}

.stat-icon.bg-primary { background: #007bff; }
.stat-icon.bg-success { background: #28a745; }
.stat-icon.bg-info { background: #17a2b8; }

.stat-content h4 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
