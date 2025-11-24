<?php
require_once __DIR__ . '/../../../Models/EventPayment.php';
$events = $db->query("SELECT id, name, date FROM events WHERE date >= CURDATE() ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($events as $event) {
    echo "<h4>{$event['name']} (" . date('d/m/Y', strtotime($event['date'])) . ")</h4>";
    $payments = (new EventPayment($db))->getPaymentsByEvent($event['id']);
    if ($payments) {
        echo "<table class='table'><thead><tr><th>Socio</th><th>Importe</th><th>Estado</th><th>Acción</th></tr></thead><tbody>";
        foreach ($payments as $p) {
            $estado = $p['status'] === 'paid' ? 'Pagado' : 'Pendiente';
            echo "<tr>
                    <td>{$p['first_name']} {$p['last_name']}</td>
                    <td>" . number_format($p['amount'], 2) . " €</td>
                    <td>{$estado}</td>";
            if ($p['status'] === 'pending') {
                echo "<td>
                    <form method='POST' action='index.php?page=dashboard&action=markEventPaymentPaid' style='display:inline;'>
                        <input type='hidden' name='payment_id' value='{$p['id']}'>
                        <input type='hidden' name='event_id' value='{$event['id']}'>
                        <input type='text' name='method' placeholder='Método' style='width:90px;margin-right:4px;'>
                        <input type='date' name='payment_date' style='width:120px;margin-right:4px;'>
                        <button type='submit' class='btn btn-sm btn-success'>Registrar pago</button>
                    </form>
                </td>";
            } else {
                echo "<td><span class='badge badge-success'>Pagado</span></td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No hay pagos registrados para este evento.</p>";
    }
}
?>
