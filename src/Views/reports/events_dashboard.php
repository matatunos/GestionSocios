


<?php
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
$proximosEventos = count(array_filter($filteredEvents, function($e) { return strtotime($e['date']) > time(); }));
$eventosHoy = count(array_filter($filteredEvents, function($e) { return date('Y-m-d', strtotime($e['date'])) === date('Y-m-d'); }));
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
    <div class="filter-row">
        <form method="get" style="display:flex; gap:1rem; align-items:center;">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="action" value="dashboard_events">
            <label>Desde: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"></label>
            <label>Hasta: <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"></label>
            <label>Estado:
                <select name="estado">
                    <option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos</option>
                    <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </label>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total eventos</div>
            <div class="kpi-value"><?= $totalEventos ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Próximos eventos</div>
            <div class="kpi-value"><?= $proximosEventos ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Eventos hoy</div>
            <div class="kpi-value"><?= $eventosHoy ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ocupación media (%)</div>
            <div class="kpi-value"><?= $ocupacionMedia ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ingresos totales (€)</div>
            <div class="kpi-value"><?= $ingresosTotales ?></div>
        </div>
    </div>
    <div class="chart-row">
        <canvas id="chartAsistencia" height="120"></canvas>
        <canvas id="chartIngresos" height="120"></canvas>
        <canvas id="chartOcupacion" height="120"></canvas>
        <canvas id="chartProximos" height="120"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const eventos = <?= json_encode(array_values($filteredEvents)) ?>;
const labels = eventos.map(e => e.name);
const statsArr = <?= json_encode(array_map(function($e) use ($attendanceModel) { return $attendanceModel->getStatsByEvent($e['id']); }, array_values($filteredEvents))) ?>;
const asistencia = statsArr.map(s => s.confirmed ?? 0);
const ingresos = eventos.map((e, i) => (e.price ?? 0) * (statsArr[i].confirmed ?? 0));
const ocupacion = eventos.map((e, i) => {
    const max = e.max_attendees ?? 0;
    const total = (statsArr[i].registered ?? 0) + (statsArr[i].confirmed ?? 0) + (statsArr[i].attended ?? 0) + (statsArr[i].cancelled ?? 0);
    return max ? Math.round((total / max) * 100) : 0;
});
const proximos = eventos.map(e => (new Date(e.date) > new Date()) ? 1 : 0);
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
new Chart(document.getElementById('chartOcupacion'), {
    type: 'bar',
    data: { labels, datasets: [{ label: '% Ocupación', data: ocupacion, backgroundColor: '#f59e42' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('chartProximos'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Próximos eventos', data: proximos, backgroundColor: '#6366f1' }] },
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
