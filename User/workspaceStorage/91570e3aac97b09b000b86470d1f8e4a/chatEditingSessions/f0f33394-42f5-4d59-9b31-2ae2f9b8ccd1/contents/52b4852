<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Transacción</title>
</head>
<body>
    <div class="container">
        <h1>Detalle de Transacción #<?= $transactionModel->id ?></h1>
        
        <div class="info-card">
            <h3>Información de la Transacción</h3>
            <dl>
                <dt>Fecha:</dt><dd><?= date('d/m/Y', strtotime($transactionModel->transaction_date)) ?></dd>
                <dt>Tipo:</dt><dd><?= ucfirst($transactionModel->type) ?></dd>
                <dt>Importe:</dt><dd class="amount-<?= $transactionModel->type ?>"><?= ($transactionModel->type === 'ingreso' ? '+' : '-') . number_format(abs($transactionModel->amount), 2) ?> €</dd>
                <dt>Descripción:</dt><dd><?= htmlspecialchars($transactionModel->description) ?></dd>
                <?php if ($transactionModel->reference): ?>
                    <dt>Referencia:</dt><dd><?= htmlspecialchars($transactionModel->reference) ?></dd>
                <?php endif; ?>
                <?php if ($transactionModel->category): ?>
                    <dt>Categoría:</dt><dd><?= htmlspecialchars($transactionModel->category) ?></dd>
                <?php endif; ?>
                <?php if ($transactionModel->counterparty): ?>
                    <dt>Contraparte:</dt><dd><?= htmlspecialchars($transactionModel->counterparty) ?></dd>
                <?php endif; ?>
                <dt>Estado:</dt><dd>
                    <?= $transactionModel->is_matched ? '<span class="badge badge-success">Emparejada</span>' : '<span class="badge badge-warning">Sin emparejar</span>' ?>
                    <?= $transactionModel->is_reconciled ? '<span class="badge badge-info">Conciliada</span>' : '' ?>
                </dd>
            </dl>
        </div>
        
        <?php if (!empty($matches)): ?>
            <div class="matches-section">
                <h3>Emparejamientos</h3>
                <?php foreach ($matches as $match): ?>
                    <div class="match-item">
                        <strong><?= ucfirst($match['matched_with_type']) ?> #<?= $match['matched_with_id'] ?></strong>
                        <span class="score">Score: <?= $match['confidence_score'] ?>%</span>
                        <a href="index.php?page=bank&subpage=matching&action=unmatch&transaction_id=<?= $transactionModel->id ?>" class="btn-sm btn-danger">Desemparejar</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($suggestions)): ?>
            <div class="suggestions-section">
                <h3>Sugerencias de Emparejamiento</h3>
                <?php foreach ($suggestions as $sugg): ?>
                    <div class="suggestion-item">
                        <strong><?= ucfirst($sugg['type']) ?> #<?= $sugg['id'] ?></strong>
                        <span>Score: <?= $sugg['score'] ?>%</span>
                        <form method="POST" action="index.php?page=bank&subpage=matching&action=manual" style="display:inline;">
                            <input type="hidden" name="transaction_id" value="<?= $transactionModel->id ?>">
                            <input type="hidden" name="match_type" value="<?= $sugg['type'] ?>">
                            <input type="hidden" name="match_id" value="<?= $sugg['id'] ?>">
                            <button type="submit" class="btn-sm btn-success">Emparejar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="index.php?page=bank&subpage=transactions" class="btn-secondary">Volver</a>
        </div>
    </div>
</body>
</html>
