<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . '/../src/Config/Config.php';
require_once __DIR__ . '/../src/Config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h1>Fixing Image Paths...</h1>";

    // Fix Donors
    $sql1 = "UPDATE donors SET logo_url = SUBSTRING(logo_url, 2) WHERE logo_url LIKE '/%'";
    $stmt1 = $db->prepare($sql1);
    $stmt1->execute();
    $count1 = $stmt1->rowCount();
    echo "<p>Fixed $count1 donor paths.</p>";

    // Fix Members
    $sql2 = "UPDATE members SET photo_url = SUBSTRING(photo_url, 2) WHERE photo_url LIKE '/%'";
    $stmt2 = $db->prepare($sql2);
    $stmt2->execute();
    $count2 = $stmt2->rowCount();
    echo "<p>Fixed $count2 member paths.</p>";

    echo "<h2>Done! You can now delete this file.</h2>";
    echo "<a href='index.php'>Go back to Dashboard</a>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
