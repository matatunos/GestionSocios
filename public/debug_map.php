<?php
// Test file to debug map locations
require_once __DIR__ . '/src/Config/Database.php';

$db = (new Database())->getConnection();

echo "<h1>Debug Map Locations</h1>";

// Test members query
echo "<h2>Members with coordinates:</h2>";
$stmtMembers = $db->prepare("SELECT id, first_name, last_name, latitude, longitude, address, status FROM members WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
$stmtMembers->execute();
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Total members with coordinates: " . count($members) . "</p>";
echo "<pre>";
print_r($members);
echo "</pre>";

// Test donors query
echo "<h2>Donors with coordinates:</h2>";
$stmtDonors = $db->prepare("SELECT id, name, latitude, longitude, address FROM donors WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
$stmtDonors->execute();
$donors = $stmtDonors->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Total donors with coordinates: " . count($donors) . "</p>";
echo "<pre>";
print_r($donors);
echo "</pre>";

// Test the actual endpoint
echo "<h2>Testing MapController endpoint:</h2>";
echo "<p>Try accessing: <a href='index.php?page=map&action=getLocations' target='_blank'>index.php?page=map&action=getLocations</a></p>";
?>
