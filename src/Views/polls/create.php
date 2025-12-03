<?php 
ob_start();
$title = 'Nueva Votación'; 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i> Nueva Votación
        </h1>
        <p class="page-subtitle">Crear una nueva votación o encuesta</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=polls" class="btn btn-secondary">
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
    <div class="card-body">
        <form method="POST" action="index.php?page=polls&action=store" id="pollForm">
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="title">Título de la Votación *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="start_date">Fecha de Inicio *</label>
                    <input type="datetime-local" id="start_date" name="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha de Fin *</label>
                    <input type="datetime-local" id="end_date" name="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_anonymous" value="1" checked>
                        <span>Votación Anónima</span>
                    </label>
                    <small class="form-text">Los votos no mostrarán quién votó</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_multiple" value="1">
                        <span>Permitir Múltiples Opciones</span>
                    </label>
                    <small class="form-text">Los usuarios pueden seleccionar varias opciones</small>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Opciones de Votación</h3>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">
                        <i class="fas fa-plus"></i> Agregar Opción
                    </button>
                </div>

                <div id="optionsContainer">
                    <div class="option-item">
                        <input type="text" name="options[]" class="form-control" placeholder="Opción 1" required>
                        <button type="button" class="btn-remove" onclick="removeOption(this)" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="option-item">
                        <input type="text" name="options[]" class="form-control" placeholder="Opción 2" required>
                        <button type="button" class="btn-remove" onclick="removeOption(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Crear Votación
                </button>
                <a href="index.php?page=polls" class="btn btn-secondary">
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

.form-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--border-light);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

#optionsContainer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.option-item {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.option-item .form-control {
    flex: 1;
}

.btn-remove {
    background: var(--danger-500);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-remove:hover {
    background: var(--danger-600);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

[data-theme="dark"] .form-section {
    border-top-color: var(--dark-border);
}
</style>

<script>
let optionCount = 2;

function addOption() {
    optionCount++;
    const container = document.getElementById('optionsContainer');
    const newOption = document.createElement('div');
    newOption.className = 'option-item';
    newOption.innerHTML = `
        <input type="text" name="options[]" class="form-control" placeholder="Opción ${optionCount}" required>
        <button type="button" class="btn-remove" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newOption);
}

function removeOption(button) {
    const container = document.getElementById('optionsContainer');
    if (container.children.length > 2) {
        button.parentElement.remove();
        updateOptionPlaceholders();
    } else {
        alert('Debe haber al menos 2 opciones');
    }
}

function updateOptionPlaceholders() {
    const inputs = document.querySelectorAll('#optionsContainer input[name="options[]"]');
    inputs.forEach((input, index) => {
        input.placeholder = `Opción ${index + 1}`;
    });
    optionCount = inputs.length;
}

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const tomorrow = new Date(now.getTime() + 24 * 60 * 60 * 1000);
    const nextWeek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
    
    document.getElementById('start_date').value = tomorrow.toISOString().slice(0, 16);
    document.getElementById('end_date').value = nextWeek.toISOString().slice(0, 16);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
