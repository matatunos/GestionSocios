


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
<!-- Header -->
<div class="flex justify-between items-center mb-4">
    <div>
        <h1>Dashboard de Eventos</h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">
            Análisis y métricas de eventos
        </p>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 2rem;">
    <form method="get" style="display:flex; gap:1rem; align-items:center; flex-wrap: wrap;">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="action" value="dashboard_events">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--text-muted);">Desde:</span>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--text-muted);">Hasta:</span>
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--text-muted);">Estado:</span>
            <select name="estado" class="form-control">
                <option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos</option>
                <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </label>
        <button type="submit" class="btn btn-primary" style="margin-top: 1.25rem;">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </form>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    
    <!-- Total Events -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Total Eventos</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--primary-600);">
                    <?= $totalEventos ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--primary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-alt" style="font-size: 1.5rem; color: var(--primary-600);"></i>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Próximos Eventos</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--secondary-600);">
                    <?= $proximosEventos ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--secondary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock" style="font-size: 1.5rem; color: var(--secondary-600);"></i>
            </div>
        </div>
    </div>
    
    <!-- Events Today -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Eventos Hoy</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?= $eventosHoy ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-day" style="font-size: 1.5rem; color: #d97706;"></i>
            </div>
        </div>
    </div>
    
    <!-- Average Occupancy -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Ocupación Media</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--primary-600);">
                    <?= $ocupacionMedia ?>%
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--primary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-users" style="font-size: 1.5rem; color: var(--primary-600);"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Income -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Ingresos Totales</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--secondary-600);">
                    <?= number_format($ingresosTotales, 2) ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--secondary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-euro-sign" style="font-size: 1.5rem; color: var(--secondary-600);"></i>
            </div>
        </div>
    </div>
    
</div>

<!-- Charts -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Asistencia por Evento (Confirmados)</h2>
    <canvas id="chartAsistencia" style="max-height: 300px;"></canvas>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Ingresos por Evento (€)</h2>
    <canvas id="chartIngresos" style="max-height: 300px;"></canvas>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Ocupación (%) por Evento</h2>
    <canvas id="chartOcupacion" style="max-height: 300px;"></canvas>
</div>

