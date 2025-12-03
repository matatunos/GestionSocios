<?php 
require_once __DIR__ . '/../../Helpers/DocumentViewHelper.php';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i> Dashboard de Documentos
        </h1>
        <p class="page-subtitle">Estadísticas y análisis del sistema de gestión documental</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=documents" class="btn btn-primary">
            <i class="fas fa-folder-open"></i> Ver Documentos
        </a>
        <a href="index.php?page=documents&action=create" class="btn btn-success">
            <i class="fas fa-plus"></i> Subir Documento
        </a>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-file"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_documents']); ?></div>
            <div class="stat-label">Total Documentos</div>
            <div class="stat-trend">
                <i class="fas fa-arrow-up"></i> <?php echo $stats['new_this_week']; ?> esta semana
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-hdd"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">
                <?php 
                require_once __DIR__ . '/../../Helpers/FileUploadHelper.php';
                echo FileUploadHelper::formatBytes($stats['total_size']); 
                ?>
            </div>
            <div class="stat-label">Almacenamiento</div>
            <div class="stat-trend">
                <i class="fas fa-database"></i> <?php echo $stats['total_documents'] > 0 ? number_format($stats['total_size'] / $stats['total_documents']) : 0; ?> bytes/doc
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-download"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_downloads']); ?></div>
            <div class="stat-label">Total Descargas</div>
            <div class="stat-trend">
                <i class="fas fa-chart-bar"></i> <?php echo number_format($stats['avg_downloads'], 1); ?> promedio
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_contributors']); ?></div>
            <div class="stat-label">Colaboradores</div>
            <div class="stat-trend">
                <i class="fas fa-clock"></i> <?php echo $stats['new_this_month']; ?> este mes
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <i class="fas fa-globe"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['public_links_active']); ?></div>
            <div class="stat-label">Enlaces Públicos</div>
            <div class="stat-trend">
                <i class="fas fa-download"></i> <?php echo number_format($stats['total_public_downloads']); ?> descargas
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Gráfico de Documentos por Mes -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Documentos por Mes</h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" height="250"></canvas>
        </div>
    </div>
    
    <!-- Gráfico de Tipos de Archivo -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Distribución por Tipo</h3>
        </div>
        <div class="card-body">
            <canvas id="fileTypeChart" height="250"></canvas>
        </div>
    </div>
    
    <!-- Documentos Más Descargados -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-fire"></i> Más Descargados</h3>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($mostDownloaded as $doc): ?>
                    <div class="list-item">
                        <div class="list-icon">
                            <?php
                            require_once __DIR__ . '/../../Helpers/FileTypeHelper.php';
                            echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION), 24);
                            ?>
                        </div>
                        <div class="list-content">
                            <div class="list-title">
                                <a href="index.php?page=documents&action=download&id=<?php echo $doc['id']; ?>">
                                    <?php echo htmlspecialchars($doc['title']); ?>
                                </a>
                            </div>
                            <div class="list-meta">
                                <span><i class="fas fa-download"></i> <?php echo number_format($doc['downloads']); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Actividad Reciente -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
        </div>
        <div class="card-body">
            <div class="activity-timeline">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo DocumentViewHelper::getActivityIconClass($activity['action']); ?>">
                            <i class="fas <?php echo DocumentViewHelper::getActivityIcon($activity['action']); ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">
                                <?php if ($activity['source'] === 'public'): ?>
                                    <strong>Usuario anónimo (<?php echo htmlspecialchars(substr($activity['ip_address'], 0, 15)); ?>)</strong>
                                    <?php echo DocumentViewHelper::getActivityText($activity['action']); ?>
                                <?php else: ?>
                                    <strong><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong>
                                    <?php echo DocumentViewHelper::getActivityText($activity['action']); ?>
                                <?php endif; ?>
                                <a href="index.php?page=documents&action=preview&id=<?php echo $activity['document_id']; ?>">
                                    <?php echo htmlspecialchars($activity['document_title'] ?? $activity['file_name'] ?? 'documento'); ?>
                                </a>
                                <?php if ($activity['source'] === 'public'): ?>
                                    <span class="badge badge-success" style="margin-left: 0.5rem;">
                                        <i class="fas fa-globe"></i> Público
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="activity-time">
                                <?php echo DocumentViewHelper::timeAgo($activity['created_at']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($activityTotalPages > 1): ?>
                <div class="pagination-container" style="margin-top: 1.5rem; text-align: center;">
                    <div class="pagination">
                        <?php if ($activityCurrentPage > 1): ?>
                            <a href="?page=documents&action=dashboard&activity_page=<?php echo $activityCurrentPage - 1; ?>" 
                               class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <span class="pagination-info">
                            Página <?php echo $activityCurrentPage; ?> de <?php echo $activityTotalPages; ?>
                        </span>
                        
                        <?php if ($activityCurrentPage < $activityTotalPages): ?>
                            <a href="?page=documents&action=dashboard&activity_page=<?php echo $activityCurrentPage + 1; ?>" 
                               class="pagination-btn">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Estadísticas por Categoría -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-tags"></i> Documentos por Categoría</h3>
        </div>
        <div class="card-body">
            <?php foreach ($categoryStats as $cat): ?>
                <?php if ($cat['count'] > 0): ?>
                    <div class="category-stat">
                        <div class="category-info">
                            <span class="category-badge" style="background: <?php echo htmlspecialchars($cat['color']); ?>;">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </span>
                            <span class="category-count"><?php echo $cat['count']; ?> docs</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" 
                                 style="width: <?php echo $stats['total_documents'] > 0 ? ($cat['count'] / $stats['total_documents'] * 100) : 0; ?>%; background: <?php echo htmlspecialchars($cat['color']); ?>;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Usuarios Más Activos -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Usuarios Más Activos</h3>
        </div>
        <div class="card-body">
            <div class="ranking-list">
                <?php $rank = 1; foreach ($topUsers as $user): ?>
                    <div class="ranking-item">
                        <div class="ranking-position"><?php echo $rank++; ?></div>
                        <div class="ranking-content">
                            <div class="ranking-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            <div class="ranking-stats">
                                <span><i class="fas fa-file"></i> <?php echo $user['document_count']; ?></span>
                                <span><i class="fas fa-download"></i> <?php echo number_format($user['total_downloads']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Tags Más Usados -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-hashtag"></i> Tags Más Usados</h3>
        </div>
        <div class="card-body">
            <div class="tags-cloud">
                <?php foreach ($topTags as $tag): ?>
                    <a href="index.php?page=documents&tag_id=<?php echo $tag['id']; ?>" 
                       class="tag-badge" 
                       style="background: <?php echo htmlspecialchars($tag['color']); ?>; font-size: <?php echo 0.8 + ($tag['document_count'] / 10); ?>rem;">
                        #<?php echo htmlspecialchars($tag['name']); ?>
                        <span class="tag-count"><?php echo $tag['document_count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Carpetas Más Usadas -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-folder"></i> Carpetas Más Usadas</h3>
        </div>
        <div class="card-body">
            <?php if (empty($topFolders)): ?>
                <p class="text-muted text-center">No hay carpetas con documentos</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($topFolders as $folder): ?>
                        <div class="list-item">
                            <div class="list-icon">
                                <i class="fas fa-folder" style="color: #f59e0b; font-size: 24px;"></i>
                            </div>
                            <div class="list-content">
                                <div class="list-title">
                                    <a href="index.php?page=documents&folder_id=<?php echo $folder['id']; ?>">
                                        <?php echo htmlspecialchars($folder['name']); ?>
                                    </a>
                                </div>
                                <div class="list-meta">
                                    <span><i class="fas fa-file"></i> <?php echo $folder['document_count']; ?> documentos</span>
                                    <span><i class="fas fa-hdd"></i> <?php echo FileUploadHelper::formatBytes($folder['total_size']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Documentos Recientes -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Documentos Recientes</h3>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($recentDocuments as $doc): ?>
                    <div class="list-item">
                        <div class="list-icon">
                            <?php echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION), 24); ?>
                        </div>
                        <div class="list-content">
                            <div class="list-title">
                                <a href="index.php?page=documents&action=preview&id=<?php echo $doc['id']; ?>">
                                    <?php echo htmlspecialchars($doc['title']); ?>
                                </a>
                            </div>
                            <div class="list-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo DocumentViewHelper::timeAgo($doc['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Enlaces Públicos Activos -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-share-alt"></i> Enlaces Públicos Activos</h3>
            <a href="index.php?page=documents&action=public_links" class="btn-header">Ver todos</a>
        </div>
        <div class="card-body">
            <?php if (empty($publicDocuments)): ?>
                <p class="text-muted text-center">No hay documentos compartidos públicamente</p>
            <?php else: ?>
                <div class="list-group">
                    <?php 
                    $displayCount = 0;
                    foreach ($publicDocuments as $doc): 
                        if ($displayCount >= 5) break;
                        $displayCount++;
                    ?>
                        <div class="list-item">
                            <div class="list-icon">
                                <?php echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION), 24); ?>
                            </div>
                            <div class="list-content">
                                <div class="list-title">
                                    <a href="index.php?page=documents&action=public_stats&id=<?php echo $doc['id']; ?>">
                                        <?php echo htmlspecialchars($doc['title']); ?>
                                    </a>
                                    <?php if ($doc['status'] === 'expired'): ?>
                                        <span class="badge bg-warning">Expirado</span>
                                    <?php elseif ($doc['status'] === 'limit_reached'): ?>
                                        <span class="badge bg-danger">Límite</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="list-meta">
                                    <span><i class="fas fa-download"></i> <?php echo htmlspecialchars($doc['download_stats']); ?></span>
                                    <?php if ($doc['public_last_access']): ?>
                                        <span><i class="fas fa-clock"></i> <?php echo DocumentViewHelper::timeAgo($doc['public_last_access']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-clock"></i> Sin accesos</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.stat-trend {
    color: #10b981;
    font-size: 0.75rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.dashboard-card .card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-card .card-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-header {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    transition: background 0.2s;
}

.btn-header:hover {
    background: #dbeafe;
}

.dashboard-card .card-body {
    padding: 1.5rem;
}

.list-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.list-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 8px;
    transition: background 0.2s;
}

