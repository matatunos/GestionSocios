<?php

require_once __DIR__ . '/../Models/Certificate.php';
require_once __DIR__ . '/../Helpers/Auth.php';

class CertificateController {
    private $db;
    private $certificateModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->certificateModel = new Certificate($this->db);
    }
    
    /**
     * Generate a safe filename from member data
     */
    private function generateFilename($prefix, $member, $suffix = '') {
        $parts = [$prefix];
        
        // Add DNI if available
        if (!empty($member['dni'])) {
            $parts[] = $member['dni'];
        }
        
        // Add member name (sanitized)
        $name = trim($member['first_name'] . '_' . $member['last_name']);
        $name = $this->sanitizeFilename($name);
        if ($name) {
            $parts[] = $name;
        }
        
        // Add suffix if provided (like year)
        if ($suffix) {
            $parts[] = $suffix;
        }
        
        return implode('_', $parts) . '.pdf';
    }
    
    /**
     * Sanitize string for use in filename
     */
    private function sanitizeFilename($string) {
        // Convert to ASCII
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        // Remove special characters
        $string = preg_replace('/[^A-Za-z0-9_-]/', '_', $string);
        // Remove multiple underscores
        $string = preg_replace('/_+/', '_', $string);
        // Remove leading/trailing underscores
        $string = trim($string, '_');
        return $string;
    }
    
    /**
     * Generate membership certificate
     */
    public function membership() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: index.php?page=login');
            exit;
        }
        
        // Get member ID
        $memberId = $_GET['id'] ?? null;
        if (!$memberId) {
            $_SESSION['error'] = 'ID de socio no especificado';
            header('Location: index.php?page=members');
            exit;
        }
        
        // Generate certificate
        try {
            $result = $this->certificateModel->generateMembershipCertificate($memberId);
            
            if (!$result) {
                $_SESSION['error'] = 'No se pudo generar el certificado: Socio no encontrado';
                header('Location: index.php?page=members');
                exit;
            }
            
            $pdf = $result['pdf'];
            $member = $result['member'];
            $filename = $this->generateFilename('certificado_socio', $member);
        } catch (Exception $e) {
            error_log('Certificate generation error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar certificado: ' . $e->getMessage();
            header('Location: index.php?page=members');
            exit;
        }
        
        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }
    
    /**
     * Generate payment certificate
     */
    public function payments() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: index.php?page=login');
            exit;
        }
        
        // Get member ID and year
        $memberId = $_GET['id'] ?? null;
        $year = $_GET['year'] ?? date('Y');
        
        if (!$memberId) {
            $_SESSION['error'] = 'ID de socio no especificado';
            header('Location: index.php?page=members');
            exit;
        }
        
        // Generate certificate
        try {
            $result = $this->certificateModel->generatePaymentCertificate($memberId, $year);
            
            if (!$result) {
                $_SESSION['error'] = 'No se pudo generar el certificado: Socio no encontrado';
                header('Location: index.php?page=members');
                exit;
            }
            
            $pdf = $result['pdf'];
            $member = $result['member'];
            $filename = $this->generateFilename('certificado_pagos', $member, $year);
        } catch (Exception $e) {
            error_log('Payment certificate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar certificado: ' . $e->getMessage();
            header('Location: index.php?page=members');
            exit;
        }
        
        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }
    
    /**
     * Generate donation certificate
     */
    public function donations() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: index.php?page=login');
            exit;
        }
        
        // Get donor ID and year
        $donorId = $_GET['id'] ?? null;
        $year = $_GET['year'] ?? date('Y');
        
        if (!$donorId) {
            $_SESSION['error'] = 'ID de donante no especificado';
            header('Location: index.php?page=donors');
            exit;
        }
        
        // Generate certificate
        try {
            $pdf = $this->certificateModel->generateDonationCertificate($donorId, $year);
            
            if (!$pdf) {
                $_SESSION['error'] = 'No se pudo generar el certificado: Donante no encontrado';
                header('Location: index.php?page=donors');
                exit;
            }
        } catch (Exception $e) {
            error_log('Donation certificate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar certificado: ' . $e->getMessage();
            header('Location: index.php?page=donors');
            exit;
        }
        
        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="certificado_donaciones_' . $donorId . '_' . $year . '.pdf"');
        echo $pdf;
        exit;
    }
    
    /**
     * Download certificate instead of viewing inline
     */
    public function download() {
        // Get parameters
        $type = $_GET['type'] ?? 'membership';
        $id = $_GET['id'] ?? null;
        $year = $_GET['year'] ?? date('Y');
        
        if (!$id) {
            $_SESSION['error'] = 'ID no especificado';
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Generate certificate based on type
        $result = null;
        $filename = '';
        
        switch ($type) {
            case 'membership':
                $result = $this->certificateModel->generateMembershipCertificate($id);
                if ($result) {
                    $filename = $this->generateFilename('certificado_socio', $result['member']);
                }
                break;
                
            case 'payments':
                $result = $this->certificateModel->generatePaymentCertificate($id, $year);
                if ($result) {
                    $filename = $this->generateFilename('certificado_pagos', $result['member'], $year);
                }
                break;
                
            case 'donations':
                $result = $this->certificateModel->generateDonationCertificate($id, $year);
                if ($result) {
                    $filename = "certificado_donaciones_{$id}_{$year}.pdf";
                }
                break;
                
            default:
                $_SESSION['error'] = 'Tipo de certificado no válido';
                header('Location: index.php?page=dashboard');
                exit;
        }
        
        if (!$result || !isset($result['pdf'])) {
            $_SESSION['error'] = 'No se pudo generar el certificado';
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        $pdf = $result['pdf'];
        
        // Force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }
}
