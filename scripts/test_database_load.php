<?php
/**
 * Script para verificar la carga del esquema y datos de ejemplo
 * en la base de datos MySQL local
 */

// Configuración de la base de datos
$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'gestion_socios_test';

// Colores para la salida en consola
function colorize($text, $color) {
    $colors = [
        'green' => "\033[0;32m",
        'red' => "\033[0;31m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function printStep($message) {
    echo "\n" . colorize("➜ ", "blue") . $message . "\n";
}

function printSuccess($message) {
    echo colorize("✓ ", "green") . $message . "\n";
}

function printError($message) {
    echo colorize("✗ ", "red") . $message . "\n";
}

function printWarning($message) {
    echo colorize("⚠ ", "yellow") . $message . "\n";
}

echo colorize("\n==============================================\n", "blue");
echo colorize("  VERIFICACIÓN DE CARGA DE BASE DE DATOS\n", "blue");
echo colorize("==============================================\n\n", "blue");

try {
    // Paso 1: Conectar a MySQL (sin seleccionar base de datos)
    printStep("Conectando a MySQL...");
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    printSuccess("Conexión establecida correctamente");

    // Paso 2: Crear base de datos de prueba
    printStep("Creando base de datos de prueba '$database'...");
    $pdo->exec("DROP DATABASE IF EXISTS $database");
    $pdo->exec("CREATE DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    printSuccess("Base de datos creada correctamente");

    // Paso 3: Seleccionar la base de datos
    $pdo->exec("USE $database");

    // Paso 4: Cargar el esquema
    printStep("Cargando esquema desde schema.sql...");
    $schemaFile = __DIR__ . '/../database/schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("No se encuentra el archivo schema.sql en: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);
    
    // Deshabilitar verificación de foreign keys temporalmente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Ejecutar el esquema (dividir por punto y coma y ejecutar cada sentencia)
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );

    $tableCount = 0;
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    $tableCount++;
                }
            } catch (PDOException $e) {
                // Ignorar errores de "tabla ya existe" o inserts duplicados
                if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    printError("Error en statement: " . substr($statement, 0, 150) . "...");
                    throw $e;
                }
            }
        }
    }
    
    // Reactivar verificación de foreign keys
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    printSuccess("Esquema cargado correctamente ($tableCount tablas creadas)");

    // Paso 5: Verificar tablas creadas
    printStep("Verificando tablas creadas...");
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    printSuccess(count($tables) . " tablas encontradas");
    
    echo "\n  Tablas creadas:\n";
    foreach ($tables as $table) {
        echo "    • $table\n";
    }

    // Paso 6: Cargar datos de ejemplo
    printStep("Cargando datos de ejemplo desde sample_data.sql...");
    $sampleDataFile = __DIR__ . '/../database/sample_data.sql';
    
    if (!file_exists($sampleDataFile)) {
        throw new Exception("No se encuentra el archivo sample_data.sql en: $sampleDataFile");
    }

    $sampleData = file_get_contents($sampleDataFile);
    
    // Ejecutar los datos de ejemplo
    $statements = array_filter(
        array_map('trim', explode(';', $sampleData)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );

    $insertCount = 0;
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
                if (stripos($statement, 'INSERT INTO') !== false) {
                    $insertCount++;
                }
            } catch (PDOException $e) {
                printError("Error en statement: " . substr($statement, 0, 100) . "...");
                throw $e;
            }
        }
    }
    printSuccess("Datos de ejemplo cargados correctamente ($insertCount inserts ejecutados)");

    // Paso 7: Verificar datos cargados
    printStep("Verificando datos cargados...");
    
    $verificaciones = [
        'organization_settings' => 'Configuración de la organización',
        'annual_fees' => 'Cuotas anuales',
        'ad_prices' => 'Precios de anuncios',
        'member_categories' => 'Categorías de socios',
        'expense_categories' => 'Categorías de gastos',
        'task_categories' => 'Categorías de tareas',
        'members' => 'Socios',
        'events' => 'Eventos',
        'event_attendance' => 'Asistencias a eventos',
        'payments' => 'Pagos',
        'donors' => 'Donantes',
        'book_ads' => 'Anuncios del libro',
        'donations' => 'Donaciones',
        'expenses' => 'Gastos',
        'tasks' => 'Tareas',
        'category_fee_history' => 'Historial de cuotas'
    ];

    echo "\n  Registros por tabla:\n";
    $totalRecords = 0;
    foreach ($verificaciones as $table => $description) {
        $result = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $result->fetchColumn();
        $totalRecords += $count;
        $color = $count > 0 ? 'green' : 'yellow';
        echo "    • " . colorize(str_pad($description, 35), $color) . ": " . 
             colorize($count . " registros", $color) . "\n";
    }

    printSuccess("Total de registros cargados: $totalRecords");

    // Paso 8: Verificar integridad referencial
    printStep("Verificando integridad referencial...");
    
    // Verificar que todos los members tienen category_id válido
    $result = $pdo->query("
        SELECT COUNT(*) 
        FROM members m 
        LEFT JOIN member_categories mc ON m.category_id = mc.id 
        WHERE m.category_id IS NOT NULL AND mc.id IS NULL
    ");
    $invalidCategories = $result->fetchColumn();
    
    if ($invalidCategories > 0) {
        printWarning("$invalidCategories socios con categorías inválidas");
    } else {
        printSuccess("Todas las categorías de socios son válidas");
    }

    // Verificar que todos los payments tienen member_id válido
    $result = $pdo->query("
        SELECT COUNT(*) 
        FROM payments p 
        LEFT JOIN members m ON p.member_id = m.id 
        WHERE p.member_id IS NOT NULL AND m.id IS NULL
    ");
    $invalidMembers = $result->fetchColumn();
    
    if ($invalidMembers > 0) {
        printWarning("$invalidMembers pagos con socios inválidos");
    } else {
        printSuccess("Todos los pagos tienen socios válidos");
    }

    // Verificar que todos los book_ads tienen donor_id válido
    $result = $pdo->query("
        SELECT COUNT(*) 
        FROM book_ads ba 
        LEFT JOIN donors d ON ba.donor_id = d.id 
        WHERE d.id IS NULL
    ");
    $invalidDonors = $result->fetchColumn();
    
    if ($invalidDonors > 0) {
        printWarning("$invalidDonors anuncios con donantes inválidos");
    } else {
        printSuccess("Todos los anuncios tienen donantes válidos");
    }

    // Resumen final
    echo colorize("\n==============================================\n", "green");
    echo colorize("  ✓ VERIFICACIÓN COMPLETADA EXITOSAMENTE\n", "green");
    echo colorize("==============================================\n\n", "green");

    echo "La base de datos de prueba '$database' ha sido creada\n";
    echo "y los datos de ejemplo se han cargado correctamente.\n\n";
    
    echo colorize("Opciones:\n", "blue");
    echo "  1. Mantener la base de datos de prueba para revisión\n";
    echo "  2. Eliminar la base de datos de prueba\n\n";
    
    echo "Para eliminar la base de datos de prueba, ejecuta:\n";
    echo colorize("  mysql -u root -proot -e \"DROP DATABASE $database;\"\n\n", "yellow");

} catch (PDOException $e) {
    printError("Error de base de datos: " . $e->getMessage());
    echo "\n" . colorize("Detalles del error:\n", "red");
    echo "  Código: " . $e->getCode() . "\n";
    echo "  Archivo: " . $e->getFile() . "\n";
    echo "  Línea: " . $e->getLine() . "\n\n";
    exit(1);
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}
