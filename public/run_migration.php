<?php
// Run migration for donor image history table
// This script should be accessed via browser once and then deleted

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h1>Running Migration: donor_image_history</h1>";

    // Read SQL file
    $sqlFile = __DIR__ . '/../database/migration_donor_image_history.sql';
    $sql = file_get_contents($sqlFile);

    // Execute the SQL
    $db->exec($sql);

    echo "<p style='color: green;'><strong>✓ Migration completed successfully!</strong></p>";
    echo "<p>Table <code>donor_image_history</code> created.</p>";
    echo "<hr>";
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Delete this file (run_migration.php) for security</li>";
    echo "<li>Test the image comparison feature by editing a donor with an existing image</li>";
    echo "</ol>";
    echo "<br>";
    echo "<a href='index.php' style='display: inline-block; background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>";

} catch (Exception $e) {
    echo "<h1 style='color: red;'>Migration Failed</h1>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
