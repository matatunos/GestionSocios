<?php
session_start();

// Autoloader (simple implementation for now)
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        // Fallback for classes without namespace in src/Models or src/Controllers
        $dirs = ['Config', 'Controllers', 'Models', 'Helpers'];
        foreach ($dirs as $dir) {
            $file = __DIR__ . '/../src/' . $dir . '/' . $class_name . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Initialize Language System
require_once __DIR__ . '/../src/Helpers/Lang.php';
$lang = Lang::getInstance();

// Determine page
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

$page = $_GET['page'] ?? 'dashboard';

// Manejar acceso público a documentos (sin autenticación)
if ($page === 'public_document') {
    require_once __DIR__ . '/../src/Controllers/PublicDocumentController.php';
    $controller = new PublicDocumentController();
    $action = $_GET['action'] ?? 'view';
    
    if ($action === 'download') {
        $controller->download();
    } else {
        $controller->view();
    }
    exit;
}

// Check if it's an API request
if (strpos($requestUri, '/api/') === 0 || strpos($requestUri, $basePath . '/api/') === 0) {
    $page = 'api';
}

// Check Installation & DB Connection
if (!file_exists(__DIR__ . '/../src/Config/config.php')) {
    require __DIR__ . '/../src/Views/install/setup_required.php';
    exit;
}

require_once __DIR__ . '/../src/Config/database.php';
$dbTest = new Database();
$conn = $dbTest->getConnection();

if ($conn === null) {
    require __DIR__ . '/../src/Views/install/connection_error.php';
    exit;
}

// Check if main tables exist (users)
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() === 0) {
        require __DIR__ . '/../src/Views/install/tables_missing.php';
        exit;
    }
    // Initialize $db for controllers
    $db = $conn;
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    require __DIR__ . '/../src/Views/install/database_error.php';
    exit;
}

$action = $_GET['action'] ?? 'index';

// Check Auth (skip for login and update)
if (!isset($_SESSION['user_id']) && $page !== 'login' && $page !== 'update') {
    header('Location: index.php?page=login');
    exit;
}

