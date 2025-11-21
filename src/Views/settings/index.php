<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Configuración</h1>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="display: flex; border-bottom: 1px solid var(--border-light);">
        <button class="tab-btn active" onclick="openTab(event, 'general')">General</button>
        <button class="tab-btn" onclick="openTab(event, 'events')">Eventos y Conceptos</button>
        <button class="tab-btn" onclick="openTab(event, 'fees')">Cuotas Anuales</button>
        <button class="tab-btn" onclick="openTab(event, 'database')">Base de Datos</button>
    </div>

    <div class="p-6">
        <!-- General Tab -->
        <div id="general" class="tab-content" style="display: block;">
            <h2 class="text-lg font-semibold mb-4">Configuración General</h2>
            <form action="index.php?page=settings&action=updateGeneral" method="POST" style="max-width: 500px;">
                <div class="form-group">
                    <label class="form-label">Nombre de la Asociación</label>
                    <input type="text" name="association_name" class="form-control" value="<?php echo htmlspecialchars($settings['association_name'] ?? 'Mi Asociación'); ?>" required>
                    <small style="color: var(--text-muted);">Este nombre aparecerá en la pantalla de inicio de sesión y en la barra lateral.</small>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>

        <!-- Events Tab (Iframe or Include? Let's link or include content. Linking is easier for now to avoid conflicts, but embedding is nicer. Let's provide a link/summary or just redirect logic) -->
        <!-- Actually, let's just put the links here or replicate the UI. Replicating might be complex. 
             Better: "Manage Events" button that goes to the events page, OR we include the events view here.
             Given the structure, let's just link to the modules for now to keep it simple, 
             OR we can render the sub-views if we refactor Controllers. 
             Let's keep it simple: provide quick access cards.
        -->
        <div id="events" class="tab-content" style="display: none;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Gestión de Eventos</h2>
                <a href="index.php?page=events" class="btn btn-primary">Ir al Panel de Eventos</a>
            </div>
            <p>Gestione aquí los eventos, excursiones y actividades que sirven como conceptos de pago.</p>
        </div>

        <div id="fees" class="tab-content" style="display: none;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Cuotas Anuales</h2>
                <a href="index.php?page=fees" class="btn btn-primary">Ir al Panel de Cuotas</a>
            </div>
            <p>Defina las cuotas anuales y genere los pagos pendientes para los socios.</p>
        </div>

        <!-- Database Tab -->
        <div id="database" class="tab-content" style="display: none;">
            <h2 class="text-lg font-semibold mb-4">Configuración de Base de Datos</h2>
            <div class="alert alert-warning" style="background: #fffbeb; color: #92400e; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle"></i> <strong>Cuidado:</strong> Cambiar estos valores puede dejar la aplicación inoperativa. Asegúrese de que los nuevos datos son correctos.
            </div>
            <form action="index.php?page=settings&action=updateDatabase" method="POST" style="max-width: 500px;">
                <div class="form-group">
                    <label class="form-label">Host</label>
                    <input type="text" name="db_host" class="form-control" value="<?php echo htmlspecialchars($dbConfig['host']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de la Base de Datos</label>
                    <input type="text" name="db_name" class="form-control" value="<?php echo htmlspecialchars($dbConfig['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="db_user" class="form-control" value="<?php echo htmlspecialchars($dbConfig['user']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="db_pass" class="form-control" placeholder="Dejar en blanco para no cambiar (si se muestra vacío)">
                    <small>Por seguridad, la contraseña actual no se muestra.</small>
                </div>
                <button type="submit" class="btn btn-danger">Actualizar Conexión</button>
            </form>
        </div>
    </div>
</div>

<style>
.tab-btn {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: var(--text-light);
}
.tab-btn:hover {
    color: var(--primary-600);
}
.tab-btn.active {
    color: var(--primary-600);
    border-bottom-color: var(--primary-600);
}
</style>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
