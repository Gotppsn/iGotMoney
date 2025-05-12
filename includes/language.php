<?php
/**
 * Language Management System
 * 
 * Handles translation and language switching for the application
 */

class Language {
    private $lang; // Current language code
    private $translations = []; // Array to store translations
    private static $instance = null; // Singleton instance
    
    /**
     * Constructor
     * 
     * @param string $lang Language code (default: 'en')
     */
    private function __construct($lang = 'en') {
        $this->setLanguage($lang);
    }
    
    /**
     * Get Language instance (Singleton)
     * 
     * @param string $lang Language code
     * @return Language
     */
    public static function getInstance($lang = null) {
        if (self::$instance === null) {
            self::$instance = new self($lang);
        } elseif ($lang !== null) {
            self::$instance->setLanguage($lang);
        }
        return self::$instance;
    }
    
    /**
     * Set current language and load translations
     * 
     * @param string $lang Language code
     * @return void
     */
    public function setLanguage($lang) {
        // Only supported languages
        if (!in_array($lang, ['en', 'th'])) {
            $lang = 'en'; // Default to English if unsupported
        }
        
        $this->lang = $lang;
        $this->loadTranslations();
    }
    
    /**
     * Get current language code
     * 
     * @return string
     */
    public function getCurrentLanguage() {
        return $this->lang;
    }
    
    /**
     * Load translations for the current language
     * 
     * @return void
     */
    private function loadTranslations() {
        try {
            $langFile = dirname(__DIR__) . '/lang/' . $this->lang . '.php';
            
            if (file_exists($langFile)) {
                $translations = include($langFile);
                if (is_array($translations)) {
                    $this->translations = $translations;
                } else {
                    error_log("Language file {$langFile} did not return an array");
                    // Try to use English as fallback
                    $this->loadEnglishFallback();
                }
            } else {
                // If language file doesn't exist, try to use English
                error_log("Language file {$langFile} does not exist");
                $this->loadEnglishFallback();
            }
        } catch (Exception $e) {
            error_log("Error loading language file: " . $e->getMessage());
            // Set empty translations array to avoid further errors
            $this->translations = [];
        }
    }
    
    /**
     * Load English translations as fallback
     * 
     * @return void
     */
    private function loadEnglishFallback() {
        if ($this->lang !== 'en') {
            $langFile = dirname(__DIR__) . '/lang/en.php';
            if (file_exists($langFile)) {
                $translations = include($langFile);
                if (is_array($translations)) {
                    $this->translations = $translations;
                } else {
                    $this->translations = [];  // Empty array as last resort
                }
            }
        }
    }
    
    /**
     * Get translation for a key
     * 
     * @param string $key Translation key
     * @param array $params Parameters for replacement
     * @return string
     */
    public function get($key, $params = []) {
        // Get translation, or return the key if not found
        $translation = isset($this->translations[$key]) ? $this->translations[$key] : $key;
        
        // Replace parameters
        if (!empty($params) && is_array($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace('{' . $param . '}', $value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Get all supported languages
     * 
     * @return array
     */
    public function getSupportedLanguages() {
        return [
            'en' => 'English',
            'th' => 'ไทย'
        ];
    }
    
    /**
     * Get language name from code
     * 
     * @param string $code Language code
     * @return string
     */
    public function getLanguageName($code) {
        $languages = $this->getSupportedLanguages();
        return isset($languages[$code]) ? $languages[$code] : 'Unknown';
    }
    
    /**
     * Check if a language is supported
     * 
     * @param string $code Language code
     * @return bool
     */
    public function isSupported($code) {
        return isset($this->getSupportedLanguages()[$code]);
    }
}

/**
 * Helper function to translate text
 * 
 * @param string $key Translation key
 * @param array $params Parameters for replacement
 * @return string
 */
function __($key, $params = []) {
    if (!$key) return '';
    
    try {
        return Language::getInstance()->get($key, $params);
    } catch (Exception $e) {
        error_log("Translation error: " . $e->getMessage());
        return $key; // Return the key as fallback
    }
}
?>