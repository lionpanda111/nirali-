<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'makeup_studio';

echo "<h1>Database Connection Test</h1>";

try {
    // Try to connect without selecting a database first
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "<p style='color: green;'>✅ Successfully connected to MySQL server.</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Database '$db_name' exists.</p>";
        
        // Select the database
        $pdo->exec("USE `$db_name`");
        
        // Check for required tables
        $tables = ['courses', 'instagram_reels'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ Table '$table' exists.</p>";
                
                // Show table structure
                echo "<h3>Structure of '$table':</h3>";
                $structure = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
                echo "<pre>";
                print_r($structure);
                echo "</pre>";
                
                // Show row count
                $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch();
                echo "<p>Rows in '$table': " . $count['count'] . "</p>";
                
            } else {
                echo "<p style='color: orange;'>⚠️ Table '$table' does NOT exist.</p>";
                $missing_tables[] = $table;
            }
        }
        
        // If tables are missing, provide SQL to create them
        if (!empty($missing_tables)) {
            echo "<h3>Missing Tables</h3>";
            echo "<p>Run the following SQL to create missing tables:</p>";
            
            $sql = [];
            
            if (in_array('courses', $missing_tables)) {
                $sql[] = "CREATE TABLE IF NOT EXISTS `courses` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) NOT NULL,
                    `slug` varchar(255) NOT NULL,
                    `description` text,
                    `short_description` varchar(500) DEFAULT NULL,
                    `duration` varchar(100) DEFAULT NULL,
                    `price` decimal(10,2) DEFAULT '0.00',
                    `image` varchar(255) DEFAULT NULL,
                    `is_featured` tinyint(1) DEFAULT '0',
                    `display_order` int(11) DEFAULT '0',
                    `status` tinyint(1) DEFAULT '1',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `slug` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            }
            
            if (in_array('instagram_reels', $missing_tables)) {
                $sql[] = "CREATE TABLE IF NOT EXISTS `instagram_reels` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `video_url` varchar(500) NOT NULL,
                    `thumbnail_url` varchar(500) DEFAULT NULL,
                    `caption` text,
                    `is_active` tinyint(1) DEFAULT '1',
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            }
            
            echo "<pre>" . implode("\n\n", $sql) . "</pre>";
            
            // Execute SQL to create missing tables
            try {
                foreach ($sql as $query) {
                    $pdo->exec($query);
                }
                echo "<p style='color: green;'>✅ Successfully created missing tables.</p>";
                echo "<p><a href='check-db-connection.php'>Refresh to verify</a></p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Error creating tables: " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ Database '$db_name' does NOT exist.</p>";
        
        // Try to create the database
        try {
            $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p style='color: green;'>✅ Successfully created database '$db_name'.</p>";
            echo "<p><a href='check-db-connection.php'>Click here to continue setup</a></p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error creating database: " . $e->getMessage() . "</p>";
            echo "<p>Please create the database manually with the following command:</p>";
            echo "<pre>CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    
    // Provide troubleshooting steps
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL server is running.</li>";
    echo "<li>Check your database credentials in config.php</li>";
    echo "<li>Verify the database user has proper permissions</li>";
    echo "<li>Check if MySQL is configured to accept connections (check bind-address in my.ini/my.cnf)</li>";
    echo "</ol>";
}

echo "<h3>PHP Info:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Enabled' : 'Not Enabled') . "</li>";
echo "<li>MySQLi: " . (extension_loaded('mysqli') ? 'Enabled' : 'Not Enabled') . "</li>";
echo "</ul>";
?>
