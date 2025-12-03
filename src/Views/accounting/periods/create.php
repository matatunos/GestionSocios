<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-plus"></i> Nuevo Periodo Contable</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=periods" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=accounting&action=storePeriod" class="form-horizontal">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre del Periodo <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                    <small class="form-text">Ejemplo: Ejercicio 2025, Primer Trimestre 2025</small>
                </div>

                <div class="form-group">
                    <label for="fiscal_year">Año Fiscal <span class="required">*</span></label>
                    <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear - 1; $year <= $currentYear + 2; $year++) {
                            $selected = $year == $currentYear ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Fecha de Inicio <span class="required">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha de Fin <span class="required">*</span></label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="status">Estado <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="open" selected>Abierto</option>
                        <option value="closed">Cerrado</option>
                    </select>
                    <small class="form-text">Por defecto se crea abierto</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="index.php?page=accounting&action=periods" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- Help Card -->
    <div class="help-card">
        <h3><i class="fas fa-question-circle"></i> Ayuda</h3>
        <ul>
            <li><strong>Periodo Contable:</strong> Define un rango de fechas en el que se pueden registrar asientos contables.</li>
            <li><strong>Año Fiscal:</strong> Año al que corresponde el periodo, normalmente coincide con el año natural.</li>
            <li><strong>Estado Abierto:</strong> Permite crear y modificar asientos contables dentro del periodo.</li>
            <li><strong>Estado Cerrado:</strong> No permite crear nuevos asientos, se usa al finalizar el periodo contable.</li>
        </ul>
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

.help-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    border-left: 4px solid #17a2b8;
}

.help-card h3 {
    margin-top: 0;
    color: #17a2b8;
}

.help-card ul {
    margin: 0;
    padding-left: 20px;
}

.help-card li {
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-fill dates based on fiscal year
document.getElementById('fiscal_year').addEventListener('change', function() {
    const year = this.value;
    document.getElementById('start_date').value = year + '-01-01';
    document.getElementById('end_date').value = year + '-12-31';
});

// Trigger on page load for default year
document.addEventListener('DOMContentLoaded', function() {
    const year = document.getElementById('fiscal_year').value;
    if (year && !document.getElementById('start_date').value) {
        document.getElementById('start_date').value = year + '-01-01';
        document.getElementById('end_date').value = year + '-12-31';
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
