<?php
session_start();

// Autoloader (simple implementation for now)
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        // Fallback for classes without namespace in src/Models or src/Controllers
        $dirs = ['Config', 'Controllers', 'Models'];
        foreach ($dirs as $dir) {
            $file = __DIR__ . '/../src/' . $dir . '/' . $class_name . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Check Installation
if (!file_exists(__DIR__ . '/../src/Config/config.php')) {
    $page = 'install';
} else {
    $page = $_GET['page'] ?? 'dashboard';
}

$action = $_GET['action'] ?? 'index';

// Check Auth (skip for login, install, and update)
if (!isset($_SESSION['user_id']) && $page !== 'login' && $page !== 'install' && $page !== 'update') {
    header('Location: index.php?page=login');
    exit;
}

// Routing Logic
switch ($page) {
    case 'install':
        $controller = new InstallerController();
        if ($action === 'run') $controller->install();
        else $controller->index();
        break;
    case 'update':
        $controller = new UpdateController();
        $controller->index();
        break;
    case 'login':
        $controller = new AuthController();
        if ($action === 'login') $controller->login();
        else if ($action === 'logout') $controller->logout();
        else $controller->showLogin();
        break;
    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
    case 'members':
        $controller = new MemberController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit($_GET['id']);
        else if ($action === 'update') $controller->update($_GET['id']);
        else if ($action === 'deactivate') $controller->deactivate($_GET['id']);
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else if ($action === 'markPaid') $controller->markPaid($_GET['id']);
        else $controller->index();
        break;
    case 'fees':
        $controller = new FeeController();
        if ($action === 'store') $controller->store();
        else if ($action === 'generate') $controller->generatePayments($_GET['year']);
        else $controller->index();
        break;
    case 'events':
        $controller = new EventController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit($_GET['id']);
        else if ($action === 'update') $controller->update($_GET['id']);
        else if ($action === 'show') $controller->show($_GET['id']);
        else if ($action === 'markPaid') $controller->markPaid($_GET['id'], $_GET['member_id'] ?? null);
        else $controller->index();
        break;
    case 'donations':
        $controller = new DonationController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else $controller->index();
        break;
    case 'donors':
        $controller = new DonorController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit($_GET['id']);
        else if ($action === 'update') $controller->update($_GET['id']);
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else $controller->index();
        break;
    case 'book':
        $controller = new BookAdController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else $controller->index();
        break;
    case 'settings':
        $controller = new SettingsController();
        if ($action === 'updateGeneral') $controller->updateGeneral();
        else if ($action === 'updateDatabase') $controller->updateDatabase();
        else $controller->index();
        break;
    case 'payments':
        $controller = new PaymentController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit($_GET['id']);
        else if ($action === 'update') $controller->update($_GET['id']);
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else $controller->index();
        break;
    case 'reports':
        $controller = new ReportController();
        if ($action === 'executive') $controller->executiveReport();
        else $controller->executiveReport();
        break;
    default:
        echo "404 Not Found";
        break;
}
