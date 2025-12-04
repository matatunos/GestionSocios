<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Transacción</title>
</head>
<body>
    <div class="container">
        <h1>Nueva Transacción Manual</h1>
        
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
                <label for="transaction_date">Fecha *</label>
                <input type="date" id="transaction_date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type">Tipo *</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Importe *</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="description">Descripción *</label>
                <input type="text" id="description" name="description" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="reference">Referencia</label>
                <input type="text" id="reference" name="reference" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="category">Categoría</label>
                <input type="text" id="category" name="category" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="counterparty">Contraparte</label>
                <input type="text" id="counterparty" name="counterparty" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Crear Transacción</button>
                <a href="index.php?page=bank&subpage=transactions" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
