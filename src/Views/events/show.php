<?php
// src/Views/events/show.php

// Expected variables: $event (array), $participants (array of ['member'=>..., 'payment'=>...])
// Optional GET parameters: filter_paid (on/off), filter_unpaid (on/off)

$filterPaid = isset($_GET['filter_paid']) ? (bool)$_GET['filter_paid'] : true;
$filterUnpaid = isset($_GET['filter_unpaid']) ? (bool)$_GET['filter_unpaid'] : true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Participantes del Evento: <?= htmlspecialchars($event['name']) ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        .paid { color: var(--primary-600); }
        .unpaid { color: var(--danger-600); }
        .filter-box { margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout.php'; ?>
<div class="container">
    <h2>Evento: <?= htmlspecialchars($event['name']) ?></h2>
    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($event['date']) ?></p>
    <div class="filter-box">
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="events">
            <input type="hidden" name="action" value="show">
            <input type="hidden" name="id" value="<?= $event['id'] ?>">
            <label><input type="checkbox" name="filter_paid" value="1" <?= $filterPaid ? 'checked' : '' ?>> Mostrar Pagados</label>
            <label><input type="checkbox" name="filter_unpaid" value="1" <?= $filterUnpaid ? 'checked' : '' ?>> Mostrar No Pagados</label>
            <button type="submit" class="btn btn-primary">Aplicar filtros</button>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Socio</th>
                <th>Estado de Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($participants as $p):
            $paid = $p['payment'] && $p['payment']['status'] === 'paid';
            if (($paid && !$filterPaid) || (!$paid && !$filterUnpaid)) continue;
        ?>
            <tr>
                <td><?= htmlspecialchars($p['member']['name']) ?></td>
                <td class="<?= $paid ? 'paid' : 'unpaid' ?>"><?= $paid ? 'Pagado' : 'Pendiente' ?></td>
                <td>
                    <?php if (!$paid): ?>
                        <a href="index.php?page=events&action=markPaid&id=<?= $event['id'] ?>&member_id=<?= $p['member']['id'] ?>" class="btn btn-success btn-sm">Marcar como Pagado</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
?>
