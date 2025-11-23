<?php ob_start(); ?>

<style>
.history-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.history-header {
    margin-bottom: 2rem;
}

.history-stats {
    display: none; /* Ocultar stats por ahora ya que están vacías */
}

.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2.5rem;
}

@media (max-width: 768px) {
    .image-gallery {
        grid-template-columns: 1fr;
    }
}

.history-card {
    background: white;
    border: 3px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.history-card:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    transform: translateY(-6px);
}

.history-card.current {
    border-color: #10b981;
    border-width: 4px;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.current-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.current-badge i {
    font-size: 1.125rem;
}

.image-container {
    width: 100%;
    height: 280px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-bottom: 2px solid #e5e7eb;
}

.image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    padding: 1rem;
}

.card-content {
    padding: 1.75rem;
}

.image-date {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.date-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
}

.date-row i {
    color: #10b981;
    width: 20px;
    font-size: 1.125rem;
}

.date-row .label {
    color: #6b7280;
    font-weight: 600;
}

.date-row .value {
    color: #1f2937;
    margin-left: auto;
    font-weight: 500;
}

.replaced-text {
    color: #ef4444;
    font-weight: 600;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-restore {
    flex: 1;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    border: none !important;
    padding: 0.875rem 1.25rem !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    font-weight: 700 !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.625rem !important;
    font-size: 1rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.btn-restore:hover {
    background: linear-gradient(135deg, #059669, #047857) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4) !important;
}

.btn-restore:disabled {
    background: #9ca3af !important;
    cursor: not-allowed !important;
    opacity: 0.6 !important;
    transform: none !important;
}

.btn-restore:disabled:hover {
    transform: none !important;
    box-shadow: none !important;
}

.btn-view {
    background: #3b82f6 !important;
    color: white !important;
    border: none !important;
    padding: 0.875rem 1.25rem !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 1.125rem !important;
    min-width: 50px !important;
}

.btn-view:hover {
    background: #2563eb !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
}

.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.3;
    color: #9ca3af;
}

.empty-state h3 {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.empty-state p {
    font-size: 1.125rem;
}

.alert {
    padding: 1.125rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 5px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 5px solid #ef4444;
}
</style>

<div class="history-container">
    <div class="flex justify-between items-center mb-4">
        <div class="history-header">
            <h1><i class="fas fa-history"></i> Histórico de Imágenes</h1>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">
                Donante: <strong><?php echo htmlspecialchars($donor->name); ?></strong>
            </p>
        </div>
        <a href="index.php?page=donors&action=edit&id=<?php echo $donor->id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'restored'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Imagen restaurada correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Error al restaurar la imagen.
        </div>
    <?php endif; ?>

    <?php if (empty($images)): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>Sin Histórico de Imágenes</h3>
                <p>Este donante aún no tiene imágenes en el histórico.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="history-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($images); ?></div>
                <div class="stat-label">Imágenes en Histórico</div>
            </div>
            <?php 
            $currentImage = array_filter($images, function($img) { return $img['is_current']; });
            ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-value"><?php echo count($currentImage); ?></div>
                <div class="stat-label">Imagen Actual</div>
            </div>
        </div>

        <div class="image-gallery">
            <?php foreach ($images as $image): ?>
                <div class="history-card <?php echo $image['is_current'] ? 'current' : ''; ?>">
                    <?php if ($image['is_current']): ?>
                        <div class="current-badge">
                            <i class="fas fa-star"></i> Actual
                        </div>
                    <?php endif; ?>
                    
                    <div class="image-container">
                        <img src="/<?php echo htmlspecialchars($image['image_url']); ?>" 
                             alt="Imagen del histórico">
                    </div>
                    
                    <div class="card-content">
                        <div class="image-date">
                            <div class="date-row">
                                <i class="fas fa-upload"></i>
                                <span class="label">Subida:</span>
                                <span class="value">
                                    <?php 
                                    $uploadDate = new DateTime($image['uploaded_at']);
                                    echo $uploadDate->format('d/m/Y H:i');
                                    ?>
                                </span>
                            </div>
                            
                            <?php if ($image['replaced_at']): ?>
                                <div class="date-row">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span class="label">Reemplazada:</span>
                                    <span class="value replaced-text">
                                        <?php 
                                        $replaceDate = new DateTime($image['replaced_at']);
                                        echo $replaceDate->format('d/m/Y H:i');
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-actions">
                            <a href="/<?php echo htmlspecialchars($image['image_url']); ?>" 
                               target="_blank" 
                               class="btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <?php if ($image['is_current']): ?>
                                <button class="btn-restore" disabled>
                                    <i class="fas fa-check"></i> En Uso
                                </button>
                            <?php else: ?>
                                <form method="POST" 
                                      action="index.php?page=donors&action=restoreImage&id=<?php echo $donor->id; ?>&historyId=<?php echo $image['id']; ?>" 
                                      style="flex: 1;"
                                      onsubmit="return confirm('¿Restaurar esta imagen como actual? La imagen actual se guardará en el histórico.');">
                                    <button type="submit" class="btn-restore" style="width: 100%;">
                                        <i class="fas fa-undo"></i> Restaurar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
