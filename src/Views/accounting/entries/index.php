<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-file-invoice"></i> Libro Diario</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php?page=accounting&action=createEntry" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Asiento
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="entries">
            
            <div class="filter-group">
                <label for="period_id">Período</label>
                <select name="period_id" id="period_id" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($periods as $period): ?>
                        <option value="<?php echo $period['id']; ?>" 
                                <?php echo ($filters['period_id'] ?? '') == $period['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($period['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Estado</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="posted" <?php echo ($filters['status'] ?? '') === 'posted' ? 'selected' : ''; ?>>Contabilizado</option>
                    <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="start_date">Fecha Desde</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <label for="end_date">Fecha Hasta</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?page=accounting&action=entries" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Entries Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Período</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay asientos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($entry['entry_number']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($entry['description']); ?></td>
                                <td><?php echo htmlspecialchars($entry['period_name'] ?? ''); ?></td>
                                <td class="text-right"><?php echo number_format($entry['total_debit'], 2); ?> €</td>
                                <td class="text-right"><?php echo number_format($entry['total_credit'], 2); ?> €</td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'draft' => '<span class="badge badge-warning">Borrador</span>',
                                        'posted' => '<span class="badge badge-success">Contabilizado</span>',
                                        'cancelled' => '<span class="badge badge-danger">Cancelado</span>'
                                    ];
                                    echo $statusLabels[$entry['status']] ?? $entry['status'];
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="index.php?page=accounting&action=viewEntry&id=<?php echo $entry['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($entry['status'] === 'draft'): ?>
                                            <a href="index.php?page=accounting&action=postEntry&id=<?php echo $entry['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Contabilizar"
                                               onclick="return confirm('¿Está seguro de contabilizar este asiento? Esta acción no se puede deshacer.');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=accounting&action=entries&page_num=<?php echo $i; ?><?php 
                        echo isset($filters['period_id']) && $filters['period_id'] ? '&period_id=' . $filters['period_id'] : '';
                        echo isset($filters['status']) && $filters['status'] ? '&status=' . $filters['status'] : '';
                    ?>" 
                       class="<?php echo ($page_num ?? 1) == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
