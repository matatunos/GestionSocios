<?php ob_start(); ?>

<?php
// Calculate summary statistics
$currentYear = date('Y');
$currentYearData = $reportData[$currentYear] ?? null;
$previousYear = $currentYear - 1;
$previousYearData = $reportData[$previousYear] ?? null;

$growth = 0;
if ($currentYearData && $previousYearData && $previousYearData['total'] > 0) {
    $growth = (($currentYearData['total'] - $previousYearData['total']) / $previousYearData['total']) * 100;
}
?>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Ingresos <?= $currentYear ?></h3>
        <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: var(--primary-600);">
            <?= $currentYearData ? number_format($currentYearData['total'], 2) : '0.00' ?> €
        </p>
    </div>
    <div class="card">
        <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Crecimiento Anual</h3>
        <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: <?= $growth >= 0 ? 'var(--secondary-600)' : 'var(--danger-600)' ?>;">
            <?= $growth >= 0 ? '+' : '' ?><?= number_format($growth, 1) ?>%
        </p>
    </div>
    <div class="card">
        <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Total Histórico</h3>
        <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: var(--primary-600);">
            <?= number_format(array_sum(array_column($reportData, 'total')), 2) ?> €
        </p>
    </div>
</div>

<!-- Chart Section with Tabs -->
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Análisis Gráfico</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-sm btn-secondary chart-tab active" data-chart="bars">
                <i class="fas fa-chart-bar"></i> Barras
            </button>
            <button class="btn btn-sm btn-secondary chart-tab" data-chart="lines">
                <i class="fas fa-chart-line"></i> Tendencias
            </button>
            <button class="btn btn-sm btn-secondary chart-tab" data-chart="comparison">
                <i class="fas fa-exchange-alt"></i> Comparación
            </button>
        </div>
    </div>
    <div style="position: relative; height: 400px;">
        <canvas id="incomeChart"></canvas>
    </div>
</div>

<!-- Category Distribution Chart -->
<?php if (!empty($categoryDistribution)): ?>
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Distribución de Socios por Categoría</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
        <div style="position: relative; height: 300px;">
            <canvas id="categoryChart"></canvas>
        </div>
        <div>
            <?php foreach ($categoryDistribution as $cat): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-bottom: 1px solid var(--border-light);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 16px; height: 16px; border-radius: 4px; background-color: <?php echo htmlspecialchars($cat['category_color'] ?? '#6b7280'); ?>;"></div>
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600; color: var(--text-main);"><?php echo $cat['member_count']; ?> socios</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $cat['active_count']; ?> activos</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Detailed Table -->
<div class="card">
    <h2 style="margin-bottom: 1.5rem;">Desglose Detallado por Año</h2>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Año</th>
                    <th style="text-align: right;">Cuotas</th>
                    <th style="text-align: right;">Eventos</th>
                    <th style="text-align: right;">Donaciones</th>
                    <th style="text-align: right;">Total</th>
                    <th style="text-align: right;">Crecimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $prevTotal = null;
                foreach ($reportData as $year => $data): 
                    $yearGrowth = 0;
                    if ($prevTotal !== null && $prevTotal > 0) {
                        $yearGrowth = (($data['total'] - $prevTotal) / $prevTotal) * 100;
                    }
                ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $year ?></td>
                        <td style="text-align: right;">
                            <i class="fas fa-receipt" style="color: var(--primary-600); margin-right: 0.5rem;"></i>
                            <?= number_format($data['fee'], 2) ?> €
                        </td>
                        <td style="text-align: right;">
                            <i class="fas fa-calendar-alt" style="color: var(--secondary-600); margin-right: 0.5rem;"></i>
                            <?= number_format($data['event'], 2) ?> €
                        </td>
                        <td style="text-align: right;">
                            <i class="fas fa-hand-holding-heart" style="color: #f59e0b; margin-right: 0.5rem;"></i>
                            <?= number_format($data['donation'], 2) ?> €
                        </td>
                        <td style="text-align: right; font-weight: 700; color: var(--primary-600);">
                            <?= number_format($data['total'], 2) ?> €
                        </td>
                        <td style="text-align: right; color: <?= $yearGrowth >= 0 ? 'var(--secondary-600)' : 'var(--danger-600)' ?>;">
                            <?php if ($prevTotal !== null): ?>
                                <?= $yearGrowth >= 0 ? '+' : '' ?><?= number_format($yearGrowth, 1) ?>%
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    $prevTotal = $data['total'];
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('incomeChart').getContext('2d');

