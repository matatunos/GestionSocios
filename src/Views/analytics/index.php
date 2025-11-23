<?php ob_start(); ?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Estadísticas Avanzadas</h1>
    <div style="display: flex; gap: 0.5rem;">
        <select id="yearFilter" class="form-control" style="width: 150px;" onchange="changeYears(this.value)">
            <option value="3" <?= $selectedYears == 3 ? 'selected' : '' ?>>Últimos 3 años</option>
            <option value="5" <?= $selectedYears == 5 ? 'selected' : '' ?>>Últimos 5 años</option>
            <option value="10" <?= $selectedYears == 10 ? 'selected' : '' ?>>Últimos 10 años</option>
        </select>
    </div>
</div>

<!-- Comparative Summary -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- New Members -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">Nuevos Socios</div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-600);">
                    <?= number_format($summary['members']['current']) ?>
                </div>
            </div>
            <div style="background: var(--primary-50); color: var(--primary-700); padding: 0.5rem; border-radius: 0.5rem;">
                <i class="fas fa-user-plus" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <span class="badge <?= $summary['members']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['members']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['members']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Income -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">Ingresos <?= date('Y') ?></div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--success-600);">
                    <?= number_format($summary['income']['current'], 2) ?>€
                </div>
            </div>
            <div style="background: var(--success-50); color: var(--success-700); padding: 0.5rem; border-radius: 0.5rem;">
                <i class="fas fa-euro-sign" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <span class="badge <?= $summary['income']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['income']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['income']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Expenses -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">Gastos <?= date('Y') ?></div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--danger-600);">
                    <?= number_format($summary['expenses']['current'], 2) ?>€
                </div>
            </div>
            <div style="background: var(--danger-50); color: var(--danger-700); padding: 0.5rem; border-radius: 0.5rem;">
                <i class="fas fa-receipt" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <span class="badge <?= $summary['expenses']['change'] <= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['expenses']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['expenses']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>

    <!-- Donations -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">Donaciones <?= date('Y') ?></div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--warning-600);">
                    <?= number_format($summary['donations']['current'], 2) ?>€
                </div>
            </div>
            <div style="background: var(--warning-50); color: var(--warning-700); padding: 0.5rem; border-radius: 0.5rem;">
                <i class="fas fa-hand-holding-heart" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <span class="badge <?= $summary['donations']['change'] >= 0 ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-arrow-<?= $summary['donations']['change'] >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs($summary['donations']['change']) ?>%
            </span>
            <span style="color: var(--text-muted);">vs. año anterior</span>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Members Growth Chart -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-users"></i> Evolución de Socios</h3>
        <canvas id="membersGrowthChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Income vs Expenses Chart -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-balance-scale"></i> Ingresos vs Gastos</h3>
        <canvas id="incomeExpensesChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<!-- Monthly Trend -->
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-chart-area"></i> Tendencia Mensual <?= date('Y') ?></h3>
    <canvas id="monthlyTrendChart" style="max-height: 300px;"></canvas>
</div>

<!-- Additional Charts -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Categories Distribution -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-tags"></i> Distribución por Categorías</h3>
        <canvas id="categoriesChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Retention Rate -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-user-check"></i> Tasa de Retención</h3>
        <canvas id="retentionChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Payment Types -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-credit-card"></i> Tipos de Pago <?= date('Y') ?></h3>
        <canvas id="paymentTypesChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Prediction -->
    <?php if ($prediction): ?>
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 style="margin-bottom: 1.5rem; color: white;"><i class="fas fa-crystal-ball"></i> Predicción <?= $prediction['year'] ?></h3>
        <div style="text-align: center; padding: 2rem 0;">
            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">
                <?= number_format($prediction['predicted_income'], 0) ?>€
            </div>
            <div style="opacity: 0.9; font-size: 1rem;">Ingresos Estimados</div>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.3);">
                <div style="font-size: 0.875rem; opacity: 0.9;">Tasa de crecimiento histórico</div>
                <div style="font-size: 1.5rem; font-weight: 600; margin-top: 0.25rem;">
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
        labels: <?= json_encode(array_column($totalMembers, 'year')) ?>,
        datasets: [{
            label: 'Total de Socios',
            data: <?= json_encode(array_column($totalMembers, 'total_members')) ?>,
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

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
