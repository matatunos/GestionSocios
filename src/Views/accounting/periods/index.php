<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-calendar-alt"></i> Periodos Contables</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php?page=accounting&action=createPeriod" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Periodo
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="periods">
            
            <div class="filter-group">
                <label for="fiscal_year">Año Fiscal</label>
                <select name="fiscal_year" id="fiscal_year" class="form-control">
                    <option value="">Todos</option>
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear - 5; $year <= $currentYear + 2; $year++) {
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
                    <option value="open" <?php echo ($filters['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Abierto</option>
                    <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Cerrado</option>
                    <option value="locked" <?php echo ($filters['status'] ?? '') === 'locked' ? 'selected' : ''; ?>>Bloqueado</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?page=accounting&action=periods" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Periods Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Año Fiscal</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Asientos</th>
                        <th>Creado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($periods)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay periodos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($periods as $period): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($period['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($period['fiscal_year']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($period['start_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($period['end_date'])); ?></td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'open' => '<span class="badge badge-success">Abierto</span>',
                                        'closed' => '<span class="badge badge-secondary">Cerrado</span>',
                                        'locked' => '<span class="badge badge-dark">Bloqueado</span>'
                                    ];
                                    echo $statusBadges[$period['status']] ?? $period['status'];
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($period['entries_count'] > 0): ?>
                                        <span class="badge badge-info"><?php echo $period['entries_count']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($period['created_by_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($period['status'] === 'open'): ?>
                                            <a href="index.php?page=accounting&action=editPeriod&id=<?php echo $period['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=accounting&action=closePeriod&id=<?php echo $period['id']; ?>" 
                                               class="btn btn-sm btn-warning" 
                                               onclick="return confirm('¿Está seguro de cerrar este periodo? Esta acción no se puede deshacer.')" 
                                               title="Cerrar Periodo">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-lock"></i> Cerrado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info" style="margin-top: 20px;">
        <i class="fas fa-info-circle"></i> 
        <strong>Importante:</strong> Los periodos cerrados no permiten crear nuevos asientos contables. 
        Asegúrese de que todos los asientos del periodo estén correctamente registrados antes de cerrarlo.
    </div>
</div>

<style>
.btn-group {
    display: flex;
    gap: 5px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert i {
    margin-right: 8px;
}
</style>
