<?php
require_once __DIR__ . '/../../../Models/EventPayment.php';
$events = $db->query("SELECT id, name, date FROM events WHERE date >= CURDATE() ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($events as $event) {
    echo "<div class='card' style='margin-bottom:1.5rem;'>";
    echo "<h4 style='font-size:1.1rem;font-weight:600;margin-bottom:1rem;'><i class='fas fa-calendar-alt' style='margin-right:0.5rem;color:var(--primary-600);'></i>{$event['name']} (" . date('d/m/Y', strtotime($event['date'])) . ")</h4>";
    // Solo asistentes pendientes de pago
    $pendingStmt = $db->prepare("SELECT ea.id, m.first_name, m.last_name, ea.status FROM event_attendance ea INNER JOIN members m ON ea.member_id = m.id WHERE ea.event_id = :event_id AND ea.status = 'registered'");
    $pendingStmt->bindParam(':event_id', $event['id']);
    $pendingStmt->execute();
    $pending = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    if ($pending) {
        echo "<div class='table-responsive'><table class='table'><thead><tr><th>Socio</th><th>Estado</th><th>Acción</th></tr></thead><tbody>";
        foreach ($pending as $p) {
            echo "<tr>
                    <td>{$p['first_name']} {$p['last_name']}</td>
                    <td>Pendiente de pago</td>
                    <td>
                        <form method='POST' action='index.php?page=dashboard&action=markEventPaymentPaid' style='display:inline;'>
                            <input type='hidden' name='attendance_id' value='{$p['id']}'>
                            <button type='submit' class='btn btn-sm btn-success'><i class='fas fa-euro-sign'></i> Registrar pago</button>
                        </form>
                    </td>
                </tr>";
        }
        echo "</tbody></table></div>";
    }
    echo "</div>";
}
?>
