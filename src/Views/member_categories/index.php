<?php ob_start(); ?>

<style>
.categories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.category-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-md);
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.category-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.category-info h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.category-inactive {
    opacity: 0.6;
}

.category-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-light);
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-600);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}
</style>

<div class="categories-header">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
            <i class="fas fa-tag"></i> Categorías de Socios
        </h1>
        <p style="color: var(--text-muted);">Gestiona las diferentes categorías de membresía</p>
    </div>
    <a href="index.php?page=member_categories&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Categoría
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Statistics Overview -->
<div class="card mb-4">
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">
        <i class="fas fa-chart-pie"></i> Resumen por Categoría
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <?php foreach ($statistics as $stat): ?>
            <div style="text-align: center; padding: 1rem; background: var(--bg-body); border-radius: var(--radius-md); border-left: 3px solid <?php echo htmlspecialchars($stat['color']); ?>">
                <div style="font-size: 2rem; font-weight: 700; color: <?php echo htmlspecialchars($stat['color']); ?>">
                    <?php echo $stat['member_count']; ?>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                    <?php echo htmlspecialchars($stat['name']); ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-light); margin-top: 0.25rem;">
                    <?php echo $stat['active_members']; ?> activos
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Categories List -->
<div class="categories-list">
    <?php foreach ($categories as $category): ?>
        <div class="category-card <?php echo $category['is_active'] ? '' : 'category-inactive'; ?>" 
             style="border-left-color: <?php echo htmlspecialchars($category['color']); ?>">
            <div class="category-header">
                <div class="category-info">
                    <h3>
                        <?php echo htmlspecialchars($category['name']); ?>
                        <?php if (!$category['is_active']): ?>
                            <span class="category-badge" style="background: #9ca3af;">Inactiva</span>
                        <?php endif; ?>
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">
                        <?php echo htmlspecialchars($category['description'] ?: 'Sin descripción'); ?>
                    </p>
                </div>
                <div class="category-actions">
                    <a href="index.php?page=member_categories&action=edit&id=<?php echo $category['id']; ?>" 
                       class="btn btn-sm btn-secondary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="index.php?page=member_categories&action=delete&id=<?php echo $category['id']; ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
            </div>
            
            <div class="category-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($category['default_fee'], 2); ?> €</div>
                    <div class="stat-label">Cuota predeterminada</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color: <?php echo htmlspecialchars($category['color']); ?>">
                        <?php
                        $memberCount = (new MemberCategory($GLOBALS['db'] ?? (new Database())->getConnection()))->countMembers();
                        echo $memberCount;
                        ?>
                    </div>
                    <div class="stat-label">Socios asignados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color: var(--text-muted);"><?php echo $category['display_order']; ?></div>
                    <div class="stat-label">Orden de visualización</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($categories)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-tag" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
            <p style="color: var(--text-muted);">No hay categorías creadas todavía</p>
            <a href="index.php?page=member_categories&action=create" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Crear Primera Categoría
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
