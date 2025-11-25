<?php
// Cargar datos de config.php si existe
$configDefaults = [
    'host' => 'localhost',
    'db_name' => 'asociacion_db',
    'username' => 'root',
    'password' => '',
];
$configFile = __DIR__ . '/../Config/config.php';
if (file_exists($configFile)) {
    include $configFile;
    $configDefaults['host'] = defined('DB_HOST') ? DB_HOST : $configDefaults['host'];
    $configDefaults['db_name'] = defined('DB_NAME') ? DB_NAME : $configDefaults['db_name'];
    $configDefaults['username'] = defined('DB_USER') ? DB_USER : $configDefaults['username'];
    $configDefaults['password'] = defined('DB_PASS') ? DB_PASS : $configDefaults['password'];
}
?>
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
                <input type="text" name="host" class="form-control" placeholder="localhost" value="<?php echo $_POST['host'] ?? $configDefaults['host']; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de la Base de Datos</label>
                <input type="text" name="db_name" class="form-control" placeholder="asociacion_db" value="<?php echo $_POST['db_name'] ?? $configDefaults['db_name']; ?>" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Usuario DB <span style="color:#888;font-size:0.9em" title="Este debe ser el usuario SQL, no el usuario del sistema">(usuario SQL)</span></label>
                    <input type="text" name="username" class="form-control" placeholder="root" value="<?php echo $_POST['username'] ?? $configDefaults['username']; ?>" required pattern="^[a-zA-Z0-9_]+$" title="Solo letras, números y guiones bajos">
                    <?php if (isset($configDefaults['username'])): ?>
                    <small style="color:#888;">Precargado desde config.php: <b><?php echo htmlspecialchars($configDefaults['username']); ?></b></small>
                    <?php endif; ?>
                                <?php if (isset($configDefaults['host'])): ?>
                                <small style="color:#888;">Precargado desde config.php: <b><?php echo htmlspecialchars($configDefaults['host']); ?></b></small>
                                <?php endif; ?>
                                <?php if (isset($configDefaults['db_name'])): ?>
                                <small style="color:#888;">Precargado desde config.php: <b><?php echo htmlspecialchars($configDefaults['db_name']); ?></b></small>
                                <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña DB</label>
                    <input type="password" name="password" class="form-control" value="<?php echo $_POST['password'] ?? $configDefaults['password']; ?>">
                                <?php if (isset($configDefaults['password']) && $configDefaults['password'] !== ''): ?>
                                <small style="color:#888;">Precargado desde config.php</small>
                                <?php endif; ?>
                            <div style="background:#fef9c3;color:#92400e;padding:0.5rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:0.95em;">
                                <b>Nota:</b> El usuario DB debe ser el usuario SQL con permisos sobre la base de datos.<br>
                                Si tienes dudas, revisa el archivo <code>src/Config/config.php</code> o consulta con el administrador del servidor.
                            </div>
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

            <div class="form-group" style="margin-top:1.5rem;">
                <label class="form-label">Datos de Ejemplo</label>
                <div style="margin-top:0.75rem;">
                    <label style="display:block;margin-bottom:0.5rem;">
                        <input type="radio" name="sample_data_option" value="none" <?php echo (!isset($_POST['sample_data_option']) || $_POST['sample_data_option'] === 'none') ? 'checked' : ''; ?>>
                        No importar datos de ejemplo
                    </label>
                    <label style="display:block;margin-bottom:0.5rem;">
                        <input type="radio" name="sample_data_option" value="small" <?php echo (isset($_POST['sample_data_option']) && $_POST['sample_data_option'] === 'small') ? 'checked' : ''; ?>>
                        Datos reducidos (25 socios, 10 donantes, 10 eventos)
                    </label>
                    <label style="display:block;margin-bottom:0.5rem;">
                        <input type="radio" name="sample_data_option" value="large" <?php echo (isset($_POST['sample_data_option']) && $_POST['sample_data_option'] === 'large') ? 'checked' : ''; ?>>
                        Datos extensos (2000 socios, 500 donantes, 50 eventos, 5 años de actividad)
                    </label>
                </div>
                <small style="color:#888;display:block;margin-top:0.5rem;">Los datos de ejemplo son útiles para probar el sistema. Los datos extensos simulan una asociación grande con mucha actividad.</small>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>

<?php
// Cargar datos de config.php si existe
$configDefaults = [
    'host' => 'localhost',
    'db_name' => 'asociacion_db',
    'username' => 'root',
    'password' => '',
];
$configFile = __DIR__ . '/../Config/config.php';
if (file_exists($configFile)) {
    include $configFile;
    $configDefaults['host'] = defined('DB_HOST') ? DB_HOST : $configDefaults['host'];
    $configDefaults['db_name'] = defined('DB_NAME') ? DB_NAME : $configDefaults['db_name'];
    $configDefaults['username'] = defined('DB_USER') ? DB_USER : $configDefaults['username'];
    $configDefaults['password'] = defined('DB_PASS') ? DB_PASS : $configDefaults['password'];
}
?>
