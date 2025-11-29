<?php
require_once __DIR__ . '/../../Models/OrganizationSettings.php';
$database = new Database();
$db = $database->getConnection();
$settings = new OrganizationSettings($db);
$policy = $settings->getPasswordPolicy();
?>

<div class="card">
    <h2 class="text-lg font-semibold mb-4">
        <i class="fas fa-shield-alt"></i> Política de Contraseñas y Seguridad
    </h2>
    
    <form method="POST" action="index.php?page=settings&action=updatePasswordPolicy">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--primary-600); font-size: 1.1rem;">
            <i class="fas fa-key"></i> Requisitos de Contraseña
        </h3>
        
        <div class="form-group">
            <label class="form-label">Longitud Mínima</label>
            <input type="number" name="min_length" min="6" max="32" value="<?= $policy['min_length'] ?>" class="form-control" required>
            <small class="text-muted">Número mínimo de caracteres (6-32)</small>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="require_uppercase" <?= $policy['require_uppercase'] ? 'checked' : '' ?>>
                    <span>Requiere mayúsculas (A-Z)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="require_lowercase" <?= $policy['require_lowercase'] ? 'checked' : '' ?>>
                    <span>Requiere minúsculas (a-z)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="require_numbers" <?= $policy['require_numbers'] ? 'checked' : '' ?>>
                    <span>Requiere números (0-9)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="require_special" <?= $policy['require_special'] ? 'checked' : '' ?>>
                    <span>Requiere caracteres especiales (!@#$%)</span>
                </label>
            </div>
        </div>
        
        <hr style="margin: 2rem 0; border: none; border-top: 2px solid var(--border-light);">
        
        <h3 style="margin-bottom: 1.5rem; color: var(--primary-600); font-size: 1.1rem;">
            <i class="fas fa-lock"></i> Bloqueo de Cuenta
        </h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Máximo de Intentos Fallidos</label>
                <input type="number" name="max_attempts" min="3" max="10" value="<?= $policy['login_max_attempts'] ?>" class="form-control" required>
                <small class="text-muted">Número de intentos antes de bloquear (3-10)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Duración del Bloqueo (minutos)</label>
                <input type="number" name="lockout_duration" min="5" max="120" value="<?= $policy['login_lockout_duration'] ?>" class="form-control" required>
                <small class="text-muted">Tiempo de bloqueo en minutos (5-120)</small>
            </div>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-light); border-left: 4px solid var(--primary-500); border-radius: 4px;">
            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Los cambios en la política de contraseñas se aplicarán inmediatamente. 
                Las contraseñas existentes no se verán afectadas, pero los nuevos cambios de contraseña deberán cumplir con los requisitos actualizados.
            </p>
        </div>
        
        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Política de Seguridad
            </button>
        </div>
    </form>
</div>
