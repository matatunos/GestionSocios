<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['role'] = 'admin'; // Mock admin session

require_once __DIR__ . '/src/Config/database.php';
require_once __DIR__ . '/src/Controllers/SettingsController.php';

try {
    $controller = new SettingsController();
    $controller->index();
    echo "\nExecution finished successfully.\n";
} catch (Throwable $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
