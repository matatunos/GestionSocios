<?php 
ob_start(); 
require_once __DIR__ . '/../../Helpers/Auth.php';
$title = 'Votaciones'; 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-vote-yea"></i> Votaciones
        </h1>
        <p class="page-subtitle">Sistema de votaciones y encuestas</p>
    </div>
    <div class="page-actions">
        <?php if (Auth::isAdmin()): ?>
            <a href="index.php?page=polls&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Votación
            </a>
        <?php endif; ?>
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

<!-- Filter tabs -->
<div class="tabs-container" style="margin-bottom: 1.5rem;">
    <a href="index.php?page=polls&status=all" class="tab <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'active' : ''; ?>">
        <i class="fas fa-list"></i> Todas
    </a>
    <a href="index.php?page=polls&status=active" class="tab <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'active' : ''; ?>">
        <i class="fas fa-play-circle"></i> Activas
    </a>
    <a href="index.php?page=polls&status=closed" class="tab <?php echo (isset($_GET['status']) && $_GET['status'] === 'closed') ? 'active' : ''; ?>">
        <i class="fas fa-check-circle"></i> Cerradas
    </a>
</div>

<!-- Polls list -->
<?php if (empty($polls)): ?>
    <div class="empty-state">
        <i class="fas fa-vote-yea"></i>
        <h3>No hay votaciones</h3>
        <p>No se encontraron votaciones para mostrar</p>
        <?php if (Auth::isAdmin()): ?>
            <a href="index.php?page=polls&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Primera Votación
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="polls-grid">
        <?php foreach ($polls as $poll): 
            $is_active = (strtotime($poll['start_date']) <= time() && strtotime($poll['end_date']) >= time());
            $has_voted = false;
            
            // Check if user has voted (quick check)
            $check_query = "SELECT COUNT(*) as count FROM poll_votes WHERE poll_id = :poll_id AND member_id = :member_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bindParam(':poll_id', $poll['id']);
            $check_stmt->bindParam(':member_id', $_SESSION['user_id']);
            $check_stmt->execute();
            $vote_check = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $has_voted = $vote_check['count'] > 0;
            
            // Get total votes
            $total_query = "SELECT COUNT(DISTINCT member_id) as total FROM poll_votes WHERE poll_id = :poll_id";
            $total_stmt = $this->db->prepare($total_query);
            $total_stmt->bindParam(':poll_id', $poll['id']);
            $total_stmt->execute();
            $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
            $total_votes = $total_result['total'];
        ?>
            <div class="poll-card <?php echo $is_active ? 'active' : 'closed'; ?>">
                <div class="poll-card-header">
                    <h3 class="poll-title">
                        <a href="index.php?page=polls&action=view&id=<?php echo $poll['id']; ?>">
                            <?php echo htmlspecialchars($poll['title']); ?>
                        </a>
                    </h3>
                    <span class="poll-status <?php echo $is_active ? 'status-active' : 'status-closed'; ?>">
                        <i class="fas <?php echo $is_active ? 'fa-play-circle' : 'fa-check-circle'; ?>"></i>
                        <?php echo $is_active ? 'Activa' : 'Cerrada'; ?>
                    </span>
                </div>
                
                <p class="poll-description"><?php echo nl2br(htmlspecialchars($poll['description'])); ?></p>
                
                <div class="poll-meta">
                    <div class="poll-meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Inicio: <?php echo date('d/m/Y', strtotime($poll['start_date'])); ?></span>
                    </div>
                    <div class="poll-meta-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Fin: <?php echo date('d/m/Y', strtotime($poll['end_date'])); ?></span>
                    </div>
                    <div class="poll-meta-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo $total_votes; ?> voto<?php echo $total_votes !== 1 ? 's' : ''; ?></span>
                    </div>
                </div>
                
                <div class="poll-card-footer">
                    <?php if ($has_voted): ?>
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> Has votado
                        </span>
                    <?php elseif ($is_active): ?>
                        <a href="index.php?page=polls&action=view&id=<?php echo $poll['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-vote-yea"></i> Votar
                        </a>
                    <?php endif; ?>
                    
                    <a href="index.php?page=polls&action=view&id=<?php echo $poll['id']; ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-chart-bar"></i> Ver Resultados
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.tabs-container {
    display: flex;
    gap: 0.5rem;
    background: white;
    padding: 1rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.tab {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-secondary);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab:hover {
    background: var(--primary-50);
    color: var(--primary-600);
}

.tab.active {
    background: var(--primary-500);
    color: white;
}

.polls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.poll-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid var(--primary-500);
}

.poll-card.closed {
    border-left-color: var(--gray-400);
    opacity: 0.9;
}

.poll-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.poll-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.poll-title {
    margin: 0;
    font-size: 1.25rem;
    flex: 1;
}

.poll-title a {
    color: var(--text-primary);
    text-decoration: none;
}

.poll-title a:hover {
    color: var(--primary-600);
}

.poll-status {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-active {
    background: var(--success-100);
    color: var(--success-700);
}

.status-closed {
    background: var(--gray-100);
    color: var(--gray-700);
}

.poll-description {
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}

.poll-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem 0;
    border-top: 1px solid var(--border-light);
    border-bottom: 1px solid var(--border-light);
    margin-bottom: 1rem;
}

.poll-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.poll-meta-item i {
    color: var(--primary-500);
}

.poll-card-footer {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

@media (max-width: 768px) {
    .polls-grid {
        grid-template-columns: 1fr;
    }
    
    .poll-card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

[data-theme="dark"] .poll-card {
    background: var(--dark-card);
    border-left-color: var(--primary-400);
}

[data-theme="dark"] .poll-card.closed {
    border-left-color: var(--gray-600);
}

[data-theme="dark"] .poll-title a {
    color: var(--dark-text-primary);
}

[data-theme="dark"] .status-active {
    background: rgba(34, 197, 94, 0.15);
    color: var(--success-400);
}

[data-theme="dark"] .status-closed {
    background: rgba(100, 116, 139, 0.15);
    color: var(--gray-400);
}

[data-theme="dark"] .tabs-container {
    background: var(--dark-card);
}

[data-theme="dark"] .tab.active {
    background: var(--primary-600);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
