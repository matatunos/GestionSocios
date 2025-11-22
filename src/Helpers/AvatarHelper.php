<?php

class AvatarHelper {
    /**
     * Generate avatar HTML with image or initials
     */
    public static function generate($firstName, $lastName, $imageUrl = null, $size = 'md', $status = null) {
        $sizes = [
            'xs' => '24px',
            'sm' => '32px',
            'md' => '40px',
            'lg' => '48px',
            'xl' => '64px',
            '2xl' => '96px',
            '3xl' => '128px'
        ];
        
        $sizeValue = $sizes[$size] ?? $sizes['md'];
        $initials = self::getInitials($firstName, $lastName);
        $bgColor = self::getColorFromName($firstName . $lastName);
        
        $statusIndicator = '';
        if ($status === 'active') {
            $statusIndicator = '<span class="avatar-status avatar-status-active"></span>';
        } elseif ($status === 'inactive') {
            $statusIndicator = '<span class="avatar-status avatar-status-inactive"></span>';
        }
        
        if ($imageUrl && file_exists($_SERVER['DOCUMENT_ROOT'] . $imageUrl)) {
            return sprintf(
                '<div class="avatar avatar-%s" style="width: %s; height: %s;">
                    <img src="%s" alt="%s %s">
                    %s
                </div>',
                $size,
                $sizeValue,
                $sizeValue,
                htmlspecialchars($imageUrl),
                htmlspecialchars($firstName),
                htmlspecialchars($lastName),
                $statusIndicator
            );
        } else {
            return sprintf(
                '<div class="avatar avatar-%s avatar-initials" style="width: %s; height: %s; background: %s;">
                    <span>%s</span>
                    %s
                </div>',
                $size,
                $sizeValue,
                $sizeValue,
                $bgColor,
                $initials,
                $statusIndicator
            );
        }
    }
    
    /**
     * Get initials from name
     */
    private static function getInitials($firstName, $lastName) {
        $firstInitial = !empty($firstName) ? mb_strtoupper(mb_substr($firstName, 0, 1)) : '';
        $lastInitial = !empty($lastName) ? mb_strtoupper(mb_substr($lastName, 0, 1)) : '';
        return $firstInitial . $lastInitial;
    }
    
    /**
     * Generate consistent color from name
     */
    private static function getColorFromName($name) {
        $colors = [
            '#6366f1', // indigo
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#f43f5e', // rose
            '#f59e0b', // amber
            '#10b981', // emerald
            '#06b6d4', // cyan
            '#3b82f6', // blue
            '#a855f7', // purple
            '#14b8a6', // teal
        ];
        
        $hash = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            $hash = ord($name[$i]) + (($hash << 5) - $hash);
        }
        
        $index = abs($hash) % count($colors);
        return $colors[$index];
    }
    
    /**
     * Generate avatar image URL for data URI (SVG)
     */
    public static function generateSvgAvatar($firstName, $lastName, $size = 128) {
        $initials = self::getInitials($firstName, $lastName);
        $bgColor = self::getColorFromName($firstName . $lastName);
        
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">
                <rect width="%d" height="%d" fill="%s"/>
                <text x="50%%" y="50%%" font-family="Arial, sans-serif" font-size="%d" fill="white" 
                      text-anchor="middle" dominant-baseline="central" font-weight="600">%s</text>
            </svg>',
            $size, $size, $size, $size,
            $size, $size, $bgColor,
            $size * 0.4,
            $initials
        );
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Handle file upload for avatar
     */
    public static function uploadAvatar($file, $memberId) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profiles/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG, GIF y WebP.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'member_' . $memberId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Error al subir el archivo.');
        }
        
        // Resize and optimize image
        self::resizeImage($filepath, 300, 300);
        
        return '/uploads/profiles/' . $filename;
    }
    
    /**
     * Resize and optimize image
     */
    private static function resizeImage($filepath, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($filepath);
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;
        
        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }
        
        // Create new image
        $dest = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($dest, imagecolorallocatealpha($dest, 0, 0, 0, 127));
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
        }
        
        // Resize
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dest, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($dest, $filepath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($dest, $filepath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($dest, $filepath, 85);
                break;
        }
        
        // Free memory
        imagedestroy($source);
        imagedestroy($dest);
    }
    
    /**
     * Delete avatar file
     */
    public static function deleteAvatar($imagePath) {
        if (!empty($imagePath)) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
