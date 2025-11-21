<?php
// Test file to check what HTML is being generated
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';
$_GET['page'] = 'dashboard';

// Include the index.php to see what it generates
ob_start();
include 'index.php';
$html = ob_get_clean();

// Save to a file
file_put_contents(__DIR__ . '/test_output.html', $html);

// Check for nav-menu
if (strpos($html, 'nav-menu') !== false) {
    echo "✓ nav-menu FOUND in generated HTML\n";
    // Count how many nav links
    $count = substr_count($html, 'class="nav-link');
    echo "✓ Found $count nav-link elements\n";
} else {
    echo "✗ nav-menu NOT FOUND in generated HTML\n";
}

// Check for specific links
$links = ['Socios', 'Pagos', 'Eventos', 'Cuotas', 'Configuración'];
foreach ($links as $link) {
    if (strpos($html, $link) !== false) {
        echo "✓ '$link' found\n";
    } else {
        echo "✗ '$link' NOT found\n";
    }
}
