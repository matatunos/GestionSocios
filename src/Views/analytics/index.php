<?php
$incomeByYear = $incomeByYear ?? [];
$expensesByYear = $expensesByYear ?? [];
$monthlyTrend = $monthlyTrend ?? [];
$categoriesDistribution = $categoriesDistribution ?? [];
$retentionRate = $retentionRate ?? [];
$paymentTypes = $paymentTypes ?? [];
$summary = $summary ?? [
    'members' => ['current' => 0, 'change' => 0],
    'income' => ['current' => 0, 'change' => 0],
    'expenses' => ['current' => 0, 'change' => 0],
    'donations' => ['current' => 0, 'change' => 0]
];
$prediction = $prediction ?? null;
?>
<?php ob_start(); ?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Estadísticas Avanzadas</h1>
    <select id="yearFilter" class="form-control year-filter" onchange="changeYears(this.value)">
        <option value="3" <?= $selectedYears == 3 ? 'selected' : '' ?>>Últimos 3 años</option>
        <option value="5" <?= $selectedYears == 5 ? 'selected' : '' ?>>Últimos 5 años</option>
        <option value="10" <?= $selectedYears == 10 ? 'selected' : '' ?>>Últimos 10 años</option>
    </select>
</div>

<div class="analytics-grid">
    <!-- New Members -->
    <div class="card">
        <div class="card-header">Nuevos Socios</div>
        <div class="card-value"><?= number_format($summary['members']['current']) ?></div>
        <div class="card-icon"><i class="fas fa-user-plus"></i></div>
        <div>
            <span class="badge <?= $summary['members']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['members']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['members']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Income -->
    <div class="card">
        <div class="card-header">Ingresos <?= date('Y') ?></div>
        <div class="card-value"><?= number_format($summary['income']['current'], 2) ?>€</div>
        <div class="card-icon"><i class="fas fa-euro-sign"></i></div>
        <div>
            <span class="badge <?= $summary['income']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['income']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['income']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Expenses -->
    <div class="card">
        <div class="card-header">Gastos <?= date('Y') ?></div>
        <div class="card-value"><?= number_format($summary['expenses']['current'], 2) ?>€</div>
        <div class="card-icon"><i class="fas fa-receipt"></i></div>
        <div>
            <span class="badge <?= $summary['expenses']['change'] <= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['expenses']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['expenses']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Donations -->
    <div class="card">
        <div class="card-header">Donaciones <?= date('Y') ?></div>
        <div class="card-value"><?= number_format($summary['donations']['current'], 2) ?>€</div>
        <div class="card-icon"><i class="fas fa-hand-holding-heart"></i></div>
        <div>
            <span class="badge <?= $summary['donations']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['donations']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['donations']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>
<!-- Fin resumen comparativo -->
</div>

<!-- Charts Grid -->
<div class="analytics-grid" style="grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));">
    <!-- Members Growth Chart -->
    <div class="card">
        <h3 class="card-header"><i class="fas fa-users"></i> Evolución de Socios</h3>
        <canvas id="membersGrowthChart" class="chart-canvas"></canvas>
    </div>

    <!-- Income vs Expenses Chart -->
    <div class="card">
        <h3 class="card-header"><i class="fas fa-balance-scale"></i> Ingresos vs Gastos</h3>
        <canvas id="incomeExpensesChart" class="chart-canvas"></canvas>
    </div>
</div>

<!-- Monthly Trend -->
<div class="card">
    <h3 class="card-header"><i class="fas fa-chart-area"></i> Tendencia Mensual <?= date('Y') ?></h3>
    <?php var_dump($monthlyTrend); ?>
    <?php if (empty($monthlyTrend) || array_sum(array_column($monthlyTrend, 'income')) == 0 && array_sum(array_column($monthlyTrend, 'expenses')) == 0): ?>
        <div class="alert alert-warning" style="margin: 2em; text-align: center;">No hay datos de ingresos ni gastos para este año.</div>
    <?php endif; ?>
    <canvas id="monthlyTrendChart" class="chart-canvas"></canvas>
</div>

