<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Set content type to HTML with UTF-8
header('Content-Type: text/html; charset=utf-8');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/includes/config.php';

// Simple HTML template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - Nirali Makeup Studio</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0; border-radius: 4px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .container { max-width: 1000px; margin: 0 auto; }
        h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h3 { color: #444; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Connection Test</h1>
        <p>Testing connection to: <strong><?php echo DB_HOST; ?></strong></p>
        
        <?php
try {
    // Test database connection using our config
    $pdo = getDBConnection();
    
    if ($pdo) {
        echo "<div class='success'>✓ Successfully connected to the database server!</div>";
        
        // Get database information
        $stmt = $pdo->query("SELECT DATABASE() as db_name, USER() as db_user, VERSION() as db_version");
        $dbInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Connection Details:</h3>";
        echo "<pre>";
        echo "Database Host: " . DB_HOST . "\n";
        echo "Database Name: " . ($dbInfo['db_name'] ?? 'Not selected') . "\n";
        echo "Database User: " . ($dbInfo['db_user'] ?? 'Unknown') . "\n";
        echo "MySQL Version: " . ($dbInfo['db_version'] ?? 'Unknown') . "\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "</pre>";
        
        // List all tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<div class='success'>✓ Found " . count($tables) . " tables in the database</div>";
            echo "<h3>Database Tables:</h3>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table";
                
                // Show row count for each table
                try {
                    $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
                    echo " ($count records)";
                } catch (Exception $e) {
                    echo " (unable to count records)";
                }
                
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<div class='warning'>⚠ No tables found in the database. You'll need to import your database schema.</div>";
        }
    } else {
        echo "<div class='error'>✗ Failed to establish database connection.</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Connection Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Show configuration being used
    echo "<h3>Configuration Used:</h3>";
    echo "<pre>";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "SITE_URL: " . SITE_URL . "\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "OS: " . PHP_OS . "\n";
    echo "</pre>";
    
    // Show additional debug info
    echo "<h3>Debug Information:</h3>";
    echo "<pre>";
    print_r([
        '$_SERVER' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'Not set',
            'SERVER_ADDR' => $_SERVER['SERVER_ADDR'] ?? 'Not set',
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Not set'
        ]
    ]);
    echo "</pre>";
    
    // Show file paths
    echo "<h3>File Paths:</h3>";
    echo "<pre>";
    echo "Current file: " . __FILE__ . "\n";
    echo "Config file: " . realpath(__DIR__ . '/includes/config.php') . "\n";
    echo "Production config: " . (file_exists(__DIR__ . '/includes/config.production.php') ? 'Exists' : 'Not found') . "\n";
    echo "Working directory: " . getcwd() . "\n";
    echo "Include path: " . get_include_path() . "\n";
    echo "</pre>";
    
    // Show troubleshooting tips based on common errors
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<div class='warning'>";
        echo "<h4>🔑 Authentication Failed</h4>";
        echo "<p>Please check your database username and password in <code>includes/config.production.php</code></p>";
        echo "</div>";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<div class='warning'>";
        echo "<h4>📂 Database Not Found</h4>";
        echo "<p>The database <code>" . DB_NAME . "</code> does not exist. Please create it using your hosting control panel or run:</p>";
        echo "<pre>CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
        echo "</div>";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<div class='warning'>";
        echo "<h4>🔌 Connection Refused</h4>";
        echo "<p>Could not connect to the database server. Please check:</p>";
        echo "<ul>";
        echo "<li>Is the database server running?</li>";
        echo "<li>Is the hostname <code>" . DB_HOST . "</code> correct?</li>";
        echo "<li>Is the port open in your firewall?</li>";
        echo "<li>If using a remote database, is remote access enabled?</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "</div>"; // Close error div
}

// Show any output that was buffered
$buffer = ob_get_clean();
if (!empty($buffer)) {
    echo "<h3>Additional Output:</h3>";
    echo "<pre>" . htmlspecialchars($buffer) . "</pre>";
}
?>
    </div><!-- /.container -->
    
    <footer style="margin-top: 30px; padding: 20px 0; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 0.9em;">
        <p>Nirali Makeup Studio - Database Test | <?php echo date('Y-m-d H:i:s'); ?></p>
    </footer>
</body>
</html>    // Common solutions
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL server is running in XAMPP control panel</li>";
    echo "<li>Check if the username and password in db.php are correct</li>";
    echo "<li>Verify the database name in db.php matches your database</li>";
    echo "<li>Check if MySQL is running on the default port (3306)</li>";
    echo "</ol>";
}

echo "<p><a href='service-details.php?slug=engagement-makeup'>Try service details page again</a></p>";
?>
