<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Requerida - Gestión Socios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon i { font-size: 40px; color: white; }
        h1 {
            text-align: center;
            color: #1a202c;
            margin-bottom: 16px;
            font-size: 28px;
        }
        p {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 24px;
            text-align: center;
        }
        .steps {
            background: #f7fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        .step {
            display: flex;
            align-items: start;
            margin-bottom: 16px;
        }
        .step:last-child { margin-bottom: 0; }
        .step-number {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
            margin-right: 16px;
        }
        .step-content {
            flex: 1;
        }
        .step-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        .step-desc {
            color: #718096;
            font-size: 14px;
        }
        code {
            background: #2d3748;
            color: #68d391;
            padding: 12px 16px;
            border-radius: 8px;
            display: block;
            font-family: 'Courier New', monospace;
            margin: 16px 0;
            font-size: 14px;
            overflow-x: auto;
        }
        .note {
            background: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
        }
        .note strong {
            color: #d68910;
            display: block;
            margin-bottom: 8px;
        }
        .note p {
            text-align: left;
            font-size: 14px;
            color: #7d6608;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-tools"></i>
        </div>
        <h1>Configuración Requerida</h1>
        <p>La aplicación no está configurada. Debes ejecutar el script de instalación.</p>
        
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Abre una terminal</div>
                    <div class="step-desc">Accede al directorio del proyecto</div>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Ejecuta el instalador</div>
                    <div class="step-desc">Navega a la carpeta database y ejecuta:</div>
                </div>
            </div>
        </div>
        
        <code>cd database<br>bash install.sh</code>
        
        <div class="note">
            <strong><i class="fas fa-info-circle"></i> Nota</strong>
            <p>El script te pedirá los datos de conexión a MySQL (host, usuario, contraseña) y creará automáticamente la base de datos con todas las tablas necesarias.</p>
        </div>
    </div>
</body>
</html>
