<?php 
ob_start();
$title = 'Mensajes'; 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-comments"></i> Mensajes
        </h1>
        <p class="page-subtitle">Conversaciones privadas</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=messages&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Conversación
        </a>
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

<div class="messages-container">
    <div class="conversations-list">
        <?php if (!empty($conversations)): ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="index.php?page=messages&action=view&id=<?php echo $conv['conversation_id']; ?>" 
                   class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>">
                    <div class="conversation-avatar">
                        <?php if ($conv['sender_photo']): ?>
                            <img src="/<?php echo htmlspecialchars($conv['sender_photo']); ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="conversation-content">
                        <div class="conversation-header">
                            <h3 class="conversation-title">
                                <?php echo $conv['subject'] ? htmlspecialchars($conv['subject']) : $conv['participants']; ?>
                            </h3>
                            <span class="conversation-time">
                                <?php echo date('d/m/Y H:i', strtotime($conv['last_message_at'] ?? $conv['updated_at'])); ?>
                            </span>
                        </div>
                        <div class="conversation-preview">
                            <p><?php echo htmlspecialchars(mb_substr($conv['last_message'] ?? '', 0, 80) . (mb_strlen($conv['last_message'] ?? '') > 80 ? '...' : '')); ?></p>
                        </div>
                    </div>
                    <?php if ($conv['unread_count'] > 0): ?>
                        <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No tienes conversaciones</h3>
                <p>Inicia una nueva conversación para empezar a chatear</p>
                <a href="index.php?page=messages&action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Conversación
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.messages-container {
    max-width: 1200px;
    margin: 0 auto;
}

.conversations-list {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.conversation-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border-light);
    transition: background 0.2s;
    text-decoration: none;
    color: inherit;
    position: relative;
}

.conversation-item:hover {
    background: var(--bg-secondary);
}

.conversation-item.unread {
    background: var(--primary-50);
}

.conversation-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-600);
    font-size: 1.5rem;
    flex-shrink: 0;
    overflow: hidden;
}

.conversation-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.conversation-content {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.conversation-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-time {
    font-size: 0.875rem;
    color: var(--text-secondary);
    flex-shrink: 0;
    margin-left: 1rem;
}

.conversation-preview p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: var(--primary-500);
    color: white;
    border-radius: var(--radius-full);
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.3;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
}

.empty-state p {
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .conversation-item {
        padding: 1rem;
    }
    
    .conversation-avatar {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .conversation-title {
        font-size: 0.9rem;
    }
    
    .conversation-time {
        font-size: 0.75rem;
    }
}

[data-theme="dark"] .conversation-item.unread {
    background: var(--primary-900);
}

[data-theme="dark"] .conversation-item:hover {
    background: var(--dark-hover);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
