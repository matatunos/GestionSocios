<?php

/**
 * Helper para gestión de tipos de archivo, iconos y colores
 */
class FileTypeHelper {
    
    /**
     * Obtiene el icono FontAwesome para un tipo de archivo
     * 
     * @param string $extension Extensión del archivo
     * @param string $mimeType MIME type (opcional)
     * @return string Clase del icono FontAwesome
     */
    public static function getIcon($extension, $mimeType = '') {
        $extension = strtolower($extension);
        
        // Documentos PDF
        if ($extension === 'pdf') {
            return 'fa-file-pdf';
        }
        
        // Documentos Word
        if (in_array($extension, ['doc', 'docx', 'odt'])) {
            return 'fa-file-word';
        }
        
        // Hojas de cálculo
        if (in_array($extension, ['xls', 'xlsx', 'ods', 'csv'])) {
            return 'fa-file-excel';
        }
        
        // Presentaciones
        if (in_array($extension, ['ppt', 'pptx', 'odp'])) {
            return 'fa-file-powerpoint';
        }
        
        // Imágenes
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
            return 'fa-file-image';
        }
        
        // Archivos comprimidos
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'fa-file-archive';
        }
        
        // Archivos de código
        if (in_array($extension, ['php', 'js', 'html', 'css', 'json', 'xml', 'sql'])) {
            return 'fa-file-code';
        }
        
        // Archivos de texto
        if (in_array($extension, ['txt', 'rtf', 'md'])) {
            return 'fa-file-lines';
        }
        
        // Video
        if (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'])) {
            return 'fa-file-video';
        }
        
        // Audio
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'm4a'])) {
            return 'fa-file-audio';
        }
        
        // Por defecto
        return 'fa-file';
    }
    
    /**
     * Obtiene el color para un tipo de archivo
     * 
     * @param string $extension Extensión del archivo
     * @return string Color hex
     */
    public static function getColor($extension) {
        $extension = strtolower($extension);
        
        // PDF - Rojo
        if ($extension === 'pdf') {
            return '#ef4444';
        }
        
        // Word - Azul
        if (in_array($extension, ['doc', 'docx', 'odt'])) {
            return '#3b82f6';
        }
        
        // Excel - Verde
        if (in_array($extension, ['xls', 'xlsx', 'ods', 'csv'])) {
            return '#10b981';
        }
        
        // PowerPoint - Naranja
        if (in_array($extension, ['ppt', 'pptx', 'odp'])) {
            return '#f59e0b';
        }
        
        // Imágenes - Amarillo
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
            return '#eab308';
        }
        
        // Comprimidos - Púrpura
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return '#8b5cf6';
        }
        
        // Código - Cyan
        if (in_array($extension, ['php', 'js', 'html', 'css', 'json', 'xml', 'sql'])) {
            return '#06b6d4';
        }
        
        // Texto - Gris
        if (in_array($extension, ['txt', 'rtf', 'md'])) {
            return '#6b7280';
        }
        
        // Video - Rosa
        if (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'])) {
            return '#ec4899';
        }
        
        // Audio - Indigo
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'm4a'])) {
            return '#6366f1';
        }
        
        // Por defecto
        return '#94a3b8';
    }
    
    /**
     * Obtiene el nombre descriptivo del tipo de archivo
     * 
     * @param string $extension Extensión del archivo
     * @return string Nombre descriptivo
     */
    public static function getTypeName($extension) {
        $extension = strtolower($extension);
        
        $types = [
            'pdf' => 'Documento PDF',
            'doc' => 'Documento Word',
            'docx' => 'Documento Word',
            'odt' => 'Documento OpenDocument',
            'xls' => 'Hoja de Cálculo Excel',
            'xlsx' => 'Hoja de Cálculo Excel',
            'ods' => 'Hoja de Cálculo OpenDocument',
            'csv' => 'Archivo CSV',
            'ppt' => 'Presentación PowerPoint',
            'pptx' => 'Presentación PowerPoint',
            'odp' => 'Presentación OpenDocument',
            'jpg' => 'Imagen JPEG',
            'jpeg' => 'Imagen JPEG',
            'png' => 'Imagen PNG',
            'gif' => 'Imagen GIF',
            'webp' => 'Imagen WebP',
            'svg' => 'Imagen SVG',
            'bmp' => 'Imagen BMP',
            'zip' => 'Archivo ZIP',
            'rar' => 'Archivo RAR',
            '7z' => 'Archivo 7-Zip',
            'tar' => 'Archivo TAR',
            'gz' => 'Archivo GZIP',
            'txt' => 'Archivo de Texto',
            'rtf' => 'Documento RTF',
            'md' => 'Documento Markdown',
            'php' => 'Código PHP',
            'js' => 'Código JavaScript',
            'html' => 'Documento HTML',
            'css' => 'Hoja de Estilos CSS',
            'json' => 'Archivo JSON',
            'xml' => 'Archivo XML',
            'sql' => 'Script SQL',
        ];
        
        return $types[$extension] ?? 'Archivo ' . strtoupper($extension);
    }
    
    /**
     * Verifica si un archivo es una imagen
     * 
     * @param string $extension Extensión del archivo
     * @return bool True si es imagen
     */
    public static function isImage($extension) {
        $extension = strtolower($extension);
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
    }
    
    /**
     * Verifica si un archivo es un PDF
     * 
     * @param string $extension Extensión del archivo
     * @return bool True si es PDF
     */
    public static function isPdf($extension) {
        return strtolower($extension) === 'pdf';
    }
    
    /**
     * Verifica si un archivo se puede previsualizar en el navegador
     * 
     * @param string $extension Extensión del archivo
     * @return bool True si se puede previsualizar
     */
    public static function canPreview($extension) {
        $extension = strtolower($extension);
        
        // Imágenes y PDFs se pueden previsualizar
        $previewable = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'txt', 'md'];
        
        return in_array($extension, $previewable);
    }
    
    /**
     * Obtiene el tipo de vista previa disponible
     * 
     * @param string $extension Extensión del archivo
     * @return string Tipo de preview: 'image', 'pdf', 'text', 'none'
     */
    public static function getPreviewType($extension) {
        $extension = strtolower($extension);
        
        if (self::isImage($extension)) {
            return 'image';
        }
        
        if (self::isPdf($extension)) {
            return 'pdf';
        }
        
        if (in_array($extension, ['txt', 'md', 'csv', 'json', 'xml'])) {
            return 'text';
        }
        
        return 'none';
    }
    
    /**
     * Genera HTML para un icono de archivo
     * 
     * @param string $extension Extensión del archivo
     * @param string $size Tamaño CSS (ej: '2rem', '48px')
     * @param bool $withLabel Si incluir label del tipo
     * @return string HTML del icono
     */
    public static function renderIcon($extension, $size = '2rem', $withLabel = false) {
        $icon = self::getIcon($extension);
        $color = self::getColor($extension);
        
        $html = '<i class="fas ' . htmlspecialchars($icon) . '" style="color: ' . htmlspecialchars($color) . '; font-size: ' . htmlspecialchars($size) . ';"></i>';
        
        if ($withLabel) {
            $typeName = self::getTypeName($extension);
            $html .= '<span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">' . htmlspecialchars($typeName) . '</span>';
        }
        
        return $html;
    }
    
    /**
     * Obtiene estadísticas de tipos de archivo en un array de documentos
     * 
     * @param array $documents Array de documentos con campo 'file_extension'
     * @return array Estadísticas agrupadas por tipo
     */
    public static function getTypeStatistics($documents) {
        $stats = [];
        
        foreach ($documents as $doc) {
            $extension = strtolower($doc['file_extension'] ?? '');
            
            if (!isset($stats[$extension])) {
                $stats[$extension] = [
                    'extension' => $extension,
                    'count' => 0,
                    'total_size' => 0,
                    'icon' => self::getIcon($extension),
                    'color' => self::getColor($extension),
                    'type_name' => self::getTypeName($extension)
                ];
            }
            
            $stats[$extension]['count']++;
            $stats[$extension]['total_size'] += $doc['file_size'] ?? 0;
        }
        
        // Ordenar por cantidad descendente
        usort($stats, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return $stats;
    }
    
    /**
     * Renderiza un badge con el tipo de archivo
     * 
     * @param string $extension Extensión del archivo
     * @return string HTML del badge
     */
    public static function renderBadge($extension) {
        $icon = self::getIcon($extension);
        $color = self::getColor($extension);
        $type = self::getTypeName($extension);
        
        return '<span class="file-type-badge" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: ' . htmlspecialchars($color) . '20; color: ' . htmlspecialchars($color) . '; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600;">' .
               '<i class="fas ' . htmlspecialchars($icon) . '"></i>' .
               '<span>' . htmlspecialchars(strtoupper($extension)) . '</span>' .
               '</span>';
    }
    
    /**
     * Verifica si un archivo es editable en línea (texto plano)
     * 
     * @param string $extension Extensión del archivo
     * @return bool True si es editable
     */
    public static function isEditableInline($extension) {
        $extension = strtolower($extension);
        $editable = ['txt', 'md', 'csv', 'json', 'xml', 'html', 'css', 'js', 'php', 'sql'];
        
        return in_array($extension, $editable);
    }
    
    /**
     * Obtiene el content-type HTTP para un tipo de archivo
     * 
     * @param string $extension Extensión del archivo
     * @return string Content-Type header
     */
    public static function getContentType($extension) {
        $extension = strtolower($extension);
        
        $contentTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'html' => 'text/html',
        ];
        
        return $contentTypes[$extension] ?? 'application/octet-stream';
    }
}
