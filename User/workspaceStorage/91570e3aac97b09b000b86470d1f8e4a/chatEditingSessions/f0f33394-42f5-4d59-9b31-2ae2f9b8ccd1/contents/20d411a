<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Búsqueda Automática</title>
    <style>
        .create-search-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0;
            color: #333;
        }
        
        .btn-back {
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .btn-back:hover {
            opacity: 0.9;
        }
        
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #495057;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
        }
        
        .form-group label.required::after {
            content: ' *';
            color: #dc3545;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            opacity: 0.9;
        }
        
        .btn-cancel {
            background: white;
            color: #6c757d;
            padding: 12px 30px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .btn-cancel:hover {
            background: #f8f9fa;
        }
        
        .help-text {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            margin: 0;
            color: #004085;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="create-search-container">
        <div class="page-header">
            <a href="index.php?page=grants&subpage=searches" class="btn-back">← Volver</a>
            <h1>Nueva Búsqueda Automática</h1>
        </div>
        
        <div class="info-box">
            <p>
                <strong>💡 Búsqueda automática:</strong> El sistema buscará subvenciones en BDNS, BOE y otras fuentes oficiales
                según los criterios configurados. Las subvenciones encontradas se añadirán automáticamente con su puntuación de relevancia.
            </p>
        </div>
        
        <form method="POST" class="form-card">
            <div class="form-section">
                <h3>Información Básica</h3>
                
                <div class="form-group">
                    <label for="search_name" class="required">Nombre de la Búsqueda</label>
                    <input 
                        type="text" 
                        id="search_name" 
                        name="search_name" 
                        class="form-control" 
                        required
                        placeholder="Ej: Subvenciones culturales Cataluña">
                    <div class="help-text">Nombre descriptivo para identificar esta búsqueda</div>
                </div>
                
                <div class="form-group">
                    <label for="keywords" class="required">Palabras Clave</label>
                    <textarea 
                        id="keywords" 
                        name="keywords" 
                        class="form-control" 
                        required
                        placeholder="cultura teatro danza música artes escénicas"></textarea>
                    <div class="help-text">Palabras o frases separadas por espacios. Se buscarán en título y descripción</div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Criterios de Búsqueda</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="grant_type">Tipo de Subvención</label>
                        <select id="grant_type" name="grant_type" class="form-control">
                            <option value="">Todos los tipos</option>
                            <option value="estatal">Estatal</option>
                            <option value="autonomica">Autonómica</option>
                            <option value="provincial">Provincial</option>
                            <option value="local">Local</option>
                            <option value="europea">Europea</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Categoría</label>
                        <input 
                            type="text" 
                            id="category" 
                            name="category" 
                            class="form-control"
                            placeholder="Ej: cultura, deporte, educación">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Provincia</label>
                        <input 
                            type="text" 
                            id="province" 
                            name="province" 
                            class="form-control"
                            placeholder="Ej: Barcelona">
                    </div>
                    
                    <div class="form-group">
                        <label for="municipality">Municipio</label>
                        <input 
                            type="text" 
                            id="municipality" 
                            name="municipality" 
                            class="form-control"
                            placeholder="Ej: Barcelona">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="min_amount">Importe Mínimo (€)</label>
                    <input 
                        type="number" 
                        id="min_amount" 
                        name="min_amount" 
                        class="form-control"
                        step="0.01"
                        placeholder="5000.00">
                    <div class="help-text">Solo buscar subvenciones con importe mínimo igual o superior</div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Configuración de Ejecución</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="frequency" class="required">Frecuencia</label>
                        <select id="frequency" name="frequency" class="form-control" required>
                            <option value="daily">Diaria (cada 24 horas)</option>
                            <option value="weekly" selected>Semanal (cada 7 días)</option>
                            <option value="monthly">Mensual (cada 30 días)</option>
                        </select>
                        <div class="help-text">Con qué frecuencia se ejecutará automáticamente esta búsqueda</div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input 
                                type="checkbox" 
                                id="active" 
                                name="active" 
                                checked>
                            <label for="active">Activar búsqueda automática</label>
                        </div>
                        <div class="help-text">Si está activada, se ejecutará según la frecuencia configurada</div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Notificaciones (Próximamente)</h3>
                
                <div class="form-group">
                    <div class="help-text">
                        ⚠️ Las notificaciones por email estarán disponibles en una futura actualización.
                        Por ahora, las subvenciones encontradas aparecerán en el dashboard automáticamente.
                    </div>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24; margin-bottom: 20px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">💾 Crear Búsqueda</button>
                <a href="index.php?page=grants&subpage=searches" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
