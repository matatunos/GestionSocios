<?php
// Fetch association name if not already set
if (!isset($associationName)) {
    $associationName = 'Gestión Asociación';
    try {
        $db = (new Database())->getConnection();
        if ($db) {
            // Check if table exists first or just try query
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'association_name'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $associationName = $row['setting_value'];
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist or other error, keep default
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($associationName); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: var(--bg-body); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 2rem; box-sizing: border-box;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; color: var(--primary-600); margin-bottom: 1rem;">
                <i class="fas fa-users-rectangle"></i>
            </div>
            <h1 style="color: var(--primary-700); font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($associationName); ?></h1>
            <p style="color: var(--text-light);">Acceso Socios</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=login&action=login" method="POST">
            <div class="form-group">
                <label class="form-label">Usuario</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="form-control" style="padding-left: 2.5rem; width: 100%; box-sizing: border-box;" placeholder="admin" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control" style="padding-left: 2.5rem; width: 100%; box-sizing: border-box;" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="margin-top: 1rem;">
                Iniciar Sesión <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>
        </form>
    </div>
</body>
</html>
