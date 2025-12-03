<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Conexión - Gestión Socios</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .error-card {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon-wrapper i {
            font-size: 2.5rem;
            color: #dc2626;
        }
        h1 {
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        p {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-retry {
            background: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-retry:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-wrapper">
            <i class="fas fa-database"></i>
        </div>
        <h1>Error de Conexión</h1>
        <p>No se pudo establecer conexión con la base de datos. Esto puede deberse a que las credenciales en el archivo de configuración son incorrectas o el servidor de base de datos no está disponible.</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="index.php" class="btn btn-secondary">Reintentar</a>
            <a href="index.php?page=install" class="btn-retry">
                <i class="fas fa-cog"></i> Reconfigurar
            </a>
        </div>
    </div>
</body>
</html>
