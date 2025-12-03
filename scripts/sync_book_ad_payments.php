<?php
/**
 * Script to sync existing book ads with payments table
 * This creates payment records for book ads that don't have them yet
 */

require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Models/BookAd.php';
require_once __DIR__ . '/../src/Models/Donor.php';

echo "Starting book ad payments synchronization...\n\n";

$database = new Database();
$db = $database->getConnection();

// Get all book ads
$query = "SELECT ba.*, d.name as donor_name 
          FROM book_ads ba
          INNER JOIN donors d ON ba.donor_id = d.id
          ORDER BY ba.year DESC, ba.id ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$bookAds = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($bookAds) . " book ads\n\n";

$created = 0;
$updated = 0;
$skipped = 0;

foreach ($bookAds as $ad) {
    echo "Processing: {$ad['donor_name']} - Year {$ad['year']} ({$ad['ad_type']})... ";
    
    // Check if payment already exists
    $checkStmt = $db->prepare("SELECT id, status, amount FROM payments WHERE payment_type = 'book_ad' AND book_ad_id = ?");
    $checkStmt->execute([$ad['id']]);
    $existingPayment = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingPayment) {
        // Check if it needs updating
        if ($existingPayment['amount'] != $ad['amount'] || $existingPayment['status'] != $ad['status']) {
            $paymentStatus = $ad['status'] === 'paid' ? 'paid' : 'pending';
            $paymentDate = $ad['status'] === 'paid' ? date('Y-m-d') : NULL;
            
            $updateStmt = $db->prepare("UPDATE payments SET amount = ?, payment_date = ?, status = ? WHERE id = ?");
            $updateStmt->execute([
                $ad['amount'],
                $paymentDate,
                $paymentStatus,
                $existingPayment['id']
            ]);
            
            echo "UPDATED (status: {$paymentStatus}, amount: {$ad['amount']})\n";
            $updated++;
        } else {
            echo "SKIPPED (already synced)\n";
            $skipped++;
        }
    } else {
        // Create new payment record
        $paymentStatus = $ad['status'] === 'paid' ? 'paid' : 'pending';
        $paymentDate = $ad['status'] === 'paid' ? date('Y-m-d') : NULL;
        
        $insertStmt = $db->prepare("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type, book_ad_id) VALUES (NULL, ?, ?, ?, ?, ?, 'book_ad', ?)");
        $insertStmt->execute([
            $ad['amount'],
            $paymentDate,
            'Anuncio Libro Fiestas ' . $ad['year'] . ' - ' . $ad['donor_name'],
            $paymentStatus,
            $ad['year'],
            $ad['id']
        ]);
        
        echo "CREATED (status: {$paymentStatus}, amount: {$ad['amount']})\n";
        $created++;
    }
}

echo "\n===========================================\n";
echo "Synchronization completed!\n";
echo "Created: $created\n";
echo "Updated: $updated\n";
echo "Skipped: $skipped\n";
echo "Total processed: " . count($bookAds) . "\n";
echo "===========================================\n";
