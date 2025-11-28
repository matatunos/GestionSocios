<?php
// src/Views/settings/notifications.php
$config = @include(__DIR__ . '/../../Config/notifications.php');
?>
<h2>Configuración de Notificaciones</h2>
<form method="post" action="index.php?page=settings&action=save_notifications">
    <div class="form-group">
        <label for="ntfy_topic">ntfy Topic URL</label>
        <input type="text" name="ntfy_topic" id="ntfy_topic" class="form-control" value="<?php echo htmlspecialchars($config['ntfy_topic'] ?? ''); ?>" placeholder="https://ntfy.sh/mi-topic">
    </div>
    <div class="form-group">
        <label for="telegram_token">Telegram Bot Token</label>
        <input type="text" name="telegram_token" id="telegram_token" class="form-control" value="<?php echo htmlspecialchars($config['telegram_token'] ?? ''); ?>" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
    </div>
    <div class="form-group">
        <label for="telegram_chat">Telegram Chat/Group ID</label>
        <input type="text" name="telegram_chat" id="telegram_chat" class="form-control" value="<?php echo htmlspecialchars($config['telegram_chat'] ?? ''); ?>" placeholder="-1001234567890">
    </div>
    <button type="submit" class="btn btn-primary">Guardar configuración</button>
</form>
