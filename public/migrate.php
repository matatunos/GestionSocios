<?php
require_once __DIR__ . '/../src/Config/Database.php';

try {
    $db = (new Database())->getConnection();
    if ($db) {
        // Check if column exists
        $check = $db->query("SHOW COLUMNS FROM members LIKE 'deactivated_at'");
        if ($check->fetch()) {
            echo "Column 'deactivated_at' already exists.";
        } else {
            $sql = "ALTER TABLE members ADD COLUMN deactivated_at DATETIME NULL DEFAULT NULL";
            $db->exec($sql);
            echo "Migration applied successfully: Added 'deactivated_at' column.";
        }
    } else {
        echo "Could not connect to database.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
