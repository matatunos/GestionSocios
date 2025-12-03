<?php

/**
 * Helper para funciones de visualización de documentos
 */
class DocumentViewHelper {
    
    /**
     * Obtener clase CSS para icono de actividad
     */
    public static function getActivityIconClass($action) {
        $classes = [
            'uploaded' => 'uploaded',
            'downloaded' => 'downloaded',
            'public_download' => 'public-download',
            'deleted' => 'deleted',
            'edited' => 'edited',
            'updated' => 'edited',
            'previewed' => 'previewed',
            'viewed' => 'previewed'
        ];
        
        return $classes[$action] ?? 'uploaded';
    }
    
    /**
     * Obtener icono Font Awesome para acción
     */
    public static function getActivityIcon($action) {
        $icons = [
            'uploaded' => 'fa-upload',
            'downloaded' => 'fa-download',
            'public_download' => 'fa-globe',
            'deleted' => 'fa-trash',
            'edited' => 'fa-edit',
            'updated' => 'fa-edit',
            'previewed' => 'fa-eye',
            'viewed' => 'fa-eye',
            'created' => 'fa-plus',
            'restored' => 'fa-undo'
        ];
        
        return $icons[$action] ?? 'fa-file';
    }
    
    /**
     * Obtener texto descriptivo para acción
     */
    public static function getActivityText($action) {
        $texts = [
            'uploaded' => 'subió',
            'downloaded' => 'descargó',
            'public_download' => 'descargó públicamente',
            'deleted' => 'eliminó',
            'edited' => 'editó',
            'updated' => 'actualizó',
            'previewed' => 'previsualizó',
            'viewed' => 'visualizó',
            'created' => 'creó',
            'restored' => 'restauró'
        ];
        
        return $texts[$action] ?? 'realizó una acción en';
    }
    
    /**
     * Formatear tiempo relativo (hace X tiempo)
     */
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'hace ' . $diff . ' segundo' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 60);
        if ($diff < 60) {
            return 'hace ' . $diff . ' minuto' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 60);
        if ($diff < 24) {
            return 'hace ' . $diff . ' hora' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 24);
        if ($diff < 7) {
            return 'hace ' . $diff . ' día' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 7);
        if ($diff < 4) {
            return 'hace ' . $diff . ' semana' . ($diff != 1 ? 's' : '');
        }
        
        return date('d/m/Y', $timestamp);
    }
}
