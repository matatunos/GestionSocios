<?php
// Vista de registro de actividad con filtros y paginación
?>
<div class="card mb-4">
    <h2 class="card-title">Registro de actividad</h2>
    <form method="get" class="mb-3 flex flex-wrap gap-2">
        <input type="hidden" name="page" value="audit_log">
        <label>Administrador:
            <input type="text" name="user_id" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>" placeholder="ID o nombre">
        </label>
        <label>Tipo de actividad:
            <input type="text" name="action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>" placeholder="Acción">
        </label>
        <label>Entidad:
            <input type="text" name="entity" value="<?= htmlspecialchars($_GET['entity'] ?? '') ?>" placeholder="Entidad">
        </label>
        <label>Desde:
            <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </label>
        <label>Hasta:
            <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </label>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Entidad</th>
                    <th>ID</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No hay registros de actividad.</td></tr>
                <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <td><?= htmlspecialchars($log['username']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['entity']) ?></td>
                    <td><?= htmlspecialchars($log['entity_id']) ?></td>
                    <td><?= htmlspecialchars($log['details']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination mt-3">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=audit_log&page_num=<?= $i ?><?= isset($_GET['user_id']) ? '&user_id=' . urlencode($_GET['user_id']) : '' ?><?= isset($_GET['action']) ? '&action=' . urlencode($_GET['action']) : '' ?><?= isset($_GET['entity']) ? '&entity=' . urlencode($_GET['entity']) : '' ?><?= isset($_GET['date_from']) ? '&date_from=' . urlencode($_GET['date_from']) : '' ?><?= isset($_GET['date_to']) ? '&date_to=' . urlencode($_GET['date_to']) : '' ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <div class="btn-group mt-2">
        <a href="index.php?page=audit_log&action=export_excel" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Exportar Excel</a>
        <a href="index.php?page=audit_log&action=export_pdf" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Exportar PDF</a>
    </div>
</div>
