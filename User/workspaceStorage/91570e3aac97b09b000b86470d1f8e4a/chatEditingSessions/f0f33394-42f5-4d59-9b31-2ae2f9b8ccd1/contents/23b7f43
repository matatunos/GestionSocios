<?php

class VerifactuSignature {
    private $db;
    
    // Propiedades
    public $id;
    public $invoice_id;
    public $hash;
    public $previous_hash;
    public $signature;
    public $qr_code;
    public $qr_url;
    public $csv;
    public $registration_number;
    public $sent_to_aeat;
    public $sent_at;
    public $aeat_response;
    public $aeat_status;
    public $signature_timestamp;
    public $signature_algorithm;
    public $certificate_id;
    public $verifactu_version;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Generar firma Verifactu para una factura
     */
    public function generateSignature($invoice_id, $invoice_data) {
        try {
            // Obtener hash de la factura anterior (cadena de bloques)
            $previous_hash = $this->getLastHash();
            
            // Generar hash de esta factura
            $hash = $this->calculateHash($invoice_data, $previous_hash);
            
            // Generar firma electrónica
            $signature = $this->createSignature($hash);
            
            // Generar código QR
            $qr_data = $this->generateQRData($invoice_id, $hash);
            $qr_code = $this->generateQRCode($qr_data);
            
            // Guardar en base de datos
            $this->invoice_id = $invoice_id;
            $this->hash = $hash;
            $this->previous_hash = $previous_hash;
            $this->signature = $signature;
            $this->qr_code = $qr_code;
            $this->qr_url = $qr_data['url'];
            $this->signature_algorithm = 'SHA256';
            $this->verifactu_version = '1.0';
            
            return $this->save();
            
        } catch (Exception $e) {
            error_log("Error generando firma Verifactu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener el último hash de la cadena
     */
    private function getLastHash() {
        $query = "SELECT hash FROM verifactu_signatures 
                  ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['hash'] : null;
    }
    
    /**
     * Calcular hash SHA-256 de la factura
     * Según normativa Verifactu: NIF + Fecha + Número + Importe + Hash Anterior
     */
    private function calculateHash($invoice_data, $previous_hash = null) {
        // Datos a incluir en el hash según normativa
        $data_string = implode('|', [
            $invoice_data['nif_emisor'] ?? '',
            $invoice_data['full_number'] ?? '',
            $invoice_data['issue_date'] ?? '',
            number_format($invoice_data['total'], 2, '.', ''),
            $invoice_data['customer_tax_id'] ?? '',
            $previous_hash ?? ''
        ]);
        
        return hash('sha256', $data_string);
    }
    
    /**
     * Crear firma electrónica
     * En producción usaría certificado digital real
     */
    private function createSignature($hash) {
        // TODO: Implementar firma real con certificado digital
        // Por ahora, firma simplificada para desarrollo
        $timestamp = date('Y-m-d\TH:i:s');
        $signature_data = [
            'hash' => $hash,
            'timestamp' => $timestamp,
            'algorithm' => 'SHA256',
            'version' => '1.0'
        ];
        
        return json_encode($signature_data);
    }
    
    /**
     * Generar datos para código QR
     */
    private function generateQRData($invoice_id, $hash) {
        // Obtener configuración
        $config = $this->getVerifactuConfig();
        
        // URL base según entorno
        $base_url = $config['environment'] === 'production' 
            ? $config['production_url'] 
            : $config['test_url'];
        
        // Generar CSV (Código Seguro de Verificación)
        $csv = $this->generateCSV($invoice_id, $hash);
        
        // URL de verificación con parámetros
        $url = $base_url . '?' . http_build_query([
            'nif' => $config['nif_emisor'],
            'csv' => $csv,
            'hash' => substr($hash, 0, 16) // Primeros 16 caracteres
        ]);
        
        return [
            'url' => $url,
            'csv' => $csv,
            'hash' => $hash
        ];
    }
    
    /**
     * Generar Código Seguro de Verificación
     */
    private function generateCSV($invoice_id, $hash) {
        // Formato: YYYY-MMDD-XXXXXXXX (año-mes-día-aleatorio)
        $date_part = date('Y-md');
        $random_part = strtoupper(substr(md5($invoice_id . $hash . time()), 0, 8));
        
        return $date_part . '-' . $random_part;
    }
    
    /**
     * Generar código QR en base64
     */
    private function generateQRCode($qr_data) {
        // Usar biblioteca PHP QR Code
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        try {
            // Generar QR con TCQRCODE o similar
            // Por ahora, retornar datos en base64 simple
            $qr_content = json_encode($qr_data);
            return base64_encode($qr_content);
            
        } catch (Exception $e) {
            error_log("Error generando QR: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener configuración de Verifactu
     */
    private function getVerifactuConfig() {
        $query = "SELECT setting_key, setting_value 
                  FROM organization_settings 
                  WHERE category = 'verifactu'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $config = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
        
        return $config;
    }
    
    /**
     * Guardar firma en base de datos
     */
    private function save() {
        $query = "INSERT INTO verifactu_signatures 
                  (invoice_id, hash, previous_hash, signature, qr_code, qr_url, 
                   csv, signature_algorithm, verifactu_version)
                  VALUES 
                  (:invoice_id, :hash, :previous_hash, :signature, :qr_code, :qr_url,
                   :csv, :signature_algorithm, :verifactu_version)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':invoice_id', $this->invoice_id);
        $stmt->bindParam(':hash', $this->hash);
        $stmt->bindParam(':previous_hash', $this->previous_hash);
        $stmt->bindParam(':signature', $this->signature);
        $stmt->bindParam(':qr_code', $this->qr_code);
        $stmt->bindParam(':qr_url', $this->qr_url);
        $stmt->bindParam(':csv', $this->csv);
        $stmt->bindParam(':signature_algorithm', $this->signature_algorithm);
        $stmt->bindParam(':verifactu_version', $this->verifactu_version);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener firma por ID de factura
     */
    public function getByInvoiceId($invoice_id) {
        $query = "SELECT * FROM verifactu_signatures WHERE invoice_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$invoice_id]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $row['id'];
            $this->invoice_id = $row['invoice_id'];
            $this->hash = $row['hash'];
            $this->previous_hash = $row['previous_hash'];
            $this->signature = $row['signature'];
            $this->qr_code = $row['qr_code'];
            $this->qr_url = $row['qr_url'];
            $this->csv = $row['csv'];
            $this->registration_number = $row['registration_number'];
            $this->sent_to_aeat = $row['sent_to_aeat'];
            $this->sent_at = $row['sent_at'];
            $this->aeat_response = $row['aeat_response'];
            $this->aeat_status = $row['aeat_status'];
            $this->signature_timestamp = $row['signature_timestamp'];
            $this->signature_algorithm = $row['signature_algorithm'];
            $this->certificate_id = $row['certificate_id'];
            $this->verifactu_version = $row['verifactu_version'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Enviar firma a AEAT (SII/Verifactu)
     */
    public function sendToAEAT() {
        // TODO: Implementar envío real al SII
        // Requiere certificado digital y conexión SOAP/REST con AEAT
        
        $this->sent_to_aeat = true;
        $this->sent_at = date('Y-m-d H:i:s');
        $this->aeat_status = 'pending';
        
        $query = "UPDATE verifactu_signatures 
                  SET sent_to_aeat = :sent, sent_at = :sent_at, aeat_status = :status
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':sent', $this->sent_to_aeat);
        $stmt->bindParam(':sent_at', $this->sent_at);
        $stmt->bindParam(':status', $this->aeat_status);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Actualizar estado de respuesta AEAT
     */
    public function updateAEATResponse($status, $response) {
        $this->aeat_status = $status;
        $this->aeat_response = $response;
        
        $query = "UPDATE verifactu_signatures 
                  SET aeat_status = :status, aeat_response = :response
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $this->aeat_status);
        $stmt->bindParam(':response', $this->aeat_response);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar integridad de la cadena de hashes
     */
    public static function verifyChain($db) {
        $query = "SELECT id, hash, previous_hash FROM verifactu_signatures ORDER BY id";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $errors = [];
        
        for ($i = 1; $i < count($signatures); $i++) {
            $current = $signatures[$i];
            $previous = $signatures[$i - 1];
            
            if ($current['previous_hash'] !== $previous['hash']) {
                $errors[] = [
                    'id' => $current['id'],
                    'expected' => $previous['hash'],
                    'found' => $current['previous_hash']
                ];
            }
        }
        
        return [
            'valid' => empty($errors),
            'total' => count($signatures),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener estadísticas de Verifactu
     */
    public static function getStats($db) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN sent_to_aeat = 1 THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN aeat_status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                    SUM(CASE WHEN aeat_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN aeat_status = 'error' THEN 1 ELSE 0 END) as errors,
                    SUM(CASE WHEN aeat_status = 'pending' THEN 1 ELSE 0 END) as pending
                  FROM verifactu_signatures";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
