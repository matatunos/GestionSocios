<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-chart-line"></i> Cuenta de Resultados</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=exportReport&type=income_statement&start_date=<?php echo urlencode($startDate ?? date('Y-01-01')); ?>&end_date=<?php echo urlencode($endDate ?? date('Y-12-31')); ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="index.php?page=accounting&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card no-print">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="page" value="accounting">
            <input type="hidden" name="action" value="incomeStatement">
            
            <div class="filter-group">
                <label for="period_id">Periodo Contable</label>
                <select name="period_id" id="period_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Seleccionar periodo...</option>
                    <?php foreach ($periods as $period): ?>
                        <option value="<?php echo $period['id']; ?>" 
                                <?php echo ($periodId ?? '') == $period['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($period['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="start_date">O desde fecha</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       value="<?php echo $startDate; ?>">
            </div>

            <div class="filter-group">
                <label for="end_date">Hasta fecha</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="<?php echo $endDate; ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-chart-line"></i> Generar
                </button>
            </div>
        </form>
    </div>

    <!-- Income Statement Header -->
    <div class="report-header">
        <h2>Cuenta de Resultados (Pérdidas y Ganancias)</h2>
        <p>Periodo: <strong><?php echo date('d/m/Y', strtotime($startDate)); ?></strong> 
           a <strong><?php echo date('d/m/Y', strtotime($endDate)); ?></strong></p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card bg-success">
            <div class="card-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($totalIncome, 2, ',', '.'); ?> €</h3>
                <p>Ingresos Totales</p>
            </div>
        </div>

        <div class="summary-card bg-danger">
            <div class="card-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($totalExpenses, 2, ',', '.'); ?> €</h3>
                <p>Gastos Totales</p>
            </div>
        </div>

        <div class="summary-card <?php echo $netProfit >= 0 ? 'bg-primary' : 'bg-warning'; ?>">
            <div class="card-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($netProfit, 2, ',', '.'); ?> €</h3>
                <p><?php echo $netProfit >= 0 ? 'Beneficio' : 'Pérdida'; ?> Neto</p>
            </div>
        </div>

        <div class="summary-card bg-info">
            <div class="card-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $totalIncome > 0 ? number_format(($netProfit / $totalIncome) * 100, 1) : '0.0'; ?>%</h3>
                <p>Margen Neto</p>
            </div>
        </div>
    </div>

    <!-- Income Statement Content -->
    <div class="income-statement-container">
        <!-- INGRESOS (Income) -->
        <div class="statement-section">
            <h3 class="section-title bg-success">INGRESOS</h3>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Importe €</th>
                        <th class="text-right">% Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incomeAccounts)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay ingresos en el periodo</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($incomeAccounts as $account): ?>
                            <?php $percentage = $totalIncome > 0 ? ($account['balance'] / $totalIncome) * 100 : 0; ?>
                            <tr class="level-<?php echo $account['level']; ?>">
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td class="text-right"><?php echo number_format($account['balance'], 2, ',', '.'); ?></td>
                                <td class="text-right text-muted"><?php echo number_format($percentage, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL INGRESOS</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalIncome, 2, ',', '.'); ?></strong></td>
                        <td class="text-right"><strong>100.0%</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- GASTOS (Expenses) -->
        <div class="statement-section">
            <h3 class="section-title bg-danger">GASTOS</h3>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-right">Importe €</th>
                        <th class="text-right">% Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenseAccounts)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay gastos en el periodo</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenseAccounts as $account): ?>
                            <?php $percentage = $totalExpenses > 0 ? ($account['balance'] / $totalExpenses) * 100 : 0; ?>
                            <tr class="level-<?php echo $account['level']; ?>">
                                <td><strong><?php echo htmlspecialchars($account['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td class="text-right"><?php echo number_format($account['balance'], 2, ',', '.'); ?></td>
                                <td class="text-right text-muted"><?php echo number_format($percentage, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL GASTOS</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalExpenses, 2, ',', '.'); ?></strong></td>
                        <td class="text-right"><strong>100.0%</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Net Result -->
    <div class="net-result-card <?php echo $netProfit >= 0 ? 'profit' : 'loss'; ?>">
        <div class="result-label"><?php echo $netProfit >= 0 ? 'BENEFICIO' : 'PÉRDIDA'; ?> DEL EJERCICIO</div>
        <div class="result-amount"><?php echo number_format(abs($netProfit), 2, ',', '.'); ?> €</div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <h3>Comparativa Visual</h3>
        <canvas id="incomeChart"></canvas>
    </div>
</div>

<style>
.report-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-header h2 {
    margin: 0 0 10px 0;
    color: #333;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.summary-card {
    padding: 1.5rem;
    border-radius: 8px;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.summary-card.bg-success { background: #28a745; }
.summary-card.bg-danger { background: #dc3545; }
.summary-card.bg-primary { background: #007bff; }
.summary-card.bg-warning { background: #ffc107; color: #333; }
.summary-card.bg-info { background: #17a2b8; }

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.card-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.card-content p {
    margin: 0;
    opacity: 0.9;
}

.income-statement-container {
    display: grid;
    gap: 20px;
    margin-bottom: 20px;
}

.statement-section {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title {
    color: white;
    padding: 15px;
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.section-title.bg-success { background: #28a745; }
.section-title.bg-danger { background: #dc3545; }

.statement-table {
    width: 100%;
    border-collapse: collapse;
}

.statement-table thead th {
    background: #f8f9fa;
    padding: 10px;
    border-bottom: 2px solid #dee2e6;
    font-weight: bold;
    text-align: left;
}

.statement-table tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid #e9ecef;
}

.statement-table tr.level-0 td {
    font-weight: bold;
}

.statement-table tr.level-1 td:nth-child(2) {
    padding-left: 25px;
}

.statement-table tr.level-2 td:nth-child(2) {
    padding-left: 40px;
}

.statement-table tfoot tr {
    background: #e9ecef;
    font-weight: bold;
}

.statement-table tfoot td {
    padding: 12px 10px;
    border-top: 2px solid #333;
}

.net-result-card {
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.net-result-card.profit {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.net-result-card.loss {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    color: white;
}

.result-label {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.result-amount {
    font-size: 36px;
    font-weight: bold;
}

.chart-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .content-header .header-actions {
        display: none !important;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Ingresos', 'Gastos', 'Resultado'],
        datasets: [{
            label: 'Importe (€)',
            data: [
                <?php echo $totalIncome; ?>,
                <?php echo $totalExpenses; ?>,
                <?php echo abs($netProfit); ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.5)',
                'rgba(220, 53, 69, 0.5)',
                '<?php echo $netProfit >= 0 ? "rgba(0, 123, 255, 0.5)" : "rgba(255, 193, 7, 0.5)"; ?>'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                '<?php echo $netProfit >= 0 ? "rgba(0, 123, 255, 1)" : "rgba(255, 193, 7, 1)"; ?>'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.parsed.y.toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' €';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('es-ES') + ' €';
                    }
                }
            }
        }
    }
});
</script>
