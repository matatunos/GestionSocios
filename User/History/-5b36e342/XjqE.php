<?php

/**
 * GrantScraperHelper
 * 
 * Sistema de scraping automático para búsqueda de subvenciones en fuentes oficiales españolas:
 * - BDNS (Base de Datos Nacional de Subvenciones)
 * - BOE (Boletín Oficial del Estado)
 * - DOGC/DOG/BOJA/etc (Boletines autonómicos)
 * - Páginas de organismos públicos
 * 
 * Características:
 * - Parsing de HTML/XML
 * - Detección de duplicados
 * - Auto-insert con scoring de relevancia
 * - Rate limiting para evitar bloqueos
 * - User-Agent rotation
 * - Logging de errores
 */

class GrantScraperHelper {
    
    private $db;
    private $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Buscar subvenciones en todas las fuentes configuradas
     */
    public function searchAll($keywords = '', $filters = []) {
        $results = [
            'bdns' => [],
            'boe' => [],
            'total_found' => 0,
            'total_new' => 0,
            'total_updated' => 0,
            'errors' => []
        ];
        
        // BDNS - Base de Datos Nacional de Subvenciones
        try {
            $bdnsResults = $this->searchBDNS($keywords, $filters);
            $results['bdns'] = $bdnsResults;
            $results['total_found'] += count($bdnsResults);
        } catch (Exception $e) {
            $results['errors'][] = "BDNS: " . $e->getMessage();
        }
        
        // BOE - Boletín Oficial del Estado
        try {
            $boeResults = $this->searchBOE($keywords, $filters);
            $results['boe'] = $boeResults;
            $results['total_found'] += count($boeResults);
        } catch (Exception $e) {
            $results['errors'][] = "BOE: " . $e->getMessage();
        }
        
        // Procesar resultados: insertar o actualizar
        foreach (array_merge($results['bdns'], $results['boe']) as $grant) {
            try {
                $insertResult = $this->insertOrUpdateGrant($grant, $filters);
                if ($insertResult['action'] === 'inserted') {
                    $results['total_new']++;
                } else if ($insertResult['action'] === 'updated') {
                    $results['total_updated']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = "Insert/Update: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Scraping de BDNS (Base de Datos Nacional de Subvenciones)
     * API Pública: https://www.pap.hacienda.gob.es/bdnstrans/
     */
    public function searchBDNS($keywords = '', $filters = []) {
        $grants = [];
        
        // URL de la API de BDNS (formato XML)
        $baseUrl = 'https://www.pap.hacienda.gob.es/bdnstrans/busqueda';
        
        // Construir parámetros de búsqueda
        $params = [
            'texto' => $keywords,
            'formato' => 'xml',
            'pagina' => 1,
            'resultados' => 50
        ];
        
        if (!empty($filters['province'])) {
            $params['provincia'] = $filters['province'];
        }
        
        if (!empty($filters['category'])) {
            $params['materia'] = $filters['category'];
        }
        
        $url = $baseUrl . '?' . http_build_query($params);
        
        // Hacer petición
        $xml = $this->fetchUrl($url);
        
        if (!$xml) {
            throw new Exception("No se pudo conectar con BDNS");
        }
        
        // Parsear XML
        try {
            $xmlObj = simplexml_load_string($xml);
            
            if (!$xmlObj) {
                throw new Exception("Error al parsear XML de BDNS");
            }
            
            // Iterar sobre resultados
            foreach ($xmlObj->subvencion as $item) {
                $grant = [
                    'source' => 'bdns',
                    'title' => (string)$item->titulo,
                    'description' => (string)$item->descripcion,
                    'organization' => (string)$item->organismo,
                    'grant_type' => $this->mapGrantType((string)$item->ambito),
                    'category' => (string)$item->materia,
                    'min_amount' => $this->parseAmount((string)$item->importeMin),
                    'max_amount' => $this->parseAmount((string)$item->importeMax),
                    'total_budget' => $this->parseAmount((string)$item->presupuesto),
                    'announcement_date' => $this->parseDate((string)$item->fechaPublicacion),
                    'open_date' => $this->parseDate((string)$item->fechaApertura),
                    'deadline' => $this->parseDate((string)$item->fechaCierre),
                    'url' => (string)$item->enlace,
                    'reference_code' => (string)$item->codigo,
                    'province' => (string)$item->provincia,
                    'municipality' => (string)$item->municipio,
                    'requirements' => (string)$item->requisitos,
                    'status' => $this->determineStatus((string)$item->estado, (string)$item->fechaCierre),
                    'auto_discovered' => true
                ];
                
                $grants[] = $grant;
            }
            
        } catch (Exception $e) {
            throw new Exception("Error procesando datos de BDNS: " . $e->getMessage());
        }
        
        return $grants;
    }
    
    /**
     * Scraping de BOE (Boletín Oficial del Estado)
     * Buscar convocatorias de subvenciones publicadas
     */
    public function searchBOE($keywords = '', $filters = []) {
        $grants = [];
        
        // BOE tiene un buscador público
        $baseUrl = 'https://www.boe.es/buscar/act.php';
        
        // Buscar por "convocatoria" + keywords
        $searchTerm = 'convocatoria subvención ' . $keywords;
        
        $params = [
            'q' => $searchTerm,
            'id_materia' => '20', // Código para subvenciones
            'fecha_desde' => date('d/m/Y', strtotime('-90 days')), // Últimos 3 meses
            'fecha_hasta' => date('d/m/Y')
        ];
        
        $url = $baseUrl . '?' . http_build_query($params);
        
        // Hacer petición
        $html = $this->fetchUrl($url);
        
        if (!$html) {
            throw new Exception("No se pudo conectar con BOE");
        }
        
        // Parsear HTML con DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML($html); // @ para suprimir warnings de HTML mal formado
        
        $xpath = new DOMXPath($dom);
        
        // Buscar elementos de resultados (estructura del BOE)
        $resultNodes = $xpath->query("//li[contains(@class, 'resultado')]");
        
        foreach ($resultNodes as $node) {
            try {
                // Extraer título
                $titleNode = $xpath->query(".//a[@class='titulo']", $node)->item(0);
                $title = $titleNode ? trim($titleNode->textContent) : '';
                
                // Extraer enlace
                $link = $titleNode ? 'https://www.boe.es' . $titleNode->getAttribute('href') : '';
                
                // Extraer fecha
                $dateNode = $xpath->query(".//span[@class='fecha']", $node)->item(0);
                $dateText = $dateNode ? trim($dateNode->textContent) : '';
                
                // Extraer descripción
                $descNode = $xpath->query(".//p[@class='descripcion']", $node)->item(0);
                $description = $descNode ? trim($descNode->textContent) : '';
                
                if (empty($title) || empty($link)) {
                    continue; // Saltar si no tiene datos básicos
                }
                
                // Intentar extraer más detalles accediendo a la página individual
                // (NOTA: Esto puede ser lento, considerar hacer en background)
                $details = $this->fetchBOEDetails($link);
                
                $grant = [
                    'source' => 'boe',
                    'title' => $title,
                    'description' => $description,
                    'organization' => $details['organization'] ?? 'Gobierno de España',
                    'grant_type' => 'estatal',
                    'category' => $details['category'] ?? '',
                    'announcement_date' => $this->parseDate($dateText),
                    'deadline' => $details['deadline'] ?? null,
                    'url' => $link,
                    'official_document' => $link,
                    'reference_code' => $this->extractBOEReference($link),
                    'requirements' => $details['requirements'] ?? '',
                    'status' => 'prospecto',
                    'auto_discovered' => true
                ];
                
                $grants[] = $grant;
                
                // Rate limiting: esperar 1 segundo entre peticiones
                sleep(1);
                
            } catch (Exception $e) {
                // Continuar con el siguiente resultado si hay error
                continue;
            }
        }
        
        return $grants;
    }
    
    /**
     * Obtener detalles adicionales de una convocatoria BOE
     */
    private function fetchBOEDetails($url) {
        $details = [];
        
        try {
            $html = $this->fetchUrl($url);
            if (!$html) return $details;
            
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Intentar extraer organismo
            $orgNode = $xpath->query("//p[contains(@class, 'organismo')]")->item(0);
            if ($orgNode) {
                $details['organization'] = trim($orgNode->textContent);
            }
            
            // Buscar plazos en el texto
            $contentNode = $xpath->query("//div[@id='textoCompleto']")->item(0);
            if ($contentNode) {
                $text = $contentNode->textContent;
                
                // Buscar patrones de fechas (plazo, deadline, etc.)
                if (preg_match('/plazo.*?(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i', $text, $matches)) {
                    $details['deadline'] = $this->parseDate($matches[1]);
                }
                
                // Buscar requisitos
                if (preg_match('/requisitos?[:\s]+(.*?)(?:documentaci[oó]n|bases|plazo)/is', $text, $matches)) {
                    $details['requirements'] = trim(substr($matches[1], 0, 500));
                }
            }
            
        } catch (Exception $e) {
            // Si falla, devolver lo que tenemos
        }
        
        return $details;
    }
    
    /**
     * Insertar o actualizar subvención en la base de datos
     */
    private function insertOrUpdateGrant($grantData, $originalFilters = []) {
        require_once __DIR__ . '/../Models/Grant.php';
        
        $grantModel = new Grant($this->db);
        
        // Verificar si ya existe (por título + organismo + deadline)
        $checkQuery = "SELECT id FROM grants 
                       WHERE title = :title 
                       AND organization = :organization 
                       AND deadline = :deadline
                       LIMIT 1";
        
        $stmt = $this->db->prepare($checkQuery);
        $stmt->execute([
            ':title' => $grantData['title'],
            ':organization' => $grantData['organization'],
            ':deadline' => $grantData['deadline']
        ]);
        
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Actualizar si hay cambios
            $grantModel->id = $existing['id'];
            // Aquí podrías actualizar campos que cambien (status, etc.)
            return ['action' => 'skipped', 'id' => $existing['id'], 'reason' => 'already_exists'];
        }
        
        // Insertar nueva subvención
        $grantModel->title = $grantData['title'];
        $grantModel->description = $grantData['description'] ?? '';
        $grantModel->organization = $grantData['organization'];
        $grantModel->grant_type = $grantData['grant_type'] ?? 'estatal';
        $grantModel->category = $grantData['category'] ?? '';
        $grantModel->min_amount = $grantData['min_amount'] ?? null;
        $grantModel->max_amount = $grantData['max_amount'] ?? null;
        $grantModel->total_budget = $grantData['total_budget'] ?? null;
        $grantModel->announcement_date = $grantData['announcement_date'] ?? null;
        $grantModel->open_date = $grantData['open_date'] ?? null;
        $grantModel->deadline = $grantData['deadline'];
        $grantModel->url = $grantData['url'] ?? '';
        $grantModel->official_document = $grantData['official_document'] ?? '';
        $grantModel->reference_code = $grantData['reference_code'] ?? '';
        $grantModel->requirements = $grantData['requirements'] ?? '';
        $grantModel->province = $grantData['province'] ?? '';
        $grantModel->municipality = $grantData['municipality'] ?? '';
        $grantModel->status = $grantData['status'] ?? 'prospecto';
        $grantModel->our_status = 'identificada';
        $grantModel->auto_discovered = true;
        $grantModel->search_keywords = $originalFilters['keywords'] ?? '';
        
        // Calcular relevancia
        $orgProfile = $this->getOrganizationProfile();
        $grantModel->relevance_score = $grantModel->calculateRelevance([
            'province' => $grantModel->province,
            'municipality' => $grantModel->municipality,
            'category' => $grantModel->category,
            'min_amount' => $grantModel->min_amount,
            'max_amount' => $grantModel->max_amount,
            'title' => $grantModel->title,
            'description' => $grantModel->description
        ], $orgProfile);
        
        $grantModel->created_by = 1; // Sistema
        
        if ($grantModel->create()) {
            return ['action' => 'inserted', 'id' => $grantModel->id, 'score' => $grantModel->relevance_score];
        } else {
            throw new Exception("Error al insertar subvención en base de datos");
        }
    }
    
    /**
     * Obtener perfil de la organización para scoring
     */
    private function getOrganizationProfile() {
        $query = "SELECT setting_key, setting_value FROM organization_settings 
                  WHERE category IN ('location', 'grants')";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $profile = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $profile[$row['setting_key']] = $row['setting_value'];
        }
        
        return $profile;
    }
    
    /**
     * Hacer petición HTTP con user-agent aleatorio y manejo de errores
     */
    private function fetchUrl($url, $timeout = 30) {
        $userAgent = $this->user_agents[array_rand($this->user_agents)];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para evitar problemas con certificados
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode para URL: $url");
        }
        
        return $response;
    }
    
    /**
     * Mapear tipo de ámbito a nuestro enum
     */
    private function mapGrantType($ambito) {
        $ambito = strtolower($ambito);
        
        if (strpos($ambito, 'estatal') !== false || strpos($ambito, 'nacional') !== false) {
            return 'estatal';
        } else if (strpos($ambito, 'autonóm') !== false || strpos($ambito, 'comunidad') !== false) {
            return 'autonomica';
        } else if (strpos($ambito, 'provincial') !== false) {
            return 'provincial';
        } else if (strpos($ambito, 'local') !== false || strpos($ambito, 'municipal') !== false) {
            return 'local';
        } else if (strpos($ambito, 'europ') !== false || strpos($ambito, 'ue') !== false) {
            return 'europea';
        }
        
        return 'estatal'; // Por defecto
    }
    
    /**
     * Parsear cantidad monetaria
     */
    private function parseAmount($amount) {
        if (empty($amount)) return null;
        
        // Eliminar símbolos de moneda y espacios
        $amount = str_replace(['€', ' ', '.'], '', $amount);
        $amount = str_replace(',', '.', $amount);
        
        return is_numeric($amount) ? (float)$amount : null;
    }
    
    /**
     * Parsear fecha en diferentes formatos
     */
    private function parseDate($dateStr) {
        if (empty($dateStr)) return null;
        
        // Intentar varios formatos comunes
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, trim($dateStr));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        // Si no funciona, intentar strtotime
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }
    
