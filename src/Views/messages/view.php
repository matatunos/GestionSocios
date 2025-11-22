<?php 
ob_start();
$conversation_title = $conversationInfo['subject'] ?? $conversationInfo['participants'] ?? 'Conversación';
$title = $conversation_title;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-comments"></i> <?php echo htmlspecialchars($conversation_title); ?>
        </h1>
        <p class="page-subtitle"><?php echo htmlspecialchars($conversationInfo['participants'] ?? ''); ?></p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=messages" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="chat-container">
    <div class="messages-area" id="messagesArea">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'message-own' : 'message-other'; ?>">
                    <?php if ($msg['sender_id'] != $_SESSION['user_id']): ?>
                        <div class="message-avatar">
                            <?php if ($msg['photo_url']): ?>
                                <img src="/<?php echo htmlspecialchars($msg['photo_url']); ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="message-content">
                        <?php if ($msg['sender_id'] != $_SESSION['user_id']): ?>
                            <div class="message-sender"><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></div>
                        <?php endif; ?>
                        <div class="message-bubble">
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            <?php if ($msg['attachment_url']): ?>
                                <a href="/<?php echo htmlspecialchars($msg['attachment_url']); ?>" target="_blank" class="message-attachment">
                                    <i class="fas fa-paperclip"></i> Archivo adjunto
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="message-time"><?php echo date('d/m/Y H:i', strtotime($msg['sent_at'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-chat">
                <i class="fas fa-comment-dots"></i>
                <p>No hay mensajes aún. ¡Sé el primero en escribir!</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="message-input-area">
        <form method="POST" action="index.php?page=messages&action=send" id="messageForm">
            <input type="hidden" name="conversation_id" value="<?php echo $_GET['id']; ?>">
            <div class="input-group">
                <textarea name="message" id="messageInput" class="form-control" placeholder="Escribe tu mensaje..." rows="2" required></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.chat-container {
    max-width: 1000px;
    margin: 0 auto;
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 300px);
    min-height: 500px;
}

.messages-area {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message {
    display: flex;
    gap: 0.75rem;
    max-width: 70%;
}

.message-own {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-other {
    margin-right: auto;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-600);
    font-size: 1.25rem;
    flex-shrink: 0;
    overflow: hidden;
}

.message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.message-content {
    flex: 1;
    min-width: 0;
}

.message-sender {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.message-bubble {
    background: var(--bg-secondary);
    padding: 0.875rem 1.125rem;
    border-radius: var(--radius-lg);
    word-wrap: break-word;
}

.message-own .message-bubble {
    background: var(--primary-500);
    color: white;
}

.message-bubble p {
    margin: 0;
    line-height: 1.5;
}

.message-time {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.message-own .message-time {
    text-align: right;
}

.message-attachment {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: inherit;
    font-size: 0.875rem;
}

.message-attachment:hover {
    background: rgba(255, 255, 255, 0.2);
}

.empty-chat {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-chat i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.message-input-area {
    padding: 1rem;
    border-top: 2px solid var(--border-light);
    background: var(--bg-primary);
}

.input-group {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}

.input-group textarea {
    flex: 1;
    resize: none;
    border-radius: var(--radius-md);
}

.input-group .btn {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 250px);
    }
    
    .message {
        max-width: 85%;
    }
    
    .messages-area {
        padding: 1rem;
    }
}

[data-theme="dark"] .message-bubble {
    background: var(--dark-secondary);
}

[data-theme="dark"] .message-input-area {
    background: var(--dark-bg);
    border-top-color: var(--dark-border);
}
</style>

<script>
// Auto-scroll to bottom
const messagesArea = document.getElementById('messagesArea');
messagesArea.scrollTop = messagesArea.scrollHeight;

// Auto-resize textarea
const messageInput = document.getElementById('messageInput');
messageInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Submit with Enter (Shift+Enter for new line)
messageInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('messageForm').submit();
    }
});

// Polling for new messages every 3 seconds
<?php if (!empty($messages)): ?>
let lastMessageId = <?php echo end($messages)['id']; ?>;
const conversationId = <?php echo $_GET['id']; ?>;

setInterval(function() {
    fetch(`index.php?page=messages&action=poll&conversation_id=${conversationId}&last_message_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    appendMessage(msg);
                    lastMessageId = msg.id;
                });
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        })
        .catch(error => console.error('Polling error:', error));
}, 3000);

function appendMessage(msg) {
    const isOwn = msg.sender_id == <?php echo $_SESSION['user_id']; ?>;
    const messageHtml = `
        <div class="message ${isOwn ? 'message-own' : 'message-other'}">
            ${!isOwn ? `
                <div class="message-avatar">
                    ${msg.photo_url ? `<img src="/${msg.photo_url}" alt="Avatar">` : '<i class="fas fa-user"></i>'}
                </div>
            ` : ''}
            <div class="message-content">
                ${!isOwn ? `<div class="message-sender">${msg.first_name} ${msg.last_name}</div>` : ''}
                <div class="message-bubble">
                    <p>${msg.message.replace(/\n/g, '<br>')}</p>
                </div>
                <div class="message-time">${new Date(msg.sent_at).toLocaleString('es-ES')}</div>
            </div>
        </div>
    `;
    messagesArea.insertAdjacentHTML('beforeend', messageHtml);
}
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
