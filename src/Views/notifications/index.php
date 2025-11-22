<?php ob_start(); ?>

<?php $title = 'Notificaciones'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-bell"></i> Notificaciones
        </h1>
        <p class="page-subtitle">Historial completo de notificaciones</p>
    </div>
    <div class="page-actions">
        <?php if (Auth::hasPermission('notifications_create')): ?>
            <a href="index.php?page=notifications&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Notificación
            </a>
        <?php endif; ?>
        <form method="POST" action="index.php?page=notifications&action=markAllAsRead" style="display: inline;">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </button>
        </form>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Estadísticas -->
<?php if (!empty($stats)): ?>
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $unread_count; ?></div>
            <div class="stat-label">Sin Leer</div>
        </div>
    </div>
    
    <?php 
    $total_count = 0;
    foreach ($stats as $stat) {
        $total_count += $stat['total'];
    }
    ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-inbox"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $total_count; ?></div>
            <div class="stat-label">Total</div>
        </div>
    </div>
    
    <?php 
    $type_labels = [
        'payment_reminder' => 'Pagos',
        'event_reminder' => 'Eventos',
        'announcement' => 'Anuncios',
        'system' => 'Sistema',
        'welcome' => 'Bienvenida'
    ];
    
    foreach ($stats as $stat):
        if ($stat['total'] == 0) continue;
        $label = $type_labels[$stat['type']] ?? ucfirst($stat['type']);
    ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-tag"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stat['total']; ?></div>
            <div class="stat-label"><?php echo $label; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card filter-card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="notifications">
        
        <div class="filter-row">
            <div class="filter-group">
                <label>Tipo</label>
                <select name="type" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="payment_reminder" <?php echo (($_GET['type'] ?? '') === 'payment_reminder') ? 'selected' : ''; ?>>Recordatorio de pago</option>
                    <option value="event_reminder" <?php echo (($_GET['type'] ?? '') === 'event_reminder') ? 'selected' : ''; ?>>Recordatorio de evento</option>
                    <option value="announcement" <?php echo (($_GET['type'] ?? '') === 'announcement') ? 'selected' : ''; ?>>Anuncio</option>
                    <option value="system" <?php echo (($_GET['type'] ?? '') === 'system') ? 'selected' : ''; ?>>Sistema</option>
                    <option value="welcome" <?php echo (($_GET['type'] ?? '') === 'welcome') ? 'selected' : ''; ?>>Bienvenida</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="unread" <?php echo (($_GET['status'] ?? '') === 'unread') ? 'selected' : ''; ?>>Sin leer</option>
                    <option value="read" <?php echo (($_GET['status'] ?? '') === 'read') ? 'selected' : ''; ?>>Leídas</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="index.php?page=notifications" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista de notificaciones -->
<div class="card">
    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No hay notificaciones</h3>
            <p>Cuando recibas notificaciones, aparecerán aquí.</p>
        </div>
    <?php else: ?>
        <div class="notifications-timeline">
            <?php 
            $current_date = null;
            foreach ($notifications as $notification):
                $notif_date = date('Y-m-d', strtotime($notification['created_at']));
                
                // Mostrar separador de fecha
                if ($notif_date !== $current_date):
                    $current_date = $notif_date;
                    $date_label = date('d/m/Y', strtotime($notif_date));
                    if ($notif_date === date('Y-m-d')) $date_label = 'Hoy';
                    if ($notif_date === date('Y-m-d', strtotime('-1 day'))) $date_label = 'Ayer';
            ?>
                <div class="timeline-date"><?php echo $date_label; ?></div>
            <?php endif; ?>
            
            <div class="timeline-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                <div class="timeline-marker <?php echo $notification['is_read'] ? '' : 'active'; ?>">
                    <?php
                    $type_icons = [
                        'payment_reminder' => 'fa-money-bill-wave',
                        'event_reminder' => 'fa-calendar-alt',
                        'announcement' => 'fa-bullhorn',
                        'system' => 'fa-cog',
                        'welcome' => 'fa-hand-wave'
                    ];
                    $icon = $type_icons[$notification['type']] ?? 'fa-bell';
                    ?>
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                
                <div class="timeline-content">
                    <div class="timeline-header">
                        <div>
                            <h4 class="timeline-title">
                                <?php echo htmlspecialchars($notification['title']); ?>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge badge-primary" style="font-size: 0.7rem; margin-left: 0.5rem;">Nuevo</span>
                                <?php endif; ?>
                            </h4>
                            <p class="timeline-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        </div>
                        <div class="timeline-actions">
                            <span class="timeline-time">
                                <i class="fas fa-clock"></i>
                                <?php echo date('H:i', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="timeline-footer">
                        <?php if ($notification['link']): ?>
                            <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt"></i> Ver más
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST" action="index.php?page=notifications&action=markAsRead" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                                <input type="hidden" name="redirect" value="index.php?page=notifications">
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-check"></i> Marcar como leída
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" action="index.php?page=notifications&action=delete" style="display: inline;" onsubmit="return confirm('¿Eliminar esta notificación?');">
                            <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.notifications-timeline {
    padding: 1rem 0;
}

.timeline-date {
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--primary-600);
    padding: 0.5rem 0;
    margin: 1rem 0;
    border-bottom: 2px solid var(--primary-200);
}

.timeline-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    border-radius: var(--radius-md);
    transition: var(--transition);
}

.timeline-item.unread {
    background: rgba(99, 102, 241, 0.05);
}

.timeline-item:hover {
    background: var(--primary-50);
    transform: translateX(4px);
}

.timeline-marker {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-glass);
    border: 2px solid var(--border-light);
    border-radius: 50%;
    font-size: 1.25rem;
    color: var(--text-muted);
}

.timeline-marker.active {
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    border-color: var(--primary-600);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.timeline-content {
    flex: 1;
    min-width: 0;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.timeline-title {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-main);
    display: flex;
    align-items: center;
}

.timeline-message {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-muted);
    line-height: 1.5;
}

.timeline-time {
    font-size: 0.8125rem;
    color: var(--text-light);
    white-space: nowrap;
}

.timeline-footer {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

[data-theme="dark"] .timeline-date {
    color: var(--primary-400);
    border-color: rgba(99, 102, 241, 0.3);
}

[data-theme="dark"] .timeline-item:hover {
    background: rgba(99, 102, 241, 0.08);
}

[data-theme="dark"] .timeline-marker {
    background: rgba(51, 65, 85, 0.5);
    border-color: rgba(100, 116, 139, 0.3);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>