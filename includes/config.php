<?php
/**
 * Configuration Loader
 * Loads the appropriate configuration based on environment
 */

// Determine if we're in development or production
$isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);

if ($isLocal) {
    // Local Development Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'makeup_studio');
    
    define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/Nirali/makeup-studio');
    
    // Enable error reporting for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production Configuration - Load from separate file
    $productionConfig = __DIR__ . '/config.production.php';
    if (file_exists($productionConfig)) {
        require_once $productionConfig;
    } else {
        // Fallback to environment variables if config file doesn't exist
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'makeup_studio');
        define('SITE_URL', 'https://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com'));
    }
}

// Common Configuration
define('SITE_NAME', 'Nirali Makeup Studio');
define('INSTAGRAM_USERNAME', 'niralimakeupstudio');

define('MAIL_FROM', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'niralimakeup.com'));
define('ADMIN_EMAIL', 'admin@' . ($_SERVER['HTTP_HOST'] ?? 'niralimakeup.com'));

/**
 * Create database connection with improved error handling
 * 
 * @return PDO Database connection
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5, // 5 second connection timeout
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set the timezone to match the application
            $pdo->exec("SET time_zone = '+05:30';");
            
        } catch (PDOException $e) {
            $errorMessage = sprintf(
                "Database Connection Failed!\n" .
                "Error: %s\n" .
                "DSN: mysql:host=%s;dbname=%s\n" .
                "PHP Version: %s\n" .
                "OS: %s\n" .
                "Time: %s",
                $e->getMessage(),
                DB_HOST,
                DB_NAME,
                PHP_VERSION,
                PHP_OS,
                date('Y-m-d H:i:s')
            );
            
            // Log detailed error
            error_log($errorMessage);
            
            // In development, show detailed error
            if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
                throw new PDOException("Could not connect to the database. Check your configuration.\n" . $errorMessage);
            }
            
            // In production, show user-friendly message
            throw new PDOException('Unable to connect to the database. Please try again later.');
        }
    }
    
    return $pdo;
}

/**
 * Helper function to redirect to a specific page
 */
function redirect($page) {
    header("Location: " . SITE_URL . "/" . ltrim($page, '/'));
    exit();
}




// Session is started in header.php

// Set default timezone
date_default_timezone_set('Asia/Kolkata');
?>
