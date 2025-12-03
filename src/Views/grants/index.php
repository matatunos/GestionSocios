<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-hand-holding-usd"></i> Gestión de Subvenciones</h1>
        <div class="header-actions">
            <a href="index.php?page=grants&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
            <a href="index.php?page=grants&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Subvención
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
        <form method="GET" action="index.php" class="filters-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <input type="hidden" name="page" value="grants">
            <input type="hidden" name="action" value="index">
            
            <div class="form-group">
                <label>Tipo</label>
                <select name="grant_type" class="form-control">
                    <option value="">Todos</option>
                    <option value="estatal" <?php echo ($filters['grant_type'] ?? '') === 'estatal' ? 'selected' : ''; ?>>Estatal</option>
                    <option value="autonomica" <?php echo ($filters['grant_type'] ?? '') === 'autonomica' ? 'selected' : ''; ?>>Autonómica</option>
                    <option value="provincial" <?php echo ($filters['grant_type'] ?? '') === 'provincial' ? 'selected' : ''; ?>>Provincial</option>
                    <option value="local" <?php echo ($filters['grant_type'] ?? '') === 'local' ? 'selected' : ''; ?>>Local</option>
                    <option value="europea" <?php echo ($filters['grant_type'] ?? '') === 'europea' ? 'selected' : ''; ?>>Europea</option>
                    <option value="privada" <?php echo ($filters['grant_type'] ?? '') === 'privada' ? 'selected' : ''; ?>>Privada</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado Convocatoria</label>
                <select name="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="prospecto" <?php echo ($filters['status'] ?? '') === 'prospecto' ? 'selected' : ''; ?>>Prospecto</option>
                    <option value="abierta" <?php echo ($filters['status'] ?? '') === 'abierta' ? 'selected' : ''; ?>>Abierta</option>
                    <option value="cerrada" <?php echo ($filters['status'] ?? '') === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                    <option value="resuelta" <?php echo ($filters['status'] ?? '') === 'resuelta' ? 'selected' : ''; ?>>Resuelta</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nuestro Estado</label>
                <select name="our_status" class="form-control">
                    <option value="">Todos</option>
                    <option value="identificada" <?php echo ($filters['our_status'] ?? '') === 'identificada' ? 'selected' : ''; ?>>Identificada</option>
                    <option value="en_revision" <?php echo ($filters['our_status'] ?? '') === 'en_revision' ? 'selected' : ''; ?>>En Revisión</option>
                    <option value="solicitada" <?php echo ($filters['our_status'] ?? '') === 'solicitada' ? 'selected' : ''; ?>>Solicitada</option>
                    <option value="concedida" <?php echo ($filters['our_status'] ?? '') === 'concedida' ? 'selected' : ''; ?>>Concedida</option>
                    <option value="denegada" <?php echo ($filters['our_status'] ?? '') === 'denegada' ? 'selected' : ''; ?>>Denegada</option>
                </select>
            </div>

            <div class="form-group">
                <label>Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Título, organismo..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>

            <div class="form-group" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?page=grants&action=index" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de subvenciones -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <?php if (empty($grants)): ?>
                <div style="padding: 3rem; text-align: center; color: #6c757d;">
                    <i class="fas fa-folder-open" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p style="font-size: 1.125rem; font-weight: 500;">No se encontraron subvenciones</p>
                    <a href="index.php?page=grants&action=create" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Crear Primera Subvención
                    </a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Subvención</th>
                            <th>Tipo</th>
                            <th>Importe</th>
                            <th>Deadline</th>
                            <th>Estado</th>
                            <th>Relevancia</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grants as $grant): ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" style="font-weight: 600; color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($grant['title']); ?>
                                    </a>
                                    <div style="font-size: 0.875rem; color: #6c757d; margin-top: 0.25rem;">
                                        <?php echo htmlspecialchars($grant['organization']); ?>
                                    </div>
                                    <?php if ($grant['application_count'] > 0): ?>
                                        <div style="font-size: 0.75rem; color: #059669; margin-top: 0.25rem;">
                                            <i class="fas fa-file-alt"></i> <?php echo $grant['application_count']; ?> solicitud(es)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo ucfirst($grant['grant_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($grant['max_amount']): ?>
                                        <strong><?php echo number_format($grant['max_amount'], 0); ?> €</strong>
                                    <?php elseif ($grant['min_amount']): ?>
                                        Desde <?php echo number_format($grant['min_amount'], 0); ?> €
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $deadline = strtotime($grant['deadline']);
                                    $today = strtotime(date('Y-m-d'));
                                    $days = ceil(($deadline - $today) / 86400);
                                    ?>
                                    <div><?php echo date('d/m/Y', $deadline); ?></div>
                                    <?php if ($days <= 30 && $days > 0): ?>
                                        <div style="font-size: 0.75rem; color: <?php echo $days <= 7 ? '#dc3545' : '#ffc107'; ?>; font-weight: 600;">
                                            <?php echo $days; ?> días
                                        </div>
                                    <?php elseif ($days <= 0): ?>
                                        <div style="font-size: 0.75rem; color: #dc3545; font-weight: 600;">Vencida</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $grant['status'] === 'abierta' ? 'success' : 
                                            ($grant['status'] === 'cerrada' ? 'secondary' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($grant['status']); ?>
                                    </span>
                                    <div style="margin-top: 0.25rem;">
                                        <span class="badge badge-<?php 
                                            echo $grant['our_status'] === 'solicitada' ? 'info' : 
                                                ($grant['our_status'] === 'concedida' ? 'success' : 
                                                ($grant['our_status'] === 'denegada' ? 'danger' : 'secondary')); 
                                        ?>" style="font-size: 0.75rem;">
                                            <?php echo ucfirst(str_replace('_', ' ', $grant['our_status'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $score = $grant['relevance_score'];
                                    $color = $score >= 70 ? '#28a745' : ($score >= 50 ? '#ffc107' : '#6c757d');
                                    ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden;">
                                            <div style="width: <?php echo $score; ?>%; height: 100%; background: <?php echo $color; ?>;"></div>
                                        </div>
                                        <span style="font-size: 0.75rem; font-weight: 600; color: <?php echo $color; ?>;">
                                            <?php echo $score; ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" class="btn btn-sm btn-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?page=grants&action=edit&id=<?php echo $grant['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <div style="padding: 1rem; border-top: 1px solid #dee2e6; display: flex; justify-content: center;">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=grants&action=index&page_num=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>" class="btn btn-sm btn-secondary">Anterior</a>
                            <?php endif; ?>
                            <span style="margin: 0 1rem;">Página <?php echo $page; ?> de <?php echo $totalPages; ?></span>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=grants&action=index&page_num=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>" class="btn btn-sm btn-secondary">Siguiente</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.875rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.875rem;
}
.btn-primary { background: #2563eb; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
