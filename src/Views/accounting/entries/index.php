<?php ob_start(); ?>

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
    <div class="entries-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Período</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="8" class="text-center empty-state">
                                <i class="fas fa-file-invoice"></i>
                                <p>No hay asientos registrados</p>
                            </td>
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
                                <td class="text-center">
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

<style>
/* Entries Card */
.entries-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    margin-top: 2rem;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

/* Table Styling */
.table-responsive {
    padding: 0;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.data-table thead {
    background: #f9fafb;
}

.data-table thead th {
    padding: 0.875rem 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
}

.data-table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}

.data-table tbody tr {
    transition: background-color 0.15s ease;
}

.data-table tbody tr:hover {
    background-color: #fafbfc;
}

/* Empty State */
.empty-state {
    padding: 3rem 1rem !important;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.empty-state p {
    margin: 0;
    font-size: 1rem;
}

/* Number Formatting */
.text-right {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, 'SF Mono', 'Roboto Mono', monospace;
    font-weight: 400;
}

.text-center {
    text-align: center;
}

/* Status Badges */
.badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.025em;
    border: 1px solid;
}

.badge-warning {
    color: #d97706;
    background: #fef3c7;
    border-color: #fde68a;
}

.badge-success {
    color: #059669;
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.badge-danger {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border-top: 1px solid #f3f4f6;
}

.pagination a {
    padding: 0.5rem 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.15s ease;
}

.pagination a:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.pagination a.active {
    background: #111827;
    color: white;
    border-color: #111827;
}

/* Responsive Design */
@media (max-width: 768px) {
    .data-table {
        font-size: 0.85rem;
    }
    
    .data-table thead th,
    .data-table tbody td {
        padding: 0.625rem 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

/* Print Styles */
@media print {
    .entries-card {
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
    
    .data-table tbody tr:hover {
        background-color: transparent !important;
    }
    
    .action-buttons,
    .pagination {
        display: none;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
