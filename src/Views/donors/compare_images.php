<?php ob_start(); ?>

<style>
.comparison-container {
    max-width: 1200px;
    margin: 0 auto;
}

.comparison-header {
    text-align: center;
    margin-bottom: 2rem;
}

.comparison-header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.comparison-header p {
    color: var(--text-muted);
    font-size: 1.125rem;
}

.images-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .images-comparison {
        grid-template-columns: 1fr;
    }
}

.image-option {
    background: var(--card-bg);
    border: 3px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    text-align: center;
}

.image-option:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
}

.image-label {
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.image-label i {
    margin-right: 0.5rem;
}

.image-preview {
    background: var(--bg-color);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview img {
    max-width: 100%;
    max-height: 400px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.button-group {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-choice {
    padding: 1rem 2rem;
    font-size: 1.125rem;
    font-weight: 600;
    border-radius: 8px;
    border: 2px solid;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-keep-old {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

.btn-keep-old:hover {
    background: var(--bg-color);
    border-color: var(--text-color);
}

.btn-use-new {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.btn-use-new:hover {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.alert-info {
    background: #3b82f6;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border-left: 4px solid #1d4ed8;
    font-size: 1.125rem;
}

.alert-info i {
    margin-right: 0.5rem;
}
</style>

<div class="comparison-container">
    <div class="comparison-header">
        <h1><i class="fas fa-image"></i> Comparar Imágenes</h1>
        <p>Donante: <strong><?php echo htmlspecialchars($donor->name); ?></strong></p>
    </div>

    <div class="alert-info">
        <i class="fas fa-info-circle"></i>
        Has subido una nueva imagen para este donante. Por favor, selecciona qué imagen deseas mantener.
    </div>

    <form method="POST" action="index.php?page=donors&action=selectImage&id=<?php echo $donor->id; ?>" id="comparisonForm">
        <div class="images-comparison">
            <div class="image-option" data-choice="old">
                <div class="image-label">
                    <i class="fas fa-history"></i> Imagen Actual
                </div>
                <div class="image-preview">
                    <img src="<?php echo htmlspecialchars($_SESSION['image_comparison']['old_image']); ?>" 
                         alt="Imagen actual">
                </div>
                <button type="button" class="btn-choice btn-keep-old" onclick="selectChoice('old')" style="width: 100%; font-size: 1.25rem; padding: 1.25rem;">
                    <i class="fas fa-check-circle"></i> MANTENER ESTA IMAGEN
                </button>
            </div>

            <div class="image-option" data-choice="new">
                <div class="image-label" style="color: var(--primary-color);">
                    <i class="fas fa-sparkles"></i> Nueva Imagen
                </div>
                <div class="image-preview" style="border: 2px solid var(--primary-color);">
                    <img src="<?php echo htmlspecialchars($_SESSION['image_comparison']['new_image_temp']); ?>" 
                         alt="Nueva imagen">
                </div>
                <button type="button" class="btn-choice btn-use-new" onclick="selectChoice('new')" style="width: 100%; font-size: 1.25rem; padding: 1.25rem;">
                    <i class="fas fa-check-double"></i> USAR ESTA NUEVA IMAGEN
                </button>
            </div>
        </div>

        <input type="hidden" name="choice" id="choiceInput">
    </form>

    <div class="button-group" style="margin-top: 3rem;">
        <a href="index.php?page=donors" class="btn btn-secondary" onclick="return confirm('¿Cancelar la comparación? Se descartará la nueva imagen.');">
            <i class="fas fa-times"></i> Cancelar
        </a>
    </div>
</div>

<script>
function selectChoice(choice) {
    document.getElementById('choiceInput').value = choice;
    
    if (choice === 'old') {
        if (confirm('¿Estás seguro de que quieres mantener la imagen actual? La nueva imagen se descartará.')) {
            document.getElementById('comparisonForm').submit();
        }
    } else {
        if (confirm('¿Estás seguro de que quieres usar la nueva imagen? La imagen actual se guardará en el histórico.')) {
            document.getElementById('comparisonForm').submit();
        }
    }
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
