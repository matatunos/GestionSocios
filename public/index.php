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

// Determine page
$page = $_GET['page'] ?? 'dashboard';

// Check Installation & DB Connection
if (!file_exists(__DIR__ . '/../src/Config/config.php')) {
    $page = 'install';
} else {
    // Config exists, verify connection if not already in install mode
    if ($page !== 'install') {
        require_once __DIR__ . '/../src/Config/Database.php';
        $dbTest = new Database();
        if ($dbTest->getConnection() === null) {
            $page = 'db_error';
        } else {
            // Initialize $db for controllers
            $db = $dbTest->getConnection();
        }
    }
}

$action = $_GET['action'] ?? 'index';

// Check Auth (skip for login, install, update, and db_error)
if (!isset($_SESSION['user_id']) && $page !== 'login' && $page !== 'install' && $page !== 'update' && $page !== 'db_error') {
    header('Location: index.php?page=login');
    exit;
}

// Routing Logic
switch ($page) {
    case 'db_error':
        require __DIR__ . '/../src/Views/install/db_error.php';
        break;
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
        else if ($action === 'compareImages') $controller->compareImages();
        else if ($action === 'selectImage') $controller->selectImage($_GET['id']);
        else if ($action === 'imageHistory') $controller->imageHistory($_GET['id']);
        else if ($action === 'restoreImage') $controller->restoreImage($_GET['id'], $_GET['historyId']);
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
        else if ($action === 'gallery') $controller->gallery();
        else if ($action === 'compareImages') $controller->compareImages();
        else if ($action === 'selectImage') $controller->selectImage($_GET['id']);
        else if ($action === 'imageHistory') $controller->imageHistory($_GET['id']);
        else if ($action === 'restoreImage') $controller->restoreImage($_GET['id'], $_GET['historyId']);
        else $controller->index();
        break;
    case 'book':
        $controller = new BookAdController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else $controller->index();
        break;
    case 'member_categories':
        $controller = new MemberCategoryController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else $controller->index();
        break;
    case 'expenses':
        $controller = new ExpenseController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else $controller->index();
        break;
    case 'treasury':
        $controller = new TreasuryController($db);
        $controller->dashboard();
        break;
    case 'calendar':
        $controller = new CalendarController($db);
        if ($action === 'api') $controller->api();
        else if ($action === 'viewEvent') $controller->viewEvent();
        else if ($action === 'registerAttendance') $controller->registerAttendance();
        else if ($action === 'updateAttendanceStatus') $controller->updateAttendanceStatus();
        else if ($action === 'deleteAttendance') $controller->deleteAttendance();
        else $controller->index();
        break;
    case 'notifications':
        $controller = new NotificationsController($db);
        if ($action === 'getRecent') $controller->getRecent();
        else if ($action === 'markAsRead') $controller->markAsRead();
        else if ($action === 'markAllAsRead') $controller->markAllAsRead();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
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
        require_once __DIR__ . '/../src/Controllers/ReportController.php';
        $controller = new ReportController();
        if ($action === 'executive') $controller->executiveReport();
        else if ($action === 'export_members') $controller->exportMembers();
        else if ($action === 'export_donors') $controller->exportDonors();
        else if ($action === 'export_movements') $controller->exportMovements();
        else $controller->executiveReport();
        break;
    case 'search':
        $controller = new SearchController();
        $controller->search();
        break;
    case 'export':
        $controller = new ExportController($db);
        if ($action === 'members_excel') $controller->exportMembersExcel();
        else if ($action === 'members_pdf') $controller->exportMembersPDF();
        else if ($action === 'donations_excel') $controller->exportDonationsExcel();
        else if ($action === 'expenses_excel') $controller->exportExpensesExcel();
        else if ($action === 'events_excel') $controller->exportEventsExcel();
        else if ($action === 'payments_excel') $controller->exportPaymentsExcel();
        else header('Location: index.php?page=dashboard');
        break;
    case 'documents':
        $controller = new DocumentController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'download') $controller->download();
        else $controller->index();
        break;
    case 'ad_prices':
        require_once __DIR__ . '/../src/Controllers/AdPriceController.php';
        $controller = new AdPriceController();
        if ($action === 'store') {
            $controller->store();
        } else {
            $controller->index();
        }
        break;
    case 'gallery':
        require_once __DIR__ . '/../src/Controllers/GalleryController.php';
        $controller = new GalleryController();
        $controller->index();
        break;
    default:
        echo "404 Not Found";
        break;
}
