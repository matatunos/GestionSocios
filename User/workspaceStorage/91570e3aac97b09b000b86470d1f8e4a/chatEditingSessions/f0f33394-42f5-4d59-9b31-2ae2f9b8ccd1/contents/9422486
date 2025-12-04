<?php ob_start(); ?>
<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-plus"></i> Nueva Subvención</h1>
        <a href="index.php?page=grants" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form method="POST" action="index.php?page=grants&action=store" class="form-card">
        <div class="form-section">
            <h3>Información Básica</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Título <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group full-width">
                    <label>Descripción</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Organismo Convocante <span class="required">*</span></label>
                    <input type="text" name="organization" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Código de Referencia</label>
                    <input type="text" name="reference_code" class="form-control">
                </div>
                <div class="form-group">
                    <label>Tipo <span class="required">*</span></label>
                    <select name="grant_type" class="form-control" required>
                        <option value="estatal">Estatal</option>
                        <option value="autonomica">Autonómica</option>
                        <option value="provincial">Provincial</option>
                        <option value="local">Local</option>
                        <option value="europea">Europea</option>
                        <option value="privada">Privada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <input type="text" name="category" class="form-control" placeholder="ej: cultura, deporte, social">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Importes y Plazos</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Importe Mínimo</label>
                    <input type="number" name="min_amount" class="form-control" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Importe Máximo</label>
                    <input type="number" name="max_amount" class="form-control" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Presupuesto Total</label>
                    <input type="number" name="total_budget" class="form-control" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Fecha Publicación</label>
                    <input type="date" name="announcement_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Fecha Apertura</label>
                    <input type="date" name="open_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Deadline <span class="required">*</span></label>
                    <input type="date" name="deadline" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Localización</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Provincia</label>
                    <input type="text" name="province" class="form-control" value="<?php echo htmlspecialchars($locationSettings['province'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Municipio</label>
                    <input type="text" name="municipality" class="form-control" value="<?php echo htmlspecialchars($locationSettings['municipality'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Subvención
            </button>
            <a href="index.php?page=grants" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<style>
.form-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.form-section { margin-bottom: 2rem; }
.form-section h3 { margin-bottom: 1rem; font-size: 1.125rem; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
.form-group.full-width { grid-column: 1 / -1; }
.form-control { width: 100%; padding: 0.5rem; border: 1px solid #dee2e6; border-radius: 4px; }
.form-actions { display: flex; gap: 1rem; margin-top: 2rem; }
.required { color: #dc3545; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
