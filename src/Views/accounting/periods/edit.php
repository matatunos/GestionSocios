<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Editar Periodo Contable</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=periods" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=accounting&action=updatePeriod" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo $period['id']; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre del Periodo <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="<?php echo htmlspecialchars($period['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="fiscal_year">Año Fiscal <span class="required">*</span></label>
                    <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear - 5; $year <= $currentYear + 2; $year++) {
                            $selected = $year == $period['fiscal_year'] ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Fecha de Inicio <span class="required">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="<?php echo $period['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha de Fin <span class="required">*</span></label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="<?php echo $period['end_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Estado <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="open" <?php echo $period['status'] === 'open' ? 'selected' : ''; ?>>Abierto</option>
                        <option value="closed" <?php echo $period['status'] === 'closed' ? 'selected' : ''; ?>>Cerrado</option>
                        <option value="locked" <?php echo $period['status'] === 'locked' ? 'selected' : ''; ?>>Bloqueado</option>
                    </select>
                </div>
            </div>

            <?php if ($period['status'] === 'closed' || $period['status'] === 'locked'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Advertencia:</strong> Este periodo está cerrado. Cambiarlo a estado "Abierto" permitirá 
                    crear nuevos asientos contables en este periodo.
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="index.php?page=accounting&action=periods" class="btn btn-secondary">
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

.alert {
    padding: 15px;
    border-radius: 8px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    margin: 20px 0;
}

.alert i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