    /**
     * Determinar estado de la convocatoria basado en fechas
     */
    private function determineStatus($estado, $deadline) {
        $estado = strtolower($estado);
        
        if (strpos($estado, 'cerr') !== false || strpos($estado, 'vencid') !== false) {
            return 'cerrada';
        }
        
        if ($deadline) {
            $deadlineTime = strtotime($deadline);
            $now = time();
            
            if ($deadlineTime < $now) {
                return 'cerrada';
            } else if ($deadlineTime > $now && $deadlineTime < strtotime('+7 days')) {
                return 'abierta'; // Próxima a cerrar
            }
        }
        
        if (strpos($estado, 'abiert') !== false || strpos($estado, 'activ') !== false) {
            return 'abierta';
        }
        
        return 'prospecto';
    }
    
    /**
     * Extraer código de referencia del BOE desde URL
     */
    private function extractBOEReference($url) {
        if (preg_match('/BOE-[A-Z]-\d{4}-\d+/', $url, $matches)) {
            return $matches[0];
        }
        return '';
    }
    
    /**
     * Ejecutar búsqueda automática basada en configuración guardada
     */
    public static function runScheduledSearches($db) {
        $query = "SELECT * FROM grant_searches WHERE active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $searches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $scraper = new self($db);
        $totalResults = 0;
        
        foreach ($searches as $search) {
            // Verificar si toca ejecutar según frecuencia
            if (!self::shouldRunSearch($search)) {
                continue;
            }
            
            $filters = [
                'keywords' => $search['keywords'],
                'grant_type' => $search['grant_type'],
                'province' => $search['province'],
                'municipality' => $search['municipality'],
                'min_amount' => $search['min_amount'],
                'category' => $search['category']
            ];
            
            try {
                $results = $scraper->searchAll($search['keywords'], $filters);
                
                // Actualizar última ejecución
                $updateQuery = "UPDATE grant_searches 
                                SET last_run = NOW(), 
                                    last_results = :results 
                                WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->execute([
                    ':results' => $results['total_new'],
                    ':id' => $search['id']
                ]);
                
                $totalResults += $results['total_new'];
                
                // Notificar a usuarios si está configurado
                if ($results['total_new'] > 0 && !empty($search['notify_users'])) {
                    self::notifyUsers($db, $search, $results);
                }
                
            } catch (Exception $e) {
                error_log("Error en búsqueda automática #{$search['id']}: " . $e->getMessage());
            }
        }
        
        return $totalResults;
    }
    
    /**
     * Verificar si debe ejecutarse una búsqueda según su frecuencia
     */
    private static function shouldRunSearch($search) {
        if (empty($search['last_run'])) {
            return true; // Primera ejecución
        }
        
        $lastRun = strtotime($search['last_run']);
        $now = time();
        
        switch ($search['frequency']) {
            case 'daily':
                return ($now - $lastRun) >= 86400; // 24 horas
            case 'weekly':
                return ($now - $lastRun) >= 604800; // 7 días
            case 'monthly':
                return ($now - $lastRun) >= 2592000; // 30 días
            default:
                return false;
        }
    }
    
    /**
     * Notificar a usuarios sobre nuevas subvenciones encontradas
     */
    private static function notifyUsers($db, $search, $results) {
        $userIds = json_decode($search['notify_users'], true);
        
        if (empty($userIds) || !is_array($userIds)) {
            return;
        }
        
        // TODO: Implementar sistema de notificaciones
        // Por ahora, solo logueamos
        error_log("Notificación: {$results['total_new']} nuevas subvenciones encontradas para búsqueda '{$search['search_name']}'");
    }
}
