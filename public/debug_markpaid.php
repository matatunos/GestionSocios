<?php
// Script de diagnóstico para markPaid
require_once __DIR__ . '/src/Config/Database.php';

$db = (new Database())->getConnection();
$currentYear = date('Y');

echo "<h2>Diagnóstico de markPaid</h2>";
echo "<p>Año actual: $currentYear</p>";

// Verificar si existe cuota para el año actual
$feeStmt = $db->prepare("SELECT * FROM annual_fees WHERE year = ?");
$feeStmt->execute([$currentYear]);
$fee = $feeStmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>1. Cuota para año $currentYear:</h3>";
if ($fee) {
    echo "<pre>";
    print_r($fee);
    echo "</pre>";
} else {
    echo "<p style='color: red;'><strong>❌ NO HAY CUOTA DEFINIDA PARA $currentYear</strong></p>";
    echo "<p>Necesitas crear una cuota para el año $currentYear en la tabla annual_fees</p>";
}

// Listar todas las cuotas disponibles
echo "<h3>2. Todas las cuotas en annual_fees:</h3>";
$allFees = $db->query("SELECT * FROM annual_fees ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);
if ($allFees) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Año</th><th>Monto</th></tr>";
    foreach ($allFees as $f) {
        echo "<tr><td>{$f['id']}</td><td>{$f['year']}</td><td>{$f['amount']} €</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No hay cuotas definidas en la tabla</p>";
}

// Verificar pagos del socio ID 1
echo "<h3>3. Pagos del socio ID 1:</h3>";
$payments = $db->query("SELECT * FROM payments WHERE member_id = 1 AND payment_type = 'fee' ORDER BY fee_year DESC")->fetchAll(PDO::FETCH_ASSOC);
if ($payments) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Año</th><th>Monto</th><th>Estado</th><th>Fecha Pago</th></tr>";
    foreach ($payments as $p) {
        echo "<tr><td>{$p['id']}</td><td>{$p['fee_year']}</td><td>{$p['amount']} €</td><td>{$p['status']}</td><td>{$p['payment_date']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay pagos registrados para el socio ID 1</p>";
}

echo "<hr>";
echo "<h3>Solución:</h3>";
echo "<p>Si no hay cuota para $currentYear, ejecuta este SQL:</p>";
echo "<pre>INSERT INTO annual_fees (year, amount) VALUES ($currentYear, 50.00);</pre>";
echo "<p>(Ajusta el monto según corresponda)</p>";
?>
