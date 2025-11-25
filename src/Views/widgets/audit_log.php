<?php
// Widget de actividad reciente multiusuario
require_once __DIR__ . '/../../Models/AuditLog.php';
require_once __DIR__ . '/../../Config/database.php';

$database = new Database();
$db = $database->getConnection();
$auditLog = new AuditLog($db);
$recent = $auditLog->readRecent(20);
?>
<div class="card mb-4">
    <h2 class="card-title">Actividad reciente</h2>
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
                <?php foreach ($recent as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <td><?= htmlspecialchars($log['username']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['entity']) ?></td>
                    <td><?= htmlspecialchars($log['entity_id']) ?></td>
                    <td><?= htmlspecialchars($log['details']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="btn-group mt-2">
        <a href="index.php?page=audit_log&action=export_excel" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Exportar Excel</a>
        <a href="index.php?page=audit_log&action=export_pdf" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Exportar PDF</a>
    </div>
</div>
