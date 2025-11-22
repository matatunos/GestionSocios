<?php 
ob_start();
$title = 'Nueva Conversación'; 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i> Nueva Conversación
        </h1>
        <p class="page-subtitle">Iniciar chat con otros socios</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=messages" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form method="POST" action="index.php?page=messages&action=store">
            <div class="form-group">
                <label for="recipients">Destinatarios *</label>
                <select name="recipients[]" id="recipients" class="form-control" multiple required style="min-height: 150px;">
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['id']; ?>">
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Mantén Ctrl (o Cmd en Mac) para seleccionar múltiples destinatarios</small>
            </div>

            <div class="form-group">
                <label for="subject">Asunto (opcional)</label>
                <input type="text" name="subject" id="subject" class="form-control" placeholder="Ej: Reunión junta directiva">
            </div>

            <div class="form-group">
                <label for="message">Mensaje inicial *</label>
                <textarea name="message" id="message" class="form-control" rows="6" required placeholder="Escribe tu mensaje..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar Mensaje
                </button>
                <a href="index.php?page=messages" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-text {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

select[multiple] {
    padding: 0.5rem;
}

select[multiple] option {
    padding: 0.5rem;
    border-radius: var(--radius-md);
    margin-bottom: 0.25rem;
}

select[multiple] option:hover {
    background: var(--primary-100);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
