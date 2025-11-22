<?php
// Ensure page and action variables are available
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión <?php echo htmlspecialchars($associationName); ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="nav-brand">
                    <i class="fas fa-users-cog"></i>
                    <span>Gestión</span>
                </div>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-chevron-left"></i>
                </button>
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
            
            <ul class="nav-menu">
                <li>
                    <a href="index.php?page=dashboard" class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Panel</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=members" class="nav-link <?php echo ($page === 'members') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Socios</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=member_categories" class="nav-link <?php echo ($page === 'member_categories') ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Categorías</span>
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
                    <a href="index.php?page=gallery" class="nav-link <?php echo ($page === 'gallery') ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>
                        <span>Galería Imágenes</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=donations" class="nav-link <?php echo ($page === 'donations') ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Donaciones</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=expenses" class="nav-link <?php echo ($page === 'expenses') ? 'active' : ''; ?>">
                        <i class="fas fa-receipt"></i>
                        <span>Gastos</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=treasury" class="nav-link <?php echo ($page === 'treasury') ? 'active' : ''; ?>">
                        <i class="fas fa-coins"></i>
                        <span>Tesorería</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=calendar" class="nav-link <?php echo ($page === 'calendar') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Calendario</span>
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
                
                <!-- Dark Mode Toggle -->
                <div class="dark-mode-toggle" style="margin-bottom: 0.75rem;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">
                            <i class="fas fa-moon"></i>
                            <span class="toggle-text">Modo Oscuro</span>
                        </span>
                    </label>
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

        // Initialize Sidebar State
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

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
                html += `
                    <a href="${result.url}" class="search-result-item" style="display: block; text-decoration: none; color: inherit;">
                        <span class="search-result-type" style="background: ${result.type_color}">
                            ${result.type_label}
                        </span>
                        <div class="search-result-title">${result.title}</div>
                        <div class="search-result-subtitle">${result.subtitle}</div>
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

        // Mobile Menu
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const sidebar = document.getElementById('sidebar');

        // Show mobile menu button on mobile devices
        if (window.innerWidth <= 768) {
            mobileMenuToggle.style.display = 'flex';
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                mobileMenuToggle.style.display = 'flex';
            } else {
                mobileMenuToggle.style.display = 'none';
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            }
        });

        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
        });

        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
        });
    </script>
</body>
</html>
