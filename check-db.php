<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'makeup_studio';
$db_user = 'root';
$db_pass = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Connection Successful!</h2>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'instagram_reels'");
    if($stmt->rowCount() > 0) {
        echo "<h3>Table 'instagram_reels' exists.</h3>";
        
        // Get table structure
        echo "<h4>Table Structure:</h4>";
        $structure = $pdo->query("DESCRIBE instagram_reels")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Get record count
        $count = $pdo->query("SELECT COUNT(*) as count FROM instagram_reels")->fetch();
        echo "<p>Number of records: " . $count['count'] . "</p>";
        
        // Get sample data
        $data = $pdo->query("SELECT * FROM instagram_reels LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>Sample Data:</h4>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "<h3 style='color: red;'>Table 'instagram_reels' does NOT exist.</h3>";
        
        // Create table if it doesn't exist
        echo "<h4>Creating table 'instagram_reels'...</h4>";
        $sql = "CREATE TABLE IF NOT EXISTS `instagram_reels` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `video_url` varchar(500) NOT NULL,
            `thumbnail_url` varchar(500) DEFAULT NULL,
            `caption` text,
            `is_active` tinyint(1) DEFAULT '1',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        echo "<p>Table 'instagram_reels' created successfully.</p>";
        
        // Add sample data
        $sample_data = [
            [
                'video_url' => 'https://www.instagram.com/reel/CxYf8kZg1H2/',
                'thumbnail_url' => 'https://via.placeholder.com/300x500/f4f4f4/cccccc?text=Sample+Reel+1',
                'caption' => 'Sample Reel 1',
                'is_active' => 1
            ],
            [
                'video_url' => 'https://www.instagram.com/reel/CxYf8kZg1H3/',
                'thumbnail_url' => 'https://via.placeholder.com/300x500/f4f4f4/cccccc?text=Sample+Reel+2',
                'caption' => 'Sample Reel 2',
                'is_active' => 1
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO instagram_reels (video_url, thumbnail_url, caption, is_active) VALUES (:video_url, :thumbnail_url, :caption, :is_active)");
        
        foreach ($sample_data as $data) {
            $stmt->execute($data);
        }
        
        echo "<p>Added sample data to 'instagram_reels' table.</p>";
    }
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Try to create database if it doesn't exist
    if($e->getCode() == 1049) { // Database doesn't exist
        try {
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p>Database '$db_name' created successfully.</p>";
            echo "<p>Please refresh this page to continue setup.</p>";
        } catch(PDOException $ex) {
            die("<p>Failed to create database: " . $ex->getMessage() . "</p>");
        }
    }
}
?>
