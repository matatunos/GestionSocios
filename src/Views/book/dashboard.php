<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1><i class="fas fa-book-open" style="color: var(--primary-600); margin-right: 0.5rem;"></i> Libro de Fiestas <?php echo $year; ?></h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">Resumen financiero y gestión de contenidos</p>
    </div>
    
    <div>
        <form action="index.php" method="GET" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <input type="hidden" name="page" value="book">
            <input type="hidden" name="action" value="dashboard">
            <select name="year" onchange="this.form.submit()" class="form-select">
                <?php 
                $currentYear = date('Y');
                for($y = $currentYear + 1; $y >= $currentYear - 5; $y--): 
                ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    
    <!-- Ingresos Totales -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Ingresos Confirmados</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--secondary-600);">
                    <?php echo number_format($incomeStats['total_income'] ?? 0, 2); ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--secondary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-coins" style="font-size: 1.5rem; color: var(--secondary-600);"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            <span style="color: #d97706; font-weight: 600;">
                <?php echo number_format($incomeStats['pending_income'] ?? 0, 2); ?> €
            </span> pendientes
        </div>
    </div>

    <!-- Gastos -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Gastos (Imprenta/Otros)</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--danger-600);">
                    <?php echo number_format($expenseStats['total_cost'] ?? 0, 2); ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--danger-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-receipt" style="font-size: 1.5rem; color: var(--danger-600);"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            <?php echo $expenseStats['total_expenses'] ?? 0; ?> movimientos registrados
        </div>
    </div>

    <!-- Resultado Neto -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Resultado Neto</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: <?php echo $netResult >= 0 ? 'var(--primary-600)' : 'var(--danger-600)'; ?>;">
                    <?php echo number_format($netResult, 2); ?> €
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: <?php echo $netResult >= 0 ? 'var(--primary-100)' : 'var(--danger-100)'; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chart-line" style="font-size: 1.5rem; color: <?php echo $netResult >= 0 ? 'var(--primary-600)' : 'var(--danger-600)'; ?>;"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Balance final estimado
        </div>
    </div>

    <!-- Estadísticas Anuncios -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Total Anuncios</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?php echo $incomeStats['total_ads'] ?? 0; ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: #dbeafe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-ad" style="font-size: 1.5rem; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; font-size: 0.75rem;">
            <span class="badge badge-secondary">Full: <?php echo $incomeStats['full_page_count']; ?></span>
            <span class="badge badge-secondary">Media: <?php echo $incomeStats['half_page_count']; ?></span>
        </div>
    </div>
</div>

<!-- Main Actions Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Gestión Anunciantes -->
    <a href="index.php?page=book&year=<?php echo $year; ?>" class="card" style="text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 48px; height: 48px; background: #dbeafe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-users" style="font-size: 1.5rem; color: #3b82f6;"></i>
            </div>
            <h3 style="margin: 0; color: var(--text-main);">Anunciantes</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
            Gestionar empresas, anuncios contratados y estados de pago.
        </p>
        <div style="color: #3b82f6; font-size: 0.875rem; font-weight: 500;">
            Gestionar anuncios <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
        </div>
    </a>

    <!-- Gestión Actividades -->
    <a href="index.php?page=book_activities&year=<?php echo $year; ?>" class="card" style="text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 48px; height: 48px; background: #f3e8ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-day" style="font-size: 1.5rem; color: #a855f7;"></i>
            </div>
            <h3 style="margin: 0; color: var(--text-main);">Actividades</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
            Añadir páginas de contenido, saludas, programa de actos y fotos.
        </p>
        <div style="color: #a855f7; font-size: 0.875rem; font-weight: 500;">
            Gestionar contenido <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
        </div>
    </a>

    <!-- Maquetación y Exportación -->
    <a href="index.php?page=book_export&year=<?php echo $year; ?>" class="card" style="text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 48px; height: 48px; background: #f3f4f6; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #6b7280;"></i>
            </div>
            <h3 style="margin: 0; color: var(--text-main);">Maquetación</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
            Organizar el orden de las páginas y generar el PDF final para imprenta.
        </p>
        <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">
            Generar PDF <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
        </div>
    </a>
</div>

<!-- Recent Activity -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">Últimos Anuncios Registrados</h2>
        <a href="index.php?page=book&year=<?php echo $year; ?>" class="btn btn-sm btn-secondary">Ver todos</a>
    </div>
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Donante</th>
                    <th>Tipo</th>
                    <th style="text-align: right;">Importe</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAds)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            No hay anuncios registrados para este año.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentAds as $ad): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <?php echo htmlspecialchars($ad['donor_name']); ?>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php 
                                $types = [
                                    'full' => 'Página Completa',
                                    'media' => 'Media Página',
                                    'cover' => 'Portada',
                                    'back_cover' => 'Contraportada'
                                ];
                                echo $types[$ad['ad_type']] ?? $ad['ad_type']; 
                                ?>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?php echo number_format($ad['amount'], 2); ?> €
                            </td>
                            <td>
                                <?php if ($ad['status'] === 'paid'): ?>
                                    <span class="badge badge-success">Pagado</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #d97706; color: white;">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php echo date('d/m/Y', strtotime($ad['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
