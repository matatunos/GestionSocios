<?php

/**
 * Script de prueba para el scraper de subvenciones
 * 
 * Uso: php test_scraper.php [keywords] [source]
 * Ejemplo: php test_scraper.php "cultura deporte" bdns
 */

require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Helpers/GrantScraperHelper.php';

// Obtener parámetros
$keywords = isset($argv[1]) ? $argv[1] : '';
$source = isset($argv[2]) ? $argv[2] : 'all';

echo "\n========================================\n";
echo "TEST: Grant Scraper\n";
echo "========================================\n";
echo "Keywords: " . ($keywords ?: '[todos]') . "\n";
echo "Source: $source\n";
echo "========================================\n\n";

try {
    // Conectar a base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Error de conexión a base de datos");
    }
    
    echo "✓ Conexión a base de datos establecida\n\n";
    
    // Crear scraper
    $scraper = new GrantScraperHelper($db);
    
    // Filtros de prueba
    $filters = [
        'keywords' => $keywords,
        'province' => '', // Puedes especificar provincia aquí
        'category' => ''  // O categoría específica
    ];
    
    echo "Iniciando búsqueda...\n";
    echo "NOTA: Esto puede tardar varios minutos dependiendo de las fuentes\n\n";
    
    $startTime = microtime(true);
    
    // Ejecutar búsqueda
    if ($source === 'bdns') {
        echo "--- Buscando en BDNS ---\n";
        $results = $scraper->searchBDNS($keywords, $filters);
        echo "Resultados encontrados: " . count($results) . "\n\n";
        
        if (!empty($results)) {
            echo "Primeros 3 resultados:\n";
            foreach (array_slice($results, 0, 3) as $i => $grant) {
                echo "\n" . ($i + 1) . ". {$grant['title']}\n";
                echo "   Organismo: {$grant['organization']}\n";
                echo "   Tipo: {$grant['grant_type']}\n";
                echo "   Deadline: {$grant['deadline']}\n";
                if (!empty($grant['max_amount'])) {
                    echo "   Importe máx: " . number_format($grant['max_amount'], 2) . " €\n";
                }
            }
        }
        
    } else if ($source === 'boe') {
        echo "--- Buscando en BOE ---\n";
        $results = $scraper->searchBOE($keywords, $filters);
        echo "Resultados encontrados: " . count($results) . "\n\n";
        
        if (!empty($results)) {
            echo "Primeros 3 resultados:\n";
            foreach (array_slice($results, 0, 3) as $i => $grant) {
                echo "\n" . ($i + 1) . ". {$grant['title']}\n";
                echo "   Organismo: {$grant['organization']}\n";
                echo "   URL: {$grant['url']}\n";
                if (!empty($grant['announcement_date'])) {
                    echo "   Publicado: {$grant['announcement_date']}\n";
                }
            }
        }
        
    } else {
        // Buscar en todas las fuentes
        echo "--- Buscando en TODAS las fuentes ---\n";
        $results = $scraper->searchAll($keywords, $filters);
        
        echo "\n=== RESUMEN ===\n";
        echo "Total encontradas: {$results['total_found']}\n";
        echo "Nuevas insertadas: {$results['total_new']}\n";
        echo "Actualizadas: {$results['total_updated']}\n";
        echo "\nPor fuente:\n";
        echo "  - BDNS: " . count($results['bdns']) . "\n";
        echo "  - BOE: " . count($results['boe']) . "\n";
        
        if (!empty($results['errors'])) {
            echo "\nErrores encontrados:\n";
            foreach ($results['errors'] as $error) {
                echo "  ⚠ $error\n";
            }
        }
        
        // Mostrar algunas subvenciones encontradas
        $allGrants = array_merge($results['bdns'], $results['boe']);
        if (!empty($allGrants)) {
            echo "\n=== EJEMPLOS DE SUBVENCIONES ENCONTRADAS ===\n";
            foreach (array_slice($allGrants, 0, 5) as $i => $grant) {
                echo "\n" . ($i + 1) . ". {$grant['title']}\n";
                echo "   Fuente: {$grant['source']}\n";
                echo "   Organismo: {$grant['organization']}\n";
                echo "   Tipo: {$grant['grant_type']}\n";
                if (!empty($grant['deadline'])) {
                    echo "   Deadline: {$grant['deadline']}\n";
                }
                if (!empty($grant['max_amount'])) {
                    echo "   Importe máx: " . number_format($grant['max_amount'], 2) . " €\n";
                }
            }
        }
    }
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n========================================\n";
    echo "Tiempo de ejecución: {$duration}s\n";
    echo "========================================\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✓ Test completado\n\n";
