

<style>
.tab-content { display: none; }
.tabs-system input[type="radio"] {
    position: absolute;
    left: -9999px;
}
.tabs-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    border-bottom: 3px solid var(--border-light);
    background: var(--bg-card);
    margin-bottom: 0;
    z-index: 2;
}
.tabs-system #tab-organization:checked ~ .tabs-nav label[for="tab-organization"],
.tabs-system #tab-fees:checked ~ .tabs-nav label[for="tab-fees"],
.tabs-system #tab-ad_prices:checked ~ .tabs-nav label[for="tab-ad_prices"],
.tabs-system #tab-database:checked ~ .tabs-nav label[for="tab-database"],
.tabs-system #tab-security:checked ~ .tabs-nav label[for="tab-security"] {
    background: var(--primary-100, #e0e7ff);
    color: var(--primary-700, #3730a3);
    font-weight: bold;
    border-radius: 8px 8px 0 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.tabs-system #tab-organization:checked ~ .tab-content-wrapper #organization,
.tabs-system #tab-fees:checked ~ .tab-content-wrapper #fees,
.tabs-system #tab-ad_prices:checked ~ .tab-content-wrapper #ad_prices,
.tabs-system #tab-database:checked ~ .tab-content-wrapper #database,
.tabs-system #tab-security:checked ~ .tab-content-wrapper #security {
    display: block;
}
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

.tab-content { display: none; }
.tab-content.active-tab { display: block; }

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
    .category-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow-md);
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    .category-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }
    .category-info h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .category-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }
    .category-inactive { opacity: 0.6; }
    .category-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-light);
    }
    .stat-item { text-align: center; }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-600);
    }
    .stat-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }
    .category-actions { display: flex; gap: 0.5rem; }

    /* Organization Settings Redesign */
    .settings-section-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-light);
    }
    .settings-section-header h2 {
        font-size: 1.75rem;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    
    .settings-grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .settings-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .settings-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .settings-card .card-header {
        padding: 1.5rem;
        background: var(--bg-main);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .bg-primary-light { background: var(--primary-50); color: var(--primary-600); }
    .bg-success-light { background: #dcfce7; color: #166534; }
    .bg-purple-light { background: #f3e8ff; color: #7e22ce; }
    .bg-warning-light { background: #fef9c3; color: #854d0e; }
    
    .header-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--text-main);
    }
    .header-text p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    
    .settings-card .card-body {
        padding: 1.5rem;
    }
    
    .form-actions-sticky {
        position: sticky;
        bottom: 2rem;
        background: var(--bg-card);
        padding: 1rem 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
        display: flex;
        justify-content: flex-end;
        z-index: 10;
        margin-top: 2rem;
    }
    
    .logo-preview-container {
        width: 100%;
        height: 140px;
        background: var(--bg-main);
        border: 2px dashed var(--border-light);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .placeholder-logo {
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .settings-grid-layout {
            grid-template-columns: 1fr;
        }
        .form-actions-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 0;
            border-top: 1px solid var(--border-light);
        }
    }
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-cog"></i> Configuración</h1>
    </div>
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn" onclick="switchTab('organization')" id="tab-organization"><i class="fas fa-building"></i> Organización</button>
            <button class="tab-btn" onclick="switchTab('members')" id="tab-members"><i class="fas fa-users"></i> Socios</button>
            <button class="tab-btn" onclick="switchTab('ad_prices')" id="tab-ad_prices"><i class="fas fa-tags"></i> Precios Anuncios</button>
            <button class="tab-btn" onclick="switchTab('database')" id="tab-database"><i class="fas fa-database"></i> Base de Datos</button>
            <button class="tab-btn" onclick="switchTab('admin_users')" id="tab-admin_users"><i class="fas fa-user-shield"></i> Administración de Usuarios</button>
            <button class="tab-btn" onclick="switchTab('notifications')" id="tab-notifications"><i class="fas fa-bell"></i> Notificaciones</button>
        </div>
        <!-- Organization Tab -->
                <div id="notifications" class="tab-content">
                    <?php require __DIR__ . '/notifications.php'; ?>
                </div>
        <div id="organization" class="tab-content active-tab">
            <div class="settings-section-header">
                <div>
                    <h2 class="section-title"><i class="fas fa-building"></i> Configuración de la Organización</h2>
                    <p class="text-muted">Gestiona la información pública, legal y de contacto de tu entidad.</p>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="index.php?page=settings&action=updateOrganization" method="POST" enctype="multipart/form-data">
                <div class="settings-grid-layout">
                    
                    <!-- Información General -->
                    <div class="settings-card">
                        <div class="card-header">
                            <div class="header-icon bg-primary-light text-primary">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="header-text">
                                <h3>Información General</h3>
                                <p>Datos básicos de identificación</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4">
                                <label class="form-label" for="org_name">Nombre Completo de la Entidad</label>
                                <input type="text" name="org_name" id="org_name" class="form-control form-control-lg" value="<?= htmlspecialchars($generalSettings['org_name']['value'] ?? '') ?>" placeholder="Ej. Asociación Cultural...">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_short_name">Siglas / Acrónimo</label>
                                    <input type="text" name="org_short_name" id="org_short_name" class="form-control" value="<?= htmlspecialchars($generalSettings['org_short_name']['value'] ?? '') ?>" placeholder="Ej. AC...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_founded_year">Año de Fundación</label>
                                    <input type="number" name="org_founded_year" id="org_founded_year" class="form-control" value="<?= htmlspecialchars($generalSettings['org_founded_year']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_cif">CIF / NIF</label>
                                    <input type="text" name="org_cif" id="org_cif" class="form-control" value="<?= htmlspecialchars($generalSettings['org_cif']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_registry_number">Nº Registro Oficial</label>
                                    <input type="text" name="org_registry_number" id="org_registry_number" class="form-control" value="<?= htmlspecialchars($generalSettings['org_registry_number']['value'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="settings-card">
                        <div class="card-header">
                            <div class="header-icon bg-success-light text-success">
                                <i class="fas fa-address-card"></i>
                            </div>
                            <div class="header-text">
                                <h3>Datos de Contacto</h3>
                                <p>Dirección y medios de comunicación</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label" for="org_address">Dirección Postal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="org_address" id="org_address" class="form-control" value="<?= htmlspecialchars($contactSettings['org_address']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" for="org_city">Ciudad</label>
                                    <input type="text" name="org_city" id="org_city" class="form-control" value="<?= htmlspecialchars($contactSettings['org_city']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="org_province">Provincia</label>
                                    <input type="text" name="org_province" id="org_province" class="form-control" value="<?= htmlspecialchars($contactSettings['org_province']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="org_country">País</label>
                                    <input type="text" name="org_country" id="org_country" class="form-control" value="<?= htmlspecialchars($contactSettings['org_country']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="org_email">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="org_email" id="org_email" class="form-control" value="<?= htmlspecialchars($contactSettings['org_email']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_phone">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" name="org_phone" id="org_phone" class="form-control" value="<?= htmlspecialchars($contactSettings['org_phone']['value'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_website">Sitio Web</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="url" name="org_website" id="org_website" class="form-control" value="<?= htmlspecialchars($contactSettings['org_website']['value'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logo y Marca -->
                    <div class="settings-card">
                        <div class="card-header">
                            <div class="header-icon bg-purple-light text-purple">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="header-text">
                                <h3>Identidad Visual</h3>
                                <p>Logo y colores corporativos</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center mb-4">
                                <div class="col-md-4 text-center">
                                    <?php $currentLogo = $brandingSettings['org_logo']['value'] ?? ''; ?>
                                    <div class="logo-preview-container mb-2">
                                        <?php if ($currentLogo): ?>
                                            <img src="<?= htmlspecialchars($currentLogo) ?>" alt="Logo Actual" class="img-fluid rounded" style="max-height: 120px;">
                                        <?php else: ?>
                                            <div class="placeholder-logo">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                                <p class="small text-muted mt-2">Sin logo</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($currentLogo): ?>
                                        <a href="index.php?page=settings&action=deleteLogo" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar el logo actual?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Subir Nuevo Logo</label>
                                        <input type="file" name="org_logo" class="form-control" accept="image/*">
                                        <small class="text-muted d-block mt-1">Recomendado: PNG transparente. Máx 5MB.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="org_logo_width">Ancho de visualización (px)</label>
                                        <input type="number" name="org_logo_width" id="org_logo_width" class="form-control" style="max-width: 150px;" value="<?= htmlspecialchars($brandingSettings['org_logo_width']['value'] ?? 180) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_primary_color">Color Primario</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="org_primary_color" id="org_primary_color" class="form-control form-control-color" value="<?= htmlspecialchars($brandingSettings['org_primary_color']['value'] ?? '#6366f1') ?>">
                                        <span class="text-muted small"><?= htmlspecialchars($brandingSettings['org_primary_color']['value'] ?? '#6366f1') ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_secondary_color">Color Secundario</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="org_secondary_color" id="org_secondary_color" class="form-control form-control-color" value="<?= htmlspecialchars($brandingSettings['org_secondary_color']['value'] ?? '#8b5cf6') ?>">
                                        <span class="text-muted small"><?= htmlspecialchars($brandingSettings['org_secondary_color']['value'] ?? '#8b5cf6') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Representantes Legales -->
                    <div class="settings-card">
                        <div class="card-header">
                            <div class="header-icon bg-warning-light text-warning">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="header-text">
                                <h3>Junta Directiva</h3>
                                <p>Representantes legales actuales</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_president_name">Presidente/a</label>
                                    <input type="text" name="org_president_name" id="org_president_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_president_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_vicepresident_name">Vicepresidente/a</label>
                                    <input type="text" name="org_vicepresident_name" id="org_vicepresident_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_vicepresident_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_secretary_name">Secretario/a</label>
                                    <input type="text" name="org_secretary_name" id="org_secretary_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_secretary_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_treasurer_name">Tesorero/a</label>
                                    <input type="text" name="org_treasurer_name" id="org_treasurer_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_treasurer_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_vocal1_name">Vocal 1</label>
                                    <input type="text" name="org_vocal1_name" id="org_vocal1_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_vocal1_name']['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="org_vocal2_name">Vocal 2</label>
                                    <input type="text" name="org_vocal2_name" id="org_vocal2_name" class="form-control" value="<?= htmlspecialchars($legalSettings['org_vocal2_name']['value'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="org_legal_text">Texto Legal (Pie de documentos)</label>
                                <textarea name="org_legal_text" id="org_legal_text" class="form-control" rows="3" placeholder="Texto que aparecerá en el pie de página de facturas y documentos oficiales..."><?= htmlspecialchars($legalSettings['org_legal_text']['value'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="form-actions-sticky">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        <!-- Ad Prices Tab -->
        <div id="ad_prices" class="tab-content">
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
        <div id="members" class="tab-content">
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

                <hr style="margin: 3rem 0; border-color: var(--border-light);">
            
            
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="section-title" style="margin: 0;">Categorías de Socios</h2>
                    <a href="index.php?page=member_categories&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </a>
                </div>
                
                <!-- Categories List -->
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card <?php echo $category['is_active'] ? '' : 'category-inactive'; ?>" 
                             style="border-left-color: <?php echo htmlspecialchars($category['color']); ?>">
                            <div class="category-header">
                                <div class="category-info">
                                    <h3>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <?php if (!$category['is_active']): ?>
                                            <span class="category-badge" style="background: #9ca3af;">Inactiva</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p style="color: var(--text-muted); font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($category['description'] ?: 'Sin descripción'); ?>
                                    </p>
                                </div>
                                <div class="category-actions">
                                    <a href="index.php?page=member_categories&action=edit&id=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="index.php?page=member_categories&action=delete&id=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="category-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo number_format($category['default_fee'], 2); ?> €</div>
                                    <div class="stat-label">Cuota predeterminada</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value" style="color: <?php echo htmlspecialchars($category['color']); ?>">
                                        <?php
                                        $catStat = null;
                                        foreach ($statistics as $s) {
                                            if ($s['id'] == $category['id']) {
                                                $catStat = $s;
                                                break;
                                            }
                                        }
                                        echo $catStat ? $catStat['member_count'] : 0;
                                        ?>
                                    </div>
                                    <div class="stat-label">Socios asignados</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value" style="color: var(--text-muted);"><?php echo $category['display_order']; ?></div>
                                    <div class="stat-label">Orden de visualización</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
        </div>  <!-- End of members tab -->
        <!-- Database Tab -->
        <div id="database" class="tab-content">
            <h2 class="section-title"><i class="fas fa-database"></i> Configuración y Backup de Base de Datos</h2>
            <div class="card alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle"></i> <strong>Cuidado:</strong> Cambiar estos valores puede dejar la aplicación inoperativa. Asegúrese de que los nuevos datos son correctos.
            </div>
            <form action="index.php?page=settings&action=updateDatabase" method="POST" class="card mb-4" style="max-width: 600px; margin: 0 auto;">
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
            <div class="card text-center p-4 mb-4">
                <div class="text-center">
                    <form action="index.php?page=settings&action=downloadBackup" method="POST" class="w-auto mx-auto">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-download"></i> Descargar Backup de Base de Datos
                        </button>
                    </form>
                </div>
                <p class="mt-3 text-muted"><i class="fas fa-info-circle"></i> El backup incluye toda la estructura y datos actuales en formato SQL.</p>
            </div>
        </div>
        <!-- Administración de Usuarios Tab -->
        <div id="admin_users" class="tab-content">
            <?php include __DIR__ . '/admin_users.php'; ?>
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
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active-tab');
    });
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.classList.remove('active');
    });
    // Show selected tab
    document.getElementById(tabName).classList.add('active-tab');
    document.getElementById('tab-' + tabName).classList.add('active');
    if (history.pushState) {
        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=settings&tab=' + tabName;
        history.pushState({path:newurl},'',newurl);
    }
}
window.addEventListener('DOMContentLoaded', function() {
    var tab = (new URLSearchParams(window.location.search)).get('tab') || 'organization';
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active-tab');
    });
    var activeTab = document.getElementById(tab);
    if (activeTab) {
        activeTab.classList.add('active-tab');
    } else {
        document.querySelectorAll('.tab-content')[0].classList.add('active-tab');
    }
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.classList.remove('active');
        if (button.id === 'tab-' + tab) {
            button.classList.add('active');
        }
    });
});
</script>
<noscript>
<style>
    .tab-content { display: block !important; }
</style>
<div class="alert alert-warning">La navegación por pestañas requiere JavaScript. Todas las secciones se muestran abajo.</div>
</noscript>


