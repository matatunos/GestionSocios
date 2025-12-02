<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Conexión - Gestión Socios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .causes {
            background: #f7fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        .cause {
            display: flex;
            align-items: start;
            margin-bottom: 16px;
        }
        .cause:last-child { margin-bottom: 0; }
        .cause-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            margin-right: 12px;
            color: #ef4444;
        }
        .cause-text {
            color: #4a5568;
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
        .solution {
            background: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
        }
        .solution strong {
            color: #0050b3;
            display: block;
            margin-bottom: 8px;
        }
        .solution p {
            text-align: left;
            font-size: 14px;
            color: #003a8c;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-database"></i>
        </div>
        <h1>Error de Conexión</h1>
        <p>No se pudo establecer conexión con la base de datos MySQL.</p>
        
        <div class="causes">
            <div class="cause">
                <i class="fas fa-times-circle cause-icon"></i>
                <div class="cause-text">El servidor MySQL no está ejecutándose</div>
            </div>
            <div class="cause">
                <i class="fas fa-times-circle cause-icon"></i>
                <div class="cause-text">Las credenciales en <code style="display:inline;padding:2px 6px;margin:0;">config.php</code> son incorrectas</div>
            </div>
            <div class="cause">
                <i class="fas fa-times-circle cause-icon"></i>
                <div class="cause-text">El usuario MySQL no tiene permisos desde este host</div>
            </div>
            <div class="cause">
                <i class="fas fa-times-circle cause-icon"></i>
                <div class="cause-text">El host o puerto de MySQL es incorrecto</div>
            </div>
        </div>
        
        <div class="solution">
            <strong><i class="fas fa-wrench"></i> Solución</strong>
            <p>Ejecuta el script de instalación para reconfigurar la conexión:</p>
        </div>
        
        <code>cd database<br>bash install.sh</code>
        
        <p style="font-size: 14px; margin-top: 20px;">
            O edita manualmente el archivo <code style="display:inline;padding:2px 6px;">src/Config/config.php</code> con las credenciales correctas.
        </p>
    </div>
</body>
</html>
