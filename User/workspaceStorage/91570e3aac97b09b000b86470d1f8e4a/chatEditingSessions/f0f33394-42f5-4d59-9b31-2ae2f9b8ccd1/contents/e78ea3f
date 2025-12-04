<?php
$pageTitle = 'Facturas Emitidas';
require_once __DIR__ . '/../layout/header.php';
?>

<style>
.invoice-header {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.invoice-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1rem 0;
}

.filters-section {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.filter-group input,
.filter-group select {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.2s;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}

.btn-success {
    background: #059669;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-info {
    background: #0891b2;
    color: white;
}

.invoices-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.invoices-table table {
    width: 100%;
    border-collapse: collapse;
}

.invoices-table thead {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.invoices-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
}

.invoices-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.875rem;
    color: #111827;
}

.invoices-table tbody tr:hover {
    background: #f9fafb;
}

.invoice-number {
    font-weight: 600;
    color: #2563eb;
    font-family: ui-monospace, monospace;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.status-draft {
    background: #fef3c7;
    color: #92400e;
}

.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.amount {
    font-family: ui-monospace, monospace;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.empty-state svg {
    width: 3rem;
    height: 3rem;
    margin: 0 auto 1rem;
    color: #d1d5db;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.pagination a,
.pagination span {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 0.875rem;
    text-decoration: none;
    color: #374151;
}

.pagination a:hover {
    background: #f9fafb;
}

.pagination .active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1rem;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 600;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0.25rem;
}
</style>

<div class="invoice-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Facturas Emitidas</h1>
        <a href="index.php?page=invoices&action=create" class="btn btn-primary">
            + Nueva Factura
        </a>
    </div>
    
    <form method="GET" class="filters-section">
        <input type="hidden" name="page" value="invoices">
        
        <div class="filters-row">
            <div class="filter-group">
                <label>Estado</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Borrador</option>
                    <option value="issued" <?= ($filters['status'] ?? '') === 'issued' ? 'selected' : '' ?>>Emitida</option>
                    <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Pagada</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Serie</label>
                <select name="series_id">
                    <option value="">Todas</option>
                    <?php foreach ($series as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($filters['series_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Tipo Cliente</label>
                <select name="customer_type">
                    <option value="">Todos</option>
                    <option value="member" <?= ($filters['customer_type'] ?? '') === 'member' ? 'selected' : '' ?>>Socio</option>
                    <option value="external" <?= ($filters['customer_type'] ?? '') === 'external' ? 'selected' : '' ?>>Externo</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Desde</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" placeholder="Número, cliente..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="index.php?page=invoices" class="btn btn-secondary btn-sm">Limpiar</a>
        </div>
    </form>
</div>

<?php if (empty($invoices)): ?>
    <div class="invoices-table">
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No hay facturas</p>
            <p style="font-size: 0.875rem;">Crea tu primera factura para comenzar</p>
        </div>
    </div>
<?php else: ?>
    <div class="invoices-table">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Base</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td>
                            <span class="invoice-number"><?= htmlspecialchars($invoice['full_number']) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($invoice['issue_date'])) ?></td>
                        <td>
                            <?= htmlspecialchars($invoice['customer_name']) ?>
                            <?php if ($invoice['customer_type'] === 'member'): ?>
                                <span style="color: #6b7280; font-size: 0.75rem;">(Socio #<?= $invoice['member_number'] ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="amount"><?= number_format($invoice['subtotal'] - $invoice['discount_amount'], 2) ?> €</td>
                        <td class="amount"><?= number_format($invoice['tax_amount'], 2) ?> €</td>
                        <td class="amount" style="font-weight: 600;"><?= number_format($invoice['total'], 2) ?> €</td>
                        <td>
                            <span class="status-badge status-<?= $invoice['status'] ?>">
                                <?php
                                $statuses = [
                                    'draft' => 'Borrador',
                                    'issued' => 'Emitida',
                                    'paid' => 'Pagada',
                                    'cancelled' => 'Cancelada'
                                ];
                                echo $statuses[$invoice['status']] ?? $invoice['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=invoices&action=view&id=<?= $invoice['id'] ?>" 
                               class="btn btn-info btn-sm">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=invoices&p=<?= $page - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                    ← Anterior
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=invoices&p=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=invoices&p=<?= $page + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                    Siguiente →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
