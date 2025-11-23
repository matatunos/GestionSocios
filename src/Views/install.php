<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Asociación</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: var(--bg-body);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .install-card {
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
        }
        .step-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-700);
            border-bottom: 2px solid var(--primary-100);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; color: var(--primary-600); margin-bottom: 1rem;">
                <i class="fas fa-cogs"></i>
            </div>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Configuración Inicial</h1>
            <p style="color: var(--text-muted);">Bienvenido al asistente de instalación</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=install&action=run" method="POST">
            <div class="step-title">1. Base de Datos</div>
            <div class="form-group">
                <label class="form-label">Host</label>
                <input type="text" name="host" class="form-control" placeholder="localhost" value="<?php echo $_POST['host'] ?? 'localhost'; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de la Base de Datos</label>
                <input type="text" name="db_name" class="form-control" placeholder="asociacion_db" value="<?php echo $_POST['db_name'] ?? 'asociacion_db'; ?>" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Usuario DB</label>
                    <input type="text" name="username" class="form-control" placeholder="root" value="<?php echo $_POST['username'] ?? 'root'; ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña DB</label>
                    <input type="password" name="password" class="form-control" value="<?php echo $_POST['password'] ?? ''; ?>">
                </div>
            </div>

            <div class="step-title" style="margin-top: 2rem;">2. Administrador del Sistema</div>
            <div class="form-group">
                <label class="form-label">Usuario Admin</label>
                <input type="text" name="admin_user" class="form-control" placeholder="admin" value="<?php echo $_POST['admin_user'] ?? 'admin'; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña Admin</label>
                <input type="password" name="admin_pass" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="margin-top: 1.5rem;">
                Instalar y Configurar <i class="fas fa-rocket" style="margin-left: 0.5rem;"></i>
            </button>
        </form>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
