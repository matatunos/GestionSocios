<?php
// Fetch association name for layout
if (!isset($associationName)) {
    $associationName = 'Asociación';
    try {
        $db = (new Database())->getConnection();
        if ($db) {
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
    <title>Gestión <?php echo htmlspecialchars($associationName); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Using FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="index.php" class="nav-brand">
                <i class="fas fa-users-rectangle"></i>
                <span><?php echo htmlspecialchars($associationName); ?></span>
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="index.php?page=dashboard" class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                    </div>
                    <a href="index.php?page=login&action=logout" class="btn btn-sm btn-danger w-full">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php echo $content; ?>
        </main>
    </div>

    <script>
        // Simple mobile sidebar toggle could be added here
    </script>
</body>
</html>
