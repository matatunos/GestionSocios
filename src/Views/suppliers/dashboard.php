<?php
$pageTitle = "Dashboard de Proveedores";
require_once __DIR__ . '/../layout.php';

// Prepare chart data
$months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$chartData = array_values($monthlyStats);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Proveedores</h1>
        <div class="btn-group">
            <a href="index.php?page=suppliers" class="btn btn-secondary">
                <i class="fas fa-list"></i> Listado
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-calendar"></i> <?php echo $year; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php for($y = date('Y'); $y >= date('Y')-4; $y--): ?>
                        <a class="dropdown-item" href="index.php?page=suppliers&action=dashboard&year=<?php echo $y; ?>">
                            <?php echo $y; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Proveedores</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalSuppliers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Facturado (<?php echo $year; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($totalAmount, 2, ',', '.'); ?> €
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendiente de Pago</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($pendingAmount, 2, ',', '.'); ?> €
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Promedio Factura</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $avg = ($totalAmount > 0) ? $totalAmount / array_sum($monthlyStats) : 0; // Rough approx if count not available directly
                                // Better: Use count from DB if needed, but for now let's just show N/A or calculate if we had count
                                // Let's skip average for now or calculate properly if we had total invoice count for year
                                echo "N/A"; 
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Gasto Mensual (<?php echo $year; ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Suppliers -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Proveedores</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($topSuppliers)): ?>
                        <p class="text-center text-muted my-5">No hay datos suficientes</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($topSuppliers as $supplier): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <div class="font-weight-bold"><?php echo htmlspecialchars($supplier['name']); ?></div>
                                        <small class="text-muted"><?php echo $supplier['invoice_count']; ?> facturas</small>
                                    </div>
                                    <span class="badge badge-primary badge-pill">
                                        <?php echo number_format($supplier['total_amount'], 2, ',', '.'); ?> €
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Facturas Recientes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
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
                                <td colspan="6" class="text-center">No hay facturas recientes</td>
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
                                            <span class="badge badge-success">Pagada</span>
                                        <?php elseif ($inv['status'] == 'pending'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo $inv['status']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=suppliers&action=show&id=<?php echo $inv['supplier_id']; ?>" class="btn btn-sm btn-info">
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
