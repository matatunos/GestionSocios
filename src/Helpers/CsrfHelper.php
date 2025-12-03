<?php

/**
 * CSRF Protection Helper
 * Provides methods to generate and validate CSRF tokens
 */
class CsrfHelper {
    
    /**
     * Generate a CSRF token and store it in the session
     * @return string The generated token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate a CSRF token against the session token
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get HTML input field for CSRF token
     * @return string HTML input field
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
    
    /**
     * Validate CSRF token from POST request
     * @param bool $regenerate Whether to regenerate token after validation (default: false)
     * @return bool True if valid, false otherwise
     */
    public static function validateRequest($regenerate = false) {
        $token = $_POST['csrf_token'] ?? '';
        $valid = self::validateToken($token);
        
        // Regenerar token después de uso exitoso para mayor seguridad
        if ($valid && $regenerate) {
            unset($_SESSION['csrf_token']);
            self::generateToken();
        }
        
        return $valid;
    }
}
