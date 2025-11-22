<?php ob_start(); ?>

<style>
.comparison-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.comparison-header {
    text-align: center;
    margin-bottom: 2rem;
}

.comparison-header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.comparison-header p {
    color: var(--text-muted);
    font-size: 1.125rem;
}

.images-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .images-comparison {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

.image-option {
    background: white;
    border: 3px solid #e5e7eb;
    border-radius: 12px;
    padding: 2rem;
    transition: all 0.3s ease;
    text-align: center;
}

.image-option:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
}

.image-label {
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: #1f2937;
}

.image-label i {
    margin-right: 0.5rem;
}

.image-preview {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e5e7eb;
}

.image-preview img {
    max-width: 100%;
    max-height: 450px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    object-fit: contain;
}

.button-group {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-choice {
    padding: 1.25rem 2rem !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    border: 3px solid !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.75rem !important;
    text-transform: uppercase !important;
}

.btn-keep-old {
    background: #6b7280 !important;
    border-color: #6b7280 !important;
    color: white !important;
}

.btn-keep-old:hover {
    background: #4b5563 !important;
    border-color: #4b5563 !important;
    transform: scale(1.05) !important;
}

.btn-use-new {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: white !important;
}

.btn-use-new:hover {
    background: #059669 !important;
    border-color: #059669 !important;
    transform: scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
}

.alert-info {
    background: #3b82f6;
    color: white;
    padding: 1.25rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border-left: 4px solid #1d4ed8;
    font-size: 1.125rem;
    font-weight: 600;
}

.alert-info i {
    margin-right: 0.75rem;
}
</style>

<div class="comparison-container">
    <div class="comparison-header">
        <h1><i class="fas fa-image"></i> Comparar Imágenes</h1>
        <p>Socio: <strong><?php echo htmlspecialchars($member->first_name . ' ' . $member->last_name); ?></strong></p>
    </div>

    <div class="alert-info">
        <i class="fas fa-info-circle"></i>
        Has subido una nueva imagen para este socio. Por favor, selecciona qué imagen deseas mantener.
    </div>

    <form method="POST" action="index.php?page=members&action=selectImage&id=<?php echo $member->id; ?>" id="comparisonForm">
        <div class="images-comparison">
            <div class="image-option" data-choice="old">
                <div class="image-label">
                    <i class="fas fa-history"></i> Imagen Actual
                </div>
                <div class="image-preview">
                    <img src="/<?php echo htmlspecialchars($_SESSION['image_comparison']['old_image']); ?>" 
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
                    <img src="/<?php echo htmlspecialchars($_SESSION['image_comparison']['new_image_temp']); ?>" 
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
        <a href="index.php?page=members" class="btn btn-secondary" onclick="return confirm('¿Cancelar la comparación?  Se descartará la nueva imagen.');">
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
