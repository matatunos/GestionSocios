<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-plus"></i> Nuevo Presupuesto</h1>
        <div class="header-actions">
            <a href="index.php?page=budget" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=budget&action=store" class="form-horizontal">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                    <small class="form-text">Ejemplo: Presupuesto Anual 2025</small>
                </div>

                <div class="form-group">
                    <label for="fiscal_year">Año Fiscal <span class="required">*</span></label>
                    <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear - 1; $year <= $currentYear + 3; $year++) {
                            $selected = $year == $currentYear ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="account_id">Cuenta Contable</label>
                    <select name="account_id" id="account_id" class="form-control">
                        <option value="">Seleccione una cuenta...</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>">
                                <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Opcional: vincular a una cuenta específica</small>
                </div>

                <div class="form-group">
                    <label for="amount">Monto <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" name="amount" id="amount" class="form-control" 
                               step="0.01" min="0" required>
                        <span class="input-group-text">€</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="period_type">Tipo de Periodo <span class="required">*</span></label>
                    <select name="period_type" id="period_type" class="form-control" required onchange="updatePeriodOptions()">
                        <option value="yearly">Anual</option>
                        <option value="monthly">Mensual</option>
                        <option value="quarterly">Trimestral</option>
                    </select>
                </div>

                <div class="form-group" id="period_number_group">
                    <label for="period_number">Periodo <span class="required">*</span></label>
                    <select name="period_number" id="period_number" class="form-control" required>
                        <option value="1">Todo el año</option>
                    </select>
                    <small class="form-text" id="period_help">Para presupuesto anual</small>
                </div>

                <div class="form-group">
                    <label for="status">Estado <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="draft">Borrador</option>
                        <option value="approved">Aprobado</option>
                        <option value="active">Activo</option>
                        <option value="closed">Cerrado</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="index.php?page=budget" class="btn btn-secondary">
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

.input-group {
    display: flex;
}

.input-group-text {
    padding: 0.5rem 1rem;
    background: #e9ecef;
    border: 1px solid #ced4da;
    border-left: none;
    border-radius: 0 4px 4px 0;
}

.input-group .form-control {
    border-radius: 4px 0 0 4px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function updatePeriodOptions() {
    const periodType = document.getElementById('period_type').value;
    const periodNumber = document.getElementById('period_number');
    const periodHelp = document.getElementById('period_help');
    const periodGroup = document.getElementById('period_number_group');
    
    // Clear existing options
    periodNumber.innerHTML = '';
    
    if (periodType === 'yearly') {
        periodNumber.innerHTML = '<option value="1">Todo el año</option>';
        periodHelp.textContent = 'Para presupuesto anual';
        periodGroup.style.display = 'none';
    } else if (periodType === 'monthly') {
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        months.forEach((month, index) => {
            periodNumber.innerHTML += `<option value="${index + 1}">${month}</option>`;
        });
        periodHelp.textContent = 'Seleccione el mes';
        periodGroup.style.display = 'block';
    } else if (periodType === 'quarterly') {
        for (let i = 1; i <= 4; i++) {
            periodNumber.innerHTML += `<option value="${i}">Trimestre ${i}</option>`;
        }
        periodHelp.textContent = 'Seleccione el trimestre';
        periodGroup.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePeriodOptions();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
