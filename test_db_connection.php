<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config
require_once 'includes/config.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    $pdo = getDBConnection();
    if ($pdo === null) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "<h2 style='color: green;'>✅ Database Connection: Success!</h2>";
    
    // Check if contact_messages table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Contact Messages Table: Exists</h3>";
        
        // Show table structure
        echo "<h4>Table Structure:</h4>";
        $columns = $pdo->query("DESCRIBE contact_messages")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3 style='color: red;'>❌ Contact Messages Table: Does not exist</h3>";
        echo "<p>Click the button below to create the table:</p>";
        echo "<a href='admin/check_contact_table.php' class='btn btn-primary'>Create Contact Messages Table</a>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Show connection details (without password)
    echo "<h3>Connection Details:</h3>";
    echo "<pre>DB_HOST: " . DB_HOST . "
DB_NAME: " . DB_NAME . "
DB_USER: " . DB_USER . "
</pre>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    table { border-collapse: collapse; margin: 15px 0; }
    th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    .btn { 
        display: inline-block;
        padding: 10px 15px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin: 10px 0;
    }
    .btn:hover { background-color: #45a049; }
</style>
