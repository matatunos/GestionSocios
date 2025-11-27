<?php
// Diagnostic script to test export.php rendering
// Place this in public/test_export.php and access via browser

session_start();

// Simulate logged-in admin user
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Set page parameter
$_GET['page'] = 'book_export';
$_GET['year'] = date('Y');

// Include the main index routing
require __DIR__ . '/../src/Config/database.php';

$database = new Database();
$db = $database->getConnection();

// Manually instantiate and call the controller
require_once __DIR__ . '/../src/Controllers/BookExportController.php';
$controller = new BookExportController();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!-- Starting controller index method -->\n";
$controller->index();
echo "\n<!-- Controller index method completed -->\n";
