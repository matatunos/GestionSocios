<?php

class Lang {
    private static $instance = null;
    private $lang = 'es';
    private $translations = [];
    
    private function __construct() {
        // Get language from session or browser
        if (isset($_SESSION['lang'])) {
            $this->lang = $_SESSION['lang'];
        } else {
            $this->lang = $this->detectLanguage();
        }
        
        $this->loadTranslations();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Detect language from browser
     */
    private function detectLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $primaryLang = substr($languages[0], 0, 2);
            
            // Check if we have this language
            if (in_array($primaryLang, ['es', 'en'])) {
                return $primaryLang;
            }
        }
        
        return 'es'; // Default to Spanish
    }
    
    /**
     * Load translation file
     */
    private function loadTranslations() {
        $file = __DIR__ . "/../Lang/{$this->lang}.php";
        
        if (file_exists($file)) {
            $this->translations = require $file;
        } else {
            // Fallback to Spanish
            $this->translations = require __DIR__ . "/../Lang/es.php";
        }
    }
    
    /**
     * Get translation
     */
    public function get($key, $replacements = []) {
        $translation = $this->translations[$key] ?? $key;
        
        // Replace placeholders
        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace(":$placeholder", $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Set language
     */
    public function setLanguage($lang) {
        if (in_array($lang, ['es', 'en'])) {
            $this->lang = $lang;
            $_SESSION['lang'] = $lang;
            $this->loadTranslations();
            return true;
        }
        return false;
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        return $this->lang;
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages() {
        return [
            'es' => 'Español',
            'en' => 'English'
        ];
    }
}

/**
 * Helper function for translations
 */
function __($key, $replacements = []) {
    return Lang::getInstance()->get($key, $replacements);
}

/**
 * Helper function to get current language
 */
function current_lang() {
    return Lang::getInstance()->getCurrentLanguage();
}
