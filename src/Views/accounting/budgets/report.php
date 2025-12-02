<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-chart-bar"></i> Reporte: Presupuesto vs Real</h1>
        <div class="header-actions">
            <a href="index.php?page=budget" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="budget">
            <input type="hidden" name="action" value="report">
            
            <div class="filter-group">
                <label for="fiscal_year">Año Fiscal <span class="required">*</span></label>
                <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++) {
                        $selected = ($selectedYear ?? $currentYear) == $year ? 'selected' : '';
                        echo "<option value=\"$year\" $selected>$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="account_id">Cuenta Contable</label>
                <select name="account_id" id="account_id" class="form-control">
                    <option value="">Todas las cuentas</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>" 
                                <?php echo ($selectedAccountId ?? '') == $account['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-chart-line"></i> Generar Reporte
                </button>
            </div>
        </form>
    </div>

    <?php if (isset($budgetData) && !empty($budgetData)): ?>
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="card-content">
                    <h3><?php echo number_format($totalBudget, 2, ',', '.'); ?> €</h3>
                    <p>Presupuesto Total</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="card-content">
                    <h3><?php echo number_format($totalActual, 2, ',', '.'); ?> €</h3>
                    <p>Gasto Real</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon <?php echo $totalVariance >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <h3><?php echo number_format($totalVariance, 2, ',', '.'); ?> €</h3>
                    <p>Variación (<?php echo number_format($totalVariancePercent, 1); ?>%)</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="card-content">
                    <h3><?php echo number_format($totalExecutionPercent, 1); ?>%</h3>
                    <p>Ejecución</p>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="table-card">
            <h3>Detalle por Cuenta</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Periodo</th>
                            <th class="text-right">Presupuesto</th>
                            <th class="text-right">Real</th>
                            <th class="text-right">Variación</th>
                            <th class="text-right">% Ejecución</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budgetData as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['account_code']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['account_name']); ?></small>
                                </td>
                                <td>
                                    <?php
                                    if ($row['period_type'] === 'yearly') {
                                        echo 'Anual';
                                    } elseif ($row['period_type'] === 'monthly') {
                                        $months = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                        echo $months[$row['period_number']] ?? $row['period_number'];
                                    } elseif ($row['period_type'] === 'quarterly') {
                                        echo 'T' . $row['period_number'];
                                    }
                                    ?>
                                </td>
                                <td class="text-right">
                                    <strong><?php echo number_format($row['budget_amount'], 2, ',', '.'); ?> €</strong>
                                </td>
                                <td class="text-right">
                                    <?php echo number_format($row['actual_amount'], 2, ',', '.'); ?> €
                                </td>
                                <td class="text-right <?php echo $row['variance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <strong><?php echo number_format($row['variance'], 2, ',', '.'); ?> €</strong><br>
                                    <small>(<?php echo number_format($row['variance_percent'], 1); ?>%)</small>
                                </td>
                                <td class="text-right">
                                    <?php
                                    $execPercent = $row['execution_percent'];
                                    $barClass = 'bg-success';
                                    if ($execPercent > 90) $barClass = 'bg-warning';
                                    if ($execPercent > 100) $barClass = 'bg-danger';
                                    ?>
                                    <div class="progress-container">
                                        <div class="progress-bar <?php echo $barClass; ?>" 
                                             style="width: <?php echo min($execPercent, 100); ?>%">
                                        </div>
                                    </div>
                                    <small><?php echo number_format($execPercent, 1); ?>%</small>
                                </td>
                                <td class="text-center">
                                    <?php if ($execPercent < 80): ?>
                                        <span class="badge badge-success">Bajo Control</span>
                                    <?php elseif ($execPercent < 95): ?>
                                        <span class="badge badge-info">Normal</span>
                                    <?php elseif ($execPercent < 100): ?>
                                        <span class="badge badge-warning">Atención</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Excedido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL</strong></td>
                            <td class="text-right"><strong><?php echo number_format($totalBudget, 2, ',', '.'); ?> €</strong></td>
                            <td class="text-right"><strong><?php echo number_format($totalActual, 2, ',', '.'); ?> €</strong></td>
                            <td class="text-right <?php echo $totalVariance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo number_format($totalVariance, 2, ',', '.'); ?> €</strong>
                            </td>
                            <td class="text-right"><strong><?php echo number_format($totalExecutionPercent, 1); ?>%</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-card">
            <h3>Comparativa Visual</h3>
            <canvas id="budgetChart"></canvas>
        </div>
    <?php elseif (isset($budgetData)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No hay datos de presupuesto para los filtros seleccionados.
        </div>
    <?php endif; ?>
</div>

<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.card-icon.bg-primary { background: #007bff; }
.card-icon.bg-success { background: #28a745; }
.card-icon.bg-danger { background: #dc3545; }
.card-icon.bg-warning { background: #ffc107; }
.card-icon.bg-info { background: #17a2b8; }

.card-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.card-content p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.progress-container {
    width: 100px;
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin: 0 auto 5px;
}

.progress-bar {
    height: 100%;
    transition: width 0.3s ease;
}

.progress-bar.bg-success { background: #28a745; }
.progress-bar.bg-warning { background: #ffc107; }
.progress-bar.bg-danger { background: #dc3545; }

.total-row {
    background: #f8f9fa;
    font-weight: bold;
    border-top: 2px solid #dee2e6;
}

.chart-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

@media print {
    .content-header .header-actions,
    .filters-card,
    .btn {
        display: none !important;
    }
}
</style>

<?php if (isset($budgetData) && !empty($budgetData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('budgetChart').getContext('2d');
const budgetChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($budgetData, 'account_code')); ?>,
        datasets: [
            {
                label: 'Presupuesto',
                data: <?php echo json_encode(array_column($budgetData, 'budget_amount')); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Real',
                data: <?php echo json_encode(array_column($budgetData, 'actual_amount')); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('es-ES') + ' €';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + 
                               context.parsed.y.toLocaleString('es-ES', {
                                   minimumFractionDigits: 2,
                                   maximumFractionDigits: 2
                               }) + ' €';
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>
