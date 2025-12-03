<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Editar Presupuesto</h1>
        <div class="header-actions">
            <a href="index.php?page=budget" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=budget&action=update" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo $budget['id']; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="<?php echo htmlspecialchars($budget['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="fiscal_year">Año Fiscal <span class="required">*</span></label>
                    <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear - 1; $year <= $currentYear + 3; $year++) {
                            $selected = $year == $budget['fiscal_year'] ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($budget['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="account_id">Cuenta Contable</label>
                    <select name="account_id" id="account_id" class="form-control">
                        <option value="">Seleccione una cuenta...</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>" 
                                    <?php echo $account['id'] == $budget['account_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Monto <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" name="amount" id="amount" class="form-control" 
                               step="0.01" min="0" value="<?php echo $budget['amount']; ?>" required>
                        <span class="input-group-text">€</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="period_type">Tipo de Periodo <span class="required">*</span></label>
                    <select name="period_type" id="period_type" class="form-control" required onchange="updatePeriodOptions()">
                        <option value="yearly" <?php echo $budget['period_type'] === 'yearly' ? 'selected' : ''; ?>>Anual</option>
                        <option value="monthly" <?php echo $budget['period_type'] === 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="quarterly" <?php echo $budget['period_type'] === 'quarterly' ? 'selected' : ''; ?>>Trimestral</option>
                    </select>
                </div>

                <div class="form-group" id="period_number_group">
                    <label for="period_number">Periodo <span class="required">*</span></label>
                    <select name="period_number" id="period_number" class="form-control" required>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                    <small class="form-text" id="period_help"></small>
                </div>

                <div class="form-group">
                    <label for="status">Estado <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="draft" <?php echo $budget['status'] === 'draft' ? 'selected' : ''; ?>>Borrador</option>
                        <option value="approved" <?php echo $budget['status'] === 'approved' ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="active" <?php echo $budget['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="closed" <?php echo $budget['status'] === 'closed' ? 'selected' : ''; ?>>Cerrado</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
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
const initialPeriodNumber = <?php echo $budget['period_number']; ?>;

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
            const value = index + 1;
            const selected = value === initialPeriodNumber ? 'selected' : '';
            periodNumber.innerHTML += `<option value="${value}" ${selected}>${month}</option>`;
        });
        periodHelp.textContent = 'Seleccione el mes';
        periodGroup.style.display = 'block';
    } else if (periodType === 'quarterly') {
        for (let i = 1; i <= 4; i++) {
            const selected = i === initialPeriodNumber ? 'selected' : '';
            periodNumber.innerHTML += `<option value="${i}" ${selected}>Trimestre ${i}</option>`;
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
