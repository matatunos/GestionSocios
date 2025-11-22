<?php
// Evitar acceso directo
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
}

$title = 'Nueva Notificación';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-plus"></i> Nueva Notificación
        </h1>
        <p class="page-subtitle">Enviar notificación a uno o varios socios</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=notifications" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?page=notifications&action=store">
        <div class="form-grid">
            <!-- Destinatarios -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="recipients">Destinatarios <span class="required">*</span></label>
                <div style="margin-bottom: 0.75rem;">
                    <label style="display: inline-flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="selectAll" onchange="toggleAllMembers(this)" style="margin-right: 0.5rem;">
                        <strong>Seleccionar todos los socios activos</strong>
                    </label>
                </div>
                
                <div class="members-select-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.5rem; max-height: 300px; overflow-y: auto; padding: 1rem; background: var(--bg-glass); border: 1px solid var(--border-light); border-radius: var(--radius-md);">
                    <?php foreach ($members as $member): ?>
                        <label style="display: flex; align-items: center; padding: 0.5rem; border-radius: 0.5rem; cursor: pointer; transition: var(--transition);" class="member-checkbox-label">
                            <input type="checkbox" name="recipients[]" value="<?php echo $member['id']; ?>" class="member-checkbox" style="margin-right: 0.5rem;">
                            <span style="font-size: 0.875rem;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small class="form-text">Selecciona los socios que recibirán esta notificación</small>
            </div>
            
            <!-- Tipo -->
            <div class="form-group">
                <label for="type">Tipo de Notificación <span class="required">*</span></label>
                <select name="type" id="type" class="form-select" required>
                    <option value="announcement">Anuncio</option>
                    <option value="payment_reminder">Recordatorio de pago</option>
                    <option value="event_reminder">Recordatorio de evento</option>
                    <option value="system">Sistema</option>
                    <option value="welcome">Bienvenida</option>
                </select>
            </div>
            
            <!-- Link (opcional) -->
            <div class="form-group">
                <label for="link">Enlace (opcional)</label>
                <input type="text" name="link" id="link" class="form-input" placeholder="index.php?page=...">
                <small class="form-text">URL relativa a la que dirigir al hacer clic</small>
            </div>
            
            <!-- Título -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="title">Título <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-input" required maxlength="255" placeholder="Ej: Recordatorio de pago de cuota mensual">
            </div>
            
            <!-- Mensaje -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="message">Mensaje <span class="required">*</span></label>
                <textarea name="message" id="message" class="form-input" rows="5" required placeholder="Escribe el contenido de la notificación..."></textarea>
                <small class="form-text">El mensaje que verán los destinatarios</small>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Enviar Notificación
            </button>
            <a href="index.php?page=notifications" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<style>
.member-checkbox-label:hover {
    background: var(--primary-50);
}

[data-theme="dark"] .member-checkbox-label:hover {
    background: rgba(99, 102, 241, 0.1);
}

.required {
    color: var(--danger-500);
}
</style>

<script>
function toggleAllMembers(checkbox) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// Auto-uncheck "select all" if any individual checkbox is unchecked
document.querySelectorAll('.member-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const allChecked = Array.from(document.querySelectorAll('.member-checkbox')).every(c => c.checked);
        document.getElementById('selectAll').checked = allChecked;
    });
});
</script>
