<?php ob_start(); ?>
<div class="page-content">
<?php
// Inicializar variables de paginación si no existen
if (!isset($page)) $page = 1;
if (!isset($totalPages)) $totalPages = 1;
if (!isset($totalRecords)) $totalRecords = isset($expenses) ? count($expenses) : 0;
if (!isset($filters)) $filters = [ 'year' => '', 'month' => '', 'category_id' => '' ];
?>
<?php
// Genera la URL de paginación manteniendo los filtros
function buildPageUrl($page, $filters) {
    $params = [
        'page' => 'expenses',
        'year' => $filters['year'],
        'month' => $filters['month'],
        'category_id' => $filters['category_id'],
        'page_num' => $page
    ];
    // Elimina filtros vacíos
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) unset($params[$k]);
    }
    return 'index.php?' . http_build_query($params);
}
?>

<style>
.expense-filters {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: end;
}

.expense-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border-left: 4px solid;
}

.expense-item {
    background: white;
    padding: 1rem;
    border-radius: var(--radius-md);
    margin-bottom: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}

.expense-item:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
            <i class="fas fa-receipt"></i> Gestión de Gastos
        </h1>
        <p style="color: var(--text-muted);">Control de gastos y egresos de la asociación</p>
    </div>
    <div class="btn-group">
        <a href="index.php?page=export&action=expenses_excel" class="btn btn-secondary">
            <i class="fas fa-download"></i> Exportar Excel
        </a>
        <a href="index.php?page=expenses&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Gasto
        </a>
    </div>
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

