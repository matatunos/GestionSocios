<?php

require_once __DIR__ . '/../Models/Document.php';
require_once __DIR__ . '/../Helpers/FileTypeHelper.php';
require_once __DIR__ . '/../Config/database.php';

class PublicDocumentController {
    private $db;
    private $documentModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->documentModel = new Document($this->db);
    }
    
    /**
     * Vista previa y descarga de documento público
     */
    public function view() {
        $token = $_GET['token'] ?? null;
        
        if (!$token) {
            $this->showError('Token no proporcionado', 'El enlace no es válido.');
            return;
        }
        
        // Obtener documento por token
        $document = $this->documentModel->getByPublicToken($token);
        
        if (!$document) {
            $this->showError('Documento no encontrado', 'El enlace no existe o ha sido eliminado.');
            return;
        }
        
        // Verificar si el token es válido
        if (!$document['is_valid']) {
            // Determinar razón
            if ($document['token_expires_at'] && strtotime($document['token_expires_at']) < time()) {
                $this->showError('Enlace expirado', 'Este enlace ha caducado el ' . date('d/m/Y H:i', strtotime($document['token_expires_at'])) . '.');
            } else if ($document['public_download_limit'] && $document['public_downloads'] >= $document['public_download_limit']) {
                $this->showError('Límite alcanzado', 'Este documento ha alcanzado su límite de descargas.');
            } else if (!$document['public_enabled']) {
                $this->showError('Enlace desactivado', 'Este enlace ha sido revocado por el administrador.');
            } else {
                $this->showError('Enlace inválido', 'Este enlace no está disponible.');
            }
            return;
        }
        
        // Registrar acceso (no descarga)
        $this->documentModel->logPublicAccess($document['id'], $token, false);
        
        // Mostrar vista previa
        require_once __DIR__ . '/../Views/public/document_preview.php';
    }
    
    /**
     * Descarga de documento público
     */
    public function download() {
        $token = $_GET['token'] ?? null;
        
        if (!$token) {
            $this->showError('Token no proporcionado', 'El enlace no es válido.');
            return;
        }
        
        // Obtener documento por token
        $document = $this->documentModel->getByPublicToken($token);
        
        if (!$document || !$document['is_valid']) {
            $this->showError('Descarga no disponible', 'Este enlace no es válido o ha expirado.');
            return;
        }
        
        // Verificar límite antes de descargar
        if ($document['public_download_limit'] && $document['public_downloads'] >= $document['public_download_limit']) {
            $this->showError('Límite alcanzado', 'Este documento ha alcanzado su límite de descargas.');
            return;
        }
        
        // Registrar descarga
        $this->documentModel->logPublicAccess($document['id'], $token, true);
        
        // Servir archivo
        $filePath = __DIR__ . '/../../public/' . $document['file_path'];
        
        if (!file_exists($filePath)) {
            $this->showError('Archivo no encontrado', 'El archivo físico no está disponible.');
            return;
        }
        
        // Headers para descarga
        header('Content-Type: ' . ($document['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Enviar archivo
        readfile($filePath);
        exit;
    }
    
    /**
     * Mostrar página de error
     */
    private function showError($title, $message) {
        require_once __DIR__ . '/../Views/public/document_error.php';
    }
}
