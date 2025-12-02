<?php
require_once __DIR__ . '/../../layout.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-plus"></i> Nuevo Asiento Contable</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=entries" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="index.php?page=accounting&action=storeEntry" id="entryForm">
            <div class="form-section">
                <h3>Información del Asiento</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="entry_date">Fecha <span class="required">*</span></label>
                        <input type="date" name="entry_date" id="entry_date" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="period_id">Período <span class="required">*</span></label>
                        <select name="period_id" id="period_id" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>">
                                    <?php echo htmlspecialchars($period['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Descripción <span class="required">*</span></label>
                        <input type="text" name="description" id="description" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="reference">Referencia</label>
                        <input type="text" name="reference" id="reference" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Líneas del Asiento</h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addLine()">
                        <i class="fas fa-plus"></i> Añadir Línea
                    </button>
                </div>

                <div id="linesContainer">
                    <!-- Lines will be added here -->
                </div>

                <div class="totals-section">
                    <div class="total-item">
                        <strong>Total Débito:</strong>
                        <span id="totalDebit">0.00 €</span>
                    </div>
                    <div class="total-item">
                        <strong>Total Crédito:</strong>
                        <span id="totalCredit">0.00 €</span>
                    </div>
                    <div class="total-item">
                        <strong>Diferencia:</strong>
                        <span id="difference">0.00 €</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Guardar Asiento
                </button>
                <a href="index.php?page=accounting&action=entries" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let lineCounter = 0;
const accounts = <?php echo json_encode($accounts); ?>;

function addLine() {
    lineCounter++;
    const container = document.getElementById('linesContainer');
    const lineDiv = document.createElement('div');
    lineDiv.className = 'entry-line';
    lineDiv.id = 'line-' + lineCounter;
    
    lineDiv.innerHTML = `
        <div class="line-grid">
            <div class="form-group">
                <label>Cuenta</label>
                <select name="account_id[]" class="form-control" required onchange="updateTotals()">
                    <option value="">Seleccione...</option>
                    ${accounts.map(acc => `<option value="${acc.id}">${acc.code} - ${acc.name}</option>`).join('')}
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="line_description[]" class="form-control">
            </div>
            <div class="form-group">
                <label>Débito</label>
                <input type="number" name="debit[]" class="form-control" step="0.01" min="0" value="0" 
                       oninput="updateTotals()" onchange="updateTotals()">
            </div>
            <div class="form-group">
                <label>Crédito</label>
                <input type="number" name="credit[]" class="form-control" step="0.01" min="0" value="0" 
                       oninput="updateTotals()" onchange="updateTotals()">
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeLine(${lineCounter})" 
                        style="margin-top: 1.5rem;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(lineDiv);
    updateTotals();
}

function removeLine(lineId) {
    const line = document.getElementById('line-' + lineId);
    if (line) {
        line.remove();
        updateTotals();
    }
}

function updateTotals() {
    const debits = document.querySelectorAll('input[name="debit[]"]');
    const credits = document.querySelectorAll('input[name="credit[]"]');
    
    let totalDebit = 0;
    let totalCredit = 0;
    
    debits.forEach(input => {
        totalDebit += parseFloat(input.value) || 0;
    });
    
    credits.forEach(input => {
        totalCredit += parseFloat(input.value) || 0;
    });
    
    const difference = Math.abs(totalDebit - totalCredit);
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2) + ' €';
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2) + ' €';
    document.getElementById('difference').textContent = difference.toFixed(2) + ' €';
    
    // Enable/disable submit button based on balance
    const submitBtn = document.getElementById('submitBtn');
    if (difference < 0.01 && totalDebit > 0 && totalCredit > 0) {
        submitBtn.disabled = false;
        document.getElementById('difference').style.color = 'green';
    } else {
        submitBtn.disabled = true;
        document.getElementById('difference').style.color = 'red';
    }
}

// Add initial lines
addLine();
addLine();
</script>

<style>
.form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--card-bg);
    border-radius: 8px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.entry-line {
    margin-bottom: 1rem;
    padding: 1rem;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.line-grid {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.totals-section {
    display: flex;
    justify-content: flex-end;
    gap: 2rem;
    margin-top: 1rem;
    padding: 1rem;
    background: var(--card-bg);
    border-radius: 4px;
    font-size: 1.1rem;
}

.total-item {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

@media (max-width: 768px) {
    .line-grid {
        grid-template-columns: 1fr;
    }
    
    .totals-section {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
