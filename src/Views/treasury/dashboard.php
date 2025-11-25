<?php 
$page_title = "Tesorería";
ob_start(); 
?>
<link rel="stylesheet" href="/public/css/unificado.css">

<div class="flex justify-between items-center mb-4">
    <div>
        <h1>Dashboard de Tesorería</h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">
            Vista financiera del año <?php echo date('Y'); ?>
        </p>
    </div>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    
    <!-- Year Balance -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Balance Anual</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: <?php echo $stats['balance'] >= 0 ? 'var(--secondary-600)' : 'var(--danger-600)'; ?>;">
                    <?php echo number_format($stats['balance'], 2); ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: <?php echo $stats['balance'] >= 0 ? 'var(--secondary-100)' : 'var(--danger-100)'; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-wallet" style="font-size: 1.5rem; color: <?php echo $stats['balance'] >= 0 ? 'var(--secondary-600)' : 'var(--danger-600)'; ?>;"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                <span>Ingresos:</span>
                <span style="color: var(--secondary-600); font-weight: 600;">+<?php echo number_format($stats['year_income'], 2); ?> €</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Gastos:</span>
                <span style="color: var(--danger-600); font-weight: 600;">-<?php echo number_format($stats['year_expenses'], 2); ?> €</span>
            </div>
        </div>
    </div>
    
    <!-- Month Balance -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Balance del Mes</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: <?php echo $stats['month_balance'] >= 0 ? 'var(--secondary-600)' : 'var(--danger-600)'; ?>;">
                    <?php echo number_format($stats['month_balance'], 2); ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--primary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-day" style="font-size: 1.5rem; color: var(--primary-600);"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                <span>Ingresos:</span>
                <span style="color: var(--secondary-600); font-weight: 600;">+<?php echo number_format($stats['month_income'], 2); ?> €</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Gastos:</span>
                <span style="color: var(--danger-600); font-weight: 600;">-<?php echo number_format($stats['month_expenses'], 2); ?> €</span>
            </div>
        </div>
    </div>
    
    <!-- Pending Payments -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Pagos Pendientes</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?php echo $stats['pending_count']; ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; color: #d97706;"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            <div style="display: flex; justify-content: space-between;">
                <span>Monto estimado:</span>
                <span style="color: #d97706; font-weight: 600;"><?php echo number_format($stats['pending_amount'], 2); ?> €</span>
            </div>
        </div>
    </div>
    
</div>

<!-- Monthly Evolution Chart -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Evolución Mensual <?php echo date('Y'); ?></h2>
    <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
</div>

<!-- Two Columns Layout -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    
    <!-- Recent Payments -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Últimos Pagos</h2>
            <a href="index.php?page=payments" class="btn btn-sm btn-secondary">Ver todos</a>
        </div>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php if (empty($recentPayments)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No hay pagos registrados</p>
            <?php else: ?>
                <?php foreach ($recentPayments as $payment): ?>
                    <div style="padding: 0.75rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: var(--text-main);">
                                <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?> • Cuota <?php echo $payment['fee_year']; ?>
                            </div>
                        </div>
                        <div style="font-weight: 600; color: var(--secondary-600);">
                            +<?php echo number_format($payment['amount'], 2); ?> €
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Expenses -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Últimos Gastos</h2>
            <a href="index.php?page=expenses" class="btn btn-sm btn-secondary">Ver todos</a>
        </div>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php if (empty($recentExpenses)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No hay gastos registrados</p>
            <?php else: ?>
                <?php foreach ($recentExpenses as $expense): ?>
                    <div style="padding: 0.75rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: var(--text-main);">
                                <?php echo htmlspecialchars($expense['description']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                <?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?>
                                <?php if (!empty($expense['category_name'])): ?>
                                    • <?php echo htmlspecialchars($expense['category_name']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-weight: 600; color: var(--danger-600);">
                            -<?php echo number_format($expense['amount'], 2); ?> €
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- Pending Payments Table -->
<?php if (!empty($pendingPayments)): ?>
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light);">
        <h2 style="margin: 0;">Socios con Pago Pendiente (<?php echo count($pendingPayments); ?>)</h2>
    </div>
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>Contacto</th>
                    <th>Categoría</th>
                    <th style="text-align: right;">Cuota Estimada</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPayments as $member): ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </td>
                        <td style="font-size: 0.875rem;">
                            <div><i class="fas fa-envelope" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($member['email']); ?></div>
                            <div style="margin-top: 0.25rem;"><i class="fas fa-phone" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($member['phone']); ?></div>
                        </td>
                        <td>
                            <?php if (!empty($member['category_name'])): ?>
                                <span class="badge" style="background-color: <?php echo htmlspecialchars($member['category_color'] ?? '#6b7280'); ?>; color: white;">
                                    <?php echo htmlspecialchars($member['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.875rem;">Sin categoría</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #d97706;">
                            <?php echo number_format($member['expected_amount'], 2); ?> €
                        </td>
                        <td style="text-align: right;">
                            <a href="index.php?page=members&action=markPaid&id=<?php echo $member['id']; ?>" 
                               class="btn btn-sm btn-primary"
                               onclick="return confirm('¿Marcar la cuota de <?php echo date('Y'); ?> como pagada?');">
                                <i class="fas fa-check"></i> Registrar Pago
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Pending Book Ads Table -->
<?php if (!empty($pendingBookAds)): ?>
<div class="card" style="padding: 0; overflow: hidden; margin-top: 1.5rem;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light); background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
        <h2 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-book" style="color: #d97706;"></i>
            Anuncios Libro Pendientes de Pago (<?php echo count($pendingBookAds); ?>)
        </h2>
    </div>
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Donante/Negocio</th>
                    <th>Año</th>
                    <th>Contacto</th>
                    <th>Tipo Anuncio</th>
                    <th style="text-align: right;">Importe</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingBookAds as $ad): ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <?php echo htmlspecialchars($ad['donor_name']); ?>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.875rem;">
                                <?php echo $ad['fee_year'] ?? $ad['book_year']; ?>
                            </span>
                        </td>
                        <td style="font-size: 0.875rem;">
                            <?php if (!empty($ad['donor_email'])): ?>
                                <div><i class="fas fa-envelope" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($ad['donor_email']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($ad['donor_phone'])): ?>
                                <div style="margin-top: 0.25rem;"><i class="fas fa-phone" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($ad['donor_phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                $adTypes = [
                                    'media' => 'Media Página',
                                    'full' => 'Página Completa',
                                    'cover' => 'Portada',
                                    'back_cover' => 'Contraportada'
                                ];
                                echo $adTypes[$ad['ad_type']] ?? $ad['ad_type'];
                            ?>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #d97706;">
                            <?php echo number_format($ad['amount'], 2); ?> €
                        </td>
                        <td style="text-align: right;">
                            <a href="index.php?page=book&action=markPaid&id=<?php echo $ad['book_ad_id']; ?>" 
                               class="btn btn-sm btn-primary"
                               onclick="return confirm('¿Marcar este anuncio como pagado?');">
                                <i class="fas fa-check"></i> Registrar Pago
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Monthly Evolution Chart
const ctx = document.getElementById('monthlyChart');
const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

const monthlyData = <?php echo json_encode($monthlyEvolution); ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthNames,
        datasets: [
            {
                label: 'Ingresos',
                data: monthlyData.map(m => m.income),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            },
            {
                label: 'Gastos',
                data: monthlyData.map(m => m.expenses),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
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
                        label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(context.parsed.y);
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(value);
                    }
                }
            }
        }
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
