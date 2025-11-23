<?php

require_once __DIR__ . '/../Helpers/Lang.php';

class LanguageController {
    
    /**
     * Change language
     */
    public function change() {
        $lang = $_GET['lang'] ?? 'es';
        
        $langHelper = Lang::getInstance();
        if ($langHelper->setLanguage($lang)) {
            // Redirect to previous page or dashboard
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard';
            header("Location: $redirect");
            exit;
        } else {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }
}
