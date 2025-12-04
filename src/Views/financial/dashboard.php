<?php ob_start(); ?>
<style>
        .financial-dashboard {
            max-width: 1600px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0;
            color: #333;
        }
        
        .period-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .period-selector select {
            padding: 8px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }
        
        /* Alerts */
        .alerts-section {
            margin-bottom: 25px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .stat-card.income::before {
            background: linear-gradient(180deg, #28a745, #20c997);
        }
        
        .stat-card.expense::before {
            background: linear-gradient(180deg, #dc3545, #fd7e14);
        }
        
        .stat-card.balance::before {
            background: linear-gradient(180deg, #667eea, #764ba2);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .stat-card .amount {
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .stat-card.income .amount {
            color: #28a745;
        }
        
        .stat-card.expense .amount {
            color: #dc3545;
        }
        
        .stat-card.balance .amount {
            color: #667eea;
        }
        
        .stat-card .subtitle {
            font-size: 12px;
            color: #6c757d;
        }
        
        /* Chart Section */
        .chart-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .chart-section h2 {
            margin: 0 0 20px 0;
            color: #333;
        }
        
        .chart-container {
            height: 300px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            border-bottom: 2px solid #e9ecef;
            padding: 20px 0;
        }
        
        .chart-bar {
            flex: 1;
            max-width: 60px;
            margin: 0 5px;
            position: relative;
        }
        
        .chart-bar .bar {
            width: 100%;
            background: linear-gradient(180deg, #667eea, #764ba2);
            border-radius: 4px 4px 0 0;
            transition: all 0.3s;
        }
        
        .chart-bar:hover .bar {
            opacity: 0.8;
        }
        
        .chart-bar .label {
            text-align: center;
            font-size: 11px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .chart-bar .value {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            font-weight: 600;
            color: #495057;
        }
        
        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .summary-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-item .label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .summary-item .value {
            font-weight: 600;
            font-size: 15px;
        }
        
        .btn-action {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .btn-action:hover {
            opacity: 0.9;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .quick-action-card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .quick-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .quick-action-card .icon {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .quick-action-card .title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .quick-action-card .subtitle {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="financial-dashboard">
        <div class="page-header">
            <h1>💰 Dashboard Financiero Consolidado</h1>
            
            <form method="GET" class="period-selector">
                <input type="hidden" name="page" value="financial">
                <select name="period" onchange="this.form.submit()">
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Este Mes</option>
                    <option value="quarter" <?= $period === 'quarter' ? 'selected' : '' ?>>Este Trimestre</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Este Año</option>
                </select>
                
                <select name="year" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                
                <?php if ($period === 'month'): ?>
                    <select name="month" onchange="this.form.submit()">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= sprintf('%02d', $m) ?>" <?= $month == sprintf('%02d', $m) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
            <div class="alerts-section">
                <?php foreach ($alerts as $alert): ?>
                    <div class="alert alert-<?= $alert['type'] ?>">
                        <span><?= $alert['message'] ?></span>
                        <a href="<?= $alert['action'] ?>" class="btn-action"><?= $alert['action_label'] ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Main Stats -->
        <div class="stats-grid">
            <div class="stat-card income">
                <h3>💵 Ingresos Totales</h3>
                <p class="amount">+<?= number_format($cashFlow['bank_ingresos'], 2) ?> €</p>
                <p class="subtitle">Movimientos bancarios del período</p>
            </div>
            
            <div class="stat-card expense">
                <h3>💸 Egresos Totales</h3>
                <p class="amount">-<?= number_format($cashFlow['bank_egresos'], 2) ?> €</p>
                <p class="subtitle">Movimientos bancarios del período</p>
            </div>
            
            <div class="stat-card balance">
                <h3>📊 Balance Neto</h3>
                <p class="amount"><?= number_format($cashFlow['net_cashflow'], 2) ?> €</p>
                <p class="subtitle">Diferencia ingresos - egresos</p>
            </div>
            
            <div class="stat-card">
                <h3>📄 Facturas Pendientes</h3>
                <p class="amount"><?= number_format($cashFlow['invoices_pending'], 2) ?> €</p>
                <p class="subtitle"><?= $invoicesData['total_invoices'] ?? 0 ?> facturas emitidas</p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="index.php?page=bank&subpage=import" class="quick-action-card">
                <div class="icon">📥</div>
                <div class="title">Importar Extracto</div>
                <div class="subtitle">Subir archivo CSV/OFX</div>
            </a>
            
            <a href="index.php?page=bank&subpage=matching&action=auto" class="quick-action-card">
                <div class="icon">🔗</div>
                <div class="title">Matching Automático</div>
                <div class="subtitle">Emparejar transacciones</div>
            </a>
            
            <a href="index.php?page=bank&subpage=reconciliation&action=start" class="quick-action-card">
                <div class="icon">✓</div>
                <div class="title">Conciliar Banco</div>
                <div class="subtitle">Verificar saldos</div>
            </a>
            
            <a href="index.php?page=grants&subpage=searches" class="quick-action-card">
                <div class="icon">🔍</div>
                <div class="title">Buscar Subvenciones</div>
                <div class="subtitle">Scraping automático</div>
            </a>
        </div>
        
        <!-- Chart -->
        <div class="chart-section">
            <h2>📈 Evolución Mensual <?= $year ?></h2>
            <div class="chart-container">
                <?php 
                $maxAmount = 0;
                foreach ($monthlyEvolution as $data) {
                    $maxAmount = max($maxAmount, $data['ingresos'], $data['egresos']);
                }
                ?>
                
                <?php foreach ($monthlyEvolution as $data): ?>
                    <div class="chart-bar">
                        <?php 
                        $height = $maxAmount > 0 ? ($data['ingresos'] / $maxAmount * 250) : 0;
                        ?>
                        <div class="value"><?= number_format($data['ingresos'], 0) ?></div>
                        <div class="bar" style="height: <?= $height ?>px;"></div>
                        <div class="label"><?= $data['month_name'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-grid">
            <!-- Bank Summary -->
            <div class="summary-card">
                <h3>🏦 Resumen Bancario</h3>
                <div class="summary-item">
                    <span class="label">Transacciones totales</span>
                    <span class="value"><?= $bankData['total_transactions'] ?? 0 ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Sin emparejar</span>
                    <span class="value" style="color: #dc3545;"><?= $bankData['unmatched_count'] ?? 0 ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Sin conciliar</span>
                    <span class="value" style="color: #ffc107;"><?= $bankData['unreconciled_count'] ?? 0 ?></span>
                </div>
                <div style="margin-top: 15px;">
                    <a href="index.php?page=bank" class="btn-action">Ver Detalle →</a>
                </div>
            </div>
            
            <!-- Invoices Summary -->
            <div class="summary-card">
                <h3>📄 Resumen de Facturas</h3>
                <div class="summary-item">
                    <span class="label">Total facturado</span>
                    <span class="value"><?= number_format($cashFlow['invoices_total'], 2) ?> €</span>
                </div>
                <div class="summary-item">
                    <span class="label">Cobrado</span>
                    <span class="value" style="color: #28a745;"><?= number_format($cashFlow['invoices_paid'], 2) ?> €</span>
                </div>
                <div class="summary-item">
                    <span class="label">Pendiente</span>
                    <span class="value" style="color: #dc3545;"><?= number_format($cashFlow['invoices_pending'], 2) ?> €</span>
                </div>
                <div style="margin-top: 15px;">
                    <a href="index.php?page=invoices" class="btn-action">Ver Facturas →</a>
                </div>
            </div>
            
            <!-- Grants Summary -->
            <div class="summary-card">
                <h3>🎯 Resumen de Subvenciones</h3>
                <div class="summary-item">
                    <span class="label">Subvenciones identificadas</span>
                    <span class="value"><?= $grantsData['total_grants'] ?? 0 ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Solicitudes presentadas</span>
                    <span class="value"><?= $grantsData['total_applications'] ?? 0 ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Importe concedido</span>
                    <span class="value" style="color: #28a745;"><?= number_format($cashFlow['grants_granted'], 2) ?> €</span>
                </div>
                <div style="margin-top: 15px;">
                    <a href="index.php?page=grants" class="btn-action">Ver Subvenciones →</a>
                </div>
            </div>
            
            <!-- Expenses Summary -->
            <div class="summary-card">
                <h3>💳 Resumen de Gastos</h3>
                <div class="summary-item">
                    <span class="label">Total gastos</span>
                    <span class="value" style="color: #dc3545;"><?= number_format($cashFlow['expenses_total'], 2) ?> €</span>
                </div>
                <div class="summary-item">
                    <span class="label">Gastos registrados</span>
                    <span class="value"><?= $expensesData['total_expenses'] ?? 0 ?></span>
                </div>
                <div style="margin-top: 15px;">
                    <a href="index.php?page=accounting" class="btn-action">Ver Contabilidad →</a>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 12px;">
            <p style="margin: 0; color: #6c757d;">
                <strong>💡 Consejo:</strong> Ejecuta el matching automático regularmente para mantener tus movimientos bancarios vinculados con facturas y subvenciones.
            </p>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