<!-- Two Columns Layout -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    
    <!-- Upcoming Events List -->
    <div class="card">
        <h2 style="margin-bottom: 1rem;">Próximos Eventos</h2>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php 
            $upcomingEvents = array_filter($filteredEvents, function($e) { 
                return strtotime($e['date']) > time(); 
            });
            ?>
            <?php if (empty($upcomingEvents)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No hay próximos eventos</p>
            <?php else: ?>
                <?php foreach ($upcomingEvents as $e): ?>
                    <div style="padding: 0.75rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: var(--text-main);">
                                <?= htmlspecialchars($e['name']) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($e['date'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Event Rankings -->
    <div class="card">
        <h2 style="margin-bottom: 1rem;">Ranking de Eventos</h2>
        <div style="max-height: 400px; overflow-y: auto;">
            
            <!-- Top Revenue -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">
                    <i class="fas fa-trophy" style="color: #d97706;"></i> Más Beneficios
                </h3>
                <?php
                $ranking_beneficios = $filteredEvents;
                usort($ranking_beneficios, function($a, $b) use ($attendanceModel) {
                    $benefA = ($a['price'] ?? 0) * ($attendanceModel->getStatsByEvent($a['id'])['confirmed'] ?? 0);
                    $benefB = ($b['price'] ?? 0) * ($attendanceModel->getStatsByEvent($b['id'])['confirmed'] ?? 0);
                    return $benefB <=> $benefA;
                });
                foreach (array_slice($ranking_beneficios, 0, 3) as $e):
                    $benef = ($e['price'] ?? 0) * ($attendanceModel->getStatsByEvent($e['id'])['confirmed'] ?? 0);
                ?>
                    <div style="padding: 0.5rem 0; display: flex; justify-content: space-between;">
                        <span style="color: var(--text-main);"><?= htmlspecialchars($e['name']) ?></span>
                        <span style="font-weight: 600; color: var(--secondary-600);"><?= number_format($benef, 2) ?> €</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Most Popular -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">
                    <i class="fas fa-star" style="color: var(--primary-600);"></i> Más Populares
                </h3>
                <?php
                $ranking_populares = $filteredEvents;
                usort($ranking_populares, function($a, $b) use ($attendanceModel) {
                    $confA = $attendanceModel->getStatsByEvent($a['id'])['confirmed'] ?? 0;
                    $confB = $attendanceModel->getStatsByEvent($b['id'])['confirmed'] ?? 0;
                    return $confB <=> $confA;
                });
                foreach (array_slice($ranking_populares, 0, 3) as $e):
                    $conf = $attendanceModel->getStatsByEvent($e['id'])['confirmed'] ?? 0;
                ?>
                    <div style="padding: 0.5rem 0; display: flex; justify-content: space-between;">
                        <span style="color: var(--text-main);"><?= htmlspecialchars($e['name']) ?></span>
                        <span style="font-weight: 600; color: var(--primary-600);"><?= $conf ?> confirmados</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Best Occupancy -->
            <div>
                <h3 style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">
                    <i class="fas fa-chart-line" style="color: var(--secondary-600);"></i> Mayor Ocupación
                </h3>
                <?php
                $ranking_ocupacion = $filteredEvents;
                usort($ranking_ocupacion, function($a, $b) use ($attendanceModel) {
                    $statsA = $attendanceModel->getStatsByEvent($a['id']);
                    $statsB = $attendanceModel->getStatsByEvent($b['id']);
                    $maxA = $a['max_attendees'] ?? 0;
                    $maxB = $b['max_attendees'] ?? 0;
                    $totalA = ($statsA['registered'] ?? 0) + ($statsA['confirmed'] ?? 0) + ($statsA['attended'] ?? 0) + ($statsA['cancelled'] ?? 0);
                    $totalB = ($statsB['registered'] ?? 0) + ($statsB['confirmed'] ?? 0) + ($statsB['attended'] ?? 0) + ($statsB['cancelled'] ?? 0);
                    $ocupA = $maxA ? ($totalA / $maxA) * 100 : 0;
                    $ocupB = $maxB ? ($totalB / $maxB) * 100 : 0;
                    return $ocupB <=> $ocupA;
                });
                foreach (array_slice($ranking_ocupacion, 0, 3) as $e):
                    $stats = $attendanceModel->getStatsByEvent($e['id']);
                    $max = $e['max_attendees'] ?? 0;
                    $total = ($stats['registered'] ?? 0) + ($stats['confirmed'] ?? 0) + ($stats['attended'] ?? 0) + ($stats['cancelled'] ?? 0);
                    $ocup = $max ? round(($total / $max) * 100, 1) : 0;
                ?>
                    <div style="padding: 0.5rem 0; display: flex; justify-content: space-between;">
                        <span style="color: var(--text-main);"><?= htmlspecialchars($e['name']) ?></span>
                        <span style="font-weight: 600; color: var(--secondary-600);"><?= $ocup ?>%</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
new Chart(document.getElementById('chartAsistencia'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Confirmados', data: asistencia, backgroundColor: '#22c55e' }] },
    options: { 
        responsive: true, 
        maintainAspectRatio: true,
        plugins: { legend: { display: false } }
    }
});
new Chart(document.getElementById('chartIngresos'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Ingresos (€)', data: ingresos, backgroundColor: '#2563eb' }] },
    options: { 
        responsive: true, 
        maintainAspectRatio: true,
        plugins: { legend: { display: false } }
    }
});
new Chart(document.getElementById('chartOcupacion'), {
    type: 'bar',
    data: { labels, datasets: [{ label: '% Ocupación', data: ocupacion, backgroundColor: '#f59e42' }] },
    options: { 
        responsive: true, 
        maintainAspectRatio: true,
        plugins: { legend: { display: false } }
    }
});
</script>
