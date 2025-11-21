<?php

/**
 * Security utility functions for CSRF protection
 */
class Security {
    
    /**
     * Generate a CSRF token and store it in the session
     * @return string The generated token
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get the current CSRF token from session
     * @return string|null The token or null if not set
     */
    public static function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? null;
    }
    
    /**
     * Validate the CSRF token from POST request
     * @param string|null $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrfToken($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        $sessionToken = self::getCsrfToken();
        
        // Both must exist and match - use strict checks
        if ($token === '' || $sessionToken === null || $sessionToken === '') {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Generate HTML input field for CSRF token
     * @return string HTML input element
     */
    public static function csrfInputField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

/**
 * Helper function to generate CSRF input field
 * @return string HTML input element
 */
function csrf_input_field() {
    return Security::csrfInputField();
}

/**
 * Helper function to validate CSRF token
 * @param string|null $token Optional token to validate, defaults to $_POST['csrf_token']
 * @return bool True if valid, false otherwise
 */
function validate_csrf_token($token = null) {
    return Security::validateCsrfToken($token);
}
