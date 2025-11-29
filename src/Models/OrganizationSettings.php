<?php

class OrganizationSettings {
    private $conn;
    private $table_name = "organization_settings";
    private static $cache = null;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todas las configuraciones
     */
    public function getAll() {
        if (self::$cache !== null) {
            return self::$cache;
        }
        
        $query = "SELECT setting_key, setting_value, setting_type, description, category 
                  FROM " . $this->table_name . " 
                  ORDER BY category, setting_key";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'type' => $row['setting_type'],
                'description' => $row['description'],
                'category' => $row['category']
            ];
        }
        
        self::$cache = $settings;
        return $settings;
    }

    /**
     * Obtener configuraciones por categoría
     */
    public function getByCategory($category) {
        $query = "SELECT setting_key, setting_value, setting_type, description 
                  FROM " . $this->table_name . " 
                  WHERE category = ?
                  ORDER BY setting_key";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = [
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }

    /**
     * Obtener un valor de configuración específico
     */
    public function get($key, $default = null) {
        $settings = $this->getAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Establecer un valor de configuración
     */
    public function set($key, $value, $userId = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET setting_value = ?, updated_by = ?
                  WHERE setting_key = ?";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$value, $userId, $key]);
        
        // Limpiar caché
        self::$cache = null;
        
        return $result;
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMultiple($settings, $userId = null) {
        $this->conn->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value, $userId);
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Convertir valor según su tipo
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (int)$value : 0;
            case 'boolean':
                return (bool)$value || $value === '1' || $value === 'true';
            case 'file':
            case 'url':
            case 'email':
            case 'text':
            default:
                return $value;
        }
    }

    /**
     * Subir archivo de logo
     */
    public function uploadLogo($file) {
        $uploadDir = __DIR__ . '/../../public/uploads/organization/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Use JPG, PNG, GIF, SVG o WEBP.');
        }

        // Validar tamaño (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Si es una imagen (no SVG), redimensionar
            if ($file['type'] !== 'image/svg+xml') {
                $this->resizeLogo($filepath, 500); // Max 500px de ancho
            }
            
            return '/uploads/organization/' . $filename;
        }

        throw new Exception('Error al subir el archivo.');
    }

    /**
     * Redimensionar logo manteniendo proporción
     */
    private function resizeLogo($filepath, $maxWidth) {
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) return;

        list($width, $height) = $imageInfo;
        
        // Si es menor que el máximo, no redimensionar
        if ($width <= $maxWidth) return;

        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = (int)($height * $ratio);

        // Crear imagen según tipo
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_WEBP:
                $src = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }

        // Crear nueva imagen
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia para PNG y GIF
        if ($imageInfo[2] == IMAGETYPE_PNG || $imageInfo[2] == IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar según tipo
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst, $filepath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst, $filepath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst, $filepath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($dst, $filepath, 90);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Eliminar logo actual
     */
    public function deleteLogo($logoPath) {
        if (empty($logoPath)) return true;
        
        $filepath = __DIR__ . '/../../public' . $logoPath;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }

    /**
     * Obtener información de la organización para mostrar
     */
    public function getOrganizationInfo() {
        $settings = $this->getAll();
        
        return [
            'name' => $settings['org_name'] ?? 'Mi Asociación',
            'short_name' => $settings['org_short_name'] ?? 'MA',
            'logo' => $settings['org_logo'] ?? '',
            'logo_width' => $settings['org_logo_width'] ?? 180,
            'email' => $settings['org_email'] ?? '',
            'phone' => $settings['org_phone'] ?? '',
            'address' => $settings['org_address'] ?? '',
            'website' => $settings['org_website'] ?? '',
        ];
    }
    
    /**
     * Get password policy settings
     */
    public function getPasswordPolicy() {
        $query = "SELECT password_min_length, password_require_uppercase, 
                         password_require_lowercase, password_require_numbers, 
                         password_require_special, login_max_attempts, 
                         login_lockout_duration 
                  FROM " . $this->table_name . " 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return [
                'min_length' => (int)$row['password_min_length'],
                'require_uppercase' => (bool)$row['password_require_uppercase'],
                'require_lowercase' => (bool)$row['password_require_lowercase'],
                'require_numbers' => (bool)$row['password_require_numbers'],
                'require_special' => (bool)$row['password_require_special'],
                'login_max_attempts' => (int)$row['login_max_attempts'],
                'login_lockout_duration' => (int)$row['login_lockout_duration']
            ];
        }
        
        // Default policy if not set
        return [
            'min_length' => 8,
            'require_uppercase' => false,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => false,
            'login_max_attempts' => 5,
            'login_lockout_duration' => 15
        ];
    }
    
    /**
     * Update password policy settings
     */
    public function updatePasswordPolicy($minLength, $requireUpper, $requireLower, $requireNumbers, $requireSpecial, $maxAttempts, $lockoutDuration) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_min_length = :min_length,
                      password_require_uppercase = :require_uppercase,
                      password_require_lowercase = :require_lowercase,
                      password_require_numbers = :require_numbers,
                      password_require_special = :require_special,
                      login_max_attempts = :max_attempts,
                      login_lockout_duration = :lockout_duration";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':min_length', $minLength, PDO::PARAM_INT);
        $stmt->bindParam(':require_uppercase', $requireUpper, PDO::PARAM_BOOL);
        $stmt->bindParam(':require_lowercase', $requireLower, PDO::PARAM_BOOL);
        $stmt->bindParam(':require_numbers', $requireNumbers, PDO::PARAM_BOOL);
        $stmt->bindParam(':require_special', $requireSpecial, PDO::PARAM_BOOL);
        $stmt->bindParam(':max_attempts', $maxAttempts, PDO::PARAM_INT);
        $stmt->bindParam(':lockout_duration', $lockoutDuration, PDO::PARAM_INT);
        
        // Clear cache
        self::$cache = null;
        
        return $stmt->execute();
    }
    
    /**
     * Validate password against policy
     */
    public static function validatePassword($password, $policy) {
        $errors = [];
        
        // Check minimum length
        if (strlen($password) < $policy['min_length']) {
            $errors[] = "La contraseña debe tener al menos {$policy['min_length']} caracteres";
        }
        
        // Check uppercase requirement
        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }
        
        // Check lowercase requirement
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }
        
        // Check numbers requirement
        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }
        
        // Check special characters requirement
        if ($policy['require_special'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un carácter especial";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
