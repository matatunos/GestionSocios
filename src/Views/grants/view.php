<?php ob_start(); ?>
<div class="main-content">
    <div class="content-header">
        <h1><?php echo htmlspecialchars($grant['title']); ?></h1>
        <div class="header-actions">
            <a href="index.php?page=grants" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php?page=grants&action=edit&id=<?php echo $grant['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="index.php?page=grants&action=createApplication&grant_id=<?php echo $grant['id']; ?>" class="btn btn-success">
                <i class="fas fa-file-alt"></i> Solicitar
            </a>
        </div>
    </div>

    <div class="view-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div class="view-main">
            <div class="detail-card">
                <h3>Información de la Convocatoria</h3>
                <dl class="detail-list">
                    <dt>Organismo</dt>
                    <dd><?php echo htmlspecialchars($grant['organization']); ?></dd>
                    <dt>Tipo</dt>
                    <dd><span class="badge badge-secondary"><?php echo ucfirst($grant['grant_type']); ?></span></dd>
                    <dt>Categoría</dt>
                    <dd><?php echo htmlspecialchars($grant['category'] ?? '-'); ?></dd>
                    <dt>Descripción</dt>
                    <dd><?php echo nl2br(htmlspecialchars($grant['description'] ?? '')); ?></dd>
                    <dt>Importes</dt>
                    <dd>
                        <?php if ($grant['min_amount'] || $grant['max_amount']): ?>
                            <?php if ($grant['min_amount']): ?>Mín: <?php echo number_format($grant['min_amount'], 2); ?>€<?php endif; ?>
                            <?php if ($grant['max_amount']): ?> Máx: <strong><?php echo number_format($grant['max_amount'], 2); ?>€</strong><?php endif; ?>
                        <?php else: ?>-<?php endif; ?>
                    </dd>
                    <dt>Plazos</dt>
                    <dd>
                        <?php if ($grant['open_date']): ?>Apertura: <?php echo date('d/m/Y', strtotime($grant['open_date'])); ?><br><?php endif; ?>
                        Deadline: <strong><?php echo date('d/m/Y', strtotime($grant['deadline'])); ?></strong>
                    </dd>
                </dl>
            </div>

            <?php if (!empty($applications)): ?>
                <div class="detail-card">
                    <h3>Solicitudes (<?php echo count($applications); ?>)</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Importe Solicitado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($app['application_date'])); ?></td>
                                    <td><?php echo number_format($app['requested_amount'], 2); ?>€</td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($app['status']); ?></span></td>
                                    <td>
                                        <a href="index.php?page=grants&action=viewApplication&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="view-sidebar">
            <div class="detail-card">
                <h3>Estado</h3>
                <div style="margin-bottom: 1rem;">
                    <div style="font-size: 0.875rem; color: #6c757d; margin-bottom: 0.25rem;">Convocatoria</div>
                    <span class="badge badge-<?php echo $grant['status'] === 'abierta' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($grant['status']); ?>
                    </span>
                </div>
                <div>
                    <div style="font-size: 0.875rem; color: #6c757d; margin-bottom: 0.25rem;">Nuestro Estado</div>
                    <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $grant['our_status'])); ?></span>
                </div>
            </div>

            <div class="detail-card">
                <h3>Relevancia</h3>
                <?php 
                $score = $grant['relevance_score'];
                $color = $score >= 70 ? '#28a745' : ($score >= 50 ? '#ffc107' : '#6c757d');
                ?>
                <div style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: bold; color: <?php echo $color; ?>;">
                        <?php echo $score; ?>
                    </div>
                    <div style="font-size: 0.875rem; color: #6c757d;">Puntuación de relevancia</div>
                </div>
            </div>

            <?php if ($grant['url'] || $grant['official_document']): ?>
                <div class="detail-card">
                    <h3>Enlaces</h3>
                    <?php if ($grant['url']): ?>
                        <a href="<?php echo htmlspecialchars($grant['url']); ?>" target="_blank" class="btn btn-sm btn-secondary" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-external-link-alt"></i> Ver Convocatoria
                        </a>
                    <?php endif; ?>
                    <?php if ($grant['official_document']): ?>
                        <a href="<?php echo htmlspecialchars($grant['official_document']); ?>" target="_blank" class="btn btn-sm btn-secondary" style="width: 100%;">
                            <i class="fas fa-file-pdf"></i> Documento Oficial
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.detail-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.detail-card h3 { margin-bottom: 1rem; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; }
.detail-list { display: grid; grid-template-columns: 150px 1fr; gap: 0.75rem; margin: 0; }
.detail-list dt { font-weight: 600; color: #6c757d; font-size: 0.875rem; }
.detail-list dd { margin: 0; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
