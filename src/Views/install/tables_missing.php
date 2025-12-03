<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de Datos Vacía - Gestión Socios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .warning {
            background: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .warning strong {
            color: #d68910;
            display: block;
            margin-bottom: 8px;
        }
        .warning p {
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
            <i class="fas fa-table"></i>
        </div>
        <h1>Base de Datos Vacía</h1>
        <p>La base de datos existe pero no contiene las tablas necesarias para la aplicación.</p>
        
        <div class="warning">
            <strong><i class="fas fa-exclamation-triangle"></i> ¿Qué significa esto?</strong>
            <p>La conexión a MySQL funciona correctamente, pero las tablas (users, members, etc.) no han sido creadas. Necesitas ejecutar el script de instalación para crear la estructura de la base de datos.</p>
        </div>
        
        <p><strong>Ejecuta el script de instalación:</strong></p>
        <code>cd database<br>bash install.sh</code>
        
        <p style="font-size: 14px; margin-top: 20px; color: #718096;">
            El script creará automáticamente todas las tablas necesarias y los datos iniciales.
        </p>
    </div>
</body>
</html>
