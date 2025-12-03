<?php

/**
 * Helper para validación y seguridad de uploads de archivos
 */
class FileUploadHelper {
    
    /**
     * Tipos MIME permitidos con sus extensiones correspondientes
     */
    private static $allowedMimeTypes = [
        // Documentos PDF
        'application/pdf' => ['pdf'],
        
        // Documentos Microsoft Word
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        
        // Documentos Microsoft Excel
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        
        // Documentos Microsoft PowerPoint
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        
        // Documentos OpenDocument
        'application/vnd.oasis.opendocument.text' => ['odt'],
        'application/vnd.oasis.opendocument.spreadsheet' => ['ods'],
        'application/vnd.oasis.opendocument.presentation' => ['odp'],
        
        // Texto plano
        'text/plain' => ['txt', 'text'],
        'text/csv' => ['csv'],
        'text/rtf' => ['rtf'],
        'application/rtf' => ['rtf'],
        
        // Imágenes
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],
        'image/bmp' => ['bmp'],
        
        // Archivos comprimidos
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar'],
        'application/x-7z-compressed' => ['7z'],
        'application/x-tar' => ['tar'],
        'application/gzip' => ['gz'],
        
        // Otros formatos comunes
        'application/json' => ['json'],
        'application/xml' => ['xml'],
        'text/xml' => ['xml'],
    ];
    
    /**
     * Tamaño máximo por defecto: 10MB
     */
    const MAX_FILE_SIZE = 10485760; // 10 * 1024 * 1024
    
    /**
     * Valida un archivo subido
     * 
     * @param array $file Array de $_FILES
     * @param int $maxSize Tamaño máximo en bytes (opcional)
     * @return array ['valid' => bool, 'error' => string, 'data' => array]
     */
    public static function validateUpload($file, $maxSize = self::MAX_FILE_SIZE) {
        $result = [
            'valid' => false,
            'error' => '',
            'data' => []
        ];
        
        // Verificar que el archivo fue subido
        if (!isset($file) || !is_array($file)) {
            $result['error'] = 'No se ha proporcionado un archivo';
            return $result;
        }
        
        // Verificar errores de PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = self::getUploadErrorMessage($file['error']);
            return $result;
        }
        
        // Verificar que el archivo existe y es un upload válido
        if (!is_uploaded_file($file['tmp_name'])) {
            $result['error'] = 'El archivo no es un upload válido';
            return $result;
        }
        
        // Verificar tamaño
        if ($file['size'] > $maxSize) {
            $result['error'] = 'El archivo excede el tamaño máximo permitido de ' . self::formatBytes($maxSize);
            return $result;
        }
        
        if ($file['size'] === 0) {
            $result['error'] = 'El archivo está vacío';
            return $result;
        }
        
        // Obtener extensión del archivo
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Validar extensión
        if (empty($extension)) {
            $result['error'] = 'El archivo no tiene extensión';
            return $result;
        }
        
        // Verificar MIME type real del archivo (no confiar en el que envía el navegador)
        $mimeType = self::getMimeType($file['tmp_name']);
        
        if ($mimeType === false) {
            $result['error'] = 'No se pudo determinar el tipo de archivo';
            return $result;
        }
        
        // Validar que el MIME type está permitido
        if (!isset(self::$allowedMimeTypes[$mimeType])) {
            $result['error'] = 'Tipo de archivo no permitido: ' . $mimeType;
            return $result;
        }
        
        // Validar que la extensión coincide con el MIME type
        $allowedExtensions = self::$allowedMimeTypes[$mimeType];
        if (!in_array($extension, $allowedExtensions)) {
            $result['error'] = 'La extensión del archivo no coincide con su tipo real. ' .
                              'Extensión: ' . $extension . ', Tipo: ' . $mimeType;
            return $result;
        }
        
        // Todo OK
        $result['valid'] = true;
        $result['data'] = [
            'original_name' => $originalName,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $file['size'],
            'tmp_name' => $file['tmp_name']
        ];
        
        return $result;
    }
    
    /**
     * Obtiene el MIME type real de un archivo usando finfo
     * 
     * @param string $filePath Ruta al archivo
     * @return string|false MIME type o false si falla
     */
    public static function getMimeType($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return false;
        }
        
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return $mimeType;
    }
    
    /**
     * Genera un nombre de archivo único y seguro
     * 
     * @param string $originalName Nombre original del archivo
     * @return string Nombre seguro único
     */
    public static function generateSafeFileName($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitizar el nombre base
        $baseName = self::sanitizeFileName($baseName);
        
        // Generar nombre único
        $uniqueId = uniqid() . '_' . bin2hex(random_bytes(8));
        
        return $uniqueId . '_' . substr($baseName, 0, 50) . '.' . $extension;
    }
    
    /**
     * Sanitiza un nombre de archivo
     * 
     * @param string $fileName Nombre del archivo
     * @return string Nombre sanitizado
     */
    public static function sanitizeFileName($fileName) {
        // Remover caracteres especiales y espacios
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Remover múltiples guiones bajos consecutivos
        $fileName = preg_replace('/_+/', '_', $fileName);
        
        // Remover guiones bajos al inicio y final
        $fileName = trim($fileName, '_');
        
        return $fileName;
    }
    
    /**
     * Mueve un archivo subido a su destino final
     * 
     * @param string $tmpName Ruta temporal del archivo
     * @param string $destination Ruta de destino
     * @return bool True si se movió correctamente
     */
    public static function moveUploadedFile($tmpName, $destination) {
        // Crear directorio si no existe
        $directory = dirname($destination);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        
        // Mover archivo
        return move_uploaded_file($tmpName, $destination);
    }
    
    /**
     * Elimina un archivo de forma segura
     * 
     * @param string $filePath Ruta del archivo
     * @return bool True si se eliminó correctamente
     */
    public static function deleteFile($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Convierte bytes a formato legible
     * 
     * @param int $bytes Bytes
     * @param int $precision Decimales
     * @return string Tamaño formateado
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Traduce códigos de error de upload de PHP
     * 
     * @param int $code Código de error
     * @return string Mensaje de error
     */
    private static function getUploadErrorMessage($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el servidor (upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal en el servidor';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en el disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP detuvo la subida del archivo';
            default:
                return 'Error desconocido al subir el archivo';
        }
    }
    
    /**
     * Obtiene la extensión de archivo para un MIME type
     * 
     * @param string $mimeType MIME type
     * @return string|null Extensión o null si no se encuentra
     */
    public static function getExtensionForMime($mimeType) {
        if (isset(self::$allowedMimeTypes[$mimeType])) {
            return self::$allowedMimeTypes[$mimeType][0];
        }
        return null;
    }
    
    /**
     * Verifica si un MIME type está permitido
     * 
     * @param string $mimeType MIME type
     * @return bool True si está permitido
     */
    public static function isMimeTypeAllowed($mimeType) {
        return isset(self::$allowedMimeTypes[$mimeType]);
    }
    
    /**
     * Obtiene lista de extensiones permitidas
     * 
     * @return array Lista de extensiones
     */
    public static function getAllowedExtensions() {
        $extensions = [];
        foreach (self::$allowedMimeTypes as $mime => $exts) {
            $extensions = array_merge($extensions, $exts);
        }
        return array_unique($extensions);
    }
    
    /**
     * Obtiene atributo accept para input file
     * 
     * @return string Valor para atributo accept
     */
    public static function getAcceptAttribute() {
        $extensions = self::getAllowedExtensions();
        return '.' . implode(',.', $extensions);
    }
    
    /**
     * Extrae texto de un archivo PDF (básico)
     * 
     * @param string $filePath Ruta al PDF
     * @return string|false Texto extraído o false si falla
     */
    public static function extractTextFromPdf($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        // Intentar con pdftotext si está disponible
        if (shell_exec('which pdftotext')) {
            $output = shell_exec('pdftotext "' . escapeshellarg($filePath) . '" -');
            if ($output !== null) {
                return $output;
            }
        }
        
        // Método alternativo básico (solo extrae texto plano del PDF)
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        
        // Regex básico para extraer texto entre BT y ET (Begin Text / End Text)
        preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches);
        
        if (isset($matches[1])) {
            $text = implode("\n", $matches[1]);
            // Limpiar comandos PDF
            $text = preg_replace('/\[.*?\]|<.*?>|\(.*?\)/s', '', $text);
            $text = preg_replace('/[^a-zA-Z0-9\s\.,;:!?\-áéíóúñÁÉÍÓÚÑ]/u', '', $text);
            return trim($text);
        }
        
        return '';
    }
    
    /**
     * Genera thumbnail de una imagen
     * 
     * @param string $sourcePath Ruta de la imagen origen
     * @param string $destPath Ruta de destino del thumbnail
     * @param int $maxWidth Ancho máximo
     * @param int $maxHeight Alto máximo
     * @return bool True si se generó correctamente
     */
    public static function generateThumbnail($sourcePath, $destPath, $maxWidth = 200, $maxHeight = 200) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Calcular nuevas dimensiones manteniendo ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Crear imagen según el tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if ($source === false) {
            return false;
        }
        
        // Crear thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia para PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        // Redimensionar
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Guardar según tipo
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbnail, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbnail, $destPath, 8);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbnail, $destPath);
                break;
        }
        
        // Liberar memoria
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $result;
    }
}
