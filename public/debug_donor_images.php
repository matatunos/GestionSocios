<?php
// Debug script to check donor image paths
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Config/Database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Checking Donor Images</h2>";

$query = "SELECT id, name, logo_url FROM donors WHERE logo_url IS NOT NULL AND logo_url != ''";
$stmt = $db->prepare($query);
$stmt->execute();
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Logo URL</th><th>Starts with '/'</th><th>File Exists</th></tr>";

foreach ($donors as $donor) {
    $startsWithSlash = (substr($donor['logo_url'], 0, 1) === '/') ? 'YES' : 'NO';
    $filePath = __DIR__ . '/' . ltrim($donor['logo_url'], '/');
    $fileExists = file_exists($filePath) ? 'YES' : 'NO';
    
    echo "<tr>";
    echo "<td>{$donor['id']}</td>";
    echo "<td>{$donor['name']}</td>";
    echo "<td>{$donor['logo_url']}</td>";
    echo "<td>{$startsWithSlash}</td>";
    echo "<td>{$fileExists}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Session Data:</h3>";
session_start();
if (isset($_SESSION['image_comparison'])) {
    echo "<pre>";
    print_r($_SESSION['image_comparison']);
    echo "</pre>";
} else {
    echo "<p>No image comparison data in session</p>";
}
?>
