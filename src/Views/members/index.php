</div>
<script src="/js/members_cert_menu.js?v=1"></script>

<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'Socio eliminado correctamente.';
        ?>
    </div>
    <link rel="stylesheet" href="/css/listings.css?v=1">
<?php endif; ?>

<div class="flex justify-between items-center mb-4">
    <h1>Listado de Socios</h1>
    <form method="get" style="margin-bottom:0;">
        <label for="event_id">Selecciona evento:</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()">
            <option value="">-- Elige --</option>
            <?php foreach ($events as $ev): ?>
            <option value="<?= $ev['id'] ?>" <?= ($eventId == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['name']) ?> (<?= $ev['date'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>QR Vale Evento</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay socios activos.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= $member['id'] ?></td>
                            <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                            <td>
                                <?php if ($eventId): ?>
                                    <a href="../vouchers/show.php?event_id=<?= $eventId ?>&member_id=<?= $member['id'] ?>" target="_blank" class="btn btn-sm btn-warning">
                                        <i class="fas fa-qrcode" title="Ver QR"></i>
                                    </a>
                                <?php else: ?>
                                    <em>Selecciona evento</em>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="listing-actions">
                                    <?php if (!empty($member['logo_url'])): ?>
                                        <a href="/<?= htmlspecialchars($member['logo_url']) ?>" target="_blank" class="btn btn-sm btn-warning" title="Ver Logo">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($member['latitude']) && !empty($member['longitude'])): ?>
                                        <a href="index.php?page=map#member-<?= $member['id'] ?>" class="btn btn-sm btn-warning" title="Ver en mapa">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <div style="display: inline-block; position: relative;">
                                        <button onclick="toggleMemberCertMenu(<?= $member['id'] ?>)" class="btn btn-sm btn-info" style="background: #8b5cf6; border-color: #8b5cf6;">
                                            <i class="fas fa-certificate" title="Certificados"></i> <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                                        </button>
                                        <div id="memberCertMenu<?= $member['id'] ?>" class="dropdown-menu" style="display: none; position: absolute; right: 0; background: white; border: 1px solid var(--border-light); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1000; min-width: 220px; margin-top: 0.25rem;">
                                            <a href="index.php?page=certificates&action=membership&id=<?= $member['id'] ?>&year=<?= date('Y') ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                                <i class="fas fa-id-card" style="width: 20px;"></i> Certificado Socio <?= date('Y') ?>
                                            </a>
                                            <a href="index.php?page=certificates&action=membership&id=<?= $member['id'] ?>&year=<?= date('Y') - 1 ?>" target="_blank" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--text-base); text-decoration: none; transition: background 0.2s;">
                                                <i class="fas fa-id-card" style="width: 20px;"></i> Certificado Socio <?= date('Y') - 1 ?>
                                            </a>
                                        </div>
                                    </div>
                                    <a href="index.php?page=members&action=edit&id=<?= $member['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit" title="Editar"></i>
                                    </a>
                                    <a href="index.php?page=members&action=delete&id=<?= $member['id'] ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Seguro que quieres eliminar este socio?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
    <div style="font-size: 0.875rem; color: var(--text-muted);">
        Mostrando <?php echo ($offset + 1); ?> - <?php echo min($offset + $limit, $totalRecords); ?> de <?php echo $totalRecords; ?> registros
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <?php if ($page > 1): ?>
            <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $page - 1])); ?>" class="btn btn-sm btn-secondary">
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
                <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $i])); ?>" 
                   class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="<?php echo $i === $page ? '' : 'background: white;'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; 
            if ($endPage < $totalPages) {
                echo '<span style="padding: 0.25rem 0.5rem;">...</span>';
            }
            ?>
        </div>
        <?php if ($page < $totalPages): ?>
            <a href="index.php?<?php echo http_build_query(array_merge($_GET, ['p' => $page + 1])); ?>" class="btn btn-sm btn-secondary">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
.dropdown-item:hover {
    background: var(--primary-50) !important;
    color: var(--primary-700) !important;
}
</style>

<script src="/js/members_cert_menu.js?v=1"></script>

<?php 
$content = ob_get_clean(); 
