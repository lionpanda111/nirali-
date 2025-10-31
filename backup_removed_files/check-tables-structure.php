<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'includes/db.php';

// Function to display table structure
function displayTableStructure($pdo, $tableName) {
    try {
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table: $tableName</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error describing table $tableName: " . $e->getMessage() . "</p>";
    }
}

// Check services table structure
displayTableStructure($pdo, 'services');

// Check service_categories table structure
displayTableStructure($pdo, 'service_categories');

// Check service_category_mapping table if it exists
try {
    $tableExists = $pdo->query("SHOW TABLES LIKE 'service_category_mapping'")->rowCount() > 0;
    if ($tableExists) {
        displayTableStructure($pdo, 'service_category_mapping');
    } else {
        echo "<p>Table 'service_category_mapping' does not exist.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Error checking for service_category_mapping table: " . $e->getMessage() . "</p>";
}

// Show SQL to fix the issue
echo "<h3>Possible Solutions:</h3>";
echo "<h4>Option 1: If you have a many-to-many relationship (recommended):</h4>";
echo "<pre>
-- Create a mapping table if it doesn't exist
CREATE TABLE IF NOT EXISTS `service_category_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_category` (`service_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Then modify the query in service-details.php to join with this table
</pre>";

echo "<h4>Option 2: If you want to add category_id to services table:</h4>";
echo "<pre>
ALTER TABLE `services` 
ADD COLUMN `category_id` INT NULL AFTER `id`,
ADD INDEX `category_id` (`category_id`);

-- Then update your services with appropriate category IDs
</pre>";

// Show current services and their categories (if any)
try {
    echo "<h3>Current Services:</h3>";
    $services = $pdo->query("SELECT * FROM services LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($services, true) . "</pre>";
} catch (Exception $e) {
    echo "<p>Error fetching services: " . $e->getMessage() . "</p>";
}
?>
