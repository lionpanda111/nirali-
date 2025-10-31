<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config
require_once 'includes/config.php';

// Test database connection
try {
    $pdo = getDBConnection();
    if ($pdo === null) {
        throw new Exception("Failed to connect to database");
    }

    echo "<h2>Database Connection: Success!</h2>";

    // Check if service_categories table exists
    $tables = ['service_categories', 'service_category_mapping'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<h3>$table Table: Exists</h3>";
            
            // Show table structure
            echo "<h4>Table Structure:</h4>";
            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($columns);
            echo "</pre>";
            
            // Show some sample data
            echo "<h4>Sample Data (first 5 records):</h4>";
            $data = $pdo->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "<h3>$table Table: Does not exist</h3>";
        }
    }

} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Show connection details (without password)
    echo "<h3>Connection Details:</h3>";
    echo "<pre>DB_HOST: " . DB_HOST . "\nDB_NAME: " . DB_NAME . "\nDB_USER: " . DB_USER . "\n</pre>";
    
    // Show any PDO error if available
    if (isset($pdo)) {
        echo "<h3>PDO Error:</h3>";
        echo "<pre>";
        print_r($pdo->errorInfo());
        echo "</pre>";
    }
}