<!-- Additional Charts -->
<div class="analytics-grid" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
    <!-- Categories Distribution -->
    <div class="card">
        <h3 class="card-header"><i class="fas fa-tags"></i> Distribución por Categorías</h3>
        <canvas id="categoriesChart" class="chart-canvas"></canvas>
    </div>

    <!-- Retention Rate -->
    <div class="card">
        <h3 class="card-header"><i class="fas fa-user-check"></i> Tasa de Retención</h3>
        <canvas id="retentionChart" class="chart-canvas"></canvas>
    </div>

    <!-- Payment Types -->
    <div class="card">
        <h3 class="card-header"><i class="fas fa-credit-card"></i> Tipos de Pago <?= date('Y') ?></h3>
        <canvas id="paymentTypesChart" class="chart-canvas"></canvas>
    </div>

    <!-- Prediction -->
    <?php if ($prediction): ?>
    <div class="card prediction-card">
        <h3 class="card-header"><i class="fas fa-crystal-ball"></i> Predicción <?= $prediction['year'] ?></h3>
        <div class="prediction-content">
            <div class="prediction-value">
                <?= number_format($prediction['predicted_income'], 0) ?>€
            </div>
            <div class="prediction-label">Ingresos Estimados</div>
            <div class="prediction-growth">
                <div class="prediction-growth-label">Tasa de crecimiento histórico</div>
                <div class="prediction-growth-value">
                    <?= $prediction['growth_rate'] > 0 ? '+' : '' ?><?= $prediction['growth_rate'] ?>%
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Chart.js configuration
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#64748b';

const chartColors = {
    primary: '#4f46e5',
    success: '#10b981',
    danger: '#ef4444',
    warning: '#f59e0b',
    info: '#3b82f6',
    purple: '#8b5cf6'
};

// Members Growth Chart
const membersGrowthCtx = document.getElementById('membersGrowthChart').getContext('2d');
new Chart(membersGrowthCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($totalMembers ?? [], 'year')) ?>,
        datasets: [{
            label: 'Total de Socios',
            data: <?= json_encode(array_column($totalMembers ?? [], 'total_members')) ?>,
            borderColor: chartColors.primary,
            backgroundColor: chartColors.primary + '20',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Income vs Expenses Chart
const incomeExpensesCtx = document.getElementById('incomeExpensesChart').getContext('2d');
new Chart(incomeExpensesCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($incomeByYear, 'year')) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode(array_column($incomeByYear, 'total_income')) ?>,
            backgroundColor: chartColors.success + 'CC'
        }, {
            label: 'Gastos',
            data: <?= json_encode(array_column($expensesByYear, 'total_expenses')) ?>,
            backgroundColor: chartColors.danger + 'CC'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Monthly Trend Chart
const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(monthlyTrendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyTrend, 'month_name')) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode(array_column($monthlyTrend, 'income')) ?>,
            borderColor: chartColors.success,
            backgroundColor: chartColors.success + '20',
            fill: true,
            tension: 0.4
        }, {
            label: 'Gastos',
            data: <?= json_encode(array_column($monthlyTrend, 'expenses')) ?>,
            borderColor: chartColors.danger,
            backgroundColor: chartColors.danger + '20',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Categories Distribution Chart
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($categoriesDistribution, 'category')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($categoriesDistribution, 'member_count')) ?>,
            backgroundColor: <?= json_encode(array_column($categoriesDistribution, 'color')) ?>
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'right' }
        }
    }
});

// Retention Rate Chart
const retentionCtx = document.getElementById('retentionChart').getContext('2d');
new Chart(retentionCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($retentionRate, 'year')) ?>,
        datasets: [{
            label: 'Tasa de Retención (%)',
            data: <?= json_encode(array_column($retentionRate, 'retention_rate')) ?>,
            borderColor: chartColors.info,
            backgroundColor: chartColors.info + '20',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { 
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Payment Types Chart
const paymentTypesCtx = document.getElementById('paymentTypesChart').getContext('2d');
new Chart(paymentTypesCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($paymentTypes, 'payment_type')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($paymentTypes, 'total_amount')) ?>,
            backgroundColor: [
                chartColors.primary,
                chartColors.success,
                chartColors.warning,
                chartColors.info,
                chartColors.purple,
                chartColors.danger
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'right' }
        }
    }
});

function changeYears(years) {
    window.location.href = 'index.php?page=analytics&years=' + years;
}
</script>


<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>
