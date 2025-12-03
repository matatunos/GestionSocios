<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Conexión a Base de Datos</h2>";

// Cargar config
$configFile = __DIR__ . '/../src/Config/config.php';
echo "<p>Archivo config: " . ($configFile) . "</p>";
echo "<p>¿Existe?: " . (file_exists($configFile) ? 'SÍ' : 'NO') . "</p>";

if (file_exists($configFile)) {
    require_once $configFile;
    
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
    echo "<p>DB_PASS: " . (DB_PASS ? '***configurado***' : 'vacío') . "</p>";
    
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->exec("set names utf8mb4");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green; font-weight: bold;'>✓ CONEXIÓN EXITOSA</p>";
        
        // Contar tablas
        $stmt = $conn->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Tablas en la BD: " . $result['cnt'] . "</p>";
        
        // Verificar tabla users
        $stmt = $conn->query("SELECT COUNT(*) as cnt FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Usuarios en la tabla 'users': " . $result['cnt'] . "</p>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>✗ ERROR DE CONEXIÓN</p>";
        echo "<p>Mensaje: " . $e->getMessage() . "</p>";
        echo "<p>Código: " . $e->getCode() . "</p>";
    }
} else {
    echo "<p style='color: red;'>El archivo config.php no existe</p>";
}
?>
