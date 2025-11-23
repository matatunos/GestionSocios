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
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.tabs-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    border-bottom: 3px solid var(--border-light);
    padding-bottom: 0;
    background: var(--bg-card);
}

.tab-btn {
    background: none;
    border: none;
    padding: 1.25rem 2rem;
    font-size: 1.125rem;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 4px solid transparent;
    margin-bottom: -3px;
}

.tab-btn:hover {
    color: var(--primary-600);
    background: var(--primary-50);
}

.tab-btn.active {
    color: var(--primary-600);
    font-weight: 700;
    border-bottom-color: var(--primary-600);
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
            <button class="tab-btn <?= ($_GET['tab'] ?? 'organization') === 'organization' ? 'active' : '' ?>" onclick="openTab(event, 'organization')">
                <i class="fas fa-building"></i> Organización
            </button>
            <button class="tab-btn <?= ($_GET['tab'] ?? '') === 'fees' ? 'active' : '' ?>" onclick="openTab(event, 'fees')">
                <i class="fas fa-euro-sign"></i> Cuotas
            </button>
            <button class="tab-btn <?= ($_GET['tab'] ?? '') === 'ad_prices' ? 'active' : '' ?>" onclick="openTab(event, 'ad_prices')">
                <i class="fas fa-tags"></i> Precios Anuncios
            </button>
            <button class="tab-btn <?= ($_GET['tab'] ?? '') === 'database' ? 'active' : '' ?>" onclick="openTab(event, 'database')">
                <i class="fas fa-database"></i> Base de Datos
            </button>
            <button class="tab-btn <?= ($_GET['tab'] ?? '') === 'security' ? 'active' : '' ?>" onclick="openTab(event, 'security')">
                <i class="fas fa-shield-alt"></i> Seguridad
            </button>
        </div>

        <div class="tab-content-wrapper">

            <!-- Organization Tab -->
            <div id="organization" class="tab-content" style="display: none;">
                <h2 class="section-title">Configuración de la Organización</h2>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form action="index.php?page=settings&action=updateOrganization" method="POST" enctype="multipart/form-data">
                    <!-- Información General -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3"><i class="fas fa-info-circle"></i> Información General</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_name">Nombre Completo</label>
                                    <input type="text" name="org_name" id="org_name" class="form-control" 
                                           value="<?= htmlspecialchars($generalSettings['org_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="org_short_name">Siglas</label>
                                    <input type="text" name="org_short_name" id="org_short_name" class="form-control" 
                                           value="<?= htmlspecialchars($generalSettings['org_short_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="org_founded_year">Año Fundación</label>
                                    <input type="number" name="org_founded_year" id="org_founded_year" class="form-control" 
                                           value="<?= htmlspecialchars($generalSettings['org_founded_year']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_cif">CIF/NIF</label>
                                    <input type="text" name="org_cif" id="org_cif" class="form-control" 
                                           value="<?= htmlspecialchars($generalSettings['org_cif']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_registry_number">Nº Registro Oficial</label>
                                    <input type="text" name="org_registry_number" id="org_registry_number" class="form-control" 
                                           value="<?= htmlspecialchars($generalSettings['org_registry_number']['value'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3"><i class="fas fa-address-card"></i> Datos de Contacto</h3>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label" for="org_address">Dirección</label>
                                    <input type="text" name="org_address" id="org_address" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_address']['value'] ?? '') ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_city">Ciudad</label>
                                    <input type="text" name="org_city" id="org_city" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_city']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="org_province">Provincia</label>
                                    <input type="text" name="org_province" id="org_province" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_province']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="org_country">País</label>
                                    <input type="text" name="org_country" id="org_country" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_country']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_phone">Teléfono</label>
                                    <input type="text" name="org_phone" id="org_phone" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_phone']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_email">Email</label>
                                    <input type="email" name="org_email" id="org_email" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_email']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_website">Sitio Web</label>
                                    <input type="url" name="org_website" id="org_website" class="form-control" 
                                           value="<?= htmlspecialchars($contactSettings['org_website']['value'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logo y Branding -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3"><i class="fas fa-palette"></i> Logo y Marca</h3>
                            
                            <?php $currentLogo = $brandingSettings['org_logo']['value'] ?? ''; ?>
                            <?php if ($currentLogo): ?>
                                <div class="mb-3">
                                    <label class="form-label">Logo Actual</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($currentLogo) ?>" alt="Logo" style="max-height: 100px; max-width: 300px;">
                                        <a href="index.php?page=settings&action=deleteLogo" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('¿Eliminar el logo actual?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Subir Nuevo Logo</label>
                                <input type="file" name="org_logo" class="form-control" accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG, GIF, SVG, WEBP. Tamaño máximo: 5MB</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_logo_width">Ancho del Logo (px)</label>
                                    <input type="number" name="org_logo_width" id="org_logo_width" class="form-control" 
                                           value="<?= htmlspecialchars($brandingSettings['org_logo_width']['value'] ?? 180) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_primary_color">Color Primario</label>
                                    <input type="color" name="org_primary_color" id="org_primary_color" class="form-control" 
                                           value="<?= htmlspecialchars($brandingSettings['org_primary_color']['value'] ?? '#6366f1') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_secondary_color">Color Secundario</label>
                                    <input type="color" name="org_secondary_color" id="org_secondary_color" class="form-control" 
                                           value="<?= htmlspecialchars($brandingSettings['org_secondary_color']['value'] ?? '#8b5cf6') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Representantes Legales -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3"><i class="fas fa-user-tie"></i> Representantes Legales</h3>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_president_name">Presidente/a</label>
                                    <input type="text" name="org_president_name" id="org_president_name" class="form-control" 
                                           value="<?= htmlspecialchars($legalSettings['org_president_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_secretary_name">Secretario/a</label>
                                    <input type="text" name="org_secretary_name" id="org_secretary_name" class="form-control" 
                                           value="<?= htmlspecialchars($legalSettings['org_secretary_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_treasurer_name">Tesorero/a</label>
                                    <input type="text" name="org_treasurer_name" id="org_treasurer_name" class="form-control" 
                                           value="<?= htmlspecialchars($legalSettings['org_treasurer_name']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="org_legal_text">Texto Legal (para documentos)</label>
                                <textarea name="org_legal_text" id="org_legal_text" class="form-control" rows="3"><?= htmlspecialchars($legalSettings['org_legal_text']['value'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </div>
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
                                <label class="form-label" for="price_year">Año</label>
                                <input type="number" name="year" id="price_year" class="form-control" value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="price_media">Media Página (€)</label>
                                <input type="number" step="0.01" name="price_media" id="price_media" class="form-control" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="price_full">Página Completa (€)</label>
                                <input type="number" step="0.01" name="price_full" id="price_full" class="form-control" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="price_cover">Portada (€)</label>
                                <input type="number" step="0.01" name="price_cover" id="price_cover" class="form-control" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="price_back_cover">Contraportada (€)</label>
                                <input type="number" step="0.01" name="price_back_cover" id="price_back_cover" class="form-control" placeholder="0.00">
                            </div>
                            <button type="submit" class="btn btn-primary w-full">Guardar Precios</button>
                        </form>
                    </div>
                    <!-- Current Prices Display -->
                    <div>
                        <div class="card mb-4">
                            <h3 class="text-md font-semibold mb-2">Precios <?php echo isset($currentYear) ? $currentYear : date('Y'); ?></h3>
                            <?php if (empty($adPrices)): ?>
                                <div class="alert alert-info">No hay precios definidos para este año.</div>
                            <?php else: ?>
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
                            <?php endif; ?>
                        </div>
                        <div class="card">
                            <h3 class="text-md font-semibold mb-2">Precios <?php echo isset($currentYear) ? $currentYear + 1 : date('Y') + 1; ?></h3>
                            <?php if (empty($nextYearPrices)): ?>
                                <div class="alert alert-info">No hay precios definidos para el próximo año.</div>
                            <?php else: ?>
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
                            <?php endif; ?>
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
                                <label class="form-label" for="fee_year">Año</label>
                                <input type="number" name="year" id="fee_year" class="form-control" value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="fee_amount">Importe (€)</label>
                                <input type="number" step="0.01" name="amount" id="fee_amount" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-save"></i> Guardar Cuota
                            </button>
                        </form>
                    </div>
                    <!-- List Fees -->
                    <div class="card">
                        <h3 class="text-md font-semibold mb-4">Cuotas Definidas</h3>
                        <?php if (empty($fees)): ?>
                            <div class="alert alert-info">No hay cuotas definidas.</div>
                        <?php else: ?>
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
                        <?php endif; ?>
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
                        <label class="form-label" for="db_host">Host</label>
                        <input type="text" name="db_host" id="db_host" class="form-control" value="<?php echo htmlspecialchars($dbConfig['host'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="db_name">Nombre de la Base de Datos</label>
                        <input type="text" name="db_name" id="db_name" class="form-control" value="<?php echo htmlspecialchars($dbConfig['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="db_user">Usuario</label>
                        <input type="text" name="db_user" id="db_user" class="form-control" value="<?php echo htmlspecialchars($dbConfig['user'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="db_pass">Contraseña</label>
                        <input type="password" name="db_pass" id="db_pass" class="form-control" placeholder="Dejar en blanco para no cambiar (si se muestra vacío)">
                        <small>Por seguridad, la contraseña actual no se muestra.</small>
                    </div>
                    <button type="submit" class="btn btn-danger">Actualizar Conexión</button>
                </form>
            </div>

            <!-- Security Tab -->
            <div id="security" class="tab-content" style="display: none;">
                <h2 style="margin-bottom: 2rem; color: var(--text-color);">
                    <i class="fas fa-shield-alt" style="color: var(--primary-600);"></i> Seguridad
                </h2>
                <?php if (isset($_SESSION['password_success'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['password_success'] ?>
                    </div>
                    <?php unset($_SESSION['password_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['password_error'])): ?>
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['password_error'] ?>
                    </div>
                    <?php unset($_SESSION['password_error']); ?>
                <?php endif; ?>
                <form method="POST" action="index.php?page=settings&action=changePassword" class="card" style="max-width: 600px;">
                    <h3 style="margin-bottom: 1.5rem; color: var(--text-color);">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </h3>
                    <div class="form-group">
                        <label class="form-label" for="current_password">
                            <i class="fas fa-lock"></i> Contraseña Actual
                        </label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required 
                               placeholder="Introduce tu contraseña actual">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_password">
                            <i class="fas fa-lock"></i> Nueva Contraseña
                        </label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required 
                               placeholder="Mínimo 6 caracteres" minlength="6">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                            Mínimo 6 caracteres. Se recomienda usar una combinación de letras, números y símbolos.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">
                            <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required 
                               placeholder="Repite la nueva contraseña" minlength="6">
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cambiar Contraseña
                        </button>
                    </div>
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
    var tabcontent = document.getElementsByClassName("tab-content");
    var tablinks = document.getElementsByClassName("tab-btn");
    // Oculta todas las pestañas
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    // Quita la clase activa de todos los botones
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    // Muestra la pestaña seleccionada
    var activeTab = document.getElementById(tabName);
    if (activeTab) {
        activeTab.style.display = "block";
    }
    evt.currentTarget.className += " active";
    // Actualiza la URL
    if (history.pushState) {
        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=settings&tab=' + tabName;
        history.pushState({path:newurl},'',newurl);
    }
}

// Mostrar la pestaña correcta al cargar la página
window.addEventListener('DOMContentLoaded', function() {
    var tab = (new URLSearchParams(window.location.search)).get('tab') || 'organization';
    var tabcontent = document.getElementsByClassName("tab-content");
    var tablinks = document.getElementsByClassName("tab-btn");
    // Oculta todas las pestañas
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    // Muestra la pestaña activa
    var activeTab = document.getElementById(tab);
    if (activeTab) {
        activeTab.style.display = "block";
    }
    // Marca el botón activo
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
        if (tablinks[i].getAttribute('onclick') && tablinks[i].getAttribute('onclick').includes(tab)) {
            tablinks[i].className += " active";
        }
    }
});
</script>
<noscript>
<style>
    .tab-content { display: block !important; }
</style>
<div class="alert alert-warning">La navegación por pestañas requiere JavaScript. Todas las secciones se muestran abajo.</div>
</noscript>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
