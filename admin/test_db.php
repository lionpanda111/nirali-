<?php
// test_db.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

echo "<h2>Testing Database Connection</h2>";

try {
    // Test database connection
    $pdo = getDBConnection();
    if ($pdo) {
        echo "<p style='color:green'>✅ Database connection successful!</p>";
        
        // Check if videos table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Videos table exists!</p>";
        } else {
            echo "<p style='color:orange'>⚠️ Videos table does not exist. Creating it now...</p>";
            
            // Create the videos table
            $sql = file_get_contents(__DIR__ . '/create_videos_table.sql');
            $pdo->exec($sql);
            
            echo "<p style='color:green'>✅ Videos table created successfully!</p>";
            
            // Verify the table was created
            $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:green'>✅ Verification: Videos table now exists!</p>";
            } else {
                echo "<p style='color:red'>❌ Failed to create videos table. Please check database permissions.</p>";
            }
        }
        
        // Check uploads directory permissions
        $uploadsDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
            echo "<p style='color:orange'>⚠️ Created uploads directory.</p>";
        }
        
        if (!is_writable($uploadsDir)) {
            echo "<p style='color:red'>❌ Uploads directory is not writable. Please set permissions to 755 or 777.</p>";
        } else {
            echo "<p style='color:green'>✅ Uploads directory is writable.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Could not connect to the database. Check your config.php settings.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Display current PHP version
echo "<p>PHP Version: " . phpversion() . "</p>";

// Display database configuration (hiding password for security)
echo "<h3>Database Configuration:</h3>";
echo "<ul>";
echo "<li>DB_HOST: " . DB_HOST . "</li>";
echo "<li>DB_NAME: " . DB_NAME . "</li>";
echo "<li>DB_USER: " . DB_USER . "</li>";
echo "<li>DB_PASS: " . (defined('DB_PASS') ? '*****' : 'Not set') . "</li>";
echo "</ul>";
?>
