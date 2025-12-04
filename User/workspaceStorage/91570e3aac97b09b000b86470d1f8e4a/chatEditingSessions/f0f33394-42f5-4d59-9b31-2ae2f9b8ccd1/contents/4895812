<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Conciliación</title>
</head>
<body>
    <div class="container">
        <h1>Iniciar Nueva Conciliación</h1>
        
        <form method="POST" class="form-card">
            <div class="form-group">
                <label for="account_id">Cuenta Bancaria *</label>
                <select id="account_id" name="account_id" class="form-control" required>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['bank_name']) ?> - <?= htmlspecialchars($acc['iban']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="period_start">Fecha Inicio *</label>
                <input type="date" id="period_start" name="period_start" class="form-control" value="<?= date('Y-m-01') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="period_end">Fecha Fin *</label>
                <input type="date" id="period_end" name="period_end" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="statement_balance">Saldo según Extracto Bancario *</label>
                <input type="number" id="statement_balance" name="statement_balance" class="form-control" step="0.01" required>
                <small>Introduce el saldo final que aparece en tu extracto bancario</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Iniciar Conciliación</button>
                <a href="index.php?page=bank&subpage=reconciliation" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
