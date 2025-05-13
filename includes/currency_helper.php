<?php
/**
 * Get user settings from session or load from database
 * This helper function can be used throughout the application
 * 
 * @return UserSettings User settings object
 */
function getUserSettings() {
    static $userSettings = null;
    
    if ($userSettings === null) {
        require_once 'models/UserSettings.php';
        $userSettings = new UserSettings();
        
        if (isset($_SESSION['user_id'])) {
            $userSettings->getSettings($_SESSION['user_id']);
        }
    }
    
    return $userSettings;
}

/**
 * Format money with current user's currency settings
 * 
 * @param float $amount Amount to format
 * @param bool $includeSymbol Whether to include currency symbol
 * @return string Formatted amount
 */
function formatMoney($amount, $includeSymbol = true) {
    $settings = getUserSettings();
    return $settings->formatMoney($amount, $includeSymbol);
}

/**
 * Get current user's currency symbol
 * 
 * @return string Currency symbol
 */
function getCurrencySymbol() {
    $settings = getUserSettings();
    return $settings->getCurrencySymbol();
}

/**
 * Update common layout files to implement the currency formatter
 * - Add formatter.php to relevant includes
 * - Replace hardcoded currency symbols ($) with dynamic ones
 * 
 * This is a one-time function to help apply the currency changes
 * throughout the application
 * 
 * @return bool Success indicator
 */
function implementCurrencyChanges() {
    $baseDir = dirname(__DIR__);
    $changes = [];
    $processedFiles = 0;
    
    // Loop through view files to replace hardcoded $ with dynamic currency
    $viewDir = $baseDir . '/views';
    $viewFiles = scandir($viewDir);
    
    foreach ($viewFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $filePath = $viewDir . '/' . $file;
            $content = file_get_contents($filePath);
            
            // Replace dollar sign with currency function
            $replacedContent = preg_replace(
                '/<span class="[^"]*">\\$([\d,\.]+)<\/span>/',
                '<span class="$1"><?php echo getCurrencySymbol(); ?>$1</span>',
                $content
            );
            
            // Replace number_format with formatMoney
            $replacedContent = preg_replace(
                '/number_format\(([^,\)]+)(?:,\s*2)?\)/',
                'formatMoney($1)',
                $replacedContent
            );
            
            if ($content !== $replacedContent) {
                file_put_contents($filePath, $replacedContent);
                $changes[] = 'Updated ' . $file;
                $processedFiles++;
            }
        }
    }
    
    // Update controller files
    $controllerDir = $baseDir . '/controllers';
    $controllerFiles = scandir($controllerDir);
    
    foreach ($controllerFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'BaseController.php') {
            $filePath = $controllerDir . '/' . $file;
            $content = file_get_contents($filePath);
            
            // Add require_once for formatter helper if not already included
            if (strpos($content, 'formatter.php') === false && strpos($content, 'BaseController') === false) {
                $content = preg_replace(
                    '/(require_once[^;]+;\s+)/m',
                    "$1require_once 'includes/formatter.php';\n",
                    $content,
                    1
                );
                
                file_put_contents($filePath, $content);
                $changes[] = 'Added formatter include to ' . $file;
                $processedFiles++;
            }
        }
    }
    
    return $processedFiles > 0;
}
?>