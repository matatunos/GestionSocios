<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Donaciones del Año <?= htmlspecialchars($year) ?></h1>
    <a href="index.php?page=donations&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Añadir Donación
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>Importe (€)</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donations)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay donaciones registradas para este año.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donations as $d): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($d['member_name'] ?? $d['member_id']) ?></td>
                            <td><?= number_format($d['amount'], 2) ?> €</td>
                            <td>
                                <span class="badge badge-active">
                                    <?= htmlspecialchars(ucfirst($d['type'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($d['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
