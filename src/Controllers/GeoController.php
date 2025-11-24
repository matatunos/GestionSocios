<?php

class GeoController {
    public function reverse() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $lat = $_GET['lat'] ?? '';
        $lng = $_GET['lng'] ?? '';

        if (!$lat || !$lng) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing coordinates']);
            return;
        }

        // Nominatim requires a User-Agent identifying the application
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
        
        $opts = [
            'http' => [
                'header' => "User-Agent: GestionSociosApp/1.0 (contact@example.com)\r\n"
            ]
        ];
        
        $context = stream_context_create($opts);
        // Suppress warnings with @, handle error manually
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
             http_response_code(502);
             echo json_encode(['error' => 'Error contacting geocoding service']);
             return;
        }

        $data = json_decode($json, true);

        if (!$data || isset($data['error'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Address not found']);
            return;
        }

        $a = $data['address'] ?? [];
        
        // Map Nominatim fields to our needs
        $response = [
            'calle'   => $a['road'] ?? $a['pedestrian'] ?? $a['street'] ?? '',
            'numero'  => $a['house_number'] ?? '',
            'ciudad'  => $a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? '',
            'cp'      => $a['postcode'] ?? '',
            'provincia' => $a['province'] ?? $a['state'] ?? '',
            'direccion_completa' => $data['display_name']
        ];

        echo json_encode($response);
    }
}
