<?php ob_start(); ?>

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
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-cog"></i> Configuración</h1>
    </div>
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('organization')" id="tab-organization"><i class="fas fa-building"></i> Organización</button>
            <button class="tab-btn" onclick="switchTab('members')" id="tab-members"><i class="fas fa-users"></i> Socios</button>
            <button class="tab-btn" onclick="switchTab('ad_prices')" id="tab-ad_prices"><i class="fas fa-tags"></i> Precios Anuncios</button>
            <button class="tab-btn" onclick="switchTab('database')" id="tab-database"><i class="fas fa-database"></i> Base de Datos</button>
            <button class="tab-btn" onclick="switchTab('admin_users')" id="tab-admin_users"><i class="fas fa-user-shield"></i> Administración de Usuarios</button>
        </div>
        <!-- Organization Tab -->
        <div id="organization" class="tab-content active-tab">
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
                                </div>
                            </div>
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
                <div class="d-flex justify-content-center">
                    <form action="index.php?page=settings&action=downloadBackup" method="POST">
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

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
