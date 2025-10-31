<?php
require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = getDBConnection();
    echo "<h2>Database Connection Test</h2>";
    echo "<p>Successfully connected to database: " . DB_NAME . " on " . DB_HOST . "</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $info = $stmt->fetch();
    
    echo "<h3>Database Information:</h3>";
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    // Test courses table
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'courses'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Courses table exists</p>";
            
            // Show courses count
            $count = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch();
            echo "<p>Total courses: " . $count['count'] . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Courses table does not exist. Running migration...</p>";
            // Try to create the table
            try {
                require_once 'migrations/20241015_create_courses_table.php';
                echo "<p style='color: green;'>✓ Courses table created successfully</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error creating courses table: " . $e->getMessage() . "</p>";
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error checking courses table: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Database Connection Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h3>Configuration Details:</h3>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Database: " . DB_NAME . "</li>";
    echo "<li>User: " . DB_USER . "</li>";
    echo "<li>Password: " . (defined('DB_PASS') && DB_PASS ? '*****' : '') . "</li>";
    echo "</ul>";
    
    echo "<h3>PHP Info:</h3>";
    echo "<ul>";
    echo "<li>PHP Version: " . PHP_VERSION . "</li>";
    echo "<li>OS: " . PHP_OS . "</li>";
    echo "<li>PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "</li>";
    echo "<li>PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
}

echo "<p><a href='courses.php'>Go to Courses Management</a></p>";
?>
