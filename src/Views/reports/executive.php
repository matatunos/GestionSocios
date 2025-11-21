<?php ob_start(); ?>

<div class="mb-4">
    <div class="flex justify-between items-center">
        <h1><i class="fas fa-chart-bar"></i> Informe Ejecutivo de Ingresos</h1>
        <button onclick="window.print()" class="btn btn-primary no-print">
            <i class="fas fa-download"></i> Descargar PDF
        </button>
    </div>
</div>

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

<!-- Chart Section -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Evolución de Ingresos por Origen</h2>
    <div style="position: relative; height: 400px;">
        <canvas id="incomeChart"></canvas>
    </div>
</div>

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
const years = <?= json_encode(array_keys($reportData)) ?>;
const feeData = <?= json_encode(array_column($reportData, 'fee')) ?>;
const eventData = <?= json_encode(array_column($reportData, 'event')) ?>;
const donationData = <?= json_encode(array_column($reportData, 'donation')) ?>;

const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: years.reverse(),
        datasets: [
            {
                label: 'Cuotas Anuales',
                data: feeData.reverse(),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            },
            {
                label: 'Eventos',
                data: eventData.reverse(),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            },
            {
                label: 'Donaciones',
                data: donationData.reverse(),
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
                grid: {
                    display: false
                }
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
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
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
});
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
