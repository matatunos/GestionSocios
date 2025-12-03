<?php ob_start(); ?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-calculator"></i> Dashboard de Contabilidad</h1>
        <div class="header-actions">
            <a href="index.php?page=accounting&action=accounts" class="btn btn-primary">
                <i class="fas fa-book"></i> Plan de Cuentas
            </a>
            <a href="index.php?page=accounting&action=createEntry" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Asiento
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_entries'] ?? 0); ?></div>
                <div class="stat-label">Asientos Totales</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['posted_entries'] ?? 0); ?></div>
                <div class="stat-label">Asientos Contabilizados</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_accounts'] ?? 0); ?></div>
                <div class="stat-label">Cuentas Activas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $currentPeriod['name'] ?? 'N/A'; ?></div>
                <div class="stat-label">Período Actual</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Acceso Rápido</h2>
            </div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="index.php?page=accounting&action=accounts" class="quick-link">
                        <i class="fas fa-book"></i>
                        <span>Plan de Cuentas</span>
                    </a>
                    <a href="index.php?page=accounting&action=entries" class="quick-link">
                        <i class="fas fa-file-invoice"></i>
                        <span>Libro Diario</span>
                    </a>
                    <a href="index.php?page=accounting&action=generalLedger" class="quick-link">
                        <i class="fas fa-book-open"></i>
                        <span>Libro Mayor</span>
                    </a>
                    <a href="index.php?page=accounting&action=trialBalance" class="quick-link">
                        <i class="fas fa-balance-scale"></i>
                        <span>Balance de Sumas y Saldos</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Información del Período</h2>
            </div>
            <div class="card-body">
                <?php if ($currentPeriod): ?>
                    <div class="period-info">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($currentPeriod['name']); ?></p>
                        <p><strong>Año Fiscal:</strong> <?php echo htmlspecialchars($currentPeriod['fiscal_year']); ?></p>
                        <p><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($currentPeriod['start_date'])); ?></p>
                        <p><strong>Fecha Fin:</strong> <?php echo date('d/m/Y', strtotime($currentPeriod['end_date'])); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge badge-<?php echo $currentPeriod['status'] === 'open' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($currentPeriod['status']); ?>
                            </span>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No hay un período contable abierto. Por favor, contacte al administrador.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.stat-icon.blue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon.green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-icon.purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.stat-icon.orange {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
}

.stat-label {
    color: #374151;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    font-weight: 500;
}

/* Quick Links */
.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-color);
    transition: all 0.2s;
}

.quick-link:hover {
    background: var(--hover-bg);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.quick-link i {
    font-size: 2rem;
    color: var(--primary-color);
}

.period-info p {
    margin: 0.75rem 0;
    padding: 0.5rem;
    background: var(--card-bg);
    border-radius: 4px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-card .card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.dashboard-card .card-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.dashboard-card .card-body {
    padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .quick-links {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .quick-links {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