// Prepare data for chart
const rawYears = <?= json_encode(array_keys($reportData)) ?>;
const rawFeeData = <?= json_encode(array_column($reportData, 'fee')) ?>;
const rawEventData = <?= json_encode(array_column($reportData, 'event')) ?>;
const rawDonationData = <?= json_encode(array_column($reportData, 'donation')) ?>;
const rawTotalData = <?= json_encode(array_column($reportData, 'total')) ?>;

// Reverse arrays to show chronologically
const years = [...rawYears].reverse();
const feeData = [...rawFeeData].reverse();
const eventData = [...rawEventData].reverse();
const donationData = [...rawDonationData].reverse();
const totalData = [...rawTotalData].reverse();

// Calculate year-over-year growth
const yoyGrowth = totalData.map((value, index) => {
    if (index === 0) return 0;
    const prev = totalData[index - 1];
    return prev > 0 ? ((value - prev) / prev) * 100 : 0;
});

// Chart configuration templates
const chartConfigs = {
    bars: {
        type: 'bar',
        data: {
            labels: years,
            datasets: [
                {
                    label: 'Cuotas Anuales',
                    data: feeData,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1
                },
                {
                    label: 'Eventos',
                    data: eventData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                },
                {
                    label: 'Donaciones',
                    data: donationData,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderColor: 'rgb(245, 158, 11)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: false,
                    grid: { display: false }
                },
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
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            label += context.parsed.y.toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' €';
                            return label;
                        }
                    }
                }
            }
        }
    },
    lines: {
        type: 'line',
        data: {
            labels: years,
            datasets: [
                {
                    label: 'Total Ingresos',
                    data: totalData,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Cuotas',
                    data: feeData,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Eventos',
                    data: eventData,
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
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
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            label += context.parsed.y.toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' €';
                            return label;
                        }
                    }
                }
            }
        }
    },
    comparison: {
        type: 'bar',
        data: {
            labels: years,
            datasets: [
                {
                    label: 'Ingresos Totales',
                    data: totalData,
                    backgroundColor: years.map((y, i) => 
                        yoyGrowth[i] >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                    ),
                    borderColor: years.map((y, i) => 
                        yoyGrowth[i] >= 0 ? 'rgb(16, 185, 129)' : 'rgb(239, 68, 68)'
                    ),
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
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
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const value = context.parsed.y;
                            const growth = yoyGrowth[index];
                            
                            let label = value.toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' €';
                            
                            if (index > 0) {
                                label += '\n' + (growth >= 0 ? '↑' : '↓') + ' ' + 
                                         Math.abs(growth).toFixed(1) + '% vs año anterior';
                            }
                            
                            return label;
                        }
                    }
                }
            }
        }
    }
};

// Initialize chart with default config
let chart = new Chart(ctx, chartConfigs.bars);

// Chart switcher
document.querySelectorAll('.chart-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        const chartType = this.dataset.chart;
        
        // Update active button
        document.querySelectorAll('.chart-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Destroy and recreate chart
        chart.destroy();
        chart = new Chart(ctx, chartConfigs[chartType]);
    });
});

// Category Distribution Chart
<?php if (!empty($categoryDistribution)): ?>
const categoryCtx = document.getElementById('categoryChart');
const categoryData = <?php echo json_encode($categoryDistribution); ?>;

new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoryData.map(c => c.category_name),
        datasets: [{
            data: categoryData.map(c => c.member_count),
            backgroundColor: categoryData.map(c => c.category_color),
            borderColor: 'white',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' socios (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<!-- Print Styles -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .sidebar {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 20px !important;
    }
    .card {
        page-break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd;
    }
    body {
        background: white !important;
    }
}
</style>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
