<?php
// Database connection settings
// Check if constants are already defined to prevent redefinition warnings
if (!defined('DB_SERVER')) {
    // Use environment variables for deployment flexibility
    $db_host = $_ENV['DB_HOST'] ?? 'localhost';
    $db_username = $_ENV['DB_USERNAME'] ?? 'root';
    $db_password = $_ENV['DB_PASSWORD'] ?? '';
    $db_name = $_ENV['DB_NAME'] ?? 'apsit_database';

    // Override for specific environments
    if (isset($_SERVER['HTTP_HOST'])) {
        if ($_SERVER['HTTP_HOST'] === 'your-domain.infinityfreeapp.com') {
            // Production settings for InfinityFree
            $db_host = 'sql.infinityfree.com';
            $db_username = 'your_infinityfree_username';
            $db_password = 'your_infinityfree_password';
            $db_name = 'your_database_name';
        } elseif ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
            // Local development settings
            $db_host = 'localhost';
            $db_username = 'root';
            $db_password = '';
            $db_name = 'apsit_database';
        }
    }

    // Define constants
    define('DB_SERVER', $db_host);
    define('DB_USERNAME', $db_username);
    define('DB_PASSWORD', $db_password);
    define('DB_NAME', $db_name);
}
?>