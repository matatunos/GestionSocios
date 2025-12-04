<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cuenta</title>
</head>
<body>
    <div class="container">
        <h1>Editar Cuenta Bancaria</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form-card">
            <div class="form-group">
                <label for="bank_name">Nombre del Banco *</label>
                <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?= htmlspecialchars($accountModel->bank_name) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="account_holder">Titular *</label>
                <input type="text" id="account_holder" name="account_holder" class="form-control" value="<?= htmlspecialchars($accountModel->account_holder) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="iban">IBAN *</label>
                <input type="text" id="iban" name="iban" class="form-control" value="<?= htmlspecialchars($accountModel->iban) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="swift_bic">SWIFT/BIC</label>
                <input type="text" id="swift_bic" name="swift_bic" class="form-control" value="<?= htmlspecialchars($accountModel->swift_bic) ?>">
            </div>
            
            <div class="form-group">
                <label for="account_type">Tipo *</label>
                <select id="account_type" name="account_type" class="form-control" required>
                    <option value="corriente" <?= $accountModel->account_type === 'corriente' ? 'selected' : '' ?>>Corriente</option>
                    <option value="ahorro" <?= $accountModel->account_type === 'ahorro' ? 'selected' : '' ?>>Ahorro</option>
                    <option value="credito" <?= $accountModel->account_type === 'credito' ? 'selected' : '' ?>>Crédito</option>
                </select>
            </div>
            
            <div class="form-group">
                <label><input type="checkbox" name="is_active" <?= $accountModel->is_active ? 'checked' : '' ?>> Cuenta activa</label>
            </div>
            
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" class="form-control"><?= htmlspecialchars($accountModel->notes) ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Guardar Cambios</button>
                <a href="index.php?page=bank&subpage=accounts&action=view&id=<?= $accountModel->id ?>" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
