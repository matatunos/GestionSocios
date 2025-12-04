<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-hand-holding-usd"></i> Dashboard de Subvenciones</h1>
        <div class="header-actions">
            <a href="index.php?page=grants&action=index" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="index.php?page=grants&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Subvención
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Subvenciones</div>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($stats['total']); ?></div>
                </div>
                <i class="fas fa-folder-open" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Abiertas</div>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($stats['open']); ?></div>
                </div>
                <i class="fas fa-door-open" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Solicitadas</div>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($applicationStats['submitted']); ?></div>
                </div>
                <i class="fas fa-paper-plane" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Concedidas</div>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($applicationStats['granted']); ?></div>
                    <div style="font-size: 0.75rem; opacity: 0.9; margin-top: 0.25rem;">
                        <?php echo number_format($applicationStats['total_granted_amount'] ?? 0, 2); ?> €
                    </div>
                </div>
                <i class="fas fa-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Subvenciones próximas a vencer -->
        <div class="card">
            <div class="card-header" style="background: #fff3cd; border-bottom: 2px solid #ffc107; padding: 1rem; font-weight: 600;">
                <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> Próximas a Vencer (30 días)
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($expiring)): ?>
                    <div style="padding: 2rem; text-align: center; color: #6c757d;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No hay subvenciones próximas a vencer</p>
                    </div>
                <?php else: ?>
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Subvención</th>
                                <th>Deadline</th>
                                <th>Días</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiring as $grant): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" style="font-weight: 500;">
                                            <?php echo htmlspecialchars($grant['title']); ?>
                                        </a>
                                        <div style="font-size: 0.875rem; color: #6c757d;">
                                            <?php echo htmlspecialchars($grant['organization']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $grant['application_deadline'] ? date('d/m/Y', strtotime($grant['application_deadline'])) : '-'; ?></td>
                                    <td>
                                        <?php 
                                        $days = $grant['days_remaining'] ?? 0;
                                        $color = $days <= 7 ? '#dc3545' : ($days <= 15 ? '#ffc107' : '#28a745');
                                        ?>
                                        <span style="color: <?php echo $color; ?>; font-weight: 600;">
                                            <?php echo $days; ?> días
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $grant['status'] === 'concedida' ? 'success' : 
                                                ($grant['status'] === 'en_proceso' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($grant['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Justificaciones pendientes -->
        <div class="card">
            <div class="card-header" style="background: #f8d7da; border-bottom: 2px solid #dc3545; padding: 1rem; font-weight: 600;">
                <i class="fas fa-file-invoice" style="color: #dc3545;"></i> Justificaciones Pendientes
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($pendingJustifications)): ?>
                    <div style="padding: 2rem; text-align: center; color: #6c757d;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No hay justificaciones pendientes</p>
                    </div>
                <?php else: ?>
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Subvención</th>
                                <th>Importe</th>
                                <th>Plazo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingJustifications as $app): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?page=grants&action=viewApplication&id=<?php echo $app['id']; ?>" style="font-weight: 500;">
                                            <?php echo htmlspecialchars($app['grant_title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo number_format($app['granted_amount'], 2); ?> €</td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($app['justification_deadline'])); ?>
                                        <div style="font-size: 0.875rem; color: #dc3545; font-weight: 600;">
                                            <?php echo $app['days_remaining']; ?> días
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $app['justification_status'] === 'pendiente' ? 'danger' : 'warning'; 
                                        ?>">
                                            <?php echo ucfirst($app['justification_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subvenciones recientes -->
    <div class="card">
        <div class="card-header" style="padding: 1rem; font-weight: 600; border-bottom: 2px solid #e9ecef;">
            <i class="fas fa-clock"></i> Subvenciones Recientes
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentGrants)): ?>
                <div style="padding: 2rem; text-align: center; color: #6c757d;">
                    <p>No hay subvenciones registradas</p>
                </div>
            <?php else: ?>
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Organismo</th>
                            <th>Tipo</th>
                            <th>Importe Máx.</th>
                            <th>Deadline</th>
                            <th>Estado</th>
                            <th>Relevancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentGrants as $grant): ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" style="font-weight: 500;">
                                        <?php echo htmlspecialchars($grant['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($grant['organization'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo ucfirst($grant['category'] ?? 'general'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($grant['amount']) && $grant['amount']): ?>
                                        <?php echo number_format($grant['amount'], 2); ?> €
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $grant['application_deadline'] ? date('d/m/Y', strtotime($grant['application_deadline'])) : '-'; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $grant['status'] === 'abierta' ? 'success' : 
                                            ($grant['status'] === 'cerrada' ? 'secondary' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($grant['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $score = $grant['tracked'] ? 100 : 50;
                                    $color = $score >= 70 ? '#28a745' : ($score >= 50 ? '#ffc107' : '#6c757d');
                                    ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo $score; ?>%; height: 100%; background: <?php echo $color; ?>;"></div>
                                        </div>
                                        <span style="font-size: 0.875rem; font-weight: 600; color: <?php echo $color; ?>;">
                                            <?php echo $score; ?>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
    font-weight: 500;
}
.badge-success { background: #28a745; color: white; }
.badge-info { background: #17a2b8; color: white; }
.badge-warning { background: #ffc107; color: #000; }
.badge-danger { background: #dc3545; color: white; }
.badge-secondary { background: #6c757d; color: white; }

.table {
    width: 100%;
    border-collapse: collapse;
}
.table th {
    background: #f8f9fa;
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 2px solid #dee2e6;
}
.table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}
.table tbody tr:hover {
    background: #f8f9fa;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
