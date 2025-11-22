<?php 
$page_title = "Calendario de Eventos";
ob_start(); 
?>

<div class="flex justify-between items-center mb-4">
    <h1><i class="fas fa-calendar-alt"></i> Calendario de Eventos</h1>
    <div class="btn-group">
        <a href="index.php?page=export&action=events_excel" class="btn btn-secondary">
            <i class="fas fa-download"></i> Exportar Excel
        </a>
        <a href="index.php?page=events&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Evento
        </a>
    </div>
</div>

<!-- Calendar Card -->
<div class="card" style="padding: 1.5rem;">
    <div id="calendar"></div>
</div>

<!-- Event Legend -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1rem;">Tipos de Eventos</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 16px; height: 16px; background: #6366f1; border-radius: 4px;"></div>
            <span>Reunión</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 16px; height: 16px; background: #ec4899; border-radius: 4px;"></div>
            <span>Celebración</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 16px; height: 16px; background: #10b981; border-radius: 4px;"></div>
            <span>Actividad</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 16px; height: 16px; background: #f59e0b; border-radius: 4px;"></div>
            <span>Asamblea</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 16px; height: 16px; background: #64748b; border-radius: 4px;"></div>
            <span>Otro</span>
        </div>
    </div>
</div>

<style>
#calendar {
    max-width: 100%;
    min-height: 600px;
}

.fc {
    /* FullCalendar custom styles */
}

.fc .fc-button {
    background: var(--primary-600);
    border-color: var(--primary-600);
    text-transform: capitalize;
}

.fc .fc-button:hover {
    background: var(--primary-700);
    border-color: var(--primary-700);
}

.fc .fc-button-primary:disabled {
    background: var(--primary-300);
    border-color: var(--primary-300);
}

.fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-main);
}

.fc-theme-standard td, 
.fc-theme-standard th {
    border-color: var(--border-light);
}

.fc-daygrid-day-number {
    color: var(--text-main);
    padding: 4px;
}

.fc .fc-daygrid-day.fc-day-today {
    background-color: var(--primary-50) !important;
}

.fc-event {
    cursor: pointer;
    border: none;
    padding: 2px 4px;
    font-size: 0.875rem;
}

.fc-event:hover {
    opacity: 0.8;
}

/* Dark mode for calendar */
[data-theme="dark"] .fc {
    color: var(--text-main);
}

[data-theme="dark"] .fc .fc-toolbar-title {
    color: var(--text-main);
}

[data-theme="dark"] .fc-theme-standard td,
[data-theme="dark"] .fc-theme-standard th {
    border-color: var(--border-light);
}

[data-theme="dark"] .fc .fc-col-header-cell {
    background: rgba(30, 41, 59, 0.6);
}

[data-theme="dark"] .fc .fc-daygrid-day {
    background: transparent;
}

[data-theme="dark"] .fc .fc-daygrid-day.fc-day-today {
    background: rgba(99, 102, 241, 0.15) !important;
}

[data-theme="dark"] .fc-daygrid-day-number {
    color: var(--text-main);
}

[data-theme="dark"] .fc .fc-scrollgrid {
    border-color: var(--border-light);
}
</style>

<!-- Load FullCalendar from CDN -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('FullCalendar loading...');
    
    // Check if FullCalendar is loaded
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library not loaded!');
        return;
    }
    
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }
    
    console.log('Initializing FullCalendar...');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            list: 'Lista'
        },
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            // Fetch events from API with date range
            const start = info.start.toISOString().split('T')[0];
            const end = info.end.toISOString().split('T')[0];
            
            fetch(`index.php?page=calendar&action=api&start=${start}&end=${end}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Calendar events loaded:', data);
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            // Redirect to event detail page
            window.location.href = info.event.url;
        },
        dateClick: function(info) {
            // Optionally: quick create event on date click
            // window.location.href = 'index.php?page=events&action=create&date=' + info.dateStr;
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.extendedProps.description || info.event.title;
        }
    });
    
    console.log('Rendering calendar...');
    calendar.render();
    console.log('Calendar rendered successfully!');
    
    // Update calendar on theme change
    const observer = new MutationObserver(function(mutations) {
        calendar.render();
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
