<?php
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Member.php';
require_once __DIR__ . '/../src/Models/Payment.php';

echo "Starting Verification...\n";

$db = (new Database())->getConnection();

if ($db) {
    echo "[PASS] Database Connection\n";
} else {
    echo "[FAIL] Database Connection\n";
    exit;
}

// Test User
$user = new User($db);
if ($user->findByUsername('admin')) {
    echo "[PASS] Admin User Exists\n";
} else {
    echo "[FAIL] Admin User Not Found (Did you import schema.sql?)\n";
}

// Test Member Creation
$member = new Member($db);
$member->first_name = "Test";
$member->last_name = "User";
$member->email = "test@example.com";
$member->status = "active";
if ($member->create()) {
    echo "[PASS] Member Creation\n";
} else {
    echo "[FAIL] Member Creation\n";
}

// Test Payment Creation
// We need the ID of the member we just created. 
// Since create() doesn't return ID, we'll fetch it.
$query = "SELECT id FROM members WHERE email = 'test@example.com' ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$memberId = $row['id'] ?? null;

if ($memberId) {
    $payment = new Payment($db);
    $payment->member_id = $memberId;
    $payment->amount = 50.00;
    $payment->payment_date = date('Y-m-d');
    $payment->concept = "Test Payment";
    $payment->status = "paid";
    
    if ($payment->create()) {
        echo "[PASS] Payment Creation\n";
    } else {
        echo "[FAIL] Payment Creation\n";
    }
} else {
    echo "[FAIL] Could not retrieve created member ID\n";
}

echo "Verification Complete.\n";
