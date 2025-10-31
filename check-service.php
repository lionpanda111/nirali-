<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'engagement-makeup';

try {
    echo "<h2>Checking Service: $slug</h2>";
    
    // Check if services table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'services'")->rowCount() > 0;
    
    if (!$tableExists) {
        die("<p style='color: red;'>The 'services' table does not exist. Please run the database setup first.</p>");
    }
    
    // Check if the service exists
    $stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch();
    
    if ($service) {
        echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3 style='color: #2e7d32;'>Service Found!</h3>";
        echo "<p><strong>ID:</strong> " . htmlspecialchars($service['id']) . "</p>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($service['title']) . "</p>";
        echo "<p><strong>Slug:</strong> " . htmlspecialchars($service['slug']) . "</p>";
        echo "<p><a href='service-details.php?slug=" . urlencode($service['slug']) . "' class='btn btn-primary'>View Service</a></p>";
        echo "</div>";
    } else {
        // List all available services
        echo "<p style='color: #d32f2f;'>No service found with slug: " . htmlspecialchars($slug) . "</p>";
        
        $allServices = $pdo->query("SELECT id, title, slug FROM services WHERE status = 1 LIMIT 10");
        if ($allServices->rowCount() > 0) {
            echo "<h3>Available Services:</h3>";
            echo "<ul>";
            while ($s = $allServices->fetch()) {
                echo "<li><a href='service-details.php?slug=" . urlencode($s['slug']) . "'>" . 
                     htmlspecialchars($s['title']) . " (" . htmlspecialchars($s['slug']) . ")</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No services found in the database. Please add some services first.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #c62828;'>Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

// Show database connection info (for debugging)
echo "<div style='margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px;'>";
echo "<h3>Database Connection Info</h3>";
echo "<p><strong>Database:</strong> " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "</p>";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<p><strong>Tables in database:</strong> " . implode(", ", $tables) . "</p>";
echo "</div>";

// Show PHP info link
echo "<p style='margin-top: 20px;'><a href='phpinfo.php' class='btn btn-info'>View PHP Info</a></p>";
?>
