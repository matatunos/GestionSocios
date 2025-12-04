<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsquedas Automáticas - Subvenciones</title>
    <style>
        .searches-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header-section h1 {
            margin: 0;
            color: #333;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .searches-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .searches-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .searches-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .searches-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .searches-table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-frequency {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background: #6c757d;
            color: white;
        }
        
        .btn-icon:hover {
            opacity: 0.8;
        }
        
        .btn-run {
            background: #28a745;
        }
        
        .btn-edit {
            background: #007bff;
        }
        
        .btn-delete {
            background: #dc3545;
        }
        
        .btn-toggle {
            background: #ffc107;
            color: #333;
        }
        
        .results-info {
            font-size: 12px;
            color: #6c757d;
        }
        
        .no-searches {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-searches svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .keywords-preview {
            font-size: 13px;
            color: #495057;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="searches-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="header-section">
            <div>
                <h1>🔍 Búsquedas Automáticas de Subvenciones</h1>
                <p style="color: #6c757d; margin: 5px 0 0 0;">
                    Configura búsquedas programadas en BDNS, BOE y otras fuentes oficiales
                </p>
            </div>
            <a href="index.php?page=grants&subpage=searches&action=create" class="btn-primary">
                ➕ Nueva Búsqueda
            </a>
        </div>
        
        <?php if (empty($searches)): ?>
            <div class="no-searches">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3>No hay búsquedas programadas</h3>
                <p>Crea tu primera búsqueda automática para encontrar subvenciones relevantes</p>
                <a href="index.php?page=grants&subpage=searches&action=create" class="btn-primary">
                    Crear Primera Búsqueda
                </a>
            </div>
        <?php else: ?>
            <div class="searches-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Palabras Clave</th>
                            <th>Tipo/Zona</th>
                            <th>Frecuencia</th>
                            <th>Última Ejecución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searches as $search): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($search['search_name']) ?></strong>
                                </td>
                                <td>
                                    <div class="keywords-preview" title="<?= htmlspecialchars($search['keywords']) ?>">
                                        <?= htmlspecialchars($search['keywords']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <div><?= ucfirst($search['grant_type'] ?: 'Todos') ?></div>
                                        <?php if ($search['province']): ?>
                                            <div style="color: #6c757d;">📍 <?= htmlspecialchars($search['province']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-frequency">
                                        <?php
                                        $freqLabels = [
                                            'daily' => '📅 Diaria',
                                            'weekly' => '📆 Semanal',
                                            'monthly' => '🗓️ Mensual'
                                        ];
                                        echo $freqLabels[$search['frequency']] ?? $search['frequency'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($search['last_run']): ?>
                                        <div style="font-size: 13px;">
                                            <?= date('d/m/Y H:i', strtotime($search['last_run'])) ?>
                                            <?php if ($search['last_results'] !== null): ?>
                                                <div class="results-info">
                                                    ✨ <?= $search['last_results'] ?> nuevas
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-size: 13px;">Nunca ejecutada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $search['active'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $search['active'] ? '✓ Activa' : '✗ Inactiva' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            class="btn-icon btn-run"
                                            onclick="if(confirm('¿Ejecutar esta búsqueda ahora?')) location.href='index.php?page=grants&subpage=searches&action=run&id=<?= $search['id'] ?>'"
                                            title="Ejecutar ahora">
                                            ▶️
                                        </button>
                                        <button 
                                            class="btn-icon btn-toggle"
                                            onclick="location.href='index.php?page=grants&subpage=searches&action=toggle&id=<?= $search['id'] ?>'"
                                            title="<?= $search['active'] ? 'Desactivar' : 'Activar' ?>">
                                            🔄
                                        </button>
                                        <button 
                                            class="btn-icon btn-delete"
                                            onclick="if(confirm('¿Eliminar esta búsqueda?')) location.href='index.php?page=grants&subpage=searches&action=delete&id=<?= $search['id'] ?>'"
                                            title="Eliminar">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #007bff;">
                <strong>💡 Consejo:</strong> Las búsquedas activas se ejecutarán automáticamente según su frecuencia configurada.
                Para ejecutar una búsqueda manualmente, haz clic en el botón ▶️
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