.list-item:hover {
    background: #f8fafc;
}

.list-icon {
    flex-shrink: 0;
}

.list-content {
    flex: 1;
    min-width: 0;
}

.list-title a {
    color: #1e293b;
    text-decoration: none;
    font-weight: 500;
}

.list-title a:hover {
    color: #3b82f6;
}

.list-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
    font-size: 14px;
}

.activity-icon.uploaded { background: #10b981; }
.activity-icon.downloaded { background: #3b82f6; }
.activity-icon.public-download { background: #14b8a6; }
.activity-icon.deleted { background: #ef4444; }
.activity-icon.edited { background: #f59e0b; }
.activity-icon.previewed { background: #8b5cf6; }

.activity-content {
    flex: 1;
}

.activity-text {
    font-size: 0.875rem;
    color: #1e293b;
}

.activity-time {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

.category-stat {
    margin-bottom: 1rem;
}

.category-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.category-badge {
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.category-count {
    color: #64748b;
    font-size: 0.875rem;
}

.category-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}

.category-progress {
    height: 100%;
    transition: width 0.3s;
}

.ranking-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.ranking-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.ranking-position {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.ranking-content {
    flex: 1;
}

.ranking-name {
    font-weight: 500;
    color: #1e293b;
}

.ranking-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.tag-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: transform 0.2s, opacity 0.2s;
}

.tag-badge:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

.tag-count {
    background: rgba(255,255,255,0.3);
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Paginación */
.pagination-container {
    padding: 1rem 0;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.pagination-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.pagination-info {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #475569;
}

[data-theme="dark"] .pagination-info {
    background: #334155;
    color: #cbd5e1;
}

[data-theme="dark"] .pagination-btn {
    background: #1e40af;
}

[data-theme="dark"] .pagination-btn:hover {
    background: #1e3a8a;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Gráfico de documentos por mes
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyStats, 'month')); ?>,
        datasets: [{
            label: 'Documentos Subidos',
            data: <?php echo json_encode(array_column($monthlyStats, 'count')); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Gráfico de tipos de archivo
const fileTypeCtx = document.getElementById('fileTypeChart').getContext('2d');
new Chart(fileTypeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map(function($f) { return strtoupper($f['file_extension']); }, $fileTypeStats)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($fileTypeStats, 'count')); ?>,
            backgroundColor: [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>