<!-- Filters -->
<form method="GET" class="expense-filters">
    <input type="hidden" name="page" value="expenses">
    
    <div class="form-group" style="margin: 0; min-width: 120px;">
        <label class="form-label" style="font-size: 0.875rem;">Año</label>
        <select name="year" class="form-control">
            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo ($filters['year'] == $y) ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    
    <div class="form-group" style="margin: 0; min-width: 150px;">
        <label class="form-label" style="font-size: 0.875rem;">Mes</label>
        <select name="month" class="form-control">
            <option value="">Todos los meses</option>
            <?php
            $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            foreach ($months as $idx => $month):
                $monthNum = $idx + 1;
            ?>
                <option value="<?php echo $monthNum; ?>" <?php echo ($filters['month'] == $monthNum) ? 'selected' : ''; ?>>
                    <?php echo $month; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group" style="margin: 0; min-width: 200px;">
        <label class="form-label" style="font-size: 0.875rem;">Categoría</label>
        <select name="category_id" class="form-control">
            <option value="">Todas las categorías</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($filters['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary" style="margin-top: auto;">
        <i class="fas fa-filter"></i> Filtrar
    </button>
</form>

<!-- Statistics -->
<div class="expense-stats">
    <div class="stat-card" style="border-left-color: #ef4444;">
        <div style="font-size: 2rem; font-weight: 700; color: #ef4444;">
            <?php echo number_format($yearTotal, 2); ?> €
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
            Total <?php echo $filters['year']; ?>
        </div>
    </div>
    
        <?php foreach ($expenses as $expense): ?>
            <div class="expense-item">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <span class="category-badge" style="background: <?php echo htmlspecialchars($expense['category_color']); ?>">
                            <?php echo htmlspecialchars($expense['category_name']); ?>
                        </span>
                        <strong><?php echo htmlspecialchars($expense['description']); ?></strong>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">
                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?>
                        <?php if ($expense['provider']): ?>
                            | <i class="fas fa-building"></i> <?php echo htmlspecialchars($expense['provider']); ?>
                        <?php endif; ?>
                        <?php if ($expense['invoice_number']): ?>
                            | <i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($expense['invoice_number']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align: right; display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">
                        <?php echo number_format($expense['amount'], 2); ?> €
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="index.php?page=expenses&action=edit&id=<?php echo $expense['id']; ?>" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="index.php?page=expenses&action=delete&id=<?php echo $expense['id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este gasto?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0 1rem 0; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.95rem; color: var(--text-muted);">
                Mostrando <?php echo (($page - 1) * 20 + 1); ?> - <?php echo min($page * 20, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="<?php echo buildPageUrl($page - 1, $filters); ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                <div style="display: flex; gap: 0.25rem;">
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    if ($startPage > 1) {
                        echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                    }
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo buildPageUrl($i, $filters); ?>" 
                           class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                           style="<?php echo $i == $page ? '' : 'background: white;'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    if ($endPage < $totalPages) {
                        echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                    }
                    ?>
                </div>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo buildPageUrl($page + 1, $filters); ?>" class="btn btn-sm btn-secondary">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0 1rem 0; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.95rem; color: var(--text-muted);">
                Mostrando <?php echo (($page - 1) * 20 + 1); ?> - <?php echo min($page * 20, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="<?php echo buildPageUrl($page - 1, $filters); ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                <div style="display: flex; gap: 0.25rem;">
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    if ($startPage > 1) {
                        echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                    }
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo buildPageUrl($i, $filters); ?>" 
                           class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                           style="<?php echo $i == $page ? '' : 'background: white;'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    if ($endPage < $totalPages) {
                        echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                    }
                    ?>
                </div>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo buildPageUrl($page + 1, $filters); ?>" class="btn btn-sm btn-secondary">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo buildPageUrl($page - 1, $filters); ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                <?php endif; ?>
                                <div style="display: flex; gap: 0.25rem;">
                                    <div class="expense-item">
                                        <div style="flex: 1;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                                <span class="category-badge" style="background: <?php echo htmlspecialchars($expense['category_color']); ?>">
                                                    <?php echo htmlspecialchars($expense['category_name']); ?>
                                                </span>
                                                <strong><?php echo htmlspecialchars($expense['description']); ?></strong>
                                            </div>
                                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?>
                                                <?php if ($expense['provider']): ?>
                                                    | <i class="fas fa-building"></i> <?php echo htmlspecialchars($expense['provider']); ?>
                                                <?php endif; ?>
                                                <?php if ($expense['invoice_number']): ?>
                                                    | <i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($expense['invoice_number']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; display: flex; align-items: center; gap: 1rem;">
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">
                                                <?php echo number_format($expense['amount'], 2); ?> €
                                            </div>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="index.php?page=expenses&action=edit&id=<?php echo $expense['id']; ?>" 
                                                   class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=expenses&action=delete&id=<?php echo $expense['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('¿Eliminar este gasto?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>


                                <!-- Bloque de paginación fuera del foreach -->
                                <?php if (isset($totalPages) && $totalPages > 1): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0 1rem 0; flex-wrap: wrap; gap: 1rem;">
                                    <div style="font-size: 0.95rem; color: var(--text-muted);">
                                        Mostrando <?php echo (($page - 1) * 20 + 1); ?> - <?php echo min($page * 20, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php if ($page > 1): ?>
                                            <a href="<?php echo buildPageUrl($page - 1, $filters); ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-chevron-left"></i> Anterior
                                            </a>
                                        <?php endif; ?>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <?php 
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $page + 2);
                                            if ($startPage > 1) {
                                                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                                            }
                                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <a href="<?php echo buildPageUrl($i, $filters); ?>" 
                                                   class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                                                   style="<?php echo $i == $page ? '' : 'background: white;'; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            <?php endfor; 
                                            if ($endPage < $totalPages) {
                                                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($page < $totalPages): ?>
                                            <a href="<?php echo buildPageUrl($page + 1, $filters); ?>" class="btn btn-sm btn-secondary">
                                                Siguiente <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($totalPages) && $totalPages > 1): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0 1rem 0; flex-wrap: wrap; gap: 1rem;">
                                    <div style="font-size: 0.95rem; color: var(--text-muted);">
                                        Mostrando <?php echo (($page - 1) * 20 + 1); ?> - <?php echo min($page * 20, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php if ($page > 1): ?>
                                            <a href="<?php echo buildPageUrl($page - 1, $filters); ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-chevron-left"></i> Anterior
                                            </a>
                                        <?php endif; ?>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <?php 
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $page + 2);
                                            if ($startPage > 1) {
                                                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                                            }
                                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <a href="<?php echo buildPageUrl($i, $filters); ?>" 
                                                   class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                                                   style="<?php echo $i == $page ? '' : 'background: white;'; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            <?php endfor; 
                                            if ($endPage < $totalPages) {
                                                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($page < $totalPages): ?>
                                            <a href="<?php echo buildPageUrl($page + 1, $filters); ?>" class="btn btn-sm btn-secondary">
                                                Siguiente <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
