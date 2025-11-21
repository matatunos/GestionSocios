<?php
// src/Views/donations/index.php

// Expected variables: $donations (array of donation records), $year
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Donaciones - <?= htmlspecialchars($year) ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../layout.php'; ?>
<div class="container">
    <h2>Donaciones del Año <?= htmlspecialchars($year) ?></h2>
    <a href="index.php?page=donations&action=create" class="btn btn-primary mb-3">Añadir Donación</a>
    <?php if (empty($donations)): ?>
        <p>No hay donaciones registradas para este año.</p>
    <?php else: ?>
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
                <?php foreach ($donations as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['member_name'] ?? $d['member_id']) ?></td>
                        <td><?= number_format($d['amount'], 2) ?></td>
                        <td><?= htmlspecialchars(ucfirst($d['type'])) ?></td>
                        <td><?= htmlspecialchars($d['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
?>
