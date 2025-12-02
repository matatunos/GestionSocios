<?php
require_once __DIR__ . '/../layout.php';
?>

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
</style>
