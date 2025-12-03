<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=documents&action=public_links" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver a Enlaces Públicos
    </a>
    <h1><i class="fas fa-chart-bar"></i> Estadísticas de Enlace Público</h1>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file"></i> Documento: <?php echo htmlspecialchars($document['title']); ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Archivo:</strong> <?php echo htmlspecialchars($document['file_name']); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Token:</strong> <code><?php echo substr($document['public_token'], 0, 16); ?>...</code></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($document['public_created_at'])); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Estado:</strong> 
                            <span class="badge <?php echo $document['public_enabled'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $document['public_enabled'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_views'] ?? 0); ?></div>
                <div class="stat-label">Vistas Totales</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_downloads'] ?? 0); ?></div>
                <div class="stat-label">Descargas</div>
                <?php if ($document['public_download_limit']): ?>
                    <div class="stat-trend">
                        Límite: <?php echo $document['public_download_limit']; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['unique_ips'] ?? 0); ?></div>
                <div class="stat-label">IPs Únicas</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php 
                    if ($document['public_last_access']) {
                        echo date('d/m/Y H:i', strtotime($document['public_last_access']));
                    } else {
                        echo 'Nunca';
                    }
                    ?>
                </div>
                <div class="stat-label">Último Acceso</div>
            </div>
        </div>
    </div>
</div>

<!-- Log de Accesos -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Registro de Accesos</h3>
    </div>
    <div class="card-body">
        <?php if (empty($accessLog)): ?>
            <p class="text-muted text-center py-4">
                <i class="fas fa-info-circle"></i> No hay accesos registrados para este enlace
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>IP</th>
                            <th>User Agent</th>
                            <th>Acción</th>
                            <th>Referrer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accessLog as $log): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['access_date'])); ?></td>
                                <td>
                                    <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($log['user_agent'], 0, 80)); ?></small>
                                </td>
                                <td>
                                    <?php if ($log['downloaded']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-download"></i> Descarga
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <i class="fas fa-eye"></i> Vista
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo $log['referer'] ? htmlspecialchars(substr($log['referer'], 0, 50)) : '-'; ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
    margin-bottom: 1rem;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.stat-trend {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

[data-theme="dark"] .stat-card {
    background: #1e293b;
}

[data-theme="dark"] .stat-value {
    color: #f1f5f9;
}

[data-theme="dark"] .stat-label {
    color: #94a3b8;
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 0.875rem;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

[data-theme="dark"] .table th {
    background: #334155;
    color: #cbd5e1;
    border-bottom-color: #475569;
}

[data-theme="dark"] .table td {
    color: #e2e8f0;
    border-color: #334155;
}

[data-theme="dark"] .table tbody tr:hover {
    background: #334155;
}

code {
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

[data-theme="dark"] code {
    background: #334155;
    color: #94a3b8;
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
