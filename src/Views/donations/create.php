<?php
// src/Views/donations/create.php

// Expected variables: $members (array of members)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Donación</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../layout.php'; ?>
<div class="container">
    <h2>Añadir Donación</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=donations&action=store">
        <div class="form-group">
            <label for="member_id">Socio</label>
            <select name="member_id" id="member_id" class="form-control" required>
                <?php foreach ($members as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Importe (€)</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="type">Tipo</label>
            <select name="type" id="type" class="form-control" required>
                <option value="media">Media página</option>
                <option value="full">Página completa</option>
                <option value="cover">Portada/Trasera</option>
            </select>
        </div>
        <div class="form-group">
            <label for="year">Año</label>
            <input type="number" name="year" id="year" class="form-control" value="<?= date('Y') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
</body>
</html>
?>
