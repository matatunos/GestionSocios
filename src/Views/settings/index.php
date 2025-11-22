<?php ob_start(); ?>

<style>
.settings-container {
    max-width: 1200px;
    margin: 0 auto;
}

.settings-header {
    margin-bottom: 2rem;
}

.settings-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-color);
}

.tabs-container {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.tabs-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    border-bottom: 3px solid #e5e7eb;
    padding-bottom: 0;
    background: white;
}

.tab-btn {
    background: none;
    border: none;
    padding: 1.25rem 2rem;
    font-size: 1.125rem;
    font-weight: 700;
    color: #6b7280;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 4px solid transparent;
    margin-bottom: -3px;
}

.tab-btn:hover {
    color: var(--primary-color);
    background: #f9fafb;
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-btn i {
    margin-right: 0.75rem;
    font-size: 1.25rem;
}

.tab-content-wrapper {
    padding: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 1.5rem;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs-nav {
        overflow-x: auto;
    }
    
    .tab-btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }
}
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-cog"></i> Configuración</h1>
    </div>

    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="openTab(event, 'general')">
                <i class="fas fa-building"></i> General
            </button>
            <button class="tab-btn" onclick="openTab(event, 'fees')">
                <i class="fas fa-euro-sign"></i> Cuotas
            </button>
            <button class="tab-btn" onclick="openTab(event, 'ad_prices')">
                <i class="fas fa-tags"></i> Precios Anuncios
            </button>
            <button class="tab-btn" onclick="openTab(event, 'appearance')">
                <i class="fas fa-palette"></i> Apariencia
            </button>
            <button class="tab-btn" onclick="openTab(event, 'database')">
                <i class="fas fa-database"></i> Base de Datos
            </button>
        </div>

        <div class="tab-content-wrapper">
            <!-- General Tab -->
            <div id="general" class="tab-content" style="display: block;">
                <h2 class="section-title">Configuración General</h2>
                <form action="index.php?page=settings&action=updateGeneral" method="POST" style="max-width: 600px;">
                <div class="form-group">
                    <label class="form-label">Nombre de la Asociación</label>
                    <input type="text" name="association_name" class="form-control" value="<?php echo htmlspecialchars($settings['association_name'] ?? 'Mi Asociación'); ?>" required>
                    <small style="color: var(--text-muted);">Este nombre aparecerá en la pantalla de inicio de sesión y en la barra lateral.</small>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>

            <!-- Ad Prices Tab -->
            <div id="ad_prices" class="tab-content" style="display: none;">
                <h2 class="section-title">Precios de Anuncios (Libro de Fiestas)</h2>
                
                <div class="settings-grid">
                <!-- Form to Update Prices -->
                <div class="card">
                    <h3 class="text-md font-semibold mb-4">Actualizar Precios</h3>
                    <form action="index.php?page=ad_prices&action=store" method="POST">
                        <div class="form-group">
                            <label class="form-label">Año</label>
                            <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Media Página (€)</label>
                            <input type="number" step="0.01" name="price_media" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Página Completa (€)</label>
                            <input type="number" step="0.01" name="price_full" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Portada (€)</label>
                            <input type="number" step="0.01" name="price_cover" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contraportada (€)</label>
                            <input type="number" step="0.01" name="price_back_cover" class="form-control" placeholder="0.00">
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Guardar Precios</button>
                    </form>
                </div>

                <!-- Current Prices Display -->
                <div>
                    <div class="card mb-4">
                        <h3 class="text-md font-semibold mb-2">Precios <?php echo $currentYear; ?></h3>
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-right">Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Media Página</td>
                                    <td class="text-right"><?php echo number_format($adPrices['media'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Página Completa</td>
                                    <td class="text-right"><?php echo number_format($adPrices['full'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Portada</td>
                                    <td class="text-right"><?php echo number_format($adPrices['cover'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Contraportada</td>
                                    <td class="text-right"><?php echo number_format($adPrices['back_cover'] ?? 0, 2); ?> €</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card">
                        <h3 class="text-md font-semibold mb-2">Precios <?php echo $currentYear + 1; ?></h3>
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-right">Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Media Página</td>
                                    <td class="text-right"><?php echo number_format($nextYearPrices['media'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Página Completa</td>
                                    <td class="text-right"><?php echo number_format($nextYearPrices['full'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Portada</td>
                                    <td class="text-right"><?php echo number_format($nextYearPrices['cover'] ?? 0, 2); ?> €</td>
                                </tr>
                                <tr>
                                    <td>Contraportada</td>
                                    <td class="text-right"><?php echo number_format($nextYearPrices['back_cover'] ?? 0, 2); ?> €</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            <!-- Member Fees Tab -->
            <div id="fees" class="tab-content" style="display: none;">
                <h2 class="section-title">Cuotas de Socios</h2>
                
                <div class="settings-grid">
                <!-- Define New Fee -->
                <div class="card">
                    <h3 class="text-md font-semibold mb-4">Definir Nueva Cuota</h3>
                    <form action="index.php?page=fees&action=store" method="POST">
                        <div class="form-group">
                            <label class="form-label">Año</label>
                            <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Importe (€)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-save"></i> Guardar Cuota
                        </button>
                    </form>
                </div>

                <!-- List Fees -->
                <div class="card">
                    <h3 class="text-md font-semibold mb-4">Cuotas Definidas</h3>
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Importe</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo $fee['year']; ?></td>
                                    <td><?php echo number_format($fee['amount'], 2); ?> €</td>
                                    <td>
                                        <a href="index.php?page=fees&action=generate&year=<?php echo $fee['year']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('¿Generar pagos pendientes para todos los socios activos para el año <?php echo $fee['year']; ?>?');">
                                            <i class="fas fa-file-invoice-dollar"></i> Generar Pagos
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            <!-- Appearance Tab -->
            <div id="appearance" class="tab-content" style="display: none;">
                <h2 class="section-title">Apariencia y Tema</h2>
                <div class="card" style="max-width: 600px; margin: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 1rem; margin-bottom: 0.25rem;">Modo Oscuro</h3>
                        <p style="font-size: 0.875rem; margin: 0;">Activar tema oscuro para la interfaz</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

            <!-- Database Tab -->
            <div id="database" class="tab-content" style="display: none;">
                <h2 class="section-title">Configuración de Base de Datos</h2>
            <div class="alert alert-warning" style="background: #fffbeb; color: #92400e; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle"></i> <strong>Cuidado:</strong> Cambiar estos valores puede dejar la aplicación inoperativa. Asegúrese de que los nuevos datos son correctos.
            </div>
                <form action="index.php?page=settings&action=updateDatabase" method="POST" style="max-width: 600px;">
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
</div>

<style>

/* Switch Toggle Styles */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: var(--primary-600);
}

input:focus + .slider {
  box-shadow: 0 0 1px var(--primary-600);
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
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

// Dark Mode Logic
function toggleDarkMode() {
    const isChecked = document.getElementById('darkModeToggle').checked;
    if (isChecked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
    }
}

// Set initial state
document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        document.getElementById('darkModeToggle').checked = true;
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
