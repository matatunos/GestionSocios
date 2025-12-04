<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Cuenta Bancaria</title>
</head>
<body>
    <div class="container">
        <h1>Nueva Cuenta Bancaria</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form-card">
            <div class="form-group">
                <label for="bank_name">Nombre del Banco *</label>
                <input type="text" id="bank_name" name="bank_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="account_holder">Titular de la Cuenta *</label>
                <input type="text" id="account_holder" name="account_holder" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="iban">IBAN *</label>
                <input type="text" id="iban" name="iban" class="form-control" required placeholder="ES91 2100 0418 4502 0005 1332">
            </div>
            
            <div class="form-group">
                <label for="swift_bic">SWIFT/BIC</label>
                <input type="text" id="swift_bic" name="swift_bic" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="account_type">Tipo de Cuenta *</label>
                <select id="account_type" name="account_type" class="form-control" required>
                    <option value="corriente">Cuenta Corriente</option>
                    <option value="ahorro">Cuenta de Ahorro</option>
                    <option value="credito">Línea de Crédito</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="initial_balance">Saldo Inicial</label>
                <input type="number" id="initial_balance" name="initial_balance" class="form-control" step="0.01" value="0.00">
            </div>
            
            <div class="form-group">
                <label for="currency">Moneda</label>
                <select id="currency" name="currency" class="form-control">
                    <option value="EUR" selected>EUR (€)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label><input type="checkbox" name="is_active" checked> Cuenta activa</label>
            </div>
            
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Crear Cuenta</button>
                <a href="index.php?page=bank&subpage=accounts" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
