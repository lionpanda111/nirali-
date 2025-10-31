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
        
        // Try to insert a test record
        try {
            $testData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'subject' => 'Test Message',
                'message' => 'This is a test message.',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Script'
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages 
                (name, email, phone, subject, message, ip_address, user_agent, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            if ($stmt->execute([
                $testData['name'],
                $testData['email'],
                $testData['phone'],
                $testData['subject'],
                $testData['message'],
                $testData['ip_address'],
                $testData['user_agent']
            ])) {
                $lastId = $pdo->lastInsertId();
                echo "<h3 style='color: green;'>✅ Test record inserted successfully! (ID: $lastId)</h3>";
                
                // Show the inserted record
                $testRecord = $pdo->query("SELECT * FROM contact_messages WHERE id = $lastId")->fetch(PDO::FETCH_ASSOC);
                echo "<h4>Inserted Record:</h4>";
                echo "<pre>";
                print_r($testRecord);
                echo "</pre>";
                
                // Clean up
                $pdo->exec("DELETE FROM contact_messages WHERE id = $lastId");
                echo "<p>Test record has been cleaned up.</p>";
            } else {
                echo "<h3 style='color: red;'>❌ Failed to insert test record</h3>";
                echo "<p>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
            }
            
        } catch (PDOException $e) {
            echo "<h3 style='color: red;'>❌ Error inserting test record:</h3>";
            echo "<pre>" . $e->getMessage() . "</pre>";
        }
        
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
