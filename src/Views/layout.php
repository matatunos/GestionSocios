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
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-chevron-left"></i>
            </div>

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
                    <a href="index.php?page=members" class="nav-link <?php echo ($page === 'members') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Socios</span>
                    </a>
                </li>
                <!-- Fees link removed and moved to Settings -->
                <li>
                    <a href="index.php?page=payments" class="nav-link <?php echo ($page === 'payments') ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Pagos</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=events" class="nav-link <?php echo ($page === 'events') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Eventos</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=donors" class="nav-link <?php echo ($page === 'donors') ? 'active' : ''; ?>">
                        <i class="fas fa-address-book"></i>
                        <span>Donantes</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=donations" class="nav-link <?php echo ($page === 'donations') ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Donaciones</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=book" class="nav-link <?php echo ($page === 'book') ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i>
                        <span>Libro Fiestas</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=reports&action=executive" class="nav-link <?php echo ($page === 'reports') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Informes</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=settings" class="nav-link <?php echo ($page === 'settings') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-info" style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                    <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                </div>
                <a href="index.php?page=login&action=logout" class="btn btn-sm btn-danger w-full btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-logout-text">Cerrar Sesión</span>
                </a>
            </div>

        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php echo $content; ?>
        </main>
    </div>

    <script>
        // Initialize Dark Mode
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        // Initialize Sidebar State
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    </script>
</body>
</html>
