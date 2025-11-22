<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Panel de Control</h1>
    <a href="index.php?page=members&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Socio
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Socios Activos</h3>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: var(--primary-600);">
                    <?php echo $activeMembers; ?>
                </p>
            </div>
            <div style="background: var(--primary-50); padding: 1rem; border-radius: 50%;">
                <i class="fas fa-users" style="font-size: 1.5rem; color: var(--primary-600);"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Ingresos <?php echo date('Y'); ?></h3>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: var(--secondary-600);">
                    <?php echo number_format($yearlyIncome, 2); ?> €
                </p>
            </div>
            <div style="background: #dcfce7; padding: 1rem; border-radius: 50%;">
                <i class="fas fa-euro-sign" style="font-size: 1.5rem; color: var(--secondary-600);"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h3 style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase;">Pagos Pendientes</h3>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0 0; color: var(--danger-600);">
                    <?php echo $pendingPayments; ?>
                </p>
            </div>
            <div style="background: #fee2e2; padding: 1rem; border-radius: 50%;">
                <i class="fas fa-exclamation-circle" style="font-size: 1.5rem; color: var(--danger-600);"></i>
            </div>
        </div>
    </div>
</div>

<!-- Income Breakdown -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Desglose de Ingresos <?php echo date('Y'); ?></h2>
    
    <?php if (empty($incomeByType)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No hay ingresos registrados para este año.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Cantidad de Pagos</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $typeLabels = [
                    'fee' => ['label' => 'Cuotas Anuales', 'icon' => 'fa-receipt'],
                    'event' => ['label' => 'Eventos', 'icon' => 'fa-calendar-alt'],
                    'donation' => ['label' => 'Donaciones', 'icon' => 'fa-hand-holding-heart']
                ];
                foreach ($incomeByType as $type): 
                    $info = $typeLabels[$type['payment_type']] ?? ['label' => $type['payment_type'], 'icon' => 'fa-money-bill'];
                ?>
                    <tr>
                        <td>
                            <i class="fas <?php echo $info['icon']; ?>" style="margin-right: 0.5rem; color: var(--primary-600);"></i>
                            <?php echo $info['label']; ?>
                        </td>
                        <td><?php echo $type['count']; ?> pago<?php echo $type['count'] > 1 ? 's' : ''; ?></td>
                        <td style="text-align: right; font-weight: 600; color: var(--secondary-600);">
                            <?php echo number_format($type['total'], 2); ?> €
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Notifications Widget -->
<?php if (!empty($recentNotifications)): ?>
<div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--primary-600);">
    <div class="flex justify-between items-center mb-4">
        <h2 style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-bell" style="color: var(--primary-600);"></i>
            Notificaciones Recientes
        </h2>
        <a href="index.php?page=notifications" class="btn btn-sm btn-secondary">Ver todas</a>
    </div>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php foreach ($recentNotifications as $notification): ?>
            <div style="padding: 1rem; background: <?php echo $notification['is_read'] ? 'var(--bg-body)' : 'var(--primary-50)'; ?>; border-radius: var(--radius-md); display: flex; align-items: start; gap: 1rem;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: var(--primary-600); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell"></i>
                </div>
                <div style="flex: 1;">
                    <p style="margin: 0; font-weight: <?php echo $notification['is_read'] ? '400' : '600'; ?>;">
                        <?php echo htmlspecialchars($notification['message']); ?>
                    </p>
                    <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--text-muted);">
                        <?php 
                        $time = strtotime($notification['created_at']);
                        $diff = time() - $time;
                        if ($diff < 60) echo 'Hace un momento';
                        elseif ($diff < 3600) echo 'Hace ' . floor($diff / 60) . ' minutos';
                        elseif ($diff < 86400) echo 'Hace ' . floor($diff / 3600) . ' horas';
                        else echo date('d/m/Y H:i', $time);
                        ?>
                    </p>
                </div>
                <?php if (!$notification['is_read']): ?>
                    <span style="flex-shrink: 0; width: 8px; height: 8px; background: var(--primary-600); border-radius: 50%;"></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="flex justify-between items-center mb-4">
        <h2>Actividad Reciente</h2>
        <a href="index.php?page=payments" class="btn btn-sm btn-secondary">Ver todos los pagos</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Socio</th>
                    <th>Concepto</th>
                    <th style="text-align: right;">Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentActivity)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            No hay actividad reciente para mostrar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $typeConfig = [
                        'payment' => ['icon' => 'fa-receipt', 'color' => 'var(--primary-600)'],
                        'donation' => ['icon' => 'fa-hand-holding-heart', 'color' => 'var(--secondary-600)'],
                        'book_ad' => ['icon' => 'fa-book-open', 'color' => 'var(--warning-600)'],
                        'deactivation' => ['icon' => 'fa-user-times', 'color' => 'var(--danger-600)']
                    ];
                    
                    foreach ($recentActivity as $activity): 
                        $config = $typeConfig[$activity['type']] ?? ['icon' => 'fa-circle', 'color' => 'var(--text-muted)'];
                        $isDeactivation = $activity['type'] === 'deactivation';
                    ?>
                        <tr>
                            <td style="font-size: 0.875rem; color: var(--text-muted);">
                                <?= date('d/m/Y H:i', strtotime($activity['activity_date'])) ?>
                            </td>
                            <td style="font-weight: 500;">
                                <?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?>
                            </td>
                            <td>
                                <i class="fas <?= $config['icon'] ?>" style="margin-right: 0.5rem; color: <?= $config['color'] ?>;"></i>
                                <?= htmlspecialchars($activity['description']) ?>
                                <?php if ($activity['subtype']): ?>
                                    <span class="text-xs text-muted">(<?= htmlspecialchars($activity['subtype']) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: <?= $isDeactivation ? 'var(--text-muted)' : 'var(--secondary-600)' ?>;">
                                <?php if (!$isDeactivation): ?>
                                    <?= number_format($activity['amount'], 2) ?> €
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