// Routing Logic
switch ($page) {
    case 'update':
        $controller = new UpdateController();
        $controller->index();
        break;
    case 'login':
        $controller = new AuthController();
        if ($action === 'login') {
            $controller->login();
        } else if ($action === 'logout') {
            $controller->logout();
        } else {
            $controller->showLogin();
        }
        break;
    case 'book_page_api':
        require_once __DIR__ . '/../src/Controllers/BookPageApiController.php';
        $controller = new BookPageApiController();
        if ($action === 'savePages') {
            $controller->savePages();
        } else if ($action === 'createVersion') {
            $controller->createVersion();
        }
        break;
    case 'dashboard':
        $controller = new DashboardController();
        if ($action === 'markEventPaymentPaid') $controller->markEventPaymentPaid();
        else $controller->index();
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
        else if ($action === 'map') $controller->map();
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
        else if ($action === 'updateAttendanceStatus') $controller->updateAttendanceStatus();
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
        case 'document_categories':
            require_once __DIR__ . '/../src/Controllers/DocumentCategoryController.php';
            $controller = new DocumentCategoryController($db);
            if ($action === 'create') $controller->create();
            else if ($action === 'delete') $controller->delete();
            else $controller->index();
            break;
    case 'suppliers':
        require_once __DIR__ . '/../src/Controllers/SupplierController.php';
        $controller = new SupplierController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'show') $controller->show();
        else if ($action === 'uploadInvoice') $controller->uploadInvoice();
        else if ($action === 'deleteInvoice') $controller->deleteInvoice();
        else if ($action === 'dashboard') $controller->dashboard();
        else $controller->index();
        break;
    case 'book':
        if ($action === 'dashboard') {
            require_once __DIR__ . '/../src/Controllers/BookDashboardController.php';
            $controller = new BookDashboardController();
            $controller->index();
        } else {
            $controller = new BookAdController();
            if ($action === 'create') $controller->create();
            else if ($action === 'store') $controller->store();
            else if ($action === 'edit') $controller->edit($_GET['id']);
            else if ($action === 'update') $controller->update($_GET['id']);
            else if ($action === 'markPaid') $controller->markPaid($_GET['id']);
            else if ($action === 'delete') $controller->delete($_GET['id']);
            else $controller->index();
        }
        break;
    case 'book_activities':
        require_once __DIR__ . '/../src/Controllers/BookActivityController.php';
        $controller = new BookActivityController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit($_GET['id']);
        else if ($action === 'update') $controller->update($_GET['id']);
        else if ($action === 'delete') $controller->delete($_GET['id']);
        else $controller->index();
        break;
    case 'book_export':
        require_once __DIR__ . '/../src/Controllers/BookExportController.php';
        $controller = new BookExportController();
        if ($action === 'generatePdf') $controller->generatePdf();
        else if ($action === 'generateDocx') $controller->generateDocx();
        else $controller->index();
        break;
    case 'member_categories':
        $controller = new MemberCategoryController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'updateFee') $controller->updateFee();
        else if ($action === 'deleteFee') $controller->deleteFee();
        else if ($action === 'delete') $controller->delete();
        else $controller->index();
        break;

    case 'announcements':
        require_once __DIR__ . '/../src/Controllers/AnnouncementController.php';
        $controller = new AnnouncementController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'toggleActive') $controller->toggleActive();
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
    case 'certificates':
        require_once __DIR__ . '/../src/Controllers/CertificateController.php';
        $controller = new CertificateController();
        if ($action === 'membership') $controller->membership();
        else if ($action === 'payments') $controller->payments();
        else if ($action === 'donations') $controller->donations();
        else if ($action === 'download') $controller->download();
        else {
            $_SESSION['error'] = 'Acción no válida';
            header('Location: index.php?page=dashboard');
            exit;
        }
        break;
    case 'analytics':
        require_once __DIR__ . '/../src/Controllers/AnalyticsController.php';
        $controller = new AnalyticsController();
        if ($action === 'getData') $controller->getData();
        else $controller->index();
        break;
    case 'settings':
        require_once __DIR__ . '/../src/Controllers/SettingsController.php';
        $controller = new SettingsController();
        if ($action === 'updateGeneral') $controller->updateGeneral();
        else if ($action === 'updateDatabase') $controller->updateDatabase();
        else if ($action === 'downloadBackup') $controller->downloadBackup();
        else if ($action === 'updateOrganization') $controller->updateOrganization();
        else if ($action === 'deleteLogo') $controller->deleteLogo();
        else if ($action === 'changePassword') $controller->changePassword();
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
        if ($action === 'dashboard_events') $controller->eventsDashboard();
        else if ($action === 'export_members') $controller->exportMembers();
        else if ($action === 'export_donors') $controller->exportDonors();
        else if ($action === 'export_movements') $controller->exportMovements();
        else $controller->eventsDashboard();
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
        else if ($action === 'events_excel') $controller->exportEventsExcel();
        else if ($action === 'payments_excel') $controller->exportPaymentsExcel();
        else header('Location: index.php?page=dashboard');
        break;
    case 'documents':
        $controller = new DocumentController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'bulk_upload') $controller->bulkUpload();
        else if ($action === 'bulk_store') $controller->bulkStore();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'download') $controller->download();
        else if ($action === 'trash') $controller->trash();
        else if ($action === 'restore') $controller->restore();
        else if ($action === 'permanent_delete') $controller->permanentDelete();
        else if ($action === 'versions') $controller->versions();
        else if ($action === 'upload_version') $controller->uploadVersion();
        else if ($action === 'favorite') $controller->toggleFavorite();
        else if ($action === 'favorites') $controller->favorites();
        else if ($action === 'preview') $controller->preview();
        else if ($action === 'dashboard') $controller->dashboard();
        else if ($action === 'generate_public') $controller->generatePublic();
        else if ($action === 'revoke_public') $controller->revokePublic();
        else if ($action === 'public_links') $controller->publicLinks();
        else if ($action === 'public_stats') $controller->publicStats();
        else $controller->index();
        break;
    case 'document_folders':
        require_once __DIR__ . '/../src/Controllers/DocumentFolderController.php';
        $controller = new DocumentFolderController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else $controller->index();
        break;
    case 'document_tags':
        require_once __DIR__ . '/../src/Controllers/DocumentTagController.php';
        $controller = new DocumentTagController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
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
    case 'polls':
        $controller = new PollController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'view') $controller->view();
        else if ($action === 'vote') $controller->vote();
        else if ($action === 'close') $controller->close();
        else if ($action === 'delete') $controller->delete();
        else $controller->index();
        break;
    case 'messages':
        $controller = new MessageController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'view') $controller->view();
        else if ($action === 'send') $controller->send();
        else if ($action === 'poll') $controller->poll();
        else if ($action === 'startDirect') $controller->startDirect();
        else $controller->index();
        break;
    case 'language':
        $controller = new LanguageController();
        if ($action === 'change') $controller->change();
        else header('Location: index.php?page=dashboard');
        break;
    case 'api':
        $controller = new ApiController();
        $controller->index();
        break;
    case 'tasks':
        $controller = new TaskController($db);
        if ($action === 'create') $controller->create();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'view') $controller->view();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'complete') $controller->complete();
        else if ($action === 'addComment') $controller->addComment();
        else $controller->index();
        break;
    case 'map':
        $controller = new MapController();
        if ($action === 'getLocations') $controller->getLocations();
        else $controller->index();
        break;
    case 'geo':
        $controller = new GeoController();
        if ($action === 'reverse') $controller->reverse();
        else {
            http_response_code(404);
            echo json_encode(['error' => 'Invalid action']);
        }
        break;
    case 'audit_log':
        require_once __DIR__ . '/../src/Controllers/AuditLogController.php';
        $controller = new AuditLogController($db ?? null);
        if ($action === 'export_excel') $controller->export_excel();
        else if ($action === 'export_pdf') $controller->export_pdf();
        else $controller->index();
        break;
    case 'settings':
        $controller = new SettingsController();
        if ($action === 'updateGeneral') $controller->updateGeneral();
        else if ($action === 'updateDatabase') $controller->updateDatabase();
        else if ($action === 'resetAdminPassword') $controller->resetAdminPassword();
        else if ($action === 'updateOrganization') $controller->updateOrganization();
        else if ($action === 'deleteLogo') $controller->deleteLogo();
        else if ($action === 'changePassword') $controller->changePassword();
        else if ($action === 'save_notifications') $controller->save_notifications();
        else if ($action === 'downloadBackup') $controller->downloadBackup();
        else if ($action === 'updateSocialMedia') $controller->updateSocialMedia();
        else if ($action === 'updatePasswordPolicy') $controller->updatePasswordPolicy();
        else $controller->index();
        break;
    case 'social_media':
        $controller = new SocialMediaController();
        $controller->handleRequest();
        break;
    case 'accounting':
        $controller = new AccountingController();
        if ($action === 'accounts') $controller->accounts();
        else if ($action === 'createAccount') $controller->createAccount();
        else if ($action === 'storeAccount') $controller->storeAccount();
        else if ($action === 'editAccount') $controller->editAccount();
        else if ($action === 'updateAccount') $controller->updateAccount();
        else if ($action === 'entries') $controller->entries();
        else if ($action === 'createEntry') $controller->createEntry();
        else if ($action === 'storeEntry') $controller->storeEntry();
        else if ($action === 'viewEntry') $controller->viewEntry();
        else if ($action === 'postEntry') $controller->postEntry();
        else if ($action === 'generalLedger') $controller->generalLedger();
        else if ($action === 'trialBalance') $controller->trialBalance();
        else if ($action === 'periods') $controller->periods();
        else if ($action === 'createPeriod') $controller->createPeriod();
        else if ($action === 'storePeriod') $controller->storePeriod();
        else if ($action === 'editPeriod') $controller->editPeriod();
        else if ($action === 'updatePeriod') $controller->updatePeriod();
        else if ($action === 'closePeriod') $controller->closePeriod();
        else if ($action === 'balanceSheet') $controller->balanceSheet();
        else if ($action === 'incomeStatement') $controller->incomeStatement();
        else if ($action === 'exportReport') $controller->exportReport();
        else $controller->dashboard();
        break;
    case 'budget':
        $controller = new BudgetController();
        if ($action === 'create') $controller->create();
        else if ($action === 'store') $controller->store();
        else if ($action === 'edit') $controller->edit();
        else if ($action === 'update') $controller->update();
        else if ($action === 'delete') $controller->delete();
        else if ($action === 'report') $controller->report();
        else $controller->index();
        break;
    default:
        echo "404 Not Found";
        break;
}
