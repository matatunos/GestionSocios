<?php
$pageTitle = "Dashboard de Proveedores";
ob_start();

// Prepare chart data
$months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$chartData = array_values($monthlyStats);
?>

<style>
.suppliers-dashboard {
    padding: 2rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.year-selector {
    position: relative;
}

.year-selector select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border: 1px solid var(--border-light);
    border-radius: 0.5rem;
    background: var(--bg-card);
    color: var(--text-main);
    cursor: pointer;
    font-size: 0.875rem;
    appearance: none;
}

.year-selector i {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--text-muted);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-card);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-info {
    flex: 1;
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #374151;
    margin-bottom: 0.5rem;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.stat-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-card.primary .stat-label { color: var(--primary); }
.stat-card.primary .stat-icon { background: rgba(99, 102, 241, 0.1); color: var(--primary); }

.stat-card.success .stat-label { color: var(--success); }
.stat-card.success .stat-icon { background: rgba(34, 197, 94, 0.1); color: var(--success); }

.stat-card.warning .stat-label { color: var(--warning); }
.stat-card.warning .stat-icon { background: rgba(251, 146, 60, 0.1); color: var(--warning); }

.stat-card.info .stat-label { color: var(--info); }
.stat-card.info .stat-icon { background: rgba(59, 130, 246, 0.1); color: var(--info); }

.charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: var(--bg-card);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-card h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 1.5rem;
}

.chart-area {
    height: 320px;
}

.top-suppliers-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.supplier-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-main);
    border-radius: 0.5rem;
    transition: background 0.2s;
}

.supplier-item:hover {
    background: var(--border-light);
}

.supplier-info {
    flex: 1;
}

.supplier-name {
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 0.25rem;
}

.supplier-count {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.supplier-amount {
    font-weight: 700;
    color: var(--primary);
    font-size: 1.125rem;
}

.invoices-table {
    background: var(--bg-card);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.invoices-table h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 1.5rem;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    text-align: left;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    border-bottom: 2px solid var(--border-light);
}

table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-main);
}

table tbody tr:hover {
    background: var(--bg-main);
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge.success {
    background: rgba(34, 197, 94, 0.1);
    color: var(--success);
}

.badge.warning {
    background: rgba(251, 146, 60, 0.1);
    color: var(--warning);
}

.badge.secondary {
    background: var(--border-light);
    color: var(--text-muted);
}

@media (max-width: 968px) {
    .charts-row {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}
</style>

<div class="suppliers-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard de Proveedores</h1>
        <div class="header-actions">
            <a href="index.php?page=suppliers" class="btn btn-secondary">
                <i class="fas fa-list"></i> Listado
            </a>
            <div class="year-selector">
                <select onchange="window.location.href='index.php?page=suppliers&action=dashboard&year='+this.value">
                    <?php for($y = date('Y'); $y >= date('Y')-4; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <i class="fas fa-calendar"></i>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-info">
                <div class="stat-label">Total Proveedores</div>
                <div class="stat-value"><?php echo $totalSuppliers; ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-truck"></i>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-info">
                <div class="stat-label">Facturado (<?php echo $year; ?>)</div>
                <div class="stat-value"><?php echo number_format($totalAmount, 2, ',', '.'); ?> €</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-info">
                <div class="stat-label">Pendiente de Pago</div>
                <div class="stat-value"><?php echo number_format($pendingAmount, 2, ',', '.'); ?> €</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-info">
                <div class="stat-label">Promedio Factura</div>
                <div class="stat-value">
                    <?php 
                    $avg = ($totalAmount > 0) ? $totalAmount / array_sum($monthlyStats) : 0;
                    echo "N/A"; 
                    ?>
                </div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-calculator"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Monthly Chart -->
        <div class="chart-card">
            <h2>Gasto Mensual (<?php echo $year; ?>)</h2>
            <div class="chart-area">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Top Suppliers -->
        <div class="chart-card">
            <h2>Top Proveedores</h2>
            <?php if (empty($topSuppliers)): ?>
                <p style="text-align: center; color: var(--text-muted); padding: 3rem 0;">No hay datos suficientes</p>
            <?php else: ?>
                <div class="top-suppliers-list">
                    <?php foreach ($topSuppliers as $supplier): ?>
                        <div class="supplier-item">
                            <div class="supplier-info">
                                <div class="supplier-name"><?php echo htmlspecialchars($supplier['name']); ?></div>
                                <div class="supplier-count"><?php echo $supplier['invoice_count']; ?> facturas</div>
                            </div>
                            <div class="supplier-amount">
                                <?php echo number_format($supplier['total_amount'], 2, ',', '.'); ?> €
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="invoices-table">
        <h2>Facturas Recientes</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Nº Factura</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentInvoices)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No hay facturas recientes</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentInvoices as $inv): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                                <td><?php echo htmlspecialchars($inv['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                <td><?php echo number_format($inv['amount'], 2, ',', '.'); ?> €</td>
                                <td>
                                    <?php if ($inv['status'] == 'paid'): ?>
                                        <span class="badge success">Pagada</span>
                                    <?php elseif ($inv['status'] == 'pending'): ?>
                                        <span class="badge warning">Pendiente</span>
                                    <?php else: ?>
                                        <span class="badge secondary"><?php echo $inv['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?page=suppliers&action=show&id=<?php echo $inv['supplier_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('monthlyChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Gasto Mensual (€)',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.5)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return value + ' €';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
