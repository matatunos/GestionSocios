#!/usr/bin/env php
<?php
/**
 * Cron Job: Notificaciones Programadas
 * 
 * Este script debe ejecutarse diariamente para enviar notificaciones automáticas.
 * 
 * Configuración de Cron (ejecutar diariamente a las 9:00 AM):
 * 0 9 * * * /usr/bin/php /opt/GestionSocios/cron/notifications_cron.php >> /var/log/gestion_socios_cron.log 2>&1
 * 
 * O en Windows Task Scheduler:
 * Program: C:\php\php.exe
 * Arguments: C:\path\to\GestionSocios\cron\notifications_cron.php
 * Schedule: Daily at 9:00 AM
 */

// Incluir autoloader y configuración
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once ROOT_PATH . '/src/Helpers/NotificationHelper.php';

echo "=== Cron Job: Notificaciones Programadas ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "✓ Conexión a base de datos establecida\n\n";
    
    // Ejecutar tareas programadas
    $results = NotificationHelper::runScheduledTasks($db);
    
    echo "Resultados:\n";
    echo "  - Recordatorios de pago enviados: " . $results['payment_reminders'] . "\n";
    echo "  - Recordatorios de eventos enviados: " . $results['event_reminders'] . "\n";
    echo "  - Notificaciones antiguas eliminadas: " . $results['cleaned'] . "\n\n";
    
    echo "✓ Tareas completadas exitosamente\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Fin del Cron Job ===\n";
exit(0);
