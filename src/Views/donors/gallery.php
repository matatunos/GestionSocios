<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Galería de Logos de Donantes</h1>
    <a href="index.php?page=donors" class="btn btn-secondary">
        <i class="fas fa-list"></i> Ver Listado
    </a>
</div>

<div class="card">
    <?php if (empty($donors)): ?>
        <p class="text-muted text-center py-4">No hay donantes registrados.</p>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($donors as $donor): ?>
                <?php if (!empty($donor['logo_url'])): ?>
                    <div class="gallery-item">
                        <div class="gallery-image-container">
                            <img src="<?php echo htmlspecialchars($donor['logo_url']); ?>" alt="<?php echo htmlspecialchars($donor['name']); ?>">
                        </div>
                        <div class="gallery-caption">
                            <h3><?php echo htmlspecialchars($donor['name']); ?></h3>
                            <div class="gallery-actions">
                                <a href="<?php echo htmlspecialchars($donor['logo_url']); ?>" target="_blank" class="btn-icon" title="Ver Grande">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo htmlspecialchars($donor['logo_url']); ?>" download class="btn-icon" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="index.php?page=donors&action=edit&id=<?php echo $donor['id']; ?>" class="btn-icon" title="Editar Donante">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php 
        // Check if there are no logos at all
        $hasLogos = false;
        foreach ($donors as $donor) {
            if (!empty($donor['logo_url'])) {
                $hasLogos = true;
                break;
            }
        }
        if (!$hasLogos): ?>
            <p class="text-muted text-center py-4">Ningún donante tiene logo registrado.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .gallery-item {
        background: var(--bg-body);
        border: 1px solid var(--border-light);
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .gallery-item:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .gallery-image-container {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        padding: 1rem;
        border-bottom: 1px solid var(--border-light);
    }

    .gallery-image-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .gallery-caption {
        padding: 1rem;
        text-align: center;
    }

    .gallery-caption h3 {
        font-size: 1rem;
        margin: 0 0 0.5rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gallery-actions {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-icon {
        color: var(--text-muted);
        padding: 0.25rem;
        transition: color 0.2s;
    }

    .btn-icon:hover {
        color: var(--primary-600);
    }
</style>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
