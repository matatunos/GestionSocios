<?php
// Script to test API endpoints
// Usage: php scripts/test_api.php

$baseUrl = 'http://localhost/api/v1'; // Adjust if needed
$email = 'admin@example.com'; // Adjust with valid credentials
$password = 'admin123'; // Adjust with valid credentials

// Helper function to make requests
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

echo "Testing API...\n";

// 1. Authenticate
echo "1. Authenticating...\n";
$authResponse = makeRequest($baseUrl . '/auth', 'POST', ['email' => $email, 'password' => $password]);

if ($authResponse['code'] !== 200) {
    echo "Authentication failed: " . json_encode($authResponse['body']) . "\n";
    exit(1);
}

$token = $authResponse['body']['token'];
echo "Authentication successful. Token received.\n";

// 2. Get Members
echo "2. Getting Members...\n";
$membersResponse = makeRequest($baseUrl . '/members', 'GET', null, $token);
echo "Status: " . $membersResponse['code'] . "\n";
if (isset($membersResponse['body']['total'])) {
    echo "Total members: " . $membersResponse['body']['total'] . "\n";
}

// 3. Get Suppliers (New Endpoint)
echo "3. Getting Suppliers...\n";
$suppliersResponse = makeRequest($baseUrl . '/suppliers', 'GET', null, $token);
echo "Status: " . $suppliersResponse['code'] . "\n";
if (isset($suppliersResponse['body']['total'])) {
    echo "Total suppliers: " . $suppliersResponse['body']['total'] . "\n";
}

echo "Test complete.\n";
