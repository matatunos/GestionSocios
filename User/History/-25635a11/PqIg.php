<?php // Ensure page and action variables are available
$page = $page ?? $_GET['page'] ?? 'dashboard';
$action = $action ?? $_GET['action'] ?? 'index';

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

// Fetch unread notifications count
$unreadNotifications = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $db = (new Database())->getConnection();
        if ($db) {
            require_once __DIR__ . '/../Models/Notification.php';
            $notificationModel = new Notification($db);
            $unreadNotifications = $notificationModel->countUnread($_SESSION['user_id']);
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
    <?php
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($user_agent, 0, 4));
    ?>
    <link rel="stylesheet" href="/css/style.css?v=<?php echo time(); ?>">
    <?php if ($is_mobile): ?>
        <link rel="stylesheet" href="/css/mobile.css?v=<?php echo time(); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/css/listings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/nav-submenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/nav-submenu.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    
    <div class="app-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">

            <div class="sidebar-header">
                <div class="nav-brand">
                    <?php
                    // Obtener logo de la organización
                    $orgInfo = ['name' => 'Gestión', 'short_name' => 'Gestión', 'logo' => '', 'logo_width' => 180];
                    try {
                        if (!isset($db)) {
                            $db = (new Database())->getConnection();
                        }
                        if ($db) {
                            require_once __DIR__ . '/../Models/OrganizationSettings.php';
                            $orgSettings = new OrganizationSettings($db);
                            $orgInfo = $orgSettings->getOrganizationInfo();
                        }
                    } catch (Exception $e) {
                        // Error getting organization info, use defaults
                    }
                    
                    if (!empty($orgInfo['logo'])):
                    ?>
                        <img src="<?= htmlspecialchars($orgInfo['logo']) ?>" 
                             alt="<?= htmlspecialchars($orgInfo['name']) ?>" 
                             style="max-height: 40px; max-width: <?= (int)$orgInfo['logo_width'] ?>px;">
                    <?php else: ?>
                        <i class="fas fa-users-cog"></i>
                        <span><?= htmlspecialchars($orgInfo['short_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Global Search -->
            <div class="global-search" id="globalSearch">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="globalSearchInput" placeholder="Buscar..." autocomplete="off">
                    <kbd class="search-shortcut">Ctrl+K</kbd>
                </div>
                <div class="search-results" id="searchResults" style="display: none;"></div>
            </div>
            
            <?php if ($is_mobile): ?>
                <!-- Mobile Menu (Simplified) -->
                <ul class="nav-menu">
                                                            <!-- Menú de Contabilidad -->
                                                            <li class="nav-group">
                                                                <a href="#" class="nav-link <?php echo ($page === 'accounting' || $page === 'budget' || $page === 'invoices') ? 'active' : ''; ?>">
                                                                    <i class="fas fa-calculator"></i>
                                                                    <span>Contabilidad</span>
                                                                    <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                                                                </a>
                                                                <ul class="nav-submenu">
                                                                    <li>
                                                                        <a href="index.php?page=accounting&action=accounts" class="nav-link <?php echo ($page === 'accounting' && $action === 'accounts') ? 'active' : ''; ?>">
                                                                            <i class="fas fa-list-ol"></i>
                                                                            <span>Cuentas</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="index.php?page=accounting&action=entries" class="nav-link <?php echo ($page === 'accounting' && $action === 'entries') ? 'active' : ''; ?>">
                                                                            <i class="fas fa-book"></i>
                                                                            <span>Asientos</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="index.php?page=accounting&action=periods" class="nav-link <?php echo ($page === 'accounting' && $action === 'periods') ? 'active' : ''; ?>">
                                                                            <i class="fas fa-calendar-alt"></i>
                                                                            <span>Periodos</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="index.php?page=budget" class="nav-link <?php echo ($page === 'budget') ? 'active' : ''; ?>">
                                                                            <i class="fas fa-wallet"></i>
                                                                            <span>Presupuestos</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="index.php?page=invoices" class="nav-link <?php echo ($page === 'invoices') ? 'active' : ''; ?>">
                                                                            <i class="fas fa-file-invoice"></i>
                                                                            <span>Facturas</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                        <!-- Menú de Contabilidad -->
                                        <li class="nav-group">
                                            <a href="#" class="nav-link <?php echo ($page === 'accounting' || $page === 'budget' || $page === 'invoices') ? 'active' : ''; ?>">
                                                <i class="fas fa-calculator"></i>
                                                <span>Contabilidad</span>
                                                <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                                            </a>
                                            <ul class="nav-submenu">
                                                <li>
                                                    <a href="index.php?page=accounting&action=accounts" class="nav-link <?php echo ($page === 'accounting' && $action === 'accounts') ? 'active' : ''; ?>">
                                                        <i class="fas fa-list-ol"></i>
                                                        <span>Cuentas</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="index.php?page=accounting&action=entries" class="nav-link <?php echo ($page === 'accounting' && $action === 'entries') ? 'active' : ''; ?>">
                                                        <i class="fas fa-book"></i>
                                                        <span>Asientos</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="index.php?page=accounting&action=periods" class="nav-link <?php echo ($page === 'accounting' && $action === 'periods') ? 'active' : ''; ?>">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>Periodos</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="index.php?page=budget" class="nav-link <?php echo ($page === 'budget') ? 'active' : ''; ?>">
                                                        <i class="fas fa-wallet"></i>
                                                        <span>Presupuestos</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="index.php?page=invoices" class="nav-link <?php echo ($page === 'invoices') ? 'active' : ''; ?>">
                                                        <i class="fas fa-file-invoice"></i>
                                                        <span>Facturas</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                    <li>
                        <a href="index.php?page=dashboard" class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=members" class="nav-link <?php echo ($page === 'members') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Socios</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=donors" class="nav-link <?php echo ($page === 'donors') ? 'active' : ''; ?>">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Donantes</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=suppliers" class="nav-link <?php echo ($page === 'suppliers' && $action !== 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <span>Gestión Proveedores</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=map" class="nav-link <?php echo ($page === 'map') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Mapa</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=tasks" class="nav-link <?php echo ($page === 'tasks') ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i>
                            <span>Tareas</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=calendar" class="nav-link <?php echo ($page === 'calendar') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Calendario</span>
                        </a>
                    </li>
                       <li>
                           <a href="index.php?page=events" class="nav-link <?php echo ($page === 'events') ? 'active' : ''; ?>">
                               <i class="fas fa-calendar-check"></i>
                               <span>Eventos</span>
                           </a>
                       </li>
                    <li>
                        <a href="index.php?page=messages" class="nav-link <?php echo ($page === 'messages') ? 'active' : ''; ?>">
                            <i class="fas fa-comments"></i>
                            <span>Mensajes</span>
                            <?php
                            if (isset($_SESSION['user_id'])) {
                                require_once __DIR__ . '/../Models/Message.php';
                                $msgModel = new Message($GLOBALS['db'] ?? (new Database())->getConnection());
                                $unreadMessages = $msgModel->getUnreadCount($_SESSION['user_id']);
                                if ($unreadMessages > 0):
                            ?>
                                <span class="nav-badge"><?php echo $unreadMessages; ?></span>
                            <?php endif; } ?>
                        </a>
                    </li>
                        <li>
                            <a href="index.php?page=calendar" class="nav-link <?php echo ($page === 'calendar') ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Calendario</span>
                            </a>
                        </li>
                    <li>
                        <a href="index.php?page=notifications" class="nav-link <?php echo ($page === 'notifications') ? 'active' : ''; ?>">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones</span>
                            <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                                <span class="nav-badge"><?php echo $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=book" class="nav-link <?php echo ($page === 'book') ? 'active' : ''; ?>">
                            <i class="fas fa-book-open"></i>
                            <span>Libro Fiestas</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=announcements" class="nav-link <?php echo ($page === 'announcements') ? 'active' : ''; ?>">
                            <i class="fas fa-bullhorn"></i>
                            <span>Anuncios Públicos</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=settings" class="nav-link <?php echo ($page === 'settings') ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <!-- Desktop Menu (Full) -->
                <ul class="nav-menu">
                    <!-- Menú Dashboard con Tesorería y Contabilidad -->
                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'dashboard' || $page === 'treasury' || $page === 'accounting' || ($page === 'reports' && $action === 'dashboard_events')) ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=dashboard" class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-pie"></i>
                                    <span>Panel de Control</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=treasury" class="nav-link <?php echo ($page === 'treasury') ? 'active' : ''; ?>">
                                    <i class="fas fa-coins"></i>
                                    <span>Tesorería</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=accounting&action=dashboard" class="nav-link <?php echo ($page === 'accounting' && ($action === 'dashboard' || $action === 'index' || !$action)) ? 'active' : ''; ?>">
                                    <i class="fas fa-calculator"></i>
                                    <span>Contabilidad</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=reports&action=dashboard_events" class="nav-link <?php echo ($page === 'reports' && $action === 'dashboard_events') ? 'active' : ''; ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Eventos</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=documents&action=dashboard" class="nav-link <?php echo ($page === 'documents' && $action === 'dashboard') ? 'active' : ''; ?>">
                                    <i class="fas fa-folder-open"></i>
                                    <span>Documentos</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=suppliers&action=dashboard" class="nav-link <?php echo ($page === 'suppliers' && ($action === 'dashboard')) ? 'active' : ''; ?>">
                                    <i class="fas fa-truck"></i>
                                    <span>Dashboard Proveedores</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Menú de Socios -->
                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'members' || $page === 'member_categories') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Socios</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=members" class="nav-link <?php echo ($page === 'members') ? 'active' : ''; ?>">
                                    <i class="fas fa-list"></i>
                                    <span>Listado</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=member_categories" class="nav-link <?php echo ($page === 'member_categories') ? 'active' : ''; ?>">
                                    <i class="fas fa-tags"></i>
                                    <span>Categorías</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Menú de Donantes -->
                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'donors' || $page === 'donations') ? 'active' : ''; ?>">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Donantes</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=donors" class="nav-link <?php echo ($page === 'donors') ? 'active' : ''; ?>">
                                    <i class="fas fa-list"></i>
                                    <span>Listado</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=donations" class="nav-link <?php echo ($page === 'donations') ? 'active' : ''; ?>">
                                    <i class="fas fa-gift"></i>
                                    <span>Donaciones</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="index.php?page=gallery" class="nav-link <?php echo ($page === 'gallery') ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i>
                            <span>Galería Imágenes</span>
                        </a>
                    </li>
                       <li>
                           <a href="index.php?page=events" class="nav-link <?php echo ($page === 'events') ? 'active' : ''; ?>">
                               <i class="fas fa-calendar-check"></i>
                               <span>Eventos</span>
                           </a>
                       </li>

                    <li>
                        <a href="index.php?page=suppliers" class="nav-link <?php echo ($page === 'suppliers' && $action !== 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <span>Gestión Proveedores</span>
                        </a>
                    </li>

                    <!-- Menú de Contabilidad (operaciones técnicas) -->
                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'accounting' && $action !== 'dashboard' && $action !== 'index') ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i>
                            <span>Libros Contables</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=accounting&action=accounts" class="nav-link <?php echo ($page === 'accounting' && $action === 'accounts') ? 'active' : ''; ?>">
                                    <i class="fas fa-list-ol"></i>
                                    <span>Plan de Cuentas</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=accounting&action=entries" class="nav-link <?php echo ($page === 'accounting' && $action === 'entries') ? 'active' : ''; ?>">
                                    <i class="fas fa-file-invoice"></i>
                                    <span>Libro Diario</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=accounting&action=generalLedger" class="nav-link <?php echo ($page === 'accounting' && $action === 'generalLedger') ? 'active' : ''; ?>">
                                    <i class="fas fa-book-open"></i>
                                    <span>Libro Mayor</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=accounting&action=trialBalance" class="nav-link <?php echo ($page === 'accounting' && $action === 'trialBalance') ? 'active' : ''; ?>">
                                    <i class="fas fa-balance-scale"></i>
                                    <span>Balance de Sumas</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="index.php?page=tasks" class="nav-link <?php echo ($page === 'tasks') ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i>
                            <span>Tareas</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=analytics" class="nav-link <?php echo ($page === 'analytics') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Estadísticas</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=map" class="nav-link <?php echo ($page === 'map') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Mapa</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=notifications" class="nav-link <?php echo ($page === 'notifications') ? 'active' : ''; ?>">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones</span>
                            <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                                <span class="nav-badge"><?php echo $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'documents' || $page === 'document_folders' || $page === 'document_tags' || $page === 'document_categories') ? 'active' : ''; ?>">
                            <i class="fas fa-folder-open"></i>
                            <span>Documentos</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=documents" class="nav-link <?php echo ($page === 'documents' && !isset($_GET['action'])) ? 'active' : ''; ?>">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Todos los Documentos</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=documents&action=favorites" class="nav-link <?php echo ($page === 'documents' && $action === 'favorites') ? 'active' : ''; ?>">
                                    <i class="fas fa-star"></i>
                                    <span>Favoritos</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=documents&action=trash" class="nav-link <?php echo ($page === 'documents' && $action === 'trash') ? 'active' : ''; ?>">
                                    <i class="fas fa-trash"></i>
                                    <span>Papelera</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=documents&action=public_links" class="nav-link <?php echo ($page === 'documents' && $action === 'public_links') ? 'active' : ''; ?>">
                                    <i class="fas fa-share-alt"></i>
                                    <span>Enlaces Públicos</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=document_folders" class="nav-link <?php echo ($page === 'document_folders') ? 'active' : ''; ?>">
                                    <i class="fas fa-folder"></i>
                                    <span>Carpetas</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=document_tags" class="nav-link <?php echo ($page === 'document_tags') ? 'active' : ''; ?>">
                                    <i class="fas fa-hashtag"></i>
                                    <span>Tags</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=document_categories" class="nav-link <?php echo ($page === 'document_categories') ? 'active' : ''; ?>">
                                    <i class="fas fa-tags"></i>
                                    <span>Categorías</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                        <li>
                            <a href="index.php?page=calendar" class="nav-link <?php echo ($page === 'calendar') ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Calendario</span>
                            </a>
                        </li>
                    <li>
                        <a href="index.php?page=messages" class="nav-link <?php echo ($page === 'messages') ? 'active' : ''; ?>">
                            <i class="fas fa-comments"></i>
                            <span>Mensajes</span>
                            <?php
                            if (isset($_SESSION['user_id'])) {
                                require_once __DIR__ . '/../Models/Message.php';
                                $msgModel = new Message($GLOBALS['db'] ?? (new Database())->getConnection());
                                $unreadMessages = $msgModel->getUnreadCount($_SESSION['user_id']);
                                if ($unreadMessages > 0):
                            ?>
                                <span class="nav-badge"><?php echo $unreadMessages; ?></span>
                            <?php endif; } ?>
                        </a>
                    </li>
                    <li class="nav-group">
                        <a href="#" class="nav-link <?php echo ($page === 'book' || $page === 'book_activities' || $page === 'book_export') ? 'active' : ''; ?>">
                            <i class="fas fa-book-open"></i>
                            <span>Libro Fiestas</span>
                            <i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.8em;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li>
                                <a href="index.php?page=book&action=dashboard" class="nav-link <?php echo ($page === 'book' && $action === 'dashboard') ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-pie"></i>
                                    <span>Resumen</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=book" class="nav-link <?php echo ($page === 'book' && $action === 'index') ? 'active' : ''; ?>">
                                    <i class="fas fa-ad"></i>
                                    <span>Anunciantes</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=book_activities" class="nav-link <?php echo ($page === 'book_activities') ? 'active' : ''; ?>">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>Actividades</span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=book_export" class="nav-link <?php echo ($page === 'book_export') ? 'active' : ''; ?>">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Maquetación</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="index.php?page=announcements" class="nav-link <?php echo ($page === 'announcements') ? 'active' : ''; ?>">
                            <i class="fas fa-bullhorn"></i>
                            <span>Anuncios Públicos</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=settings" class="nav-link <?php echo ($page === 'settings') ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            
            <div class="sidebar-footer">
                <div class="user-info" style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                    <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                </div>
                
                <!-- Notifications Bell -->
                <div class="notifications-bell" style="margin-bottom: 0.75rem;">
                    <button class="btn-notification" id="notificationsButton" onclick="toggleNotifications(event)">
                        <i class="fas fa-bell"></i>
                        <span class="notification-text">Notificaciones</span>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="notification-badge" id="notificationBadge"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <!-- Dropdown -->
                    <div class="notifications-dropdown" id="notificationsDropdown" style="display: none;">
                        <div class="notifications-header">
                            <h3>Notificaciones</h3>
                            <button onclick="markAllAsRead()" class="btn-link-small">Marcar todas</button>
                        </div>
                        <div class="notifications-list" id="notificationsList">
                            <div class="notification-loading">
                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                            </div>
                        </div>
                        <div class="notifications-footer">
                            <a href="index.php?page=notifications" class="btn-link">Ver todas <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Audit Log -->
                <div class="audit-log-link" style="margin-bottom: 0.75rem;">
                    <a href="index.php?page=audit_log" class="btn btn-sm w-full" style="display: flex; align-items: center; justify-content: flex-start; gap: 0.5rem; padding: 0.5rem; background: var(--bg-card); border: 1px solid var(--border-light); color: var(--text-main); border-radius: 0.5rem; text-decoration: none;">
                        <i class="fas fa-user-shield"></i>
                        <span>Auditoría</span>
                    </a>
                </div>
                
                <!-- Language Selector -->
                <div class="language-selector" style="margin-bottom: 0.75rem;">
                    <select id="languageSelect" onchange="changeLanguage(this.value)" style="width: 100%; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border-light); background: var(--bg-card); color: var(--text-main); cursor: pointer;">
                        <option value="es" <?php echo (current_lang() === 'es') ? 'selected' : ''; ?>>🇪🇸 Español</option>
                        <option value="en" <?php echo (current_lang() === 'en') ? 'selected' : ''; ?>>🇬🇧 English</option>
                    </select>
                </div>
                
                <!-- Dark Mode Toggle -->
                <div class="dark-mode-toggle" style="margin-bottom: 0.75rem;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">
                            <i class="fas fa-moon"></i>
                            <span class="toggle-text"><?php echo __('dark_mode'); ?></span>
                        </span>
                    </label>
                </div>
                
                <a href="index.php?page=login&action=logout" class="btn btn-sm btn-danger w-full btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-logout-text"><?php echo __('logout'); ?></span>
                </a>
            </div>

        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php
            if (isset($view)) {
                // Incluir la vista correspondiente si existe
                $viewPath = __DIR__ . '/' . $view . '.php';
                if (file_exists($viewPath)) {
                    include $viewPath;
                } elseif ($view === 'events_dashboard') {
                    include __DIR__ . '/reports/events_dashboard.php';
                }
            } elseif (isset($content)) {
                echo $content;
            }
            ?>
        </main>
    </div>

    <script>
        // Language Changer
        function changeLanguage(lang) {
            window.location.href = 'index.php?page=language&action=change&lang=' + lang;
        }
        
        // Initialize Dark Mode
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.getElementById('darkModeToggle').checked = true;
        }

        // Dark Mode Toggle
        document.getElementById('darkModeToggle').addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            }
        });

        // Global Search
        const searchInput = document.getElementById('globalSearchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        // Keyboard shortcut (Ctrl+K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
            // ESC to close results
            if (e.key === 'Escape') {
                searchResults.style.display = 'none';
            }
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('index.php?page=search&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        displaySearchResults(data.results);
                    })
                    .catch(error => console.error('Search error:', error));
            }, 300);
        });

        function displaySearchResults(results) {
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="search-no-results"><i class="fas fa-search"></i><br>No se encontraron resultados</div>';
                searchResults.style.display = 'block';
                return;
            }

            let html = '';
            results.forEach(result => {
                // Avatar or icon
                let avatarHtml = '';
                if (result.avatar) {
                    avatarHtml = `<img src="${result.avatar}" class="search-result-avatar" alt="">`;
                } else {
                    const iconClass = result.type_icon || 'fa-circle';
                    avatarHtml = `<div class="search-result-icon" style="background: ${result.type_color}"><i class="fas ${iconClass}"></i></div>`;
                }
                
                // Status badge
                let statusBadge = '';
                if (result.status === 'active') {
                    statusBadge = '<span class="status-indicator status-active"></span>';
                } else if (result.status === 'inactive') {
                    statusBadge = '<span class="status-indicator status-inactive"></span>';
                } else if (result.status === 'pending') {
                    statusBadge = '<span class="status-indicator status-pending"></span>';
                }
                
                // Category badge
                let categoryBadge = '';
                if (result.badge) {
                    categoryBadge = `<span class="search-result-badge" style="background: ${result.badge_color}">${result.badge}</span>`;
                }
                
                html += `
                    <a href="${result.url}" class="search-result-item">
                        ${avatarHtml}
                        <div class="search-result-content">
                            <div class="search-result-header">
                                <span class="search-result-type" style="background: ${result.type_color}">
                                    <i class="fas ${result.type_icon || 'fa-circle'}"></i> ${result.type_label}
                                </span>
                                ${statusBadge}
                                ${categoryBadge}
                            </div>
                            <div class="search-result-title">${result.title}</div>
                            <div class="search-result-subtitle">${result.subtitle}</div>
                        </div>
                    </a>
                `;
            });

            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        // Click outside to close
        document.addEventListener('click', function(e) {
            if (!document.getElementById('globalSearch').contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Notifications System
        let notificationsLoaded = false;

        function toggleNotifications(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notificationsDropdown');
            
            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
                
                // Load notifications if not loaded yet
                if (!notificationsLoaded) {
                    loadNotifications();
                }
            } else {
                dropdown.style.display = 'none';
            }
        }

        function loadNotifications() {
            fetch('index.php?page=notifications&action=getRecent')
                .then(response => response.json())
                .then(data => {
                    displayNotifications(data.notifications);
                    updateBadge(data.unread_count);
                    notificationsLoaded = true;
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notificationsList').innerHTML = 
                        '<div class="notification-error">Error al cargar notificaciones</div>';
                });
        }

        function displayNotifications(notifications) {
            const list = document.getElementById('notificationsList');
            
            if (notifications.length === 0) {
                list.innerHTML = '<div class="notification-empty"><i class="fas fa-check-circle"></i><br>No hay notificaciones nuevas</div>';
                return;
            }

            let html = '';
            notifications.forEach(notif => {
                const typeIcons = {
                    'payment_reminder': 'fa-money-bill-wave',
                    'event_reminder': 'fa-calendar-alt',
                    'announcement': 'fa-bullhorn',
                    'system': 'fa-cog',
                    'welcome': 'fa-hand-wave'
                };
                const icon = typeIcons[notif.type] || 'fa-bell';
                const link = notif.link || '#';
                
                html += `
                    <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" onclick="handleNotificationClick(${notif.id}, '${link}')">
                        <div class="notification-icon">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notif.title}</div>
                            <div class="notification-message">${notif.message}</div>
                            <div class="notification-time">${formatTime(notif.created_at)}</div>
                        </div>
                        ${!notif.is_read ? '<span class="notification-dot"></span>' : ''}
                    </div>
                `;
            });

            list.innerHTML = html;
        }

        function handleNotificationClick(id, link) {
            // Mark as read via AJAX
            fetch('index.php?page=notifications&action=markAsRead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}`
            });

            // Navigate if there's a link
            if (link && link !== '#') {
                window.location.href = link;
            }
        }

        function markAllAsRead() {
            fetch('index.php?page=notifications&action=markAllAsRead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(() => {
                location.reload();
            });
        }

        function updateBadge(count) {
            const badge = document.getElementById('notificationBadge');
            if (count > 0) {
                if (badge) {
                    badge.textContent = count;
                } else {
                    const button = document.getElementById('notificationsButton');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.id = 'notificationBadge';
                    newBadge.textContent = count;
                    button.appendChild(newBadge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return 'Hace un momento';
            if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
            if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
            if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;
            
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notificationsDropdown');
            const button = document.getElementById('notificationsButton');
            
            if (dropdown && !dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Auto-refresh notifications every 2 minutes
        setInterval(function() {
            if (notificationsLoaded) {
                loadNotifications();
            }
        }, 120000);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            function toggleMenu() {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('active');
                
                // Change icon
                const icon = mobileMenuToggle.querySelector('i');
                if (sidebar.classList.contains('mobile-open')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', toggleMenu);
            }

            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', toggleMenu);
            }

            // Handle submenu toggle
            const navGroups = document.querySelectorAll('.nav-group > .nav-link');
            
            // Mark active submenu parent as open on page load
            const activeSublink = document.querySelector('.nav-submenu .nav-link.active');
            if (activeSublink) {
                const parentGroup = activeSublink.closest('.nav-group');
                if (parentGroup) {
                    parentGroup.classList.add('open');
                }
            }
            
            navGroups.forEach(function(navLink) {
                // Handle chevron click separately
                const chevron = navLink.querySelector('.fa-chevron-down');
                if (chevron) {
                    chevron.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const parentLi = navLink.parentElement;
                        const wasOpen = parentLi.classList.contains('open');
                        
                        // Close all other submenus
                        document.querySelectorAll('.nav-group').forEach(function(group) {
                            if (group !== parentLi) {
                                group.classList.remove('open');
                            }
                        });
                        
                        // Toggle current submenu
                        if (wasOpen) {
                            parentLi.classList.remove('open');
                        } else {
                            parentLi.classList.add('open');
                        }
                    });
                }
                
                // If clicking the main link (not the chevron), allow navigation but also toggle if it's a hash
                navLink.addEventListener('click', function(e) {
                    // If it's a # link and no chevron was clicked, toggle submenu
                    if (this.getAttribute('href') === '#') {
                        e.preventDefault();
                        const parentLi = this.parentElement;
                        const wasOpen = parentLi.classList.contains('open');
                        
                        // Close all other submenus
                        document.querySelectorAll('.nav-group').forEach(function(group) {
                            if (group !== parentLi) {
                                group.classList.remove('open');
                            }
                        });
                        
                        // Toggle current submenu
                        if (wasOpen) {
                            parentLi.classList.remove('open');
                        } else {
                            parentLi.classList.add('open');
                        }
                    }
                    // If it has a real link, let it navigate (don't prevent default)
                });
            });
        });
    </script>
</body>
</html>
