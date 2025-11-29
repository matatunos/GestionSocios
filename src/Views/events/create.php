<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Nuevo Evento</h1>
    <a href="index.php?page=events" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 900px;">
    <form action="index.php?page=events&action=store" method="POST">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nombre del Evento</label>
                <input type="text" name="name" class="form-control" required placeholder="Ej: Cena de Navidad 2024">
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Evento</label>
                <select name="event_type" class="form-control">
                    <option value="meeting">Reunión</option>
                    <option value="celebration">Celebración</option>
                    <option value="activity">Actividad</option>
                    <option value="assembly">Asamblea</option>
                    <option value="other">Otro</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Detalles del evento..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="location" class="form-control" placeholder="Lugar donde se realizará">
            </div>
            <div class="form-group">
                <label class="form-label">Color (para calendario)</label>
                <input type="color" name="color" class="form-control" value="#6366f1" style="height: 42px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hora Inicio</label>
                <input type="time" name="start_time" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Hora Fin</label>
                <input type="time" name="end_time" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Precio (€)</label>
                <input type="number" step="0.01" name="price" class="form-control" value="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">Plazas Máximas</label>
                <input type="number" name="max_attendees" class="form-control" placeholder="Dejar vacío si ilimitado">
            </div>
            <div class="form-group">
                <label class="form-label">Fecha Límite Inscripción</label>
                <input type="date" name="registration_deadline" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="requires_registration" value="1">
                <span>Requiere inscripción previa</span>
            </label>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Evento activo (visible en calendario)</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Evento
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
