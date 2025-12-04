<?php
$pageTitle = 'Importar Extracto Bancario';
ob_start();
?>

<div class="container">
    <h1>📥 Importar Extracto Bancario</h1>
        
        <div class="info-box">
            <p><strong>Formatos soportados:</strong></p>
            <ul>
                <li><strong>CSV:</strong> Formato estándar con campos: fecha, descripción, importe, saldo</li>
                <li><strong>OFX:</strong> (Próximamente) Open Financial Exchange</li>
            </ul>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" action="index.php?page=bank&subpage=import&action=process" class="form-card">
            <div class="form-group">
                <label for="account_id">Cuenta Bancaria *</label>
                <select id="account_id" name="account_id" class="form-control" required>
                    <option value="">Selecciona una cuenta...</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['bank_name']) ?> - <?= htmlspecialchars($acc['iban']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="format">Formato del Archivo *</label>
                <select id="format" name="format" class="form-control" required>
                    <option value="csv">CSV (delimitado por punto y coma)</option>
                    <option value="ofx" disabled>OFX (próximamente)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="import_file">Archivo *</label>
                <input type="file" id="import_file" name="import_file" class="form-control" accept=".csv" required>
                <small>Tamaño máximo: 5MB</small>
            </div>
            
            <div class="info-box">
                <p><strong>Formato CSV esperado:</strong></p>
                <pre>Fecha;Descripción;Importe;Saldo
01/12/2025;Pago cliente ABC;1500.00;12500.00
02/12/2025;Factura luz;-125.50;12374.50</pre>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Importar Extracto</button>
                <a href="index.php?page=bank" class="btn-secondary">Cancelar</a>
            </div>
        </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout.php';
?>
