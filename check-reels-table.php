<?php
require_once 'includes/config.php';

try {
    $pdo = getDBConnection();
    
    echo "<h1>Checking Reels Table Structure</h1>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'nirali_reels'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ nirali_reels table exists.</p>";
        
        // Show table structure
        echo "<h3>Table Structure:</h3>";
        $structure = $pdo->query("DESCRIBE nirali_reels")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ nirali_reels table does not exist.</p>";
        
        // Create the table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS `nirali_reels` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `caption` text,
            `video_path` varchar(500) DEFAULT NULL,
            `thumbnail_path` varchar(500) DEFAULT NULL,
            `instagram_url` varchar(500) DEFAULT NULL,
            `display_order` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Created nirali_reels table.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
