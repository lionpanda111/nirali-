<?php
/**
 * Production Database Configuration for Hostinger
 * This file should be kept private and not committed to version control
 */

// Database Configuration
define('DB_HOST', 'your_hostinger_db_host');  // Usually something like 'mysql.hostinger.com' or your domain
define('DB_USER', 'your_hostinger_db_username');
define('DB_PASS', 'your_hostinger_db_password');
define('DB_NAME', 'your_hostinger_db_name');

// Site Configuration
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'Nirali Makeup Studio');
define('INSTAGRAM_USERNAME', 'niralimakeupstudio');

// Email Configuration
define('MAIL_FROM', 'noreply@yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// Error Reporting - Disable display errors in production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Ensure logs directory exists
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}
