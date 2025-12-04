<?php

/**
 * Grant Scraper Cron Job
 * 
 * Script para ejecutar búsquedas automáticas de subvenciones.
 * Debe ejecutarse periódicamente desde cron (ejemplo: cada día a las 8:00)
 * 
 * Configuración crontab:
 * 0 8 * * * /usr/bin/php /path/to/cron/grant_scraper_cron.php >> /path/to/logs/grant_scraper.log 2>&1
 */

// Establecer timezone
date_default_timezone_set('Europe/Madrid');

// Incluir archivos necesarios
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Helpers/GrantScraperHelper.php';

// Logging
$logFile = __DIR__ . '/../logs/grant_scraper_' . date('Ymd') . '.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    logMessage("=== Iniciando búsqueda automática de subvenciones ===");
    
    // Conectar a base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a base de datos");
    }
    
    logMessage("Conexión a base de datos establecida");
    
    // Ejecutar búsquedas programadas
    $totalResults = GrantScraperHelper::runScheduledSearches($db);
    
    logMessage("Búsqueda completada: $totalResults nuevas subvenciones encontradas");
    
    // Opcional: Generar alertas para subvenciones próximas a vencer
    $alertsGenerated = generateDeadlineAlerts($db);
    logMessage("Alertas de deadline generadas: $alertsGenerated");
    
    logMessage("=== Proceso completado exitosamente ===");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

/**
 * Generar alertas para subvenciones próximas a vencer
 */
function generateDeadlineAlerts($db) {
    $alertsCount = 0;
    
    // Buscar subvenciones que vencen en los próximos 7 días y no tienen alerta
    $query = "SELECT id, title, organization, deadline, 
                     DATEDIFF(deadline, CURDATE()) as days_remaining
              FROM grants 
              WHERE status = 'abierta' 
              AND deadline >= CURDATE() 
              AND deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
              AND (deadline_alert_sent = 0 OR deadline_alert_sent IS NULL)
              ORDER BY deadline ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $grants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($grants as $grant) {
        // Aquí se puede implementar envío de email, notificación push, etc.
        // Por ahora, solo marcamos la alerta como enviada
        
        $updateQuery = "UPDATE grants SET deadline_alert_sent = 1 WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':id' => $grant['id']]);
        
        logMessage("Alerta generada: '{$grant['title']}' vence en {$grant['days_remaining']} días");
        $alertsCount++;
    }
    
    return $alertsCount;
}
