<?php
require_once 'includes/config.php';

try {
    $pdo = getDBConnection();
    
    echo "<h1>Adding status column to courses table...</h1>";
    
    // Add status column if it doesn't exist
    $query = "ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `status` TINYINT(1) DEFAULT 1 AFTER `display_order`";
    
    $pdo->exec($query);
    
    echo "<p style='color: green;'>✅ Successfully added 'status' column to courses table.</p>";
    
    // Show final table structure
    $stmt = $pdo->query("SHOW COLUMNS FROM `courses`");
    echo "<h2>Current Table Structure:</h2>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Database update complete!</p>";
    echo "<p>You can now <a href='admin/courses.php'>go to the courses page</a>.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
