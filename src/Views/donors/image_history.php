<?php ob_start(); ?>

<style>
.history-container {
    max-width: 1400px;
    margin: 0 auto;
}

.history-header {
    margin-bottom: 2rem;
}

.history-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

@media (max-width: 768px) {
    .image-gallery {
        grid-template-columns: 1fr;
    }
}

.history-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.history-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
}

.history-card.current {
    border-color: var(--primary-color);
    border-width: 3px;
}

.current-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.image-container {
    width: 100%;
    height: 250px;
    background: var(--bg-color);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.card-content {
    padding: 1.5rem;
}

.image-date {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.date-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.date-row i {
    color: var(--primary-color);
    width: 16px;
}

.date-row .label {
    color: var(--text-muted);
    font-weight: 500;
}

.date-row .value {
    color: var(--text-color);
    margin-left: auto;
}

.replaced-text {
    color: #ef4444;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-restore {
    flex: 1;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-restore:hover {
    background: var(--primary-hover);
}

.btn-restore:disabled {
    background: var(--border-color);
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-view {
    background: var(--bg-color);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    padding: 0.75rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: var(--border-color);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
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
                        <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
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
                            <a href="<?php echo htmlspecialchars($image['image_url']); ?>" 
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
