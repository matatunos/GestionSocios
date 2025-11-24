<?php
// Dashboard de Eventos con KPIs, filtros y gráficos avanzados
if (!isset($db) && isset($this) && property_exists($this, 'db')) {
    $db = $this->db;
}
require_once __DIR__ . '/../../Models/Event.php';
require_once __DIR__ . '/../../Models/EventAttendance.php';
$eventModel = new Event($db);
$stmt = $eventModel->readAll();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
$attendanceModel = new EventAttendance($db);
// Filtros
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$estado = $_GET['estado'] ?? '';
$filteredEvents = array_filter($events, function($e) use ($from, $to, $estado) {
    $date = strtotime($e['date']);
    $ok = true;
    if ($from) $ok = $ok && ($date >= strtotime($from));
    if ($to) $ok = $ok && ($date <= strtotime($to));
    if ($estado === 'activo') $ok = $ok && ($e['is_active']);
    if ($estado === 'inactivo') $ok = $ok && (!$e['is_active']);
    return $ok;
});
// KPIs
$totalEventos = count($filteredEvents);
$proximosEventos = array_filter($filteredEvents, function($e) { return strtotime($e['date']) > time(); });
$eventosHoy = array_filter($filteredEvents, function($e) { return date('Y-m-d', strtotime($e['date'])) === date('Y-m-d'); });
$ocupacionMedia = $totalEventos ? round(array_sum(array_map(function($e) use ($attendanceModel) {
    $stats = $attendanceModel->getStatsByEvent($e['id']);
    $max = $e['max_attendees'] ?? 0;
    $total = ($stats['registered'] ?? 0) + ($stats['confirmed'] ?? 0) + ($stats['attended'] ?? 0) + ($stats['cancelled'] ?? 0);
    return $max ? ($total / $max) * 100 : 0;
}, $filteredEvents)) / $totalEventos, 1) : 0;
$ingresosTotales = array_sum(array_map(function($e) use ($attendanceModel) {
    $stats = $attendanceModel->getStatsByEvent($e['id']);
    return ($e['price'] ?? 0) * ($stats['confirmed'] ?? 0);
}, $filteredEvents));
?>
<div class="dashboard">
    <h2><i class="fas fa-chart-bar"></i> Dashboard de Eventos</h2>
    <div class="kpi-row">
        <div class="kpi-card"><span class="kpi-label">Total eventos</span><span class="kpi-value"><?= $totalEventos ?></span></div>
        <div class="kpi-card"><span class="kpi-label">Próximos eventos</span><span class="kpi-value"><?= count($proximosEventos) ?></span></div>
        <div class="kpi-card"><span class="kpi-label">Ocupación media</span><span class="kpi-value"><?= $ocupacionMedia ?>%</span></div>
        <div class="kpi-card"><span class="kpi-label">Ingresos totales</span><span class="kpi-value">€<?= number_format($ingresosTotales,2) ?></span></div>
    </div>
    <div class="filter-row">
        <form method="GET" action="">
            <label>Desde: <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>"></label>
            <label>Hasta: <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>"></label>
            <label>Estado: <select name="estado"><option value="">Todos</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></label>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>
    <div class="chart-row">
        <canvas id="chartAsistencia" height="120"></canvas>
        <canvas id="chartIngresos" height="120"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para gráficos
const eventos = <?= json_encode($events) ?>;
const labels = eventos.map(e => e.name);
const asistencia = eventos.map(e => {
    const stats = <?= json_encode(array_map(function($e) use ($attendanceModel) { return $attendanceModel->getStatsByEvent($e['id']); }, $events)) ?>;
    return stats[labels.indexOf(e.name)].confirmed ?? 0;
});
const ingresos = eventos.map(e => (e.price ?? 0) * (<?= json_encode(array_map(function($e) use ($attendanceModel) { $s = $attendanceModel->getStatsByEvent($e['id']); return $s['confirmed'] ?? 0; }, $events)) ?>)[labels.indexOf(e.name)]);
new Chart(document.getElementById('chartAsistencia'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Confirmados', data: asistencia, backgroundColor: '#22c55e' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('chartIngresos'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Ingresos (€)', data: ingresos, backgroundColor: '#2563eb' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
<style>
.dashboard { padding:2rem; }
.kpi-row { display:flex; gap:2rem; margin-bottom:2rem; }
.kpi-card { background:#f3f4f6; border-radius:8px; padding:1rem 2rem; box-shadow:0 2px 8px #0001; display:flex; flex-direction:column; align-items:center; }
.kpi-label { font-size:1rem; color:#555; }
.kpi-value { font-size:2rem; font-weight:700; color:#2563eb; }
.filter-row { margin-bottom:2rem; }
.chart-row { display:flex; gap:2rem; }
</style>
