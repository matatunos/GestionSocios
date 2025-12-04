<?php
$pageTitle = 'Dashboard Bancario';
ob_start();
?>

<style>
        .bank-dashboard {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card.alerts {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .stat-card .amount {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        
        .accounts-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            margin: 0;
            color: #333;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        
        .account-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .account-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
        }
        
        .account-card.default {
            border-color: #28a745;
            background: #f0fff4;
        }
        
        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .account-header h3 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-default {
            background: #d4edda;
            color: #155724;
        }
        
        .account-balance {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        .account-iban {
            font-size: 13px;
            color: #6c757d;
            font-family: monospace;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .transactions-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        
        .transactions-table tr:hover {
            background: #f8f9fa;
        }
        
        .amount-ingreso {
            color: #28a745;
            font-weight: 600;
        }
        
        .amount-egreso {
            color: #dc3545;
            font-weight: 600;
        }
        
        .badge-matched {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-unmatched {
            background: #fff3cd;
            color: #856404;
        }
        
        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-box.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="bank-dashboard">
        <div class="page-header">
            <h1>💳 Dashboard Bancario</h1>
            <p style="color: #6c757d; margin: 5px 0 0 0;">
                Gestión centralizada de cuentas bancarias, transacciones y conciliación
            </p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card total">
                <h3>💰 Saldo Total</h3>
                <p class="amount"><?= number_format($totalBalance, 2) ?> €</p>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 13px;">
                    <?= count($accounts) ?> cuenta<?= count($accounts) != 1 ? 's' : '' ?> activa<?= count($accounts) != 1 ? 's' : '' ?>
                </p>
            </div>
            
            <div class="stat-card alerts">
                <h3>⚠️ Alertas Pendientes</h3>
                <p class="amount"><?= $alerts['unmatched'] + $alerts['unreconciled'] ?></p>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 13px;">
                    <?= $alerts['unmatched'] ?> sin emparejar · <?= $alerts['unreconciled'] ?> sin conciliar
                </p>
            </div>
        </div>
        
        <?php if ($alerts['unmatched'] > 0 || $alerts['unreconciled'] > 0): ?>
            <div class="alert-box warning">
                <strong>⚠️ Atención:</strong>
                Tienes <?= $alerts['unmatched'] ?> transacciones sin emparejar y <?= $alerts['unreconciled'] ?> sin conciliar.
                <a href="index.php?page=bank&subpage=matching" style="color: #856404; text-decoration: underline;">
                    Ejecutar matching automático
                </a>
            </div>
        <?php endif; ?>
        
        <div class="accounts-section">
            <div class="section-header">
                <h2>Cuentas Bancarias</h2>
                <a href="index.php?page=bank&subpage=accounts&action=create" class="btn-primary">
                    ➕ Nueva Cuenta
                </a>
            </div>
            
            <?php if (empty($accounts)): ?>
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>No hay cuentas bancarias registradas</p>
                    <a href="index.php?page=bank&subpage=accounts&action=create" class="btn-primary">
                        Crear Primera Cuenta
                    </a>
                </div>
            <?php else: ?>
                <div class="accounts-grid">
                    <?php foreach ($accounts as $account): ?>
                        <div class="account-card <?= $account['is_default'] ? 'default' : '' ?>">
                            <div class="account-header">
                                <h3><?= htmlspecialchars($account['bank_name']) ?></h3>
                                <?php if ($account['is_default']): ?>
                                    <span class="badge badge-default">Predeterminada</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="account-balance">
                                <?= number_format($account['current_balance'], 2) ?> €
                            </div>
                            
                            <div class="account-iban">
                                <?= htmlspecialchars($account['iban']) ?>
                            </div>
                            
                            <div style="margin-top: 15px; display: flex; gap: 10px;">
                                <a href="index.php?page=bank&subpage=accounts&action=view&id=<?= $account['id'] ?>"
                                   style="flex: 1; text-align: center; padding: 8px; background: #667eea; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">
                                    Ver Detalle
                                </a>
                                <a href="index.php?page=bank&subpage=transactions&account_id=<?= $account['id'] ?>"
                                   style="flex: 1; text-align: center; padding: 8px; background: #6c757d; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">
                                    Movimientos
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="accounts-section">
            <div class="section-header">
                <h2>Movimientos Recientes</h2>
                <a href="index.php?page=bank&subpage=transactions" class="btn-primary">
                    Ver Todos
                </a>
            </div>
            
            <?php if (empty($recentTransactions)): ?>
                <p style="text-align: center; color: #6c757d; padding: 20px;">
                    No hay transacciones registradas
                </p>
            <?php else: ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cuenta</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th style="text-align: right;">Importe</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                                <td style="font-size: 12px;"><?= htmlspecialchars(substr($tx['iban'], -4)) ?></td>
                                <td>
                                    <a href="index.php?page=bank&subpage=transactions&action=view&id=<?= $tx['id'] ?>"
                                       style="color: #667eea; text-decoration: none;">
                                        <?= htmlspecialchars(substr($tx['description'], 0, 50)) ?>
                                    </a>
                                </td>
                                <td><?= ucfirst($tx['type']) ?></td>
                                <td style="text-align: right;" class="amount-<?= $tx['type'] ?>">
                                    <?= $tx['type'] === 'ingreso' ? '+' : '-' ?>
                                    <?= number_format(abs($tx['amount']), 2) ?> €
                                </td>
                                <td>
                                    <?php if ($tx['is_matched']): ?>
                                        <span class="badge badge-matched">Emparejada</span>
                                    <?php else: ?>
                                        <span class="badge badge-unmatched">Sin emparejar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
            <a href="index.php?page=bank&subpage=import" 
               style="padding: 20px; background: white; border-radius: 12px; text-decoration: none; color: #333; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 36px; margin-bottom: 10px;">📥</div>
                <div style="font-weight: 600;">Importar Extracto</div>
                <div style="font-size: 13px; color: #6c757d; margin-top: 5px;">CSV / OFX</div>
            </a>
            
            <a href="index.php?page=bank&subpage=reconciliation" 
               style="padding: 20px; background: white; border-radius: 12px; text-decoration: none; color: #333; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 36px; margin-bottom: 10px;">✓</div>
                <div style="font-weight: 600;">Conciliación</div>
                <div style="font-size: 13px; color: #6c757d; margin-top: 5px;">Verificar saldos</div>
            </a>
            
            <a href="index.php?page=bank&subpage=matching&action=auto" 
               style="padding: 20px; background: white; border-radius: 12px; text-decoration: none; color: #333; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 36px; margin-bottom: 10px;">🔗</div>
                <div style="font-weight: 600;">Matching Automático</div>
                <div style="font-size: 13px; color: #6c757d; margin-top: 5px;">Emparejar transacciones</div>
            </a>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
