<?php
/**
 * Vista de Calendario de Subvenciones
 * Muestra subvenciones en un calendario con fechas de plazos, inicio y fin
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Subvenciones</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
    <style>
        .calendar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }
        
        #calendar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <h1><i class="fas fa-calendar-alt"></i> Calendario de Subvenciones</h1>
            <a href="index.php?page=grants" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>Plazo de solicitud (seguidas)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #6c757d;"></div>
                <span>Plazo de solicitud (no seguidas)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745;"></div>
                <span>Fecha de inicio</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107;"></div>
                <span>Fecha de fin</span>
            </div>
        </div>

        <div id="calendar"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            var events = <?php echo json_encode($events); ?>;
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: events,
                eventClick: function(info) {
                    var grant = info.event.extendedProps.grant;
                    if (grant) {
                        window.location.href = 'index.php?page=grants&action=view&id=' + grant.id;
                    }
                },
                eventDidMount: function(info) {
                    info.el.title = info.event.title;
                }
            });
            
            calendar.render();
        });
    </script>
</body>
</html>
