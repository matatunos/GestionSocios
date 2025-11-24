<?php
require __DIR__ . '/src/Config/Database.php';
require __DIR__ . '/src/Models/Member.php';

$db = (new Database())->getConnection();
$memberModel = new Member($db);
$members = $memberModel->readFiltered([], null, null);
foreach ($members as $m) {
    echo "ID {$m['id']} - Paid this year: " . ($m['has_paid_current_year'] ? 'YES' : 'NO') . "\n";
}
?>
