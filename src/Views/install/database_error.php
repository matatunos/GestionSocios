<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Base de Datos - Gestión Socios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
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
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
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
        .error-details {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
        }
        .error-details strong {
            color: #991b1b;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .error-details p {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #7f1d1d;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-word;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Error de Base de Datos</h1>
        <p>Se produjo un error al intentar verificar la estructura de la base de datos.</p>
        
        <?php if (isset($errorMessage)): ?>
        <div class="error-details">
            <strong><i class="fas fa-bug"></i> Detalles del error:</strong>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
        <?php endif; ?>
        
        <p><strong>Para solucionar este problema:</strong></p>
        <code>cd database<br>bash install.sh</code>
        
        <p style="font-size: 14px; margin-top: 20px; color: #718096;">
            El script de instalación reparará y recreará la estructura de la base de datos.
        </p>
    </div>
</body>
</html>
