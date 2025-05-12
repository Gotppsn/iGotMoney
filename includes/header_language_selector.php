<?php
// Include language system if not already included
if (!class_exists('Language')) {
    require_once __DIR__ . '/language.php';
}

// Get language instance
$user_language = 'en';

// Initialize user settings to get language preference
if (isset($_SESSION['user_id'])) {
    require_once 'models/UserSettings.php';
    $userSettings = new UserSettings();
    if ($userSettings->getSettings($_SESSION['user_id'])) {
        $user_language = $userSettings->language;
    }
}

$language = Language::getInstance($user_language);

// Get current language and available languages
$current_language = $language->getCurrentLanguage();
$supported_languages = $language->getSupportedLanguages();

// Get current URL for language switch
$current_url = $_SERVER['REQUEST_URI'];
// Remove any existing 'lang' parameter
$current_url = preg_replace('/([&?])lang=[^&]+(&|$)/', '$1', $current_url);
$current_url = rtrim($current_url, '&?');
$separator = (strpos($current_url, '?') === false) ? '?' : '&';
?>