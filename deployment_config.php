<?php
/**
 * Deployment Configuration
 * This file handles different environment settings
 */

// Detect environment
function getEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'local';
    } elseif (strpos($host, 'infinityfreeapp.com') !== false) {
        return 'infinityfree';
    } elseif (strpos($host, '000webhostapp.com') !== false) {
        return '000webhost';
    } elseif (strpos($host, 'herokuapp.com') !== false) {
        return 'heroku';
    } else {
        return 'production';
    }
}

// Environment-specific configurations
$environment = getEnvironment();

switch ($environment) {
    case 'local':
        // Local XAMPP settings
        define('BASE_URL', 'http://localhost/MY_PROJECT');
        define('UPLOAD_PATH', __DIR__ . '/uploads/');
        define('EMAIL_HOST', 'localhost');
        define('EMAIL_PORT', 587);
        define('EMAIL_USERNAME', 'your-email@gmail.com');
        define('EMAIL_PASSWORD', 'your-app-password');
        break;
        
    case 'infinityfree':
        // InfinityFree settings
        define('BASE_URL', 'https://your-domain.infinityfreeapp.com');
        define('UPLOAD_PATH', __DIR__ . '/uploads/');
        define('EMAIL_HOST', 'smtp.gmail.com');
        define('EMAIL_PORT', 587);
        define('EMAIL_USERNAME', 'your-email@gmail.com');
        define('EMAIL_PASSWORD', 'your-app-password');
        break;
        
    case '000webhost':
        // 000webhost settings
        define('BASE_URL', 'https://your-domain.000webhostapp.com');
        define('UPLOAD_PATH', __DIR__ . '/uploads/');
        define('EMAIL_HOST', 'smtp.gmail.com');
        define('EMAIL_PORT', 587);
        define('EMAIL_USERNAME', 'your-email@gmail.com');
        define('EMAIL_PASSWORD', 'your-app-password');
        break;
        
    default:
        // Production settings
        define('BASE_URL', 'https://your-domain.com');
        define('UPLOAD_PATH', __DIR__ . '/uploads/');
        define('EMAIL_HOST', 'smtp.gmail.com');
        define('EMAIL_PORT', 587);
        define('EMAIL_USERNAME', 'your-email@gmail.com');
        define('EMAIL_PASSWORD', 'your-app-password');
        break;
}

// Common settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('TIMEZONE', 'Asia/Kolkata');

// Error reporting
if ($environment === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
