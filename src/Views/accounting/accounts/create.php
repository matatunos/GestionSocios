<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-plus"></i> Nueva Cuenta Contable</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=accounts" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=accounting&action=storeAccount" class="form-horizontal">
            <div class="form-grid">
                <div class="form-group">
                    <label for="code">Código <span class="required">*</span></label>
                    <input type="text" name="code" id="code" class="form-control" required>
                    <small class="form-text">Ejemplo: 100, 570, 700</small>
                </div>

                <div class="form-group">
                    <label for="name">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="form-group full-width">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="account_type">Tipo de Cuenta <span class="required">*</span></label>
                    <select name="account_type" id="account_type" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="asset">Activo</option>
                        <option value="liability">Pasivo</option>
                        <option value="equity">Patrimonio</option>
                        <option value="income">Ingresos</option>
                        <option value="expense">Gastos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="balance_type">Tipo de Saldo <span class="required">*</span></label>
                    <select name="balance_type" id="balance_type" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="debit">Deudor</option>
                        <option value="credit">Acreedor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="parent_id">Cuenta Padre</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="">Ninguna (cuenta de nivel 0)</option>
                        <?php foreach ($parentAccounts as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>">
                                <?php echo htmlspecialchars($parent['code'] . ' - ' . $parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="level">Nivel</label>
                    <input type="number" name="level" id="level" class="form-control" value="0" min="0" max="5">
                    <small class="form-text">0 para cuentas principales, mayor para subcuentas</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <span>Cuenta Activa</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="index.php?page=accounting&action=accounts" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
