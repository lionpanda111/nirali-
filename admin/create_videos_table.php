<?php
// Include the database configuration and functions
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Create database connection
try {
    $pdo = getDBConnection();
    
    // SQL to create table
    $sql = "CREATE TABLE IF NOT EXISTS `videos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text,
        `video_url` varchar(255) NOT NULL,
        `thumbnail_url` varchar(255) DEFAULT NULL,
        `display_order` int(11) DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    // Execute the query
    $pdo->exec($sql);
    
    echo "Table 'videos' created successfully or already exists.";
    
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